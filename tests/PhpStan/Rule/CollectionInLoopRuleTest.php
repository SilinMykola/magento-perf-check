<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\CollectionInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CollectionInLoopRule>
 */
final class CollectionInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CollectionInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/collection-in-loop.php'], [
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getItems()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 21],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getSize()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 27],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getFirstItem()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 30],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (load()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 34],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getSize()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 40],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (count()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 43],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getSize()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 46],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getSize()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 51],
            // Later calls on the same collection add no finding.
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (load()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 86],
            // The outermost call of a chain stands for the whole chain.
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getItems()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 91],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (load()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 95],
            // Assigned again, it is another collection.
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getSize()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 98],
            // A loop inside a batch is a loop again.
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (load()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 121],
            // A variable name counts as a batch only in its function, and until it is assigned again.
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getSize()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 132],
            ['[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Collection is created and loaded (getItems()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.', 144],
        ]);
    }
}
