<?php

declare(strict_types=1);

namespace MagentoPerfRules\Cli;

use MagentoPerfRules\Rule\Severity;

final class Options
{
    public const DEFAULT_PATHS = ['app/code', 'app/design'];
    public const FORMATS = ['table', 'json'];

    /**
     * @param list<string> $paths relative to the project root
     */
    public function __construct(
        public readonly string $projectRoot,
        public readonly array $paths,
        public readonly string $format,
        public readonly Severity $failOn,
        public readonly string $memoryLimit,
        public readonly bool $includeTests = false,
        public readonly bool $help = false,
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
            if ($argument === '--include-tests') {
                $values['include-tests'] = '1';
                continue;
            }
            if (preg_match('/^--(project|paths|format|fail-on|memory-limit)=(.*)$/s', $argument, $m) !== 1) {
                throw new UsageException(sprintf('Unknown argument "%s". Run with --help for usage.', $argument));
            }
            $values[$m[1]] = $m[2];
        }

        $project = self::resolveProject($values['project'] ?? $cwd, $cwd);
        $paths = isset($values['paths'])
            ? array_values(array_filter(array_map(static fn (string $p): string => trim($p, " /"), explode(',', $values['paths'])), static fn (string $p): bool => $p !== ''))
            : self::DEFAULT_PATHS;
        if ($paths === []) {
            throw new UsageException('--paths is empty.');
        }
        if (isset($values['paths'])) {
            foreach ($paths as $path) {
                if (!file_exists($project . '/' . $path)) {
                    throw new UsageException(sprintf('Path "%s" does not exist in project %s.', $path, $project));
                }
            }
        } else {
            // Default paths that a project does not have (e.g. no app/design) are skipped.
            $paths = array_values(array_filter($paths, static fn (string $p): bool => is_dir($project . '/' . $p)));
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

        return new self($project, $paths, $format, $failOn, $memoryLimit, isset($values['include-tests']));
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
              --format=<format>     table (default) or json.
              --fail-on=<severity>  Exit with 1 when a finding has at least this severity:
                                    error (default), warning or info.
              --include-tests       Also analyze test code (Test/ and dev/tests/ directories),
                                    which is skipped by default.
              --memory-limit=<v>    Memory limit for PHPStan (default: 2G).
              -h, --help            Show this help.

            Exit codes: 0 no findings at --fail-on level, 1 findings, 2 tool error.

            TXT;
    }
}
