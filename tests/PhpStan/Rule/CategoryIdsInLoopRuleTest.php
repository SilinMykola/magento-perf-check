<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\CategoryIdsInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CategoryIdsInLoopRule>
 */
final class CategoryIdsInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CategoryIdsInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/batch-queries-in-loop.php'], [
            ['[P-LOOP-05] Product::getCategoryIds() is called inside a loop: one query per product. Call addCategoryIds() on the loaded product collection before the loop.', 93],
            ['[P-LOOP-05] Product::getCategoryIds() is called inside a loop: one query per product. Call addCategoryIds() on the loaded product collection before the loop.', 101],
            ['[P-LOOP-05] Product::getCategoryIds() is called inside a loop: one query per product. Call addCategoryIds() on the loaded product collection before the loop.', 104],
        ]);
    }
}
