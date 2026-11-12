<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Project;

use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Project\MergeJsFilesRule;
use Silin\MagentoPerfCheck\Report\Finding;

final class MergeJsFilesRuleTest extends TestCase
{
    private const PROJECT = __DIR__ . '/../../Fixtures/Rules/Settings/project';

    public function testReportsTrueValuesInTheScopesOfTheSystemSection(): void
    {
        $findings = (new MergeJsFilesRule())->check(self::PROJECT);

        self::assertSame(
            [
                'C-02 warning app/etc/config.php:26 the default scope',
                'C-02 warning app/etc/config.php:62 the website "outlet"',
                'C-02 warning app/etc/config.php:71 the store view "en"',
            ],
            array_map(static function (Finding $finding): string {
                preg_match('/is on for (.+?)\. /', $finding->message, $m);

                return sprintf('%s %s %s:%d %s', $finding->ruleId, $finding->severity->value, $finding->file, $finding->line, $m[1] ?? '');
            }, $findings),
        );
        self::assertStringStartsWith('[C-02] Merge JavaScript Files (dev/js/merge_files) is on', $findings[0]->message);
    }

    public function testProjectWithoutConfigPhp(): void
    {
        self::assertSame([], (new MergeJsFilesRule())->check(__DIR__ . '/../../Fixtures/Rules/Settings/missing'));
    }
}
