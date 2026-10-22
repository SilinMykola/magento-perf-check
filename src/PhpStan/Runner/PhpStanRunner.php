<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Runner;

use Composer\InstalledVersions;
use JsonException;
use MagentoPerfRules\Cli\UsageException;
use MagentoPerfRules\Project\TestCode;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Report\Notice;
use MagentoPerfRules\Report\NoticePrefix;
use MagentoPerfRules\Rule\RuleRegistry;

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
            static fn (string $p): bool => is_dir($p) || (is_file($p) && str_ends_with($p, '.php')),
        ));
        if ($absolutePaths === []) {
            return new PhpStanResult([], []);
        }

        $workDir = sys_get_temp_dir() . '/magento-perf-rules/' . md5($projectRoot);
        if (!is_dir($workDir) && !mkdir($workDir, 0777, true) && !is_dir($workDir)) {
            throw new UsageException('Cannot create temp directory ' . $workDir);
        }
        $config = $workDir . '/phpstan.neon';

        // A file PHPStan cannot parse makes it stop reporting rule errors for every other
        // file ("Result is incomplete because of severe errors"). Such files are excluded
        // and the analysis runs again; their parse errors stay in the result as notices.
        $testPatterns = [];
        if (!$includeTests) {
            foreach ($paths as $path) {
                if (!TestCode::contains($path) && is_dir($projectRoot . '/' . $path)) {
                    array_push($testPatterns, ...TestCode::excludePatterns($projectRoot . '/' . $path));
                }
            }
        }

        $excluded = [];
        $parseNotices = [];
        for ($pass = 1; $pass <= self::MAX_PASSES; ++$pass) {
            file_put_contents($config, $this->config($projectRoot, $absolutePaths, [...$testPatterns, ...$excluded], $workDir . '/cache'));
            $result = $this->analyse($config, $projectRoot, $memoryLimit);
            $unparsable = array_diff(array_keys($result['unparsable']), $excluded);
            if ($unparsable === []) {
                return new PhpStanResult($result['findings'], [...$parseNotices, ...$result['notices']]);
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
     * @return array{findings: list<Finding>, notices: list<Notice>, unparsable: array<string, list<Notice>>}
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
            return ['findings' => [], 'notices' => [], 'unparsable' => []];
        }

        return $this->parse($stdout, $stderr, $projectRoot);
    }

    /**
     * @param list<string> $paths
     * @param list<string> $excludePaths test-code patterns and files PHPStan cannot parse
     */
    private function config(string $projectRoot, array $paths, array $excludePaths, string $cacheDir): string
    {
        $includes = [realpath(self::PACKAGE_ROOT . '/extension.neon') ?: self::PACKAGE_ROOT . '/extension.neon'];
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
            // Templates are analyzed too (T-01 and every rule that applies to code in them).
            'fileExtensions' => ['php', 'phtml'],
            'paths' => $paths,
            'excludePaths' => ['analyse' => $excludePaths],
            'bootstrapFiles' => [$projectRoot . '/vendor/autoload.php'],
            'scanDirectories' => $scanDirectories,
            'tmpDir' => $cacheDir,
            'reportUnmatchedIgnoredErrors' => false,
            'magentoPerf' => ['reportUntypedReceivers' => true, 'projectRoot' => $projectRoot],
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
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function findPhpstan(): string
    {
        $root = $this->installPath('phpstan/phpstan');
        if ($root === null || !is_file($root . '/phpstan')) {
            throw new UsageException('phpstan/phpstan is not installed next to magento-perf-rules.');
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
     * @return array{findings: list<Finding>, notices: list<Notice>, unparsable: array<string, list<Notice>>}
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
                if (($message['identifier'] ?? null) === 'phpstan.parse') {
                    $unparsable[$this->stripContext($file)][] = new Notice('phpstan-parse', $text, $relative, $line);
                    continue;
                }
                $match = $this->ruleFor($message['identifier'] ?? null, $text);
                if ($match !== null) {
                    $findings[] = new Finding($match[0]->id, $match[1], $relative, $line, $text);
                } elseif (str_starts_with($text, NoticePrefix::UNTYPED_RECEIVER)) {
                    $notices[] = new Notice('untyped-receiver', $text, $relative, $line);
                } else {
                    $notices[] = new Notice('phpstan', $text, $relative, $line);
                }
            }
        }
        foreach ($data['errors'] ?? [] as $error) {
            $notices[] = new Notice('phpstan', $error);
        }

        return ['findings' => $findings, 'notices' => $notices, 'unparsable' => $unparsable];
    }

    /**
     * By identifier (which also gives the severity of a variant), falling back to the
     * "[RULE-ID]" message prefix.
     *
     * @return array{\MagentoPerfRules\Rule\RuleDefinition, \MagentoPerfRules\Rule\Severity}|null
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
