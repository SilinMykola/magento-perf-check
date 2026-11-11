<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\CodingStandard;

use Silin\MagentoPerfCheck\Cli\UsageException;
use Silin\MagentoPerfCheck\Process\Subprocess;
use Silin\MagentoPerfCheck\Project\TestCode;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Report\Notice;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;

/**
 * Runs a few sniffs of the project's magento/magento-coding-standard through its own phpcs and
 * reports them as this tool's rules (ADR 7). Nothing is written into the project.
 */
final class CodingStandardRunner
{
    /** Rule ID => sniff code. */
    public const SNIFFS = [
        'S-PERF-01' => 'Magento2.Performance.ForeachArrayMerge',
        'S-TPL-01' => 'Magento2.Templates.ThisInTemplate',
        'S-TPL-02' => 'Magento2.Templates.ObjectManager',
        'S-DI-01' => 'Magento2.Classes.DiscouragedDependencies',
        'S-FN-01' => 'Magento2.Functions.DiscouragedFunction',
    ];

    public const STANDARD = 'vendor/magento/magento-coding-standard/Magento2';

    /** The PHP entry point; vendor/bin/phpcs can be a shell script. */
    public const PHPCS = 'vendor/squizlabs/php_codesniffer/bin/phpcs';

    /**
     * The coding standard's ruleset leaves these sniffs out of paths that match its exclude patterns, which
     * phpcs compares with the absolute path, ignoring case: a project inside such a directory gets nothing.
     */
    private const RULESET_EXCLUDES = [
        'S-DI-01' => '#/(?:_files|fixtures|tests?)/#i',
        'S-FN-01' => '#/(?:_files|fixtures|lib|tests?)/#i',
    ];

    /** S-FN-01 keeps only these of the many functions the sniff reports; PHP function names ignore case. */
    private const SLOW_FUNCTION = '/\bfunction (?:sleep|time_nanosleep|session_\w+)\(\)/i';

    public function __construct(private readonly RuleRegistry $registry)
    {
    }

