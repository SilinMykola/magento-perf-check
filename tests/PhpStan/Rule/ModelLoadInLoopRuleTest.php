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
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 233],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 241],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 249],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 262],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 270],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 279],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 289],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 297],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 307],
            // A caching repository with the same arguments in every iteration queries once; anything a loop changes counts.
            ['[P-LOOP-01] Magento\Catalog\Model\Product::load()' . $suffix, 367],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 373],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 375],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 377],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 382],
            ['[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById()' . $suffix, 383],
            // Anything that may change the argument outside it, or make the repository load again, counts.
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::get()' . $suffix, 411],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()' . $suffix, 422],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()' . $suffix, 434],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()' . $suffix, 447],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()' . $suffix, 459],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::get()' . $suffix, 469],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()' . $suffix, 481],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()' . $suffix, 519],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()' . $suffix, 534],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()' . $suffix, 546],
            ['[P-LOOP-01] Magento\\Catalog\\Api\\ProductRepositoryInterface::get()' . $suffix, 558],
        ]);
    }
}
