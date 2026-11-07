<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\ResourceLoadInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResourceLoadInLoopRule>
 */
final class ResourceLoadInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResourceLoadInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/batch-queries-in-loop.php'], [
            ['[P-LOOP-10] Magento\Catalog\Model\ResourceModel\Product::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().', 75],
            ['[P-LOOP-10] Magento\Catalog\Model\ResourceModel\Product::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().', 79],
            ['[P-LOOP-10] Magento\Customer\Model\ResourceModel\Customer::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().', 134],
        ]);
    }
}
