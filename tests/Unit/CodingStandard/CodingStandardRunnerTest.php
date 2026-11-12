<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\CodingStandard;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Silin\MagentoPerfCheck\CodingStandard\CodingStandardRunner;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Report\Notice;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use SplFileInfo;

/**
 * Runs the real phpcs and magento-coding-standard of this package's dev dependencies, linked into
 * a project whose path has a "+", which phpcs would read as a regular expression in --ignore.
 */
final class CodingStandardRunnerTest extends TestCase
{
    private const SLOW = <<<'PHP'
        <?php
        namespace Acme\Demo\Model;
        class Export
        {
            public function run(array $groups): array
            {
                $result = [];
                foreach ($groups as $group) {
                    $result = array_merge($result, $group);
                }
                sleep(1);
                Sleep(1);
                time_nanosleep(0, 10);
                session_start();
                return is_null($result) ? [] : $result;
            }
        }
        PHP;

    private const MERGE_IN_LOOP = <<<'PHP'
        <?php
        namespace Acme\Demo\Test\Unit;
        class ExportTest
        {
            public function data(array $groups): array
            {
                $result = [];
                foreach ($groups as $group) {
                    $result = array_merge($result, $group);
                }
                return $result;
            }
        }
        PHP;

    private string $project;

    protected function setUp(): void
    {
        $this->project = sys_get_temp_dir() . '/mpc-cs+' . bin2hex(random_bytes(6));
        $files = [
            'app/code/Acme/Demo/Model/Export.php' => self::SLOW,
            'app/code/Acme/Demo/Test/Unit/ExportTest.php' => self::MERGE_IN_LOOP,
            'app/code/Acme/Demo/view/frontend/templates/title.phtml' => '<?= $this->escapeHtml($this->getTitle()) ?>' . "\n",
            'external/Mod/Model/Export.php' => self::SLOW,
            'external/Mod/Test/Unit/ExportTest.php' => self::MERGE_IN_LOOP,
        ];
        foreach ($files as $file => $content) {
            if (!is_dir(dirname($this->project . '/' . $file))) {
                mkdir(dirname($this->project . '/' . $file), 0777, true);
            }
            file_put_contents($this->project . '/' . $file, $content);
        }
        mkdir($this->project . '/app/code/Ext');
        symlink($this->project . '/external/Mod', $this->project . '/app/code/Ext/Mod');
        symlink((string) realpath(__DIR__ . '/../../../vendor'), $this->project . '/vendor');
        $this->project = (string) realpath($this->project);
    }

