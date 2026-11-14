<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Integration;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Silin\MagentoPerfCheck\Cli\Application;
use Silin\MagentoPerfCheck\PhpStan\Runner\PhpStanRunner;
use Silin\MagentoPerfCheck\Project\WorkDirectory;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use SplFileInfo;

/**
 * A run that did not analyze what it was asked to must never look like a clean one.
 */
final class AnalysisIntegrityTest extends TestCase
{
    private const SESSION_BLOCK = <<<'PHP'
        <?php
        namespace Acme\Demo\Block;
        class Foo
        {
            public function __construct(private \Magento\Customer\Model\Session $session)
            {
            }
        }
        PHP;

    private const MODULE_XML = '<?xml version="1.0"?><config><module name="Acme_Demo"/></config>';

    private const PROXY_DI = <<<'XML'
        <?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <type name="Acme\Demo\Block\Foo">
                <arguments>
                    <argument name="session" xsi:type="object">Magento\Customer\Model\Session\Proxy</argument>
                </arguments>
            </type>
        </config>
        XML;

    /** @var list<string> */
    private array $temporary = [];

    protected function tearDown(): void
    {
        foreach ($this->temporary as $project) {
            $this->remove($project);
            if (is_dir(WorkDirectory::forProject($project))) {
                $this->remove(WorkDirectory::forProject($project));
            }
        }
    }

    private function remove(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var SplFileInfo $entry */
        foreach ($iterator as $entry) {
            $entry->isDir() && !$entry->isLink() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($directory);
    }

    /**
     * @param array<string, string> $files
     */
    private function project(array $files = []): string
    {
        $root = sys_get_temp_dir() . '/mpc-integrity-' . bin2hex(random_bytes(6));
        $stubs = realpath(__DIR__ . '/../PhpStan/Stubs');
        $autoload = "<?php\n";
        foreach (['framework', 'catalog', 'di'] as $stub) {
            $autoload .= sprintf("require_once %s;\n", var_export($stubs . '/' . $stub . '.php', true));
        }
        $files = ['vendor/autoload.php' => $autoload, 'app/etc/.keep' => '', ...$files];
        foreach ($files as $file => $content) {
            $this->write($root . '/' . $file, $content);
        }
        $this->temporary[] = (string) realpath($root);

        return (string) realpath($root);
    }

    private function write(string $file, string $content): void
    {
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, $content);
    }

    /**
     * @param list<string> $argv
     * @return array{int, string, string}
     */
    private function runCommand(string $project, array $argv, ?PhpStanRunner $runner = null): array
    {
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);

        $exitCode = (new Application($runner, $stdout, $stderr))->run(['--project=' . $project, ...$argv], $project);

        rewind($stdout);
        rewind($stderr);

