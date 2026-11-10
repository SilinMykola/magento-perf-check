<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Cli;

use Silin\MagentoPerfCheck\Baseline\Baseline;
use Silin\MagentoPerfCheck\CodingStandard\CodingStandardRunner;
use Silin\MagentoPerfCheck\Config\ConfigLoader;
use Silin\MagentoPerfCheck\PhpStan\Rule\GetFirstItemWithoutLimitRule;
use Silin\MagentoPerfCheck\PhpStan\Runner\PhpStanRunner;
use Silin\MagentoPerfCheck\Project\ProjectIndexBuilder;
use Silin\MagentoPerfCheck\Report\AnalysisResult;
use Silin\MagentoPerfCheck\Report\CheckstyleReporter;
use Silin\MagentoPerfCheck\Report\GithubReporter;
use Silin\MagentoPerfCheck\Report\JsonReporter;
use Silin\MagentoPerfCheck\Report\Notice;
use Silin\MagentoPerfCheck\Report\SarifReporter;
use Silin\MagentoPerfCheck\Report\TableReporter;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Version;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\Rule\CacheIdFactorRule;
use Silin\MagentoPerfCheck\Xml\Rule\CacheIdentityRule;
use Silin\MagentoPerfCheck\Xml\Rule\EntityHandleTtlRule;
use Silin\MagentoPerfCheck\Xml\Rule\GlobalAreaRule;
use Silin\MagentoPerfCheck\Xml\Rule\IndexerWithoutMviewRule;
use Silin\MagentoPerfCheck\Xml\Rule\NonCacheableBlockRule;
use Silin\MagentoPerfCheck\Xml\Rule\ObserverEventRule;
use Silin\MagentoPerfCheck\Xml\Rule\RedundantIndexRule;
use Silin\MagentoPerfCheck\Xml\Rule\SectionInvalidationRule;
use Silin\MagentoPerfCheck\Xml\Rule\SynchronousTopicRule;
use Silin\MagentoPerfCheck\Xml\Rule\TableWithoutPrimaryKeyRule;
use Silin\MagentoPerfCheck\Xml\XmlAnalyzer;
use Throwable;

final class Application
{
    public const EXIT_CLEAN = 0;
    public const EXIT_FINDINGS = 1;
    public const EXIT_ERROR = 2;

    /** @var resource */
    private $stdout;

    /** @var resource */
    private $stderr;

    /**
     * @param resource|null $stdout
     * @param resource|null $stderr
     */
    public function __construct(
        private readonly ?PhpStanRunner $phpStanRunner = null,
        $stdout = null,
        $stderr = null,
    ) {
        $this->stdout = $stdout ?? STDOUT;
        $this->stderr = $stderr ?? STDERR;
    }

