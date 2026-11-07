<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\GetFirstItemWithoutLimitRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<GetFirstItemWithoutLimitRule>
 */
final class GetFirstItemWithoutLimitRuleTest extends RuleTestCase
{
    private bool $reportUntyped = false;

    protected function getRule(): Rule
    {
        return new GetFirstItemWithoutLimitRule($this->createReflectionProvider(), $this->reportUntyped);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testReportsOnlyUnlimitedDbCollections(): void
    {
        $message = '[P-COL-02] Magento\Catalog\Model\ResourceModel\Product\Collection::getFirstItem() without setPageSize(): '
            . 'the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().';

        $this->analyse([__DIR__ . '/../Fixtures/get-first-item.php'], [
            [$message, 21],
            [$message, 26],
            [$message, 32],
            [$message, 37],
            [$message, 118],
            [$message, 133],
            [$message, 167],
            [$message, 174],
            [$message, 190],
            [$message, 197],
            [$message, 218],
            // Only the first of two: it loads the rows.
            [$message, 224],
        ]);
    }

    public function testCountsUntypedReceiversWhenEnabled(): void
    {
        $this->reportUntyped = true;
        $message = '[P-COL-02] Magento\Catalog\Model\ResourceModel\Product\Collection::getFirstItem() without setPageSize(): '
            . 'the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().';

        $this->analyse([__DIR__ . '/../Fixtures/get-first-item.php'], [
            [$message, 21],
            [$message, 26],
            [$message, 32],
            [$message, 37],
            ['[notice:P-COL-02] getFirstItem() on a receiver of unknown type', 75],
            [$message, 118],
            [$message, 133],
            [$message, 167],
            [$message, 174],
            [$message, 190],
            [$message, 197],
            [$message, 218],
            // Only the first of two: it loads the rows.
            [$message, 224],
        ]);
    }
}
