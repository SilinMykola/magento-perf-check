<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Runner;

use Composer\InstalledVersions;
use JsonException;
use Nette\Neon\Entity;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Silin\MagentoPerfCheck\Cli\UsageException;
use Silin\MagentoPerfCheck\Process\Subprocess;
use Silin\MagentoPerfCheck\Project\ProjectIndexBuilder;
use Silin\MagentoPerfCheck\Project\TestCode;
use Silin\MagentoPerfCheck\Project\WorkDirectory;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Report\Notice;
use Silin\MagentoPerfCheck\Report\NoticePrefix;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;

/**
 * Runs PHPStan with this package's rules as a subprocess and turns its JSON output
 * into findings. Nothing is written into the analyzed project: the generated config
 * and PHPStan's result cache live in the system temp directory.
 */
final class PhpStanRunner
{
    private const PACKAGE_ROOT = __DIR__ . '/../../..';

    private const MAX_PASSES = 5;
    private const CRASH_FILES = 'crash-files.json';

    /** The switches of bitexpert/phpstan-magento that turn its own rules off. */
    private const BITEXPERT_SWITCHES = [
        'magento' => ['checkCollectionViaFactory', 'checkServiceContracts', 'checkResourceModelsUsedDirectly'],
        'extdn' => ['setTemplateDisallowedForBlockClasses'],
    ];

    /**
     * PHPStan loads its --autoload-file before it asks phpstan/extension-installer which extensions to
     * include, and passes the option on to its workers. With this empty list, the installer adds none:
     * no file is included twice and no other extension of the project runs.
     */
    private const EMPTY_EXTENSION_INSTALLER = <<<'PHP'
        <?php

        namespace PHPStan\ExtensionInstaller;

        final class GeneratedConfig
        {
            public const EXTENSIONS = [];
            public const NOT_INSTALLED = [];
            public const PHPSTAN_VERSION_CONSTRAINT = null;
        }

        PHP;

    /**
     * @param ?string $phpstanBinary path to the phpstan launcher; found through Composer when null
     * @param ?string $bitExpertRoot where bitexpert/phpstan-magento is installed; found through Composer when null
     */
    public function __construct(
        private readonly RuleRegistry $registry,
        private readonly ?string $phpstanBinary = null,
        private readonly ?string $bitExpertRoot = null,
    ) {
    }

