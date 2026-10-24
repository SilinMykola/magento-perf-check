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
        ]);
    }
}
