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
}