    /**
     * @param list<string> $paths relative to the project root
     * @param list<string> $excludedDirs absolute directories left out of the analysis, such as modules disabled in app/etc/config.php
     */
    public function run(string $projectRoot, array $paths, string $memoryLimit, bool $includeTests = false, array $excludedDirs = []): PhpStanResult
    {
        $absolutePaths = array_values(array_filter(
            array_map(static fn (string $p): string => $projectRoot . '/' . $p, $paths),
            static fn (string $p): bool => is_dir($p) || (is_file($p) && (str_ends_with($p, '.php') || str_ends_with($p, '.phtml'))),
        ));
        if ($absolutePaths === []) {
            return new PhpStanResult([], [], false);
        }

        $workDir = WorkDirectory::forProject($projectRoot);
        if (!is_dir($workDir) && !mkdir($workDir, 0777, true) && !is_dir($workDir)) {
            throw new UsageException('Cannot create temp directory ' . $workDir);
        }
        $indexFingerprint = (new ProjectIndexBuilder())->fingerprint($projectRoot);
        $notices = $this->generatedCodeNotices($projectRoot);

        // A file PHPStan cannot parse or reflect makes it stop reporting rule errors for every other
        // file ("Result is incomplete because of severe errors"). Such files are excluded
        // and the analysis runs again; their parse errors stay in the result as notices.
        $testPatterns = [];
        if (!$includeTests) {
            $testPaths = array_filter($paths, TestCode::contains(...));
            foreach ($paths as $path) {
                // A test path given next to its parent is analyzed, so the parent cannot exclude test code.
                $coversTestPath = array_filter($testPaths, static fn (string $test): bool => str_starts_with($test, rtrim($path, '/') . '/') || $path === '.');
                if (!TestCode::contains($path) && $coversTestPath === [] && is_dir($projectRoot . '/' . $path)) {
                    array_push($testPatterns, ...TestCode::excludePatterns($projectRoot . '/' . $path));
                }
            }
        }

        // A pattern, not the directory: PHPStan 1.12 would also leave out a sibling whose name starts the same.
        $excludedPatterns = array_map(static fn (string $dir): string => $dir . '/*', $excludedDirs);
        $parseNotices = [];
        // Files found to kill PHPStan in an earlier run with the same memory limit stay out while
        // unchanged, so this run starts with the configuration that run ended with and PHPStan's
        // result cache stays valid. The notice names only those in the analyzed paths.
        $known = $this->crashFiles($workDir);
        $excluded = [];
        foreach ($known as $file => [$hash, $reason, $limit]) {
            if ($limit === $memoryLimit && is_file($file) && md5_file($file) === $hash) {
                $excluded[] = $file;
                if ($this->isAnalyzed($file, $absolutePaths, [...$excludedPatterns, ...$testPatterns])) {
                    $parseNotices[] = $this->crashNotice($file, $reason, $projectRoot);
                }
            }
        }
        $autoloadFile = $this->writeConfig($workDir, self::EMPTY_EXTENSION_INSTALLER, '.php');
        $crashed = null;
        for ($pass = 1; $pass <= self::MAX_PASSES; ++$pass) {
            $exclusions = [...$excludedPatterns, ...$excluded];
            $config = $this->writeConfig(
                $workDir,
                $this->config($projectRoot, $absolutePaths, $testPatterns, $exclusions, $workDir . '/cache', $indexFingerprint),
            );
            $result = $this->analyse($config, $autoloadFile, $projectRoot, $memoryLimit);
            $unparsable = array_diff(array_keys($result['unparsable']), $excluded);
            if ($unparsable === []) {
                $complete = new PhpStanResult($result['findings'], [...$notices, ...$parseNotices, ...$result['notices']], $result['filesFound']);
                // A worker that dies, as on an obfuscated file that exhausts the memory limit, takes the
                // findings of every file with it. One process with --debug names each file before it
                // analyzes it, so the last one named is where it died. As that process holds the memory
                // of all files before it, the file is left out only when it also kills an analysis of
                // its own; then the rest is analyzed again. A file with an internal error would stop the
                // single process first, so those are left out of it.
                $crash = null;
                if ($this->workerDied($result)) {
                    $debugConfig = $this->writeConfig($workDir, $this->config(
                        $projectRoot,
                        $absolutePaths,
                        $testPatterns,
                        [...$exclusions, ...$this->internalErrorFiles($result)],
                        $workDir . '/cache',
                        $indexFingerprint,
                    ));
                    $crash = $this->crashingFile($debugConfig, $autoloadFile, $projectRoot, $memoryLimit);
                }
                if ($crash === null || in_array($crash[0], $excluded, true)
                    || !$this->crashesAlone($crash[0], $projectRoot, $testPatterns, $exclusions, $workDir, $indexFingerprint, $autoloadFile, $memoryLimit)) {
                    return $complete;
                }
                $crashed = $complete;
                $excluded[] = $crash[0];
                $parseNotices[] = $this->crashNotice($crash[0], $crash[1], $projectRoot);
                $known[$crash[0]] = [(string) md5_file($crash[0]), $crash[1], $memoryLimit];
                $this->saveCrashFiles($workDir, $known);
                continue;
            }
            foreach ($unparsable as $file) {
                $excluded[] = $file;
                array_push($parseNotices, ...$result['unparsable'][$file]);
            }
        }

        // Out of passes while files kept killing workers: the incomplete result says so.
        if ($crashed !== null) {
            return $crashed;
        }

        throw new UsageException(sprintf(
            'PHPStan kept reporting unparsable files after %d passes; last excluded: %s',
            self::MAX_PASSES,
            implode(', ', array_slice($excluded, -5)),
        ));
    }

    /**
     * Named after its content, so a run with other paths at the same time cannot overwrite
     * it, while a repeated run reuses the same file and keeps PHPStan's result cache valid.
     */
    private function writeConfig(string $workDir, string $config, string $extension = '.neon'): string
    {
        $file = $workDir . '/phpstan-' . md5($config) . $extension;
        if (!is_file($file)) {
            $temporary = $file . '.' . getmypid();
            if (file_put_contents($temporary, $config) === false || !rename($temporary, $file)) {
                throw new UsageException('Cannot write ' . $file);
            }
        }

        return $file;
    }

