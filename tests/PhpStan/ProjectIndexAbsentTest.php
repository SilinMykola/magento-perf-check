<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<IndexReportingRule>
 */
final class ProjectIndexAbsentTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new IndexReportingRule(self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/Fixtures/index/no-magento.neon'];
    }

    public function testDirectoryWithoutMagentoGivesAnEmptyIndex(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/index/anything.php'], [
            ['modules: 0, banner proxy: no', 1],
        ]);
    }
}