    /**
     * @param list<string> $ruleIds the enabled rules of this group
     * @param list<string> $paths relative to the project root
     * @param list<string> $excludedDirs absolute directories left out, such as disabled modules
     * @return array{list<Finding>, list<Notice>}
     */
    public function run(string $projectRoot, array $ruleIds, array $paths, bool $includeTests, array $excludedDirs): array
    {
        $ruleIds = array_values(array_intersect(array_keys(self::SNIFFS), $ruleIds));
        if ($ruleIds === []) {
            return [[], []];
        }
        foreach ([self::PHPCS, self::STANDARD . '/ruleset.xml'] as $required) {
            if (!is_file($projectRoot . '/' . $required)) {
                return [[], [$this->notice(sprintf('%s is missing, so %s did not run. Install magento/magento-coding-standard in the project.', $required, implode(', ', $ruleIds)))]];
            }
        }
        // phpcs skips a sniff it does not know without a word when another one is found.
        $notices = [];
        foreach ($ruleIds as $i => $ruleId) {
            [, $group, $name] = explode('.', self::SNIFFS[$ruleId]);
            if (!is_file(sprintf('%s/%s/Sniffs/%s/%sSniff.php', $projectRoot, self::STANDARD, $group, $name))) {
                $notices[] = $this->notice(sprintf('The installed magento/magento-coding-standard has no %s sniff, so %s did not run.', self::SNIFFS[$ruleId], $ruleId));
                unset($ruleIds[$i]);
            } elseif (isset(self::RULESET_EXCLUDES[$ruleId]) && preg_match(self::RULESET_EXCLUDES[$ruleId], $projectRoot . '/') === 1) {
                $notices[] = $this->notice(sprintf('The project path %s matches an exclude pattern of the coding standard\'s ruleset for %s, so %s reports nothing here.', $projectRoot, self::SNIFFS[$ruleId], $ruleId));
            }
        }
        // phpcs reports real paths: each one is mapped back to the analyzed path it came from.
        $roots = [];
        foreach ($paths as $path) {
            $path = $path === '.' ? '' : $path;
            $real = realpath($projectRoot . '/' . $path);
            if ($real !== false) {
                $roots[$real] = $path;
            }
        }
        if ($ruleIds === [] || $roots === []) {
            return [[], $notices];
        }
        uksort($roots, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        $command = [
            PHP_BINARY,
            '-d',
            'display_errors=stderr',
            $projectRoot . '/' . self::PHPCS,
            // Relative to the project, the working directory: phpcs splits --standard on commas.
            '--standard=' . self::STANDARD . '/ruleset.xml',
            '--sniffs=' . implode(',', array_map(static fn (string $id): string => self::SNIFFS[$id], $ruleIds)),
            // The project's CodeSniffer.conf can raise the severity or hide warnings.
            '--severity=5',
            '--extensions=php,phtml',
            '--report=json',
            '--no-colors',
            '-q',
            ...array_map(static fn (string $path): string => $projectRoot . ($path === '' ? '' : '/' . $path), array_values($roots)),
        ];
        try {
            [, $stdout, $stderr] = Subprocess::run($command, $projectRoot, 'phpcs');
        } catch (UsageException $e) {
            return [[], [...$notices, $this->notice($e->getMessage())]];
        }

        $report = json_decode($stdout, true);
        if (!is_array($report) || !is_array($report['files'] ?? null)) {
            $output = implode("\n", array_slice(explode("\n", trim($stderr !== '' ? $stderr : $stdout)), 0, 5));

            return [[], [...$notices, $this->notice(trim(sprintf("phpcs did not return a JSON report, so %s did not run.\n%s", implode(', ', $ruleIds), $output)))]];
        }

        $findings = [];
        foreach ($report['files'] as $file => $result) {
            if (!is_string($file) || !is_array($result) || !is_array($result['messages'] ?? null)) {
                continue;
            }
            [$root, $relative] = self::relative($file, $roots, $projectRoot);
            if (self::leftOut($relative, $root, $projectRoot, $includeTests, $excludedDirs)) {
                continue;
            }
            foreach ($result['messages'] as $message) {
                if (!is_array($message) || !is_string($message['source'] ?? null) || !is_string($message['message'] ?? null)) {
                    continue;
                }
                $line = is_int($message['line'] ?? null) ? $message['line'] : 1;
                // A file reached through two overlapping paths, one of them a symlink, is reported twice.
                $column = is_int($message['column'] ?? null) ? $message['column'] : 0;
                $key = $message['source'] . "\0" . $relative . "\0" . $line . "\0" . $column . "\0" . $message['message'];
                if (str_starts_with($message['source'], 'Internal.')) {
                    $notices[$key] = new Notice(Notice::SOURCE_CODING_STANDARD, 'phpcs: ' . trim($message['message']), $relative, $line);
                    continue;
                }
                $finding = $this->finding($message['source'], trim($message['message']), $relative, $line);
                if ($finding !== null) {
                    $findings[$key] = $finding;
                }
            }
        }

        return [array_values($findings), array_values($notices)];
    }

    /**
     * @param array<string, string> $roots real path => analyzed path, longest first
     * @return array{?string, string} the analyzed path the file belongs to, and the file relative to the project
     */
    private static function relative(string $file, array $roots, string $projectRoot): array
    {
        foreach ($roots as $real => $path) {
            if ($file === $real || str_starts_with($file, $real . '/')) {
                return [$path, ltrim($path . substr($file, strlen($real)), '/')];
            }
        }

        return [null, str_starts_with($file, $projectRoot . '/') ? substr($file, strlen($projectRoot) + 1) : $file];
    }

    /**
     * Test code and excluded directories, the same way the PHPStan runner leaves them out:
     * a test directory given as an analyzed path is analyzed. Excluded directories come both
     * as installed and resolved (ProjectIndexBuilder::disabledModuleRoots()).
     *
     * @param list<string> $excludedDirs
     */
    private static function leftOut(string $relative, ?string $root, string $projectRoot, bool $includeTests, array $excludedDirs): bool
    {
        if (!$includeTests && TestCode::contains($relative) && !TestCode::contains((string) $root)) {
            return true;
        }
        foreach ($excludedDirs as $dir) {
            if (str_starts_with($projectRoot . '/' . $relative, $dir . '/')) {
                return true;
            }
        }

        return false;
    }

    private function finding(string $source, string $message, string $file, int $line): ?Finding
    {
        // The source is the sniff code followed by the message code.
        $sniff = implode('.', array_slice(explode('.', $source), 0, 3));
        $ruleId = array_flip(self::SNIFFS)[$sniff] ?? null;
        $rule = $ruleId !== null ? $this->registry->find($ruleId) : null;
        if ($ruleId === null || $rule === null || ($ruleId === 'S-FN-01' && preg_match(self::SLOW_FUNCTION, $message) !== 1)) {
            return null;
        }

        return new Finding($ruleId, $rule->severity, $file, $line, sprintf('[%s] %s (%s)', $ruleId, $message, $sniff));
    }

    private function notice(string $message): Notice
    {
        return new Notice(Notice::SOURCE_CODING_STANDARD, $message);
    }
}