    /**
     * @return list<Notice>
     */
    private function generatedCodeNotices(string $projectRoot): array
    {
        $bitExpert = $this->bitExpertRoot();
        $gap = $bitExpert !== null ? $this->bitExpertGap($bitExpert) : null;
        if ($bitExpert !== null && $gap === null) {
            return [];
        }
        $notices = $gap === null ? [] : [new Notice(
            'generated-code',
            sprintf(
                'bitexpert/phpstan-magento%s is not used: %s. Update it: 0.32.0 or later with PHPStan 1.12, 0.40.0 or later with PHPStan 2.',
                $this->bitExpertRoot === null && InstalledVersions::isInstalled('bitexpert/phpstan-magento')
                    ? ' ' . InstalledVersions::getPrettyVersion('bitexpert/phpstan-magento') : '',
                $gap,
            ),
        )];
        $generated = $projectRoot . '/generated/code';
        if (is_dir($generated) && array_diff(scandir($generated) ?: [], ['.', '..']) !== []) {
            return $notices;
        }

        return [...$notices, new Notice(
            'generated-code',
            'generated/code is missing or empty, so the types of proxies and extension attributes are unknown and calls on them are not checked. '
            . ($gap === null ? 'Run bin/magento setup:di:compile, or install bitexpert/phpstan-magento in the tool.' : 'Run bin/magento setup:di:compile.'),
        )];
    }

    private function crashNotice(string $file, string $reason, string $projectRoot): Notice
    {
        return new Notice(
            Notice::SOURCE_PHPSTAN_CRASH,
            'PHPStan died on this file, so it was left out of the analysis: ' . $reason,
            $this->relative($file, $projectRoot),
        );
    }

    /**
     * Files an earlier run found to kill PHPStan, with the MD5 of their content then, the reason and the memory limit.
     *
     * @return array<string, array{string, string, string}>
     */
    private function crashFiles(string $workDir): array
    {
        $data = json_decode((string) @file_get_contents($workDir . '/' . self::CRASH_FILES), true);
        $files = [];
        foreach (is_array($data) ? $data : [] as $file => $entry) {
            if (is_string($file) && is_array($entry) && is_string($entry[0] ?? null) && is_string($entry[1] ?? null) && is_string($entry[2] ?? null)) {
                $files[$file] = [$entry[0], $entry[1], $entry[2]];
            }
        }

        return $files;
    }

    /**
     * Two runs on the same project at once may each drop the other's new entry; that file is then
     * searched for again.
     *
     * @param array<string, array{string, string, string}> $files
     */
    private function saveCrashFiles(string $workDir, array $files): void
    {
        $temporary = $workDir . '/' . self::CRASH_FILES . '.' . getmypid();
        if (@file_put_contents($temporary, json_encode($files, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)) !== false) {
            @rename($temporary, $workDir . '/' . self::CRASH_FILES);
        }
    }

