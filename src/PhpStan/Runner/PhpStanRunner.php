<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Runner;

use Composer\InstalledVersions;
use JsonException;
use Silin\MagentoPerfCheck\Cli\UsageException;
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

    /**
     * @param ?string $phpstanBinary path to the phpstan launcher; found through Composer when null
     */
    public function __construct(
        private readonly RuleRegistry $registry,
        private readonly ?string $phpstanBinary = null,
    ) {
    }

    /**
     * @param list<string> $paths relative to the project root
     */
    public function run(string $projectRoot, array $paths, string $memoryLimit, bool $includeTests = false): PhpStanResult
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

        $excluded = [];
        $parseNotices = [];
        for ($pass = 1; $pass <= self::MAX_PASSES; ++$pass) {
            $config = $this->writeConfig(
                $workDir,
                $this->config($projectRoot, $absolutePaths, $testPatterns, $excluded, $workDir . '/cache', $indexFingerprint),
            );
            $result = $this->analyse($config, $projectRoot, $memoryLimit);
            $unparsable = array_diff(array_keys($result['unparsable']), $excluded);
            if ($unparsable === []) {
                return new PhpStanResult($result['findings'], [...$notices, ...$parseNotices, ...$result['notices']], $result['filesFound']);
            }
            foreach ($unparsable as $file) {
                $excluded[] = $file;
                array_push($parseNotices, ...$result['unparsable'][$file]);
            }
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
    private function writeConfig(string $workDir, string $config): string
    {
        $file = $workDir . '/phpstan-' . md5($config) . '.neon';
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
        if ($this->installPath('bitexpert/phpstan-magento') !== null) {
            return [];
        }
        $generated = $projectRoot . '/generated/code';
        if (is_dir($generated) && array_diff(scandir($generated) ?: [], ['.', '..']) !== []) {
            return [];
        }

        return [new Notice(
            'generated-code',
            'generated/code is missing or empty, so the types of proxies and extension attributes are unknown and calls on them are not checked. '
            . 'Run bin/magento setup:di:compile, or install bitexpert/phpstan-magento in the tool.',
        )];
    }

    /**
     * @return array{findings: list<Finding>, notices: list<Notice>, unparsable: array<string, list<Notice>>, filesFound: bool}
     */
    private function analyse(string $config, string $projectRoot, string $memoryLimit): array
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
        ];
        [$exitCode, $stdout, $stderr] = $this->execute($command, $projectRoot);

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
     * @param list<string> $unparsable files PHPStan cannot parse
     */
    private function config(string $projectRoot, array $paths, array $testPatterns, array $unparsable, string $cacheDir, string $indexFingerprint): string
    {
        $includes = [];
        foreach (['extension.neon', 'extension-experimental.neon'] as $file) {
            $includes[] = realpath(self::PACKAGE_ROOT . '/' . $file) ?: self::PACKAGE_ROOT . '/' . $file;
        }
        $bitExpert = $this->installPath('bitexpert/phpstan-magento');
        if ($bitExpert !== null && is_file($bitExpert . '/extension.neon')) {
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

    private function installPath(string $package): ?string
    {
        if (!InstalledVersions::isInstalled($package)) {
            return null;
        }
        $path = InstalledVersions::getInstallPath($package);

        return $path !== null ? (realpath($path) ?: $path) : null;
    }

    /**
     * @param list<string> $command
     * @return array{int, string, string}
     */
    private function execute(array $command, string $cwd): array
    {
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd);
        if (!is_resource($process)) {
            throw new UsageException('Cannot start PHPStan.');
        }
        // Read both pipes without blocking each other: PHPStan can write a lot to either.
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $stdout = '';
        $stderr = '';
        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $read = array_values(array_filter([$pipes[1], $pipes[2]], static fn ($pipe): bool => !feof($pipe)));
            $write = null;
            $except = null;
            if (stream_select($read, $write, $except, 1) === false) {
                break;
            }
            foreach ($read as $pipe) {
                $chunk = (string) fread($pipe, 65536);
                if ($pipe === $pipes[1]) {
                    $stdout .= $chunk;
                } else {
                    $stderr .= $chunk;
                }
            }
        }
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $stdout, $stderr];
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