        return [$exitCode, (string) stream_get_contents($stdout), (string) stream_get_contents($stderr)];
    }

    /**
     * @param list<string> $argv
     * @return list<string>
     */
    private function ruleIds(string $project, array $argv = []): array
    {
        [, $stdout, $stderr] = $this->runCommand($project, ['--format=json', ...$argv]);
        $report = json_decode($stdout, true);
        self::assertIsArray($report, $stderr);
        /** @var array{findings: list<array{ruleId: string}>} $report */

        return array_column($report['findings'], 'ruleId');
    }

    private function incompleteRunner(): PhpStanRunner
    {
        return new PhpStanRunner(new RuleRegistry(), __DIR__ . '/../Fixtures/Project/incomplete-phpstan.php');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function formats(): array
    {
        return ['table' => ['table'], 'json' => ['json'], 'sarif' => ['sarif'], 'checkstyle' => ['checkstyle'], 'github' => ['github']];
    }

    /**
     * @dataProvider formats
     */
    public function testPhpStanErrorSectionFailsTheRunInEveryFormat(string $format): void
    {
        $project = $this->project(['app/code/Acme/Demo/Foo.php' => "<?php\n"]);

        [$exitCode, , $stderr] = $this->runCommand($project, ['--format=' . $format], $this->incompleteRunner());

        self::assertSame(Application::EXIT_ERROR, $exitCode);
        self::assertStringContainsString('Allowed memory size exhausted', $stderr);
        self::assertStringContainsString('incomplete', $stderr);
    }

    public function testIncompleteRunIsMarkedInJson(): void
    {
        $project = $this->project(['app/code/Acme/Demo/Foo.php' => "<?php\n"]);

        [, $stdout] = $this->runCommand($project, ['--format=json'], $this->incompleteRunner());

        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertTrue($report['summary']['incomplete']);
    }

    public function testIncompleteRunWritesNoBaseline(): void
    {
        $project = $this->project(['app/code/Acme/Demo/Foo.php' => "<?php\n"]);
        $baseline = $project . '/baseline.json';

        [$exitCode, , $stderr] = $this->runCommand($project, ['--generate-baseline=' . $baseline], $this->incompleteRunner());

        self::assertSame(Application::EXIT_ERROR, $exitCode);
        self::assertFileDoesNotExist($baseline);
        self::assertStringContainsString('Allowed memory size exhausted', $stderr);
    }

    public function testChangedXmlInvalidatesPhpStanResults(): void
    {
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Block/Foo.php' => self::SESSION_BLOCK,
        ]);
        $diXml = $project . '/app/code/Acme/Demo/etc/di.xml';

        self::assertSame(['P-DI-01'], $this->ruleIds($project));

        file_put_contents($diXml, self::PROXY_DI);
        self::assertSame([], $this->ruleIds($project), 'the fix the message asks for must take effect');

        unlink($diXml);
        self::assertSame(['P-DI-01'], $this->ruleIds($project), 'removing the Proxy again must be reported');
    }

    public function testRunsWithDifferentPathsDoNotShareAConfigurationFile(): void
    {
        $project = $this->project([
            'app/code/Acme/One/Foo.php' => "<?php\n",
            'app/code/Acme/Two/Foo.php' => "<?php\n",
        ]);
        $record = $project . '/record.txt';
        putenv('MPC_TEST_RECORD=' . $record);
        $runner = new PhpStanRunner(new RuleRegistry(), __DIR__ . '/../Fixtures/Project/recording-phpstan.php');

        try {
            $this->runCommand($project, ['--paths=app/code/Acme/One'], $runner);
            $this->runCommand($project, ['--paths=app/code/Acme/Two'], $runner);
        } finally {
            putenv('MPC_TEST_RECORD');
        }

        $configs = file($record, FILE_IGNORE_NEW_LINES);
        self::assertIsArray($configs);
        self::assertCount(2, array_unique($configs));
        self::assertStringContainsString('Acme/One', (string) file_get_contents($configs[0]));
        self::assertStringContainsString('Acme/Two', (string) file_get_contents($configs[1]));
    }

    public function testSingleTemplateIsAnalyzed(): void
    {
        $template = 'app/code/Acme/Demo/view/frontend/templates/foo.phtml';
        $project = $this->project([
            $template => "<?php /** @var \\Magento\\Framework\\View\\Element\\Template \$block */\n"
                . "echo \$block->getLayout()->createBlock('x')->toHtml();\n",
        ]);

        [$exitCode, $stdout, $stderr] = $this->runCommand($project, ['--format=json', '--paths=' . $template]);

        self::assertNotSame(Application::EXIT_ERROR, $exitCode, $stderr);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame(['T-01'], array_column($report['findings'], 'ruleId'));
    }

    /**
     * @return array<string, array{array<string, string>, list<string>}>
     */
    public static function nothingToAnalyze(): array
    {
        return [
            'no default paths' => [[], []],
            'file type the tool cannot read' => [['composer.json' => '{}'], ['--paths=composer.json']],
            'only an unparsable file' => [['app/code/Acme/Broken.php' => "<?php\nclass {"], ['--paths=app/code/Acme/Broken.php']],
            'directory without analyzable files' => [['app/code/Acme/readme.txt' => 'text'], []],
        ];
    }

    /**
     * @dataProvider nothingToAnalyze
     * @param array<string, string> $files
     * @param list<string> $argv
     */
    public function testNothingAnalyzedIsAnErrorNotACleanRun(array $files, array $argv): void
    {
        $project = $this->project($files);

        [$exitCode, , $stderr] = $this->runCommand($project, $argv);

        self::assertSame(Application::EXIT_ERROR, $exitCode);
        self::assertStringContainsString('Nothing to analyze', $stderr);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function formatsWithoutNoticeSummary(): array
    {
        return ['sarif' => ['sarif'], 'checkstyle' => ['checkstyle'], 'github' => ['github']];
    }

    /**
     * @dataProvider formatsWithoutNoticeSummary
     */
    public function testNoticesAreAnnouncedOutsideTableAndJson(string $format): void
    {
        $project = $this->project(['app/code/Acme/Broken.php' => "<?php\nclass {", 'app/code/Acme/Fine.php' => "<?php\n"]);

        [, , $stderr] = $this->runCommand($project, ['--format=' . $format]);

        self::assertStringContainsString('analysis notice(s)', $stderr);
    }

    public function testNoticesAreAnnouncedWhenTheReportGoesToAFile(): void
    {
        $project = $this->project(['app/code/Acme/Broken.php' => "<?php\nclass {", 'app/code/Acme/Fine.php' => "<?php\n"]);

        [, , $stderr] = $this->runCommand($project, ['--output=' . $project . '/report.txt']);

        self::assertStringContainsString('analysis notice(s)', $stderr);
    }

    public function testMissingGeneratedCodeIsANotice(): void
    {
        $project = $this->project(['app/code/Acme/Fine.php' => "<?php\n"]);

        [, $stdout] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame(['generated-code' => 1], (array) $report['summary']['notices']);

        $this->write($project . '/generated/code/Acme/Foo.php', "<?php\n");
        [, $stdout] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame([], (array) $report['summary']['notices']);
    }

    /**
     * @return array<string, array{bool}>
     */
    public static function installerLists(): array
    {
        return ['with this package' => [true], 'without this package' => [false]];
    }

    /**
     * @dataProvider installerLists
     */
    public function testExtensionsOfTheInstallerAreLeftOut(bool $listsThisPackage): void
    {
        $package = (string) realpath(__DIR__ . '/../..');
        $noisyRule = <<<'PHP'
            <?php
            namespace Acme\Noisy;
            /** @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_> */
            final class EveryClassRule implements \PHPStan\Rules\Rule
            {
                public function getNodeType(): string
                {
                    return \PhpParser\Node\Stmt\Class_::class;
                }
                public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope): array
                {
                    return [\PHPStan\Rules\RuleErrorBuilder::message('noisy')->identifier('acme.noisy')->build()];
                }
            }
            PHP;
        $extensionWithRule = "services:\n    -\n        class: Acme\\Noisy\\EveryClassRule\n        tags: [phpstan.rules.rule]\n";
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Block/Foo.php' => self::SESSION_BLOCK,
            'bitexpert/extension.neon' => $extensionWithRule,
            'noisy/EveryClassRule.php' => $noisyRule,
            'noisy/extension.neon' => $extensionWithRule,
        ]);
        // What phpstan/extension-installer generates in a project that has this package, bitexpert/phpstan-magento and another extension.
        $extensions = [
            'acme/noisy' => ['install_path' => $project . '/noisy', 'extra' => ['includes' => ['extension.neon']]],
            'bitexpert/phpstan-magento' => ['install_path' => $project . '/bitexpert', 'extra' => ['includes' => ['extension.neon']]],
        ];
        if ($listsThisPackage) {
            $extensions['silin/magento-perf-check'] = ['install_path' => $package, 'extra' => ['includes' => ['extension.neon']]];
        }
        $this->write($project . '/vendor/phpstan/extension-installer/src/GeneratedConfig.php', sprintf(
            "<?php\nnamespace PHPStan\\ExtensionInstaller;\nfinal class GeneratedConfig\n{\n    public const EXTENSIONS = %s;\n    public const NOT_INSTALLED = [];\n    public const PHPSTAN_VERSION_CONSTRAINT = null;\n}\n",
            var_export($extensions, true),
        ));
        // Loaded on demand, as Composer's autoloader does, and ahead of the tool's own autoloader, as when
        // the tool is installed in the project: the project's installer list is the one PHPStan finds.
        $classes = [
            'PHPStan\\ExtensionInstaller\\GeneratedConfig' => $project . '/vendor/phpstan/extension-installer/src/GeneratedConfig.php',
            'Acme\\Noisy\\EveryClassRule' => $project . '/noisy/EveryClassRule.php',
        ];
        file_put_contents(
            $project . '/vendor/autoload.php',
            sprintf(
                "require_once %s;\nspl_autoload_register(static function (string \$class): void {\n    \$file = %s[\$class] ?? null;\n    if (\$file !== null) {\n        require \$file;\n    }\n}, true, true);\n",
                var_export($package . '/vendor/autoload.php', true),
                var_export($classes, true),
            ),
            FILE_APPEND,
        );

        [$exitCode, $stdout, $stderr] = $this->runCommand($project, ['--format=json']);

        self::assertNotSame(Application::EXIT_ERROR, $exitCode, $stderr);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame(['P-DI-01'], array_column($report['findings'], 'ruleId'));
        self::assertSame(['generated-code' => 1], (array) $report['summary']['notices'], 'neither the other extension nor the project\'s bitexpert/phpstan-magento runs');
        $configs = glob(WorkDirectory::forProject($project) . '/phpstan-*.neon') ?: [];
        self::assertCount(1, $configs);
        $config = (string) file_get_contents($configs[0]);
        self::assertStringContainsString('/extension.neon"', $config);
        self::assertStringContainsString('/extension-experimental.neon"', $config);
    }

    public function testOwnRulesOfBitExpertAreSwitchedOff(): void
    {
        $rule = <<<'PHP'
            <?php
            namespace Acme\BitExpert;
            /** @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_> */
            final class ServiceContractRule implements \PHPStan\Rules\Rule
            {
                public function getNodeType(): string
                {
                    return \PhpParser\Node\Stmt\Class_::class;
                }
                public function processNode(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope): array
                {
                    return [\PHPStan\Rules\RuleErrorBuilder::message('use service contracts')->identifier('acme.serviceContract')->build()];
                }
            }
            PHP;
        // The parameters and switches of bitexpert/phpstan-magento 0.44.0, with one rule standing in for its own rules.
        $neon = <<<'NEON'
            parameters:
                magento:
                    checkCollectionViaFactory: true
                    checkServiceContracts: true
                    checkResourceModelsUsedDirectly: true
                    magentoRoot: %currentWorkingDirectory%
                extdn:
                    setTemplateDisallowedForBlockClasses: true
            parametersSchema:
                magento: structure([
                    checkCollectionViaFactory: bool()
                    checkServiceContracts: bool()
                    checkResourceModelsUsedDirectly: bool()
                    magentoRoot: string()
                ])
                extdn: structure([
                    setTemplateDisallowedForBlockClasses: bool()
                ])
            conditionalTags:
                Acme\BitExpert\ServiceContractRule:
                    phpstan.rules.rule: %magento.checkServiceContracts%
            services:
                -
                    class: Acme\BitExpert\ServiceContractRule

            NEON;
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Block/Foo.php' => self::SESSION_BLOCK,
            'bitexpert/extension.neon' => $neon,
            'bitexpert/ServiceContractRule.php' => $rule,
        ]);
        file_put_contents($project . '/vendor/autoload.php', "require_once __DIR__ . '/../bitexpert/ServiceContractRule.php';\n", FILE_APPEND);
        $runner = new PhpStanRunner(new RuleRegistry(), null, $project . '/bitexpert');

        [$exitCode, $stdout, $stderr] = $this->runCommand($project, ['--format=json'], $runner);

        self::assertNotSame(Application::EXIT_ERROR, $exitCode, $stderr);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame(['P-DI-01'], array_column($report['findings'], 'ruleId'));
        self::assertSame([], (array) $report['summary']['notices'], 'its own rule is off');
        $configs = glob(WorkDirectory::forProject($project) . '/phpstan-*.neon') ?: [];
        self::assertCount(1, $configs);
        $lines = file($configs[0], FILE_IGNORE_NEW_LINES) ?: [];
        self::assertStringContainsString('"' . $project . '/bitexpert/extension.neon"', $lines[0] ?? '');
        $parameters = json_decode(substr($lines[1] ?? '', strlen('parameters: ')), true);
        self::assertIsArray($parameters);
        self::assertSame(['checkCollectionViaFactory' => false, 'checkServiceContracts' => false, 'checkResourceModelsUsedDirectly' => false], $parameters['magento'] ?? null);
        self::assertSame(['setTemplateDisallowedForBlockClasses' => false], $parameters['extdn'] ?? null);
    }

    public function testBitExpertThatDoesNotDeclareItsSwitchesIsLeftOut(): void
    {
        // bitexpert/phpstan-magento 0.30.0 sets two switches that its parametersSchema does not declare, so PHPStan
        // rejects any configuration that includes it ("Unexpected item 'parameters › extdn'").
        $neon = <<<'NEON'
            parameters:
                magento:
                    checkCollectionViaFactory: true
                    checkServiceContracts: true
                    checkResourceModelsUsedDirectly: true
                extdn:
                    setTemplateDisallowedForBlockClasses: true
            parametersSchema:
                magento: structure([
                    checkCollectionViaFactory: bool()
                    checkServiceContracts: bool()
                ])

            NEON;
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Block/Foo.php' => self::SESSION_BLOCK,
            'bitexpert/extension.neon' => $neon,
        ]);
        $runner = new PhpStanRunner(new RuleRegistry(), null, $project . '/bitexpert');

        [$exitCode, $stdout, $stderr] = $this->runCommand($project, ['--format=json'], $runner);

        self::assertNotSame(Application::EXIT_ERROR, $exitCode, $stderr);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame(['P-DI-01'], array_column($report['findings'], 'ruleId'));
        $messages = array_column(array_filter((array) $report['notices'], static fn (array $n): bool => $n['source'] === 'generated-code'), 'message');
        self::assertCount(2, $messages);
        self::assertStringContainsString('does not declare the parameter magento.checkResourceModelsUsedDirectly', $messages[0]);
        $configs = glob(WorkDirectory::forProject($project) . '/phpstan-*.neon') ?: [];
        self::assertCount(1, $configs);
        self::assertStringNotContainsString('bitexpert', (string) file_get_contents($configs[0]));
    }

    public function testEnabledCodingStandardRuleIsInTheReport(): void
    {
        $merge = "<?php\nnamespace Acme\\Demo;\nclass Export\n{\n    public function run(array \$groups): array\n    {\n        \$result = [];\n"
            . "        foreach (\$groups as \$group) {\n            \$result = array_merge(\$result, \$group);\n        }\n        return \$result;\n    }\n}\n";
        $project = $this->project([
            'app/etc/config.php' => "<?php\nreturn ['modules' => ['Acme_Demo' => 1, 'Acme_Off' => 0]];\n",
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Model/Export.php' => $merge,
            'app/code/Acme/Demo/Test/Unit/ExportTest.php' => $merge,
            'app/code/Acme/Off/etc/module.xml' => '<?xml version="1.0"?><config><module name="Acme_Off"/></config>',
            'app/code/Acme/Off/Model/Export.php' => $merge,
        ]);
        $vendor = (string) realpath(__DIR__ . '/../../vendor');
        mkdir($project . '/vendor/magento', 0777, true);
        symlink($vendor . '/squizlabs', $project . '/vendor/squizlabs');
        symlink($vendor . '/magento/magento-coding-standard', $project . '/vendor/magento/magento-coding-standard');

        self::assertSame([], $this->ruleIds($project), 'experimental: off by default');

        file_put_contents($project . '/magento-perf-check.neon', "magentoPerf:\n    rules:\n        S-PERF-01:\n            enabled: true\n            severity: warning\n");
        $found = function (array $argv) use ($project): array {
            [, $stdout] = $this->runCommand($project, ['--format=json', ...$argv]);
            $report = json_decode($stdout, true);
            self::assertIsArray($report);

            return [
                array_map(static fn (array $f): string => sprintf('%s %s:%d %s', $f['ruleId'], $f['file'], $f['line'], $f['severity']), $report['findings']),
                (array) $report['summary']['notices'],
            ];
        };
        self::assertSame([['S-PERF-01 app/code/Acme/Demo/Model/Export.php:9 warning'], ['disabled-module' => 1, 'generated-code' => 1]], $found([]));
        // Without a severity in the configuration, the rule reports a recommendation.
        file_put_contents($project . '/magento-perf-check.neon', "magentoPerf:\n    rules:\n        S-PERF-01:\n            enabled: true\n");
        self::assertSame(['S-PERF-01 app/code/Acme/Demo/Model/Export.php:9 info'], $found([])[0]);
        file_put_contents($project . '/magento-perf-check.neon', "magentoPerf:\n    rules:\n        S-PERF-01:\n            enabled: true\n            severity: warning\n");
        self::assertSame(
            ['S-PERF-01 app/code/Acme/Demo/Model/Export.php:9 warning', 'S-PERF-01 app/code/Acme/Demo/Test/Unit/ExportTest.php:9 warning'],
            $found(['--include-tests'])[0],
        );

        unlink($project . '/vendor/squizlabs');
        self::assertSame([[], ['coding-standard' => 1, 'disabled-module' => 1, 'generated-code' => 1]], $found([]));
    }

    public function testXmlOfASymlinkedModuleIsAnalyzed(): void
    {
        $events = '<?xml version="1.0"?><config><event name="catalog_product_load_after">'
            . '<observer name="a" instance="Acme\Demo\Observer\A"/></event></config>';
        $project = $this->project(['external/Mod/etc/module.xml' => self::MODULE_XML, 'external/Mod/etc/events.xml' => $events]);
        mkdir($project . '/app/code/Ext', 0777, true);
        symlink($project . '/external/Mod', $project . '/app/code/Ext/Mod');

        self::assertContains('X-EVT-01', $this->ruleIds($project));
    }

    public function testOneDirectoryLinkedIntoTwoModulesIsAnalyzedForBoth(): void
    {
        $events = '<?xml version="1.0"?><config><event name="catalog_product_load_after">'
            . '<observer name="a" instance="Acme\Demo\Observer\A"/></event></config>';
        $project = $this->project([
            'shared/etc/events.xml' => $events,
            'app/code/Acme/One/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Two/etc/module.xml' => self::MODULE_XML,
        ]);
        symlink($project . '/shared/etc', $project . '/app/code/Acme/One/etc/frontend');
        symlink($project . '/shared/etc', $project . '/app/code/Acme/Two/etc/frontend');

        self::assertSame(['X-EVT-01', 'X-EVT-01'], $this->ruleIds($project));
    }

    public function testLinkToTheProjectRootDoesNotReachCoreVendor(): void
    {
        $events = '<?xml version="1.0"?><config><event name="catalog_product_load_after">'
            . '<observer name="a" instance="Core\Observer\A"/></event></config>';
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'vendor/magento/module-x/etc/module.xml' => '<?xml version="1.0"?><config><module name="Magento_X"/></config>',
            'vendor/magento/module-x/etc/events.xml' => $events,
        ]);
        symlink($project, $project . '/app/code/Acme/Demo/root');

        self::assertSame([], $this->ruleIds($project));
    }

    public function testPathOfXmlWithoutARuleIsNotAnError(): void
    {
        $project = $this->project(['app/code/Acme/Demo/etc/di.xml' => '<?xml version="1.0"?><config/>', 'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML]);

        foreach ([[], ['--paths=app/code/Acme/Demo/etc/di.xml']] as $argv) {
            [$exitCode, , $stderr] = $this->runCommand($project, $argv);
            self::assertSame(Application::EXIT_CLEAN, $exitCode, $stderr);
        }
    }

    public function testUnparsableXmlIsReportedAsANotice(): void
    {
        $file = 'app/code/Acme/Demo/view/frontend/layout/default.xml';
        $project = $this->project([$file => '<page><broken']);

        [$exitCode, $stdout] = $this->runCommand($project, ['--format=json', '--paths=' . $file]);

        self::assertSame(Application::EXIT_CLEAN, $exitCode);
        self::assertStringContainsString($file, $stdout);
    }

    public function testWholeProjectPathReportsCleanRelativeFiles(): void
    {
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Block/Foo.php' => self::SESSION_BLOCK,
        ]);

        [$exitCode, $stdout, $stderr] = $this->runCommand($project, ['--format=json', '--paths=.']);

        self::assertNotSame(Application::EXIT_ERROR, $exitCode, $stderr);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame(['app/code/Acme/Demo/Block/Foo.php'], array_column($report['findings'], 'file'));
    }

    public function testLayoutRulesFollowHandleIncludes(): void
    {
        $page = static fn (string $inside): string => '<?xml version="1.0"?><page>' . $inside . '</page>';
        $block = '<body><block class="Magento\\Framework\\View\\Element\\Template" name="fee" cacheable="false"/></body>';
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/view/frontend/layout/sales_order_view.xml' => $page('<update handle="acme_fee_total"/>'),
            'app/code/Acme/Demo/view/frontend/layout/acme_fee_total.xml' => $page($block),
            'app/code/Acme/Demo/view/frontend/layout/acme_page_index.xml' => $page($block),
            'magento-perf-check.neon' => "magentoPerf:\n    rules:\n        X-LAY-01:\n            enabled: true\n",
        ]);

        // Only the order page includes acme_fee_total, so no cacheable page uses it.
        [, $stdout] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame(
            ['X-LAY-01 app/code/Acme/Demo/view/frontend/layout/acme_page_index.xml'],
            array_map(static fn (array $f): string => $f['ruleId'] . ' ' . $f['file'], $report['findings']),
        );
    }

    public function testScriptRuleRunsOnlyWhenEnabled(): void
    {
        $script = "define(['jquery', 'jquery/ui'], function () {});\n";
        $project = $this->project([
            'app/etc/config.php' => "<?php\nreturn ['modules' => ['Acme_Demo' => 1, 'Acme_Off' => 0]];\n",
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/view/frontend/web/js/slider.js' => $script,
            'app/code/Acme/Demo/view/frontend/web/js/slider.min.js' => $script,
            'app/code/Acme/Demo/view/frontend/web/node_modules/lib/index.js' => $script,
            'app/code/Acme/Demo/Test/Js/view/frontend/web/js/spec.js' => $script,
            'app/code/Acme/Off/etc/module.xml' => '<?xml version="1.0"?><config><module name="Acme_Off"/></config>',
            'app/code/Acme/Off/view/frontend/web/js/slider.js' => $script,
        ]);

        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            self::markTestSkipped('root reads a file whatever its mode, so an unreadable file cannot be made');
        }
        // A script that cannot be read gives a "script" notice, which shows whether the scripts were read at all.
        $unreadable = $project . '/app/code/Acme/Demo/view/frontend/web/js/locked.js';
        file_put_contents($unreadable, $script);
        chmod($unreadable, 0);
        $scriptNotices = static fn (array $report): array => array_values(array_filter(
            $report['notices'],
            static fn (array $notice): bool => $notice['source'] === 'script',
        ));

        // J-DEP-01 is in the default set. The minified copy, node_modules, test code and the disabled module are left out.
        [, $stdout] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame(
            ['J-DEP-01 app/code/Acme/Demo/view/frontend/web/js/slider.js:1 warning'],
            array_map(static fn (array $f): string => sprintf('%s %s:%d %s', $f['ruleId'], $f['file'], $f['line'], $f['severity']), $report['findings']),
        );
        self::assertCount(1, $scriptNotices($report), 'the scripts are read by default');

        file_put_contents($project . '/magento-perf-check.neon', "magentoPerf:\n    rules:\n        J-DEP-01:\n            enabled: false\n");
        [, $stdout] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        self::assertSame([], $report['findings']);
        self::assertSame([], $scriptNotices($report), 'no script rule enabled: the scripts are not read');
        chmod($unreadable, 0644);
    }

    public function testFileThatCannotBeReflectedDoesNotLoseOtherFindings(): void
    {
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Block/Foo.php' => self::SESSION_BLOCK,
            'app/code/Acme/Demo/Model/Circular.php' => "<?php\nnamespace Acme\\Demo\\Model;\nclass X extends Y\n{\n}\nclass Y extends X\n{\n}\n",
        ]);

        self::assertSame(['P-DI-01'], $this->ruleIds($project));
    }

    public function testUntypedReceiverNoticesFollowTheRule(): void
    {
        $code = "<?php\nnamespace Acme\\Demo;\nclass Foo\n{\n    public function run(object \$x): void\n    {\n        \$x->getFirstItem();\n    }\n}\n";
        $project = $this->project([
            'app/code/Acme/Demo/Foo.php' => $code,
            'off.neon' => "magentoPerf:\n    includeTests: false\n",
            'on.neon' => "magentoPerf:\n    rules:\n        P-COL-02:\n            enabled: true\n",
        ]);
        $notices = function (string $config) use ($project): array {
            [, $stdout] = $this->runCommand($project, ['--format=json', '--config=' . $project . '/' . $config]);
            $report = json_decode($stdout, true);
            self::assertIsArray($report);
            /** @var array{summary: array{notices: array<string, int>}} $report */

            return $report['summary']['notices'];
        };

        self::assertArrayNotHasKey('untyped-receiver', $notices('off.neon'));
        self::assertSame(1, $notices('on.neon')['untyped-receiver'] ?? null);
    }

    public function testUntypedPrivatePropertyGetsItsTypeFromTheConstructor(): void
    {
        $code = <<<'PHP'
            <?php
            namespace Acme\Demo;
            class Model extends \Magento\Framework\Model\AbstractModel
            {
            }
            class Foo
            {
                private $factory;
                public function __construct(ModelFactory $factory)
                {
                    $this->factory = $factory;
                }
                public function run(array $ids): void
                {
                    foreach ($ids as $id) {
                        $this->factory->create()->load($id);
                    }
                }
            }
            PHP;
        $project = $this->project(['app/code/Acme/Demo/Foo.php' => $code]);

        self::assertSame(['P-LOOP-01'], $this->ruleIds($project, []));
    }

    public function testTestPathGivenNextToItsParentIsAnalyzed(): void
    {
        $test = str_replace('Acme\\Demo\\Block', 'Acme\\Demo\\Test\\Unit', self::SESSION_BLOCK);
        $project = $this->project([
            'app/code/Acme/Demo/Test/Unit/Foo.php' => $test,
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
        ]);

        self::assertSame(['P-DI-01'], $this->ruleIds($project, ['--paths=app/code/Acme/Demo,app/code/Acme/Demo/Test/Unit']));
        self::assertSame([], $this->ruleIds($project, ['--paths=app/code/Acme/Demo']));
    }

    public function testStubOfACoreClassInTestCodeDoesNotChangeOtherModules(): void
    {
        $stub = "<?php\nnamespace Magento\\Customer\\Model;\nclass Session\n{\n}\n";
        $project = $this->project([
            'app/code/Acme/Demo/Block/Foo.php' => self::SESSION_BLOCK,
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Other/Test/Unit/_stubs/Magento/Customer/Model/Session.php' => $stub,
        ]);

        self::assertSame(['P-DI-01'], $this->ruleIds($project));
    }

    public function testUnwritableBaselineFileFailsBeforeTheAnalysis(): void
    {
        $project = $this->project(['app/code/Acme/Demo/Foo.php' => "<?php\n"]);
        $runner = new PhpStanRunner(new RuleRegistry(), __DIR__ . '/../Fixtures/Project/crashing-phpstan.php');

        [$exitCode, , $stderr] = $this->runCommand($project, ['--generate-baseline=' . $project . '/missing/b.json'], $runner);

        self::assertSame(Application::EXIT_ERROR, $exitCode);
        self::assertStringContainsString('Cannot write baseline file', $stderr);
        self::assertStringNotContainsString('PHPStan failed', $stderr);
    }

    public function testSymlinkLoopEndsAndFindsTheFileOnce(): void
    {
        $events = '<?xml version="1.0"?><config><event name="catalog_product_load_after">'
            . '<observer name="a" instance="Acme\Demo\Observer\A"/></event></config>';
        $project = $this->project(['app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML, 'app/code/Acme/Demo/etc/events.xml' => $events]);
        symlink($project . '/app/code/Acme', $project . '/app/code/Acme/Demo/loop');

        self::assertSame(['X-EVT-01'], $this->ruleIds($project));
    }

    public function testDisabledModuleIsNotAnalyzed(): void
    {
        $locator = static fn (string $module): string => "<?php\nnamespace Acme\\{$module}\\Model;\nclass Foo\n{\n"
            . "    public function get(): object\n    {\n        return \\Magento\\Framework\\App\\ObjectManager::getInstance()->get(\\stdClass::class);\n    }\n}\n";
        $schema = '<?xml version="1.0"?><schema><table name="acme_old"><column name="id"/>'
            . '<constraint xsi:type="primary" referenceId="PRIMARY"><column name="id"/></constraint></table></schema>';
        $project = $this->project([
            'app/etc/config.php' => "<?php\nreturn array (\n  'modules' => \n  array (\n    'Acme_Old' => 0,\n    'Acme_Gone' => 0,\n    'Acme_Demo' => 1,\n  ),\n);\n",
            'app/code/Acme/Old/etc/module.xml' => '<?xml version="1.0"?><config><module name="Acme_Old"/></config>',
            'app/code/Acme/Old/etc/db_schema.xml' => $schema,
            'app/code/Acme/Old/Model/Foo.php' => $locator('Old'),
            'app/code/Acme/OldNew/etc/module.xml' => '<?xml version="1.0"?><config><module name="Acme_OldNew"/></config>',
            'app/code/Acme/OldNew/Model/Foo.php' => $locator('OldNew'),
            // A Composer path repository: vendor/acme/module-gone is a symlink to packages/acme/module-gone.
            'packages/acme/module-gone/etc/module.xml' => '<?xml version="1.0"?><config><module name="Acme_Gone"/></config>',
            'packages/acme/module-gone/Model/Foo.php' => $locator('Gone'),
            'vendor/composer/installed.json' => '{"packages": [{"name": "acme/module-gone", "install-path": "../acme/module-gone"}]}',
        ]);
        mkdir($project . '/vendor/acme');
        symlink('../../packages/acme/module-gone', $project . '/vendor/acme/module-gone');

        [, $stdout, $stderr] = $this->runCommand($project, ['--format=json', '--paths=app/code,vendor/acme/module-gone']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report, $stderr);
        /** @var array{findings: list<array{ruleId: string, file: string}>, notices: list<array{source: string, message: string, file: string}>} $report */

        // Acme_OldNew is not in the list yet, so it counts as enabled; its path starts like Acme/Old.
        // The table of Acme_Old has a primary key: leaving the module out must not turn it into an X-DB-01 finding.
        self::assertSame(['P-DI-06 app/code/Acme/OldNew/Model/Foo.php'], array_map(static fn (array $f): string => $f['ruleId'] . ' ' . $f['file'], $report['findings']));
        $disabled = array_values(array_filter($report['notices'], static fn (array $n): bool => $n['source'] === 'disabled-module'));
        self::assertSame([
            ['app/code/Acme/Old', 'Acme_Old is disabled in app/etc/config.php and was not analyzed.'],
            ['vendor/acme/module-gone', 'Acme_Gone is disabled in app/etc/config.php and was not analyzed.'],
        ], array_map(static fn (array $n): array => [$n['file'], $n['message']], $disabled));

        // The package seen both where Composer installed it and where it really is gives one notice.
        [, $stdout] = $this->runCommand($project, ['--format=json', '--paths=packages,vendor/acme/module-gone']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        /** @var array{notices: list<array{source: string, message: string}>} $report */
        self::assertCount(1, array_filter($report['notices'], static fn (array $n): bool => str_starts_with($n['message'], 'Acme_Gone ')));

        // Only a disabled module asked for: a clean run that says why, not "nothing to analyze".
        [$exitCode, $stdout] = $this->runCommand($project, ['--format=json', '--paths=app/code/Acme/Old']);
        self::assertSame(Application::EXIT_CLEAN, $exitCode, $stdout);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        /** @var array{notices: list<array{source: string}>} $report */
        self::assertContains('disabled-module', array_column($report['notices'], 'source'));

        // A disabled module outside the analyzed paths is not mentioned.
        [, $stdout] = $this->runCommand($project, ['--format=json', '--paths=app/code/Acme/OldNew']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report);
        /** @var array{notices: list<array{source: string}>} $report */
        self::assertNotContains('disabled-module', array_column($report['notices'], 'source'));
    }

    public function testProxyConfiguredInADisabledModuleDoesNotCount(): void
    {
        $files = [
            'app/code/Acme/Demo/Block/Foo.php' => self::SESSION_BLOCK,
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Old/etc/module.xml' => '<?xml version="1.0"?><config><module name="Acme_Old"/></config>',
            'app/code/Acme/Old/etc/di.xml' => self::PROXY_DI,
        ];
        $enabled = "<?php\nreturn ['modules' => ['Acme_Demo' => 1, 'Acme_Old' => 1]];\n";

        self::assertSame([], $this->ruleIds($this->project(['app/etc/config.php' => $enabled, ...$files])));
        self::assertSame(['P-DI-01'], $this->ruleIds($this->project(['app/etc/config.php' => str_replace("'Acme_Old' => 1", "'Acme_Old' => 0", $enabled), ...$files])));
    }

    public function testTraitCodeIsReportedOnceWhateverUsesIt(): void
    {
        $trait = <<<'PHP'
            <?php
            namespace Acme\Demo\Block;
            trait Shared
            {
                public function logger(): object
                {
                    return \Magento\Framework\App\ObjectManager::getInstance()->get(\stdClass::class);
                }

                /** @param list<int> $ids */
                public function loadAll(\Magento\Catalog\Model\Product $product, array $ids): void
                {
                    foreach ($ids as $id) {
                        $product->load($id);
                    }
                }
            }
            PHP;
        $user = static fn (string $name): string => "<?php\nnamespace Acme\\Demo\\Block;\nclass {$name}\n{\n    use Shared;\n}\n";
        $project = $this->project([
            'app/code/Acme/Demo/Block/Shared.php' => $trait,
            'app/code/Acme/Demo/Block/First.php' => $user('First'),
            'app/code/Acme/Demo/Block/Second.php' => $user('Second'),
        ]);

        [, $stdout, $stderr] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report, $stderr);
        /** @var array{findings: list<array{ruleId: string, file: string, line: int, message: string}>} $report */

        self::assertSame(
            ['P-DI-06 app/code/Acme/Demo/Block/Shared.php:7', 'P-LOOP-01 app/code/Acme/Demo/Block/Shared.php:14'],
            array_map(static fn (array $f): string => sprintf('%s %s:%d', $f['ruleId'], $f['file'], $f['line']), $report['findings']),
        );
        self::assertStringContainsString('in Acme\Demo\Block\Shared.', $report['findings'][0]['message']);
    }

    public function testTraitFindingThatNamesItsClassIsKeptPerClass(): void
    {
        $trait = <<<'PHP'
            <?php
            namespace Acme\Demo\Block;
            trait Shared
            {
                public function __construct(private \Magento\Framework\Event\ManagerInterface $events)
                {
                    $this->events->dispatch('acme_created');
                }
            }
            PHP;
        $user = static fn (string $name): string => "<?php\nnamespace Acme\\Demo\\Block;\nclass {$name}\n{\n    use Shared;\n}\n";
        $project = $this->project([
            'app/code/Acme/Demo/Block/Shared.php' => $trait,
            'app/code/Acme/Demo/Block/First.php' => $user('First'),
            'app/code/Acme/Demo/Block/Second.php' => $user('Second'),
        ]);

        [, $stdout, $stderr] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report, $stderr);
        /** @var array{findings: list<array{ruleId: string, message: string}>} $report */
        $named = array_map(static fn (array $f): string => $f['ruleId'] . ' ' . (preg_match('/constructor of (\S+):/', $f['message'], $m) === 1 ? $m[1] : '?'), $report['findings']);
        sort($named);

        // Each message names its class, so each is its own finding and stays the same when another class starts to use the trait.
        self::assertSame(['P-DI-03 Acme\Demo\Block\First', 'P-DI-03 Acme\Demo\Block\Second'], $named);
    }

    public function testTraitFindingOfOneUserOnlyIsKept(): void
    {
        $trait = <<<'PHP'
            <?php
            namespace Acme\Demo\Block;
            trait Shared
            {
                /** @param list<int> $ids */
                public function loadAll(\Magento\Catalog\Model\Product $product, array $ids): void
                {
                    foreach ($ids as $id) {
                        $product->load($id);
                        $this->model->load($id);
                    }
                }
            }
            PHP;
        $user = static fn (string $name, string $type): string => "<?php\nnamespace Acme\\Demo\\Block;\nclass {$name}\n{\n"
            . "    use Shared;\n    public function __construct(private {$type} \$model)\n    {\n    }\n}\n";
        $project = $this->project([
            'app/code/Acme/Demo/Block/Shared.php' => $trait,
            'app/code/Acme/Demo/Block/Plain.php' => "<?php\nnamespace Acme\\Demo\\Block;\nclass Plain\n{\n    public function load(int \$id): void\n    {\n    }\n}\n",
            'app/code/Acme/Demo/Block/Alpha.php' => $user('Alpha', 'Plain'),
            'app/code/Acme/Demo/Block/Beta.php' => $user('Beta', '\Magento\Catalog\Model\Product'),
        ]);

        [, $stdout, $stderr] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report, $stderr);
        /** @var array{findings: list<array{ruleId: string, file: string, line: int}>} $report */

        self::assertSame(
            ['P-LOOP-01 app/code/Acme/Demo/Block/Shared.php:9', 'P-LOOP-01 app/code/Acme/Demo/Block/Shared.php:10'],
            array_map(static fn (array $f): string => sprintf('%s %s:%d', $f['ruleId'], $f['file'], $f['line']), $report['findings']),
        );
    }

    public function testTraitFindingsOfTwoRulesOnOneLineAreKept(): void
    {
        $trait = <<<'PHP'
            <?php
            namespace Acme\Demo\Block;
            trait Shared
            {
                /** @param list<int> $ids */
                public function loadAll(array $ids): void
                {
                    foreach ($ids as $id) {
                        $this->model->load($id); \Magento\Framework\App\ObjectManager::getInstance();
                    }
                }
            }
            PHP;
        $user = static fn (string $name, string $type): string => "<?php\nnamespace Acme\\Demo\\Block;\nclass {$name}\n{\n"
            . "    use Shared;\n    public function __construct(private {$type} \$model)\n    {\n    }\n}\n";
        $project = $this->project([
            'app/code/Acme/Demo/Block/Shared.php' => $trait,
            'app/code/Acme/Demo/Block/Plain.php' => "<?php\nnamespace Acme\\Demo\\Block;\nclass Plain\n{\n    public function load(int \$id): void\n    {\n    }\n}\n",
            // A factory leaves out P-DI-06, a plain model leaves out P-LOOP-01: each rule fires in one class only.
            'app/code/Acme/Demo/Block/AlphaFactory.php' => $user('AlphaFactory', '\Magento\Catalog\Model\Product'),
            'app/code/Acme/Demo/Block/Beta.php' => $user('Beta', 'Plain'),
        ]);

        [, $stdout, $stderr] = $this->runCommand($project, ['--format=json']);
        $report = json_decode($stdout, true);
        self::assertIsArray($report, $stderr);
        /** @var array{findings: list<array{ruleId: string, file: string, line: int}>} $report */
        $found = array_map(static fn (array $f): string => sprintf('%s %s:%d', $f['ruleId'], $f['file'], $f['line']), $report['findings']);
        sort($found);

        self::assertSame(['P-DI-06 app/code/Acme/Demo/Block/Shared.php:9', 'P-LOOP-01 app/code/Acme/Demo/Block/Shared.php:9'], $found);
    }

    public function testWebapiInputTypeFollowsAChangedServiceInterface(): void
    {
        $service = static fn (string $parameter): string => "<?php\nnamespace Acme\\Demo\\Api;\ninterface SearchInterface\n{\n"
            . "    /**\n     * @param {$parameter}\n     * @return int\n     */\n    public function search({$parameter}): int;\n}\n";
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/etc/webapi.xml' => '<?xml version="1.0"?><routes><route url="/V1/acme/search" method="POST">'
                . '<service class="Acme\Demo\Api\SearchInterface" method="search"/></route></routes>',
            'app/code/Acme/Demo/etc/di.xml' => '<?xml version="1.0"?><config>'
                . '<preference for="Acme\Demo\Api\Data\RequestInterface" type="Acme\Demo\Model\Request"/></config>',
            'app/code/Acme/Demo/Api/SearchInterface.php' => $service('string $query'),
            'app/code/Acme/Demo/Api/Data/RequestInterface.php' => "<?php\nnamespace Acme\\Demo\\Api\\Data;\ninterface RequestInterface\n{\n"
                . "    public function getPoint(): \\stdClass;\n}\n",
            'app/code/Acme/Demo/Model/Request.php' => "<?php\nnamespace Acme\\Demo\\Model;\nclass Request implements \\Acme\\Demo\\Api\\Data\\RequestInterface\n{\n"
                . "    public function __construct(private \\stdClass \$point)\n    {\n    }\n\n"
                . "    public function getPoint(): \\stdClass\n    {\n        return \$this->point;\n    }\n}\n",
            'on.neon' => "magentoPerf:\n    rules:\n        P-API-01:\n            enabled: true\n",
        ]);
        $config = '--config=' . $project . '/on.neon';

        self::assertSame([], $this->ruleIds($project, [$config]), 'RequestInterface is not an input type yet');

        // Only the service interface changes; PHPStan's result cache keeps the result of Request.php.
        file_put_contents($project . '/app/code/Acme/Demo/Api/SearchInterface.php', $service('\Acme\Demo\Api\Data\RequestInterface $request'));
        self::assertSame(['P-API-01'], $this->ruleIds($project, [$config]));
        self::assertSame([], $this->ruleIds($project), 'experimental: off by default');
    }

    public function testLoopCallFollowsAChangedCalledMethod(): void
    {
        $reader = static fn (string $body): string => "<?php\nnamespace Acme\\Demo\\Model;\nclass Reader\n{\n"
            . "    public function __construct(private \\Magento\\Catalog\\Api\\ProductRepositoryInterface \$repository)\n    {\n    }\n\n"
            . "    public function name(int \$id): string\n    {\n        {$body}\n    }\n}\n";
        $project = $this->project([
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Model/Reader.php' => $reader("return (string) \$id;"),
            'app/code/Acme/Demo/Model/Listing.php' => "<?php\nnamespace Acme\\Demo\\Model;\nclass Listing\n{\n"
                . "    public function __construct(private Reader \$reader)\n    {\n    }\n\n"
                . "    /** @param list<int> \$ids */\n    public function names(array \$ids): array\n    {\n        \$names = [];\n"
                . "        foreach (\$ids as \$id) {\n            \$names[] = \$this->reader->name(\$id);\n        }\n\n        return \$names;\n    }\n}\n",
            'on.neon' => "magentoPerf:\n    rules:\n        P-LOOP-12:\n            enabled: true\n",
        ]);
        $config = '--config=' . $project . '/on.neon';

        self::assertSame([], $this->ruleIds($project, [$config]), 'name() does not query yet');

        // Only Reader.php changes, and the finding is in Listing.php: the report must follow the called method.
        file_put_contents($project . '/app/code/Acme/Demo/Model/Reader.php', $reader("return (string) \$this->repository->getById(\$id)->getName();"));
        self::assertSame(['P-LOOP-12'], $this->ruleIds($project, [$config]));
        self::assertSame([], $this->ruleIds($project), 'experimental: off by default');
    }

    public function testUpgradeRulesFollowTheInstalledAndTheTargetVersion(): void
    {
        $stubs = (string) realpath(__DIR__ . '/../PhpStan/Stubs');
        $project = $this->project([
            'vendor/autoload.php' => sprintf("<?php\nrequire_once %s;\nrequire_once %s;\n", var_export($stubs . '/framework.php', true), var_export($stubs . '/fpc.php', true)),
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Console/Sync.php' => "<?php\nnamespace Acme\\Demo\\Console;\nclass Sync extends \\Symfony\\Component\\Console\\Command\\Command\n{\n"
                . "    public function execute(\\Symfony\\Component\\Console\\Input\\InputInterface \$input, \\Symfony\\Component\\Console\\Output\\OutputInterface \$output)\n    {\n        return 0;\n    }\n}\n",
            'on.neon' => "magentoPerf:\n    rules:\n        U-CLI-01:\n            enabled: true\n",
        ]);
        $config = '--config=' . $project . '/on.neon';
        $notices = function (array $argv) use ($project): array {
            [, $stdout] = $this->runCommand($project, ['--format=json', ...$argv]);
            $report = json_decode($stdout, true);
            self::assertIsArray($report);

            return array_column($report['notices'] ?? [], 'source');
        };

        // No magento2-base: the version is unknown, and a notice says why the rule did not run.
        self::assertSame([], $this->ruleIds($project, [$config]));
        self::assertContains('upgrade-version', $notices([$config]));
        self::assertNotContains('upgrade-version', $notices([]), 'no notice while the rule is off');
        self::assertSame(['U-CLI-01'], $this->ruleIds($project, [$config, '--target-version=2.4.9']));

        $this->write($project . '/vendor/magento/magento2-base/composer.json', '{"name": "magento/magento2-base", "version": "2.4.8-p2"}');
        self::assertSame([], $this->ruleIds($project, [$config]), 'the installed 2.4.8 does not have the change');
        self::assertContains('upgrade-version', $notices([$config]), 'the skipped rule is named');
        self::assertNotContains('upgrade-version', $notices([$config, '--target-version=2.4.9']));
        self::assertSame(['U-CLI-01'], $this->ruleIds($project, [$config, '--target-version=2.4.9']));

        $this->write($project . '/vendor/magento/magento2-base/composer.json', '{"name": "magento/magento2-base", "version": "2.4.9"}');
        self::assertSame(['U-CLI-01'], $this->ruleIds($project, [$config]), 'already broken on the installed 2.4.9');
        self::assertSame(['U-CLI-01'], $this->ruleIds($project, [$config, '--target-version=2.4.8']), 'a lower target hides nothing');
        self::assertSame([], $this->ruleIds($project), 'experimental: off by default');
        self::assertNotContains('upgrade-version', $notices([]), 'no notice while the rule is off');
    }

    public function testDefaultUpgradeRuleRunsByVersionWithoutNotices(): void
    {
        $stubs = (string) realpath(__DIR__ . '/../PhpStan/Stubs');
        $project = $this->project([
            'vendor/autoload.php' => sprintf("<?php\nrequire_once %s;\nrequire_once %s;\n", var_export($stubs . '/framework.php', true), var_export($stubs . '/fpc.php', true)),
            'app/code/Acme/Demo/etc/module.xml' => self::MODULE_XML,
            'app/code/Acme/Demo/Console/Sync.php' => "<?php\nnamespace Acme\\Demo\\Console;\nclass Sync extends \\Symfony\\Component\\Console\\Command\\Command\n{\n"
                . "    protected function execute(\\Symfony\\Component\\Console\\Input\\InputInterface \$input, \\Symfony\\Component\\Console\\Output\\OutputInterface \$output)\n    {\n    }\n}\n",
            'vendor/magento/magento2-base/composer.json' => '{"name": "magento/magento2-base", "version": "2.4.5-p10"}',
        ]);
        $notices = function (array $argv) use ($project): array {
            [, $stdout] = $this->runCommand($project, ['--format=json', ...$argv]);
            $report = json_decode($stdout, true);
            self::assertIsArray($report);

            return array_column($report['notices'] ?? [], 'source');
        };

        // U-CLI-02 is in the default set: no configuration is needed, and it follows the version.
        self::assertSame([], $this->ruleIds($project), 'the installed 2.4.5-p10 still has Symfony Console 4.4');
        self::assertNotContains('upgrade-version', $notices([]), 'a default rule that does not run is not named');
        self::assertSame(['U-CLI-02'], $this->ruleIds($project, ['--target-version=2.4.6']));

        $this->write($project . '/vendor/magento/magento2-base/composer.json', '{"name": "magento/magento2-base", "version": "2.4.5-p11"}');
        self::assertSame(['U-CLI-02'], $this->ruleIds($project), 'the installed 2.4.5-p11 has Symfony Console 5.4');
    }
}