    /**
     * @param list<string> $argv arguments without the script name
     */
    public function run(array $argv, string $cwd): int
    {
        try {
            $options = Options::fromArgv($argv, $cwd);
            if ($options->help) {
                fwrite($this->stdout, Options::usage());

                return self::EXIT_CLEAN;
            }
            if ($options->version) {
                fwrite($this->stdout, 'magento-perf-check ' . Version::VERSION . "\n");

                return self::EXIT_CLEAN;
            }
            // Fail on a broken baseline or an unwritable report file before spending minutes on analysis.
            if ($options->baselineFile !== null) {
                Baseline::load($options->baselineFile);
            }
            if ($options->outputFile !== null) {
                self::assertWritable($options->outputFile, 'report file');
            }
            if ($options->generateBaselineFile !== null) {
                self::assertWritable($options->generateBaselineFile, 'baseline file');
            }
            $result = $this->analyze($options);
            if ($options->generateBaselineFile !== null) {
                if ($result->analysisFailures() !== []) {
                    throw new UsageException("The analysis is incomplete, so no baseline was written.\n" . implode("\n", $result->analysisFailures()));
                }
                $baseline = Baseline::fromFindings($result->findings);
                $baseline->write($options->generateBaselineFile);
                fwrite($this->stdout, sprintf("Baseline with %d finding(s) written to %s.\n", $baseline->size(), $options->generateBaselineFile));

                return self::EXIT_CLEAN;
            }
        } catch (UsageException $e) {
            fwrite($this->stderr, 'magento-perf-check: ' . $e->getMessage() . "\n");

            return self::EXIT_ERROR;
        } catch (Throwable $e) {
            fwrite($this->stderr, sprintf("magento-perf-check: internal error: %s\n%s\n", $e->getMessage(), $e->getTraceAsString()));

            return self::EXIT_ERROR;
        }

        $registry = new RuleRegistry();
        $reporter = match ($options->format) {
            'json' => new JsonReporter($registry),
            'sarif' => new SarifReporter($registry),
            'checkstyle' => new CheckstyleReporter(),
            'github' => new GithubReporter(),
            default => new TableReporter($registry),
        };
        $report = $reporter->render($result);
        if ($options->outputFile === null) {
            fwrite($this->stdout, $report);
        } elseif (@file_put_contents($options->outputFile, $report) === false) {
            fwrite($this->stderr, sprintf("magento-perf-check: Cannot write report file %s.\n", $options->outputFile));

            return self::EXIT_ERROR;
        } else {
            $counts = $result->countBySeverity();
            fwrite($this->stdout, sprintf(
                "%d finding(s): %d error, %d warning, %d info. Report written to %s.\n",
                count($result->findings),
                $counts['error'] ?? 0,
                $counts['warning'] ?? 0,
                $counts['info'] ?? 0,
                $options->outputFile,
            ));
        }

        // Table and JSON reports already carry this; a report going to a file or a CI tool does not show it to anyone.
        $summary = $result->noticeSummary();
        if ($summary !== null && ($options->outputFile !== null || !in_array($options->format, ['table', 'json'], true))) {
            fwrite($this->stderr, 'magento-perf-check: ' . $summary . "\n");
        }

        // PHPStan drops its findings when this happens, so PHP code may be unreported: this must not pass as a clean run.
        if ($result->analysisFailures() !== []) {
            fwrite($this->stderr, "magento-perf-check: the analysis is incomplete, so the result cannot be trusted.\n" . implode("\n", $result->analysisFailures()) . "\n");

            return self::EXIT_ERROR;
        }

        return $result->hasFindingsAtLeast($options->failOn) ? self::EXIT_FINDINGS : self::EXIT_CLEAN;
    }

    private static function assertWritable(string $file, string $description): void
    {
        $writable = is_file($file) ? is_writable($file) : !is_dir($file) && is_dir(dirname($file)) && is_writable(dirname($file));
        if (!$writable) {
            throw new UsageException(sprintf('Cannot write %s %s.', $description, $file));
        }
    }

    /**
     * @param list<string> $paths
     * @param list<Notice> $notices
     */
    private static function nothingToAnalyze(array $paths, array $notices, bool $includeTests): string
    {
        $message = $paths === []
            ? 'Nothing to analyze: the project has neither app/code nor app/design. Choose what to analyze with --paths.'
            : sprintf('Nothing to analyze in %s: no PHP, template or Magento XML files were found.', implode(', ', $paths));
        if (!$includeTests) {
            $message .= ' Test code is skipped unless --include-tests is set.';
        }
        foreach ($notices as $notice) {
            if ($notice->source === 'phpstan-parse') {
                $message .= sprintf("\n%s:%d: %s", (string) $notice->file, (int) $notice->line, $notice->message);
            }
        }

        return $message;
    }

    /**
     * One notice for each module disabled in app/etc/config.php that lies in, or holds, an analyzed path:
     * Magento does not run it, so it is not analyzed.
     *
     * @param list<string> $paths
     * @param array<string, string> $disabledRoots directory => module name
     * @return list<Notice>
     */
    private static function disabledModuleNotices(array $paths, array $disabledRoots, string $projectRoot): array
    {
        $notices = [];
        foreach ($disabledRoots as $root => $name) {
            if (!str_starts_with($root, $projectRoot . '/')) {
                continue;
            }
            $relative = substr($root, strlen($projectRoot) + 1);
            foreach ($paths as $path) {
                $path = trim($path, '/');
                if ($path === '.' || str_starts_with($relative . '/', $path . '/') || str_starts_with($path . '/', $relative . '/')) {
                    $notices[$name] = new Notice(
                        Notice::SOURCE_DISABLED_MODULE,
                        sprintf('%s is disabled in app/etc/config.php and was not analyzed.', $name),
                        $relative,
                    );
                    break;
                }
            }
        }

        return array_values($notices);
    }

