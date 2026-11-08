<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\SaveInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SaveInLoopRule>
 */
final class SaveInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SaveInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/save-in-loop.php'], [
            ['[P-LOOP-03] Magento\Catalog\Api\ProductRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 28],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 32],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::delete() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 33],
            ['[P-LOOP-03] Magento\Catalog\Model\ResourceModel\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 34],
            ['[P-LOOP-03] Magento\Catalog\Api\ProductRepositoryInterface::deleteById() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 38],
            ['[P-LOOP-03] Magento\Customer\Model\ResourceModel\Customer::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 59],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 150],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 158],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 165],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 172],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 180],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 186],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 191],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 197],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 202],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 209],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 218],
            ['[P-LOOP-03] Magento\Catalog\Model\Product::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.', 228],
        ]);
    }
}
