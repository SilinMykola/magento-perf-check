<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\Rule\CompositeTypeInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CompositeTypeInLoopRule>
 */
final class CompositeTypeInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CompositeTypeInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/composite-type-in-loop.php'], [
            ['[P-LOOP-07] Magento\ConfigurableProduct\Model\Product\Type\Configurable::getUsedProducts() is called inside a loop: it queries the database for one product per call. Collect the IDs first; getChildrenIds() and getParentIdsByChild() accept arrays.', 28],
            ['[P-LOOP-07] Magento\Catalog\Model\ResourceModel\Product::getIdBySku() is called inside a loop: it queries the database for one product per call. Collect the IDs first; getChildrenIds() and getParentIdsByChild() accept arrays.', 32],
            ['[P-LOOP-07] Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::getParentIdsByChild() is called inside a loop: it queries the database for one product per call. Collect the IDs first; getChildrenIds() and getParentIdsByChild() accept arrays.', 36],
        ]);
    }
}
