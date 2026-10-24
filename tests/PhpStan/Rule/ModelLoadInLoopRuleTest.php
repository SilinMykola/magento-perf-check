<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\ModelLoadInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ModelLoadInLoopRule>
 */
final class ModelLoadInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ModelLoadInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testReportsLoadsAndRepositoryGetsInLoopsOnly(): void
    {
        $suffix = ' is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().';

        $this->analyse([__DIR__ . '/../Fixtures/model-load-in-loop.php'], [
            ['[P-LOOP-01] Magento\Catalog\Model\Product::load()' . $suffix, 37],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 41],
            ['[P-LOOP-01] Magento\Customer\Api\CustomerRepositoryInterface::get()' . $suffix, 44],
            ['[P-LOOP-01] Magento\Catalog\Model\ProductRepository::getById()' . $suffix, 47],
        ]);
    }
}