    protected function tearDown(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->project, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var SplFileInfo $entry */
        foreach ($iterator as $entry) {
            $entry->isDir() && !$entry->isLink() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($this->project);
    }

    /**
     * @param list<string> $ruleIds
     * @param list<string> $paths
     * @param list<string> $excludedDirs
     * @return array{list<string>, list<Notice>}
     */
    private function runSniffs(array $ruleIds, array $paths = ['app/code'], bool $includeTests = false, array $excludedDirs = []): array
    {
        [$findings, $notices] = (new CodingStandardRunner(new RuleRegistry()))->run($this->project, $ruleIds, $paths, $includeTests, $excludedDirs);

        $lines = array_map(static fn (Finding $f): string => sprintf('%s %s:%d', $f->ruleId, $f->file, $f->line), $findings);
        sort($lines);

        return [$lines, $notices];
    }

    public function testEnabledSniffsBecomeFindingsOfTheirRules(): void
    {
        [$findings, $notices] = $this->runSniffs(['S-PERF-01', 'S-FN-01', 'P-LOOP-01'], ['app/code/Acme']);

        self::assertSame([], $notices);
        self::assertSame([
            'S-FN-01 app/code/Acme/Demo/Model/Export.php:11',
            'S-FN-01 app/code/Acme/Demo/Model/Export.php:12',
            'S-FN-01 app/code/Acme/Demo/Model/Export.php:13',
            'S-FN-01 app/code/Acme/Demo/Model/Export.php:14',
            'S-PERF-01 app/code/Acme/Demo/Model/Export.php:9',
        ], $findings, 'is_null() is left out, and test code too');
    }

    public function testFindingsKeepTheSeverityAndNameTheSniff(): void
    {
        [$findings] = (new CodingStandardRunner(new RuleRegistry()))->run($this->project, ['S-PERF-01'], ['app/code/Acme'], false, []);

        self::assertCount(1, $findings);
        self::assertSame('info', $findings[0]->severity->value);
        self::assertStringStartsWith('[S-PERF-01] ', $findings[0]->message);
        self::assertStringEndsWith('(Magento2.Performance.ForeachArrayMerge)', $findings[0]->message);
    }

    public function testSymlinkedModuleKeepsItsAnalyzedPathAndLeavesTestCodeOut(): void
    {
        [$findings] = $this->runSniffs(['S-PERF-01']);

        self::assertSame([
            'S-PERF-01 app/code/Acme/Demo/Model/Export.php:9',
            'S-PERF-01 app/code/Ext/Mod/Model/Export.php:9',
        ], $findings);
    }

    public function testTwoFindingsOnOneLineAreBothKept(): void
    {
        self::assertSame([
            'S-TPL-01 app/code/Acme/Demo/view/frontend/templates/title.phtml:1',
            'S-TPL-01 app/code/Acme/Demo/view/frontend/templates/title.phtml:1',
        ], $this->runSniffs(['S-TPL-01'], ['app/code/Acme'])[0]);
    }

    public function testSymlinkedModuleGivenAsThePathKeepsThatPath(): void
    {
        self::assertSame(['S-PERF-01 app/code/Ext/Mod/Model/Export.php:9'], $this->runSniffs(['S-PERF-01'], ['app/code/Ext/Mod'])[0]);
    }

    public function testFileReachedThroughTwoPathsIsReportedOnce(): void
    {
        self::assertSame([
            'S-PERF-01 app/code/Acme/Demo/Model/Export.php:9',
            'S-PERF-01 app/code/Ext/Mod/Model/Export.php:9',
        ], $this->runSniffs(['S-PERF-01'], ['app/code', 'app/code/Ext/Mod'])[0]);
    }

    public function testProjectInsideAnExcludedDirectoryOfTheRulesetIsANotice(): void
    {
        $parent = sys_get_temp_dir() . '/mpc-cs-' . bin2hex(random_bytes(6));
        mkdir($parent . '/lib/shop/app/code/Acme', 0777, true);
        file_put_contents($parent . '/lib/shop/app/code/Acme/Export.php', self::SLOW);
        symlink((string) realpath(__DIR__ . '/../../../vendor'), $parent . '/lib/shop/vendor');
        $root = (string) realpath($parent . '/lib/shop');
        try {
            [$findings, $notices] = (new CodingStandardRunner(new RuleRegistry()))->run($root, ['S-FN-01', 'S-PERF-01'], ['app/code'], false, []);
        } finally {
            unlink($parent . '/lib/shop/vendor');
            unlink($parent . '/lib/shop/app/code/Acme/Export.php');
            foreach (['/lib/shop/app/code/Acme', '/lib/shop/app/code', '/lib/shop/app', '/lib/shop', '/lib', ''] as $dir) {
                rmdir($parent . $dir);
            }
        }

        self::assertSame(['S-PERF-01'], array_map(static fn (Finding $f): string => $f->ruleId, $findings));
        self::assertCount(1, $notices);
        self::assertStringContainsString('matches an exclude pattern', $notices[0]->message);
        self::assertStringContainsString('S-FN-01', $notices[0]->message);
    }

    public function testTestCodeIsAnalyzedOnRequestOrWhenGivenAsAPath(): void
    {
        self::assertContains('S-PERF-01 app/code/Acme/Demo/Test/Unit/ExportTest.php:9', $this->runSniffs(['S-PERF-01'], ['app/code/Acme'], true)[0]);
        self::assertContains('S-PERF-01 app/code/Acme/Demo/Test/Unit/ExportTest.php:9', $this->runSniffs(['S-PERF-01'], ['app/code/Acme', 'app/code/Acme/Demo/Test'])[0]);
    }

    public function testExcludedDirectoryIsLeftOut(): void
    {
        [$findings] = $this->runSniffs(['S-PERF-01'], ['app/code'], false, [$this->project . '/external/Mod', $this->project . '/app/code/Ext/Mod']);

        self::assertSame(['S-PERF-01 app/code/Acme/Demo/Model/Export.php:9'], $findings);
    }

    public function testPhpcsDoesNotRunWhenNoRuleOfTheGroupIsEnabled(): void
    {
        unlink($this->project . '/vendor');
        mkdir($this->project . '/vendor');

        self::assertSame([[], []], $this->runSniffs(['P-LOOP-01']));
        rmdir($this->project . '/vendor');
        symlink((string) realpath(__DIR__ . '/../../../vendor'), $this->project . '/vendor');
    }

    public function testMissingCodingStandardIsANotice(): void
    {
        unlink($this->project . '/vendor');
        mkdir($this->project . '/vendor');

        [$findings, $notices] = $this->runSniffs(['S-PERF-01']);

        self::assertSame([], $findings);
        self::assertCount(1, $notices);
        self::assertSame(Notice::SOURCE_CODING_STANDARD, $notices[0]->source);
        self::assertStringContainsString('magento/magento-coding-standard', $notices[0]->message);
    }

    /**
     * @param array<string, string> $files
     */
    private function fakeVendor(array $files): void
    {
        unlink($this->project . '/vendor');
        foreach ([CodingStandardRunner::PHPCS => "<?php\n", CodingStandardRunner::STANDARD . '/ruleset.xml' => '<ruleset/>', ...$files] as $file => $content) {
            if (!is_dir(dirname($this->project . '/' . $file))) {
                mkdir(dirname($this->project . '/' . $file), 0777, true);
            }
            file_put_contents($this->project . '/' . $file, $content);
        }
    }

    public function testMissingPhpcsIsANotice(): void
    {
        $this->fakeVendor([]);
        unlink($this->project . '/' . CodingStandardRunner::PHPCS);

        [$findings, $notices] = $this->runSniffs(['S-PERF-01']);

        self::assertSame([], $findings);
        self::assertCount(1, $notices);
        self::assertStringContainsString(CodingStandardRunner::PHPCS . ' is missing', $notices[0]->message);
    }

    public function testSniffMissingFromTheInstalledVersionIsANotice(): void
    {
        $this->fakeVendor([]);

        [$findings, $notices] = $this->runSniffs(['S-TPL-02']);

        self::assertSame([], $findings);
        self::assertCount(1, $notices);
        self::assertStringContainsString('has no Magento2.Templates.ObjectManager sniff', $notices[0]->message);
    }

    public function testPhpcsWithoutAJsonReportIsANotice(): void
    {
        $this->fakeVendor([
            CodingStandardRunner::STANDARD . '/Sniffs/Performance/ForeachArrayMergeSniff.php' => "<?php\n",
            CodingStandardRunner::PHPCS => "<?php\nfwrite(STDERR, 'ERROR: the \"Magento2\" coding standard is not installed.');\nexit(3);\n",
        ]);

        [$findings, $notices] = $this->runSniffs(['S-PERF-01']);

        self::assertSame([], $findings);
        self::assertCount(1, $notices);
        self::assertStringContainsString('coding standard is not installed', $notices[0]->message);
    }

    public function testInternalPhpcsErrorIsANotice(): void
    {
        $report = json_encode(['files' => [$this->project . '/app/code/Acme/Demo/Model/Export.php' => ['messages' => [
            ['message' => 'An error occurred during processing; checking has been aborted.', 'source' => 'Internal.Exception', 'line' => 1],
        ]]]]);
        $this->fakeVendor([
            CodingStandardRunner::STANDARD . '/Sniffs/Performance/ForeachArrayMergeSniff.php' => "<?php\n",
            CodingStandardRunner::PHPCS => "<?php\necho " . var_export($report, true) . ";\n",
        ]);

        [$findings, $notices] = $this->runSniffs(['S-PERF-01']);

        self::assertSame([], $findings);
        self::assertCount(1, $notices);
        self::assertSame('app/code/Acme/Demo/Model/Export.php', $notices[0]->file);
        self::assertStringContainsString('checking has been aborted', $notices[0]->message);
    }
}
