<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Cli;

use Silin\MagentoPerfCheck\Baseline\Baseline;
use Silin\MagentoPerfCheck\Config\ConfigLoader;
use Silin\MagentoPerfCheck\PhpStan\Runner\PhpStanRunner;
use Silin\MagentoPerfCheck\Project\ProjectIndexBuilder;
use Silin\MagentoPerfCheck\Report\AnalysisResult;
use Silin\MagentoPerfCheck\Report\CheckstyleReporter;
use Silin\MagentoPerfCheck\Report\GithubReporter;
use Silin\MagentoPerfCheck\Report\JsonReporter;
use Silin\MagentoPerfCheck\Report\SarifReporter;
use Silin\MagentoPerfCheck\Report\TableReporter;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Version;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\Rule\CacheIdentityRule;
use Silin\MagentoPerfCheck\Xml\Rule\EntityHandleTtlRule;
use Silin\MagentoPerfCheck\Xml\Rule\IndexerWithoutMviewRule;
use Silin\MagentoPerfCheck\Xml\Rule\NonCacheableBlockRule;
use Silin\MagentoPerfCheck\Xml\Rule\ObserverEventRule;
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
            // Fail on a broken baseline before spending minutes on analysis.
            if ($options->baselineFile !== null) {
                Baseline::load($options->baselineFile);
            }
            $result = $this->analyze($options);
            if ($options->generateBaselineFile !== null) {
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
        fwrite($this->stdout, $reporter->render($result));

        return $result->hasFindingsAtLeast($options->failOn) ? self::EXIT_FINDINGS : self::EXIT_CLEAN;
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
        $index = (new ProjectIndexBuilder())->build($options->projectRoot);

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
        ];
        $xml = (new XmlAnalyzer(new FileDiscovery($options->projectRoot, $includeTests), $xmlRules))->analyze($paths);
        $php = ($this->phpStanRunner ?? new PhpStanRunner($registry))
            ->run($options->projectRoot, $paths, $options->memoryLimit, $includeTests);

        // Configuration first, then the baseline; --fail-on looks at what is left.
        $findings = $config->apply([...$xml->findings, ...$php->findings], $registry);
        $hidden = null;
        $unmatched = null;
        if ($options->baselineFile !== null) {
            $filtered = Baseline::load($options->baselineFile)->filter($findings);
            [$findings, $hidden, $unmatched] = [$filtered->findings, $filtered->hidden, $filtered->unmatched];
        }

        return new AnalysisResult(
            $findings,
            [...$xml->notices, ...$php->notices],
            $options->projectRoot,
            $paths,
            microtime(true) - $started,
            $hidden,
            $unmatched,
        );
    }
}
