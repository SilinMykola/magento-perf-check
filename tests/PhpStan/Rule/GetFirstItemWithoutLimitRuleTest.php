<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\Rule\GetFirstItemWithoutLimitRule;
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
        ]);
    }
}
