<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Cli;

use Silin\MagentoPerfCheck\Rule\Severity;

final class Options
{
    public const DEFAULT_PATHS = ['app/code', 'app/design'];
    public const FORMATS = ['table', 'json', 'sarif', 'checkstyle', 'github'];
    public const CONFIG_FILE = 'magento-perf-check.neon';

    /**
     * @param list<string> $paths relative to the project root
     * @param bool $pathsGiven whether --paths was on the command line (it then wins over the configuration file)
     * @param ?string $configFile absolute path of the configuration file to read, if any
     */
    public function __construct(
        public readonly string $projectRoot,
        public readonly array $paths,
        public readonly string $format,
        public readonly Severity $failOn,
        public readonly string $memoryLimit,
        public readonly bool $includeTests = false,
        public readonly bool $help = false,
        public readonly bool $pathsGiven = false,
        public readonly ?string $configFile = null,
        public readonly ?string $baselineFile = null,
        public readonly ?string $generateBaselineFile = null,
        public readonly bool $version = false,
        public readonly ?string $outputFile = null,
    ) {
    }

    /**
     * @param list<string> $argv arguments without the script name
     */
    public static function fromArgv(array $argv, string $cwd): self
    {
        $values = [];
        foreach ($argv as $argument) {
            if ($argument === '--help' || $argument === '-h') {
                return new self($cwd, self::DEFAULT_PATHS, 'table', Severity::Error, '2G', false, true);
            }
            if ($argument === '--version') {
                return new self($cwd, self::DEFAULT_PATHS, 'table', Severity::Error, '2G', version: true);
            }
            if ($argument === '--include-tests') {
                $values['include-tests'] = '1';
                continue;
            }
            if (preg_match('/^--(project|paths|format|fail-on|memory-limit|config|baseline|generate-baseline|output)=(.*)$/s', $argument, $m) !== 1) {
                throw new UsageException(sprintf('Unknown argument "%s". Run with --help for usage.', $argument));
            }
            $values[$m[1]] = $m[2];
        }

        $project = self::resolveProject($values['project'] ?? $cwd, $cwd);
        $paths = isset($values['paths'])
            ? self::resolvePaths($project, explode(',', $values['paths']), '--paths')
            : self::defaultPaths($project);

        $configFile = null;
        if (isset($values['config'])) {
            $configFile = str_starts_with($values['config'], '/') ? $values['config'] : $cwd . '/' . $values['config'];
            if (!is_file($configFile)) {
                throw new UsageException(sprintf('Configuration file %s does not exist.', $configFile));
            }
        } elseif (is_file($project . '/' . self::CONFIG_FILE)) {
            $configFile = $project . '/' . self::CONFIG_FILE;
        }

        $format = $values['format'] ?? 'table';
        if (!in_array($format, self::FORMATS, true)) {
            throw new UsageException(sprintf('Unknown format "%s". Supported formats: %s.', $format, implode(', ', self::FORMATS)));
        }

        $failOn = Severity::tryFrom($values['fail-on'] ?? 'error');
        if ($failOn === null) {
            throw new UsageException('--fail-on must be one of: error, warning, info.');
        }

        $memoryLimit = $values['memory-limit'] ?? '2G';
        if (preg_match('/^(-1|\d+[KMG]?)$/i', $memoryLimit) !== 1) {
            throw new UsageException('--memory-limit must look like 512M, 2G or -1.');
        }

        $inCwd = static fn (string $file): string => str_starts_with($file, '/') ? $file : $cwd . '/' . $file;
        foreach (['baseline', 'generate-baseline', 'output'] as $key) {
            if (isset($values[$key]) && $values[$key] === '') {
                throw new UsageException(sprintf('--%s needs a file name.', $key));
            }
        }
        if (isset($values['baseline'], $values['generate-baseline'])) {
            throw new UsageException('--baseline and --generate-baseline cannot be used together.');
        }
        if (isset($values['output'], $values['generate-baseline'])) {
            throw new UsageException('--output and --generate-baseline cannot be used together.');
        }
        if (isset($values['output'], $values['baseline']) && $inCwd($values['output']) === $inCwd($values['baseline'])) {
            throw new UsageException('--output must not be the --baseline file.');
        }

        return new self(
            $project,
            $paths,
            $format,
            $failOn,
            $memoryLimit,
            isset($values['include-tests']),
            false,
            isset($values['paths']),
            $configFile,
            isset($values['baseline']) ? $inCwd($values['baseline']) : null,
            isset($values['generate-baseline']) ? $inCwd($values['generate-baseline']) : null,
            false,
            isset($values['output']) ? $inCwd($values['output']) : null,
        );
    }

