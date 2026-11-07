<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\UnpagedBatchCollectionRule;

/**
 * @extends RuleTestCase<UnpagedBatchCollectionRule>
 */
final class UnpagedBatchCollectionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new UnpagedBatchCollectionRule($this->createReflectionProvider(), self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $message = '[P-COL-04] Magento\Catalog\Model\ResourceModel\Product\Collection is iterated without a page size in a batch job: all matching rows are held in memory at once. '
            . 'Walk it in pages with setPageSize() and setCurPage().';

        $this->analyse([__DIR__ . '/../Fixtures/command-and-batch.php'], [
            [$message, 26],
            [$message, 70],
            [$message, 73],
            [$message, 136],
            [$message, 163],
            [$message, 183],
        ]);
    }
}