    private function analyze(Options $options): AnalysisResult
    {
        $started = microtime(true);
        $registry = new RuleRegistry();
        $config = (new ConfigLoader($registry))->load($options->configFile);

        // Command-line options win over the configuration file.
        $paths = $options->pathsGiven || $config->paths === null
            ? $options->paths
            : Options::resolvePaths($options->projectRoot, $config->paths, 'magentoPerf.paths in ' . $options->configFile);
        $includeTests = $options->includeTests || ($config->includeTests ?? false);

        // Built once here; PHPStan workers then read the cached index.
        $indexBuilder = new ProjectIndexBuilder();
        $index = $indexBuilder->build($options->projectRoot);

        $xmlRules = [
            new NonCacheableBlockRule($config->allowlistHandles, $config->replaceAllowlist),
            new EntityHandleTtlRule(),
            ObserverEventRule::loadAfter(),
            ObserverEventRule::genericModelEvents(),
            ObserverEventRule::renderEvents(),
            new SectionInvalidationRule(),
            new SynchronousTopicRule(),
            new IndexerWithoutMviewRule($index),
            new TableWithoutPrimaryKeyRule($index),
            new CacheIdentityRule(),
            new RedundantIndexRule(),
            new GlobalAreaRule(),
            new CacheIdFactorRule(),
        ];
        // A module disabled in app/etc/config.php does not run: neither its PHP nor its XML is analyzed.
        $disabledRoots = $indexBuilder->disabledModuleRoots($options->projectRoot);
        $disabledNotices = self::disabledModuleNotices($paths, $disabledRoots, $options->projectRoot);
        $xml = (new XmlAnalyzer(new FileDiscovery($options->projectRoot, $includeTests, array_keys($disabledRoots)), $xmlRules))->analyze($paths);
        $php = ($this->phpStanRunner ?? new PhpStanRunner($registry))
            ->run($options->projectRoot, $paths, $options->memoryLimit, $includeTests, array_keys($disabledRoots));
        [$codingStandardFindings, $codingStandardNotices] = (new CodingStandardRunner($registry))->run(
            $options->projectRoot,
            array_values(array_filter(array_keys(CodingStandardRunner::SNIFFS), static fn (string $id): bool => $config->isEnabled($id, $registry))),
            $paths,
            $includeTests,
            array_keys($disabledRoots),
        );

        if ($xml->filesFound === 0 && !$php->filesAnalyzed && $disabledNotices === []) {
            throw new UsageException(self::nothingToAnalyze($paths, $php->notices, $includeTests));
        }

        // Configuration first, then the baseline; --fail-on looks at what is left.
        $findings = $config->apply([...$xml->findings, ...$php->findings, ...$codingStandardFindings], $registry);
        $hidden = null;
        $unmatched = null;
        if ($options->baselineFile !== null) {
            $filtered = Baseline::load($options->baselineFile)->filter($findings);
            [$findings, $hidden, $unmatched] = [$filtered->findings, $filtered->hidden, $filtered->unmatched];
        }

        // These notices tell how much P-COL-02 could not check, which means nothing while it is off.
        $phpNotices = $config->isEnabled(GetFirstItemWithoutLimitRule::ID, $registry)
            ? $php->notices
            : array_values(array_filter($php->notices, static fn (Notice $notice): bool => $notice->source !== Notice::SOURCE_UNTYPED_RECEIVER));

        return new AnalysisResult(
            $findings,
            [...$xml->notices, ...$phpNotices, ...$codingStandardNotices, ...$disabledNotices],
            $options->projectRoot,
            $paths,
            microtime(true) - $started,
            $hidden,
            $unmatched,
        );
    }
}
