<?php

declare(strict_types=1);

namespace MagentoPerfRules\Cli;

use MagentoPerfRules\Config\ConfigLoader;
use MagentoPerfRules\PhpStan\Runner\PhpStanRunner;
use MagentoPerfRules\Project\ProjectIndexBuilder;
use MagentoPerfRules\Report\AnalysisResult;
use MagentoPerfRules\Report\JsonReporter;
use MagentoPerfRules\Report\TableReporter;
use MagentoPerfRules\Rule\RuleRegistry;
use MagentoPerfRules\Xml\FileDiscovery;
use MagentoPerfRules\Xml\Rule\NonCacheableBlockRule;
use MagentoPerfRules\Xml\XmlAnalyzer;
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
            $result = $this->analyze($options);
        } catch (UsageException $e) {
            fwrite($this->stderr, 'magento-perf-check: ' . $e->getMessage() . "\n");

            return self::EXIT_ERROR;
        } catch (Throwable $e) {
            fwrite($this->stderr, sprintf("magento-perf-check: internal error: %s\n%s\n", $e->getMessage(), $e->getTraceAsString()));

            return self::EXIT_ERROR;
        }

        $registry = new RuleRegistry();
        $reporter = $options->format === 'json' ? new JsonReporter($registry) : new TableReporter($registry);
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
        (new ProjectIndexBuilder())->build($options->projectRoot);

        $xmlRules = [new NonCacheableBlockRule($config->allowlistHandles, $config->replaceAllowlist)];
        $xml = (new XmlAnalyzer(new FileDiscovery($options->projectRoot, $includeTests), $xmlRules))->analyze($paths);
        $php = ($this->phpStanRunner ?? new PhpStanRunner($registry))
            ->run($options->projectRoot, $paths, $options->memoryLimit, $includeTests);

        return new AnalysisResult(
            $config->apply([...$xml->findings, ...$php->findings]),
            [...$xml->notices, ...$php->notices],
            $options->projectRoot,
            $paths,
            microtime(true) - $started,
        );
    }
}