    /**
     * Normalizes and checks paths given on the command line or in the configuration file.
     *
     * @param list<string> $paths
     * @return list<string>
     */
    public static function resolvePaths(string $project, array $paths, string $origin): array
    {
        $paths = array_values(array_filter(
            array_map(static fn (string $p): string => trim($p, " /"), $paths),
            static fn (string $p): bool => $p !== '',
        ));
        if ($paths === []) {
            throw new UsageException($origin . ' is empty.');
        }
        foreach ($paths as $path) {
            if (!file_exists($project . '/' . $path)) {
                throw new UsageException(sprintf('Path "%s" (%s) does not exist in project %s.', $path, $origin, $project));
            }
        }

        return $paths;
    }

    /**
     * Default paths that a project does not have (e.g. no app/design) are skipped.
     *
     * @return list<string>
     */
    private static function defaultPaths(string $project): array
    {
        return array_values(array_filter(self::DEFAULT_PATHS, static fn (string $p): bool => is_dir($project . '/' . $p)));
    }

    private static function resolveProject(string $project, string $cwd): string
    {
        if ($project === '') {
            throw new UsageException('--project is empty.');
        }
        if (str_starts_with($project, '~/')) {
            $project = (getenv('HOME') ?: '') . substr($project, 1);
        } elseif (!str_starts_with($project, '/')) {
            $project = $cwd . '/' . $project;
        }
        $real = realpath($project);
        if ($real === false || !is_dir($real)) {
            throw new UsageException(sprintf('Project directory %s does not exist.', $project));
        }
        $missing = array_values(array_filter(
            ['app/etc', 'vendor/autoload.php'],
            static fn (string $required): bool => !file_exists($real . '/' . $required),
        ));
        if ($missing !== []) {
            throw new UsageException(sprintf(
                '%s is not a Magento project: missing %s.',
                $real,
                implode(' and ', $missing),
            ));
        }

        return $real;
    }

    public static function usage(): string
    {
        return <<<'TXT'
            Usage: magento-perf-check [options]

            Finds Magento 2 performance and Full Page Cache anti-patterns in XML and PHP.

            Options:
              --project=<path>      Magento project to analyze (default: current directory).
                                    Nothing is written into the project.
              --paths=<p1,p2>       Paths relative to the project (default: app/code,app/design).
                                    Explicit paths are analyzed even under vendor/.
              --format=<format>     table (default), json, sarif (SARIF 2.1.0), checkstyle or
                                    github (GitHub Actions annotations).
              --fail-on=<severity>  Exit with 1 when a finding has at least this severity:
                                    error (default), warning or info.
              --config=<file>       Configuration file (default: magento-perf-check.neon in the
                                    project root, when it exists).
              --generate-baseline=<file>
                                    Write all current findings to <file> (relative to the
                                    current directory) and exit with 0.
              --baseline=<file>     Report only findings that are not in <file>.
              --output=<file>       Write the report to <file> (relative to the current
                                    directory) instead of stdout; print a one-line summary.
              --include-tests       Also analyze test code (Test/ and dev/tests/ directories),
                                    which is skipped by default.
              --memory-limit=<v>    Memory limit for PHPStan (default: 2G).
              --version             Show the version.
              -h, --help            Show this help.

            Exit codes: 0 no findings at --fail-on level, 1 findings, 2 tool error.

            TXT;
    }
}