    /**
     * @param list<string> $paths absolute
     * @param list<string> $excludedPatterns fnmatch() patterns
     */
    private function isAnalyzed(string $file, array $paths, array $excludedPatterns): bool
    {
        // --paths=. gives <root>/. and test patterns such as <root>/./Test/*.
        foreach ($excludedPatterns as $pattern) {
            if (fnmatch(str_replace('/./', '/', $pattern), $file)) {
                return false;
            }
        }
        foreach ($paths as $path) {
            $path = preg_replace('#/\.$#', '', $path) ?? $path;
            if ($file === $path || str_starts_with($file, rtrim($path, '/') . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Files named by an internal error of the run ("... while analysing file <path>").
     *
     * @param array{notices: list<Notice>} $result
     * @return list<string>
     */
    private function internalErrorFiles(array $result): array
    {
        $files = [];
        foreach ($result['notices'] as $notice) {
            if ($notice->source === Notice::SOURCE_ANALYSIS_FAILURE
                && preg_match('/ while analysing file (.+?\.(?:php|phtml))\b/', $notice->message, $m) === 1) {
                $files[] = $m[1];
            }
        }

        return $files;
    }

    /**
     * @param array{notices: list<Notice>} $result
     */
    private function workerDied(array $result): bool
    {
        foreach ($result['notices'] as $notice) {
            // A dead worker; not a timeout or an internal error, which a single process does not locate.
            if ($notice->source === Notice::SOURCE_ANALYSIS_FAILURE
                && (str_contains($notice->message, 'Child process error') || str_contains($notice->message, 'memory limit'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether an analysis of this file alone, with the same limit, also fails.
     *
     * @param list<string> $testPatterns
     * @param list<string> $exclusions
     */
    private function crashesAlone(string $file, string $projectRoot, array $testPatterns, array $exclusions, string $workDir, string $indexFingerprint, string $autoloadFile, string $memoryLimit): bool
    {
        $config = $this->writeConfig($workDir, $this->config($projectRoot, [$file], $testPatterns, $exclusions, $workDir . '/cache', $indexFingerprint));
        try {
            $result = $this->analyse($config, $autoloadFile, $projectRoot, $memoryLimit);
        } catch (UsageException) {
            return true;
        }

        return $this->workerDied($result);
    }

    /**
     * The file a single PHPStan process dies on, with the last line of its error output, or null
     * when it does not die.
     *
     * @return array{string, string}|null
     */
    private function crashingFile(string $config, string $autoloadFile, string $projectRoot, string $memoryLimit): ?array
    {
        [$exitCode, $stdout, $stderr] = Subprocess::run([
            PHP_BINARY,
            $this->phpstanBinary ?? $this->findPhpstan(),
            'analyse',
            '--debug',
            '--no-progress',
            '--no-interaction',
            '--error-format=json',
            '--memory-limit=' . $memoryLimit,
            '--configuration=' . $config,
            '--autoload-file=' . $autoloadFile,
        ], $projectRoot, 'PHPStan');
        if ($exitCode === 0 || $exitCode === 1) {
            return null;
        }
        $file = null;
        foreach (preg_split('/\R/', $stdout) ?: [] as $line) {
            if ($line !== '' && is_file($line)) {
                $file = $line;
            }
        }
        if ($file === null) {
            return null;
        }
        // The stack trace comes first; the line that says why is near the end.
        $reason = 'exit code ' . $exitCode;
        foreach (['crashed', 'Fatal error'] as $marker) {
            foreach (preg_split('/\R/', $stderr) ?: [] as $line) {
                if (str_contains($line, $marker)) {
                    $reason = trim($line);
                    break 2;
                }
            }
        }

        return [$file, $reason];
    }

    /**
     * @return array{findings: list<Finding>, notices: list<Notice>, unparsable: array<string, list<Notice>>, filesFound: bool}
     */
    private function analyse(string $config, string $autoloadFile, string $projectRoot, string $memoryLimit): array
    {
        $command = [
            PHP_BINARY,
            $this->phpstanBinary ?? $this->findPhpstan(),
            'analyse',
            '--no-progress',
            '--no-interaction',
            '--error-format=json',
            '--memory-limit=' . $memoryLimit,
            '--configuration=' . $config,
            '--autoload-file=' . $autoloadFile,
        ];
        [$exitCode, $stdout, $stderr] = Subprocess::run($command, $projectRoot, 'PHPStan');

        // 0: no errors, 1: errors found. Anything else is a crash (out of memory, fatal error).
        if ($exitCode !== 0 && $exitCode !== 1) {
            throw new UsageException(sprintf(
                "PHPStan failed with exit code %d.\n%s",
                $exitCode,
                trim($stderr !== '' ? $stderr : $stdout),
            ));
        }

        // The paths hold no PHP files at all (for example a theme directory).
        if (str_contains($stdout . $stderr, 'No files found to analyse')) {
            return ['findings' => [], 'notices' => [], 'unparsable' => [], 'filesFound' => false];
        }

        return $this->parse($stdout, $stderr, $projectRoot);
    }

    /**
     * @param list<string> $paths
     * @param list<string> $testPatterns test code, left out of symbol discovery too: a stub of a core class in a module's Test directory must not replace the real class for all other code
     * @param list<string> $unparsable files PHPStan cannot parse, and directories left out of the analysis
     */
    private function config(string $projectRoot, array $paths, array $testPatterns, array $unparsable, string $cacheDir, string $indexFingerprint): string
    {
        $includes = [];
        foreach (['extension.neon', 'extension-experimental.neon'] as $file) {
            $includes[] = realpath(self::PACKAGE_ROOT . '/' . $file) ?: self::PACKAGE_ROOT . '/' . $file;
        }
        $bitExpert = $this->bitExpertRoot();
        $bitExpertIncluded = $bitExpert !== null && $this->bitExpertGap($bitExpert) === null;
        if ($bitExpertIncluded) {
            $includes[] = $bitExpert . '/extension.neon';
        }

        $scanDirectories = [];
        if (is_dir($projectRoot . '/generated/code')) {
            $scanDirectories[] = $projectRoot . '/generated/code';
        }

        $parameters = [
            // Only this package's rules run; PHPStan's built-in level rules stay off.
            'customRulesetUsed' => true,
            // Older Magento code often has "private $x;" with no type: the constructor tells what it is.
            'inferPrivatePropertyTypeFromConstructor' => true,
            // Templates are analyzed too (T-01 and every rule that applies to code in them).
            'fileExtensions' => ['php', 'phtml'],
            'paths' => $paths,
            'excludePaths' => ['analyseAndScan' => $testPatterns, 'analyse' => $unparsable],
            'bootstrapFiles' => [$projectRoot . '/vendor/autoload.php'],
            'scanDirectories' => $scanDirectories,
            'tmpDir' => $cacheDir,
            // A worker that times out now fails the run, and the generated configuration is not the user's to edit.
            'parallel' => ['processTimeout' => 1800.0],
            'reportUnmatchedIgnoredErrors' => false,
            // PHPStan drops its result cache when a parameter changes. PHP rules read the index, so
            // a changed di.xml must invalidate results for PHP files that did not change.
            'magentoPerf' => ['reportUntypedReceivers' => true, 'projectRoot' => $projectRoot, 'indexFingerprint' => $indexFingerprint],
        ];
        if ($bitExpertIncluded) {
            // Its types are what the analysis needs; its own rules are not about performance.
            foreach (self::BITEXPERT_SWITCHES as $section => $switches) {
                $parameters[$section] = array_fill_keys($switches, false);
            }
        }

        // NEON is a superset of JSON for these values.
        return 'includes: ' . $this->json($includes) . "\n"
            . 'parameters: ' . $this->json($parameters) . "\n";
    }

    /**
     * @param array<mixed> $value
     */
    private function json(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
    }

    private function findPhpstan(): string
    {
        $root = $this->installPath('phpstan/phpstan');
        if ($root === null || !is_file($root . '/phpstan')) {
            throw new UsageException('phpstan/phpstan is not installed next to magento-perf-check.');
        }

        return $root . '/phpstan';
    }

    /**
     * Why bitexpert/phpstan-magento at $root cannot be included, or null when it can: its extension.neon must
     * declare every switch that turns its own rules off. 0.30.0 sets two of them without declaring them, and
     * PHPStan then rejects the whole configuration.
     */
    private function bitExpertGap(string $root): ?string
    {
        $file = $root . '/extension.neon';
        try {
            $neon = is_file($file) ? Neon::decode((string) file_get_contents($file)) : null;
        } catch (NeonException) {
            $neon = null;
        }
        if (!is_array($neon)) {
            return 'its extension.neon cannot be read';
        }
        foreach (self::BITEXPERT_SWITCHES as $section => $switches) {
            $schemas = $neon['parametersSchema'] ?? null;
            $schema = is_array($schemas) ? $schemas[$section] ?? null : null;
            $declared = $schema instanceof Entity && is_array($schema->attributes[0] ?? null) ? array_keys($schema->attributes[0]) : [];
            foreach ($switches as $switch) {
                if (!in_array($switch, $declared, true)) {
                    return sprintf('its extension.neon does not declare the parameter %s.%s', $section, $switch);
                }
            }
        }

        return null;
    }

    private function bitExpertRoot(): ?string
    {
        return $this->bitExpertRoot ?? $this->installPath('bitexpert/phpstan-magento');
    }

    private function installPath(string $package): ?string
    {
        if (!InstalledVersions::isInstalled($package)) {
            return null;
        }
        $path = InstalledVersions::getInstallPath($package);

        return $path !== null ? (realpath($path) ?: $path) : null;
    }

    /**
     * @return array{findings: list<Finding>, notices: list<Notice>, unparsable: array<string, list<Notice>>, filesFound: bool}
     */
    private function parse(string $stdout, string $stderr, string $projectRoot): array
    {
        try {
            $data = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new UsageException("PHPStan returned output that is not JSON.\n" . trim($stderr . "\n" . $stdout));
        }
        if (!is_array($data) || !is_array($data['files'] ?? null)) {
            throw new UsageException('PHPStan JSON output has no "files" section.');
        }
        /** @var array{files: array<string, array{messages?: list<array{message?: string, line?: int|null, identifier?: string|null}>}>, errors?: list<string>} $data */

        $findings = [];
        $notices = [];
        $unparsable = [];
        // Trait code is analysed once per class that uses it; a finding the first class gives stands for the same finding of the others.
        $firstContext = [];
        foreach ($data['files'] as $file => $fileResult) {
            $relative = $this->relative($file, $projectRoot);
            foreach ($fileResult['messages'] ?? [] as $message) {
                $text = $message['message'] ?? '';
                $line = $message['line'] ?? 0;
                // PHPStan discards every finding of the run when it meets one of these, see run().
                if (($message['identifier'] ?? null) === 'phpstan.parse' || str_starts_with($text, 'Reflection error')) {
                    $unparsable[$this->stripContext($file)][] = new Notice('phpstan-parse', $text, $relative, $line);
                    continue;
                }
                $match = $this->ruleFor($message['identifier'] ?? null, $text);
                if ($match !== null) {
                    $key = $match[0]->id . "\0" . $relative . "\0" . $line . "\0" . $text;
                    if (($firstContext[$key] ??= $file) !== $file) {
                        continue;
                    }
                    $findings[] = new Finding($match[0]->id, $match[1], $relative, $line, $text);
                } elseif (str_starts_with($text, NoticePrefix::UNTYPED_RECEIVER)) {
                    $notices[] = new Notice(Notice::SOURCE_UNTYPED_RECEIVER, $text, $relative, $line);
                } else {
                    $notices[] = new Notice('phpstan', $text, $relative, $line);
                }
            }
        }
        // Errors outside any file mean part of the analysis did not happen: a worker died or hit its memory limit.
        foreach ($data['errors'] ?? [] as $error) {
            $notices[] = new Notice(Notice::SOURCE_ANALYSIS_FAILURE, $error);
        }

        return ['findings' => $findings, 'notices' => $notices, 'unparsable' => $unparsable, 'filesFound' => true];
    }

    /**
     * By identifier (which also gives the severity of a variant), falling back to the
     * "[RULE-ID]" message prefix.
     *
     * @return array{\Silin\MagentoPerfCheck\Rule\RuleDefinition, \Silin\MagentoPerfCheck\Rule\Severity}|null
     */
    private function ruleFor(?string $identifier, string $text): ?array
    {
        if ($identifier !== null) {
            $match = $this->registry->findByIdentifier($identifier);
            if ($match !== null) {
                return $match;
            }
        }
        if (preg_match('/^\[([A-Z]-[A-Z]+-\d+)\] /', $text, $m) === 1) {
            $rule = $this->registry->find($m[1]);

            return $rule !== null ? [$rule, $rule->severity] : null;
        }

        return null;
    }

    private function relative(string $file, string $projectRoot): string
    {
        $file = $this->stripContext($file);

        return str_starts_with($file, $projectRoot . '/') ? substr($file, strlen($projectRoot) + 1) : $file;
    }

    /**
     * PHPStan reports "path (in context of class X)" for trait code.
     */
    private function stripContext(string $file): string
    {
        return preg_replace('/ \(in context of .*\)$/', '', $file) ?? $file;
    }
}
