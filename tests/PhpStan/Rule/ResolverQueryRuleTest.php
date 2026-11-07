<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\ResolverQueryRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResolverQueryRule>
 */
final class ResolverQueryRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResolverQueryRule($this->createReflectionProvider(), self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $message = static fn (string $query, string $field): string => sprintf(
            '[X-GQL-02] %s runs a query in the resolver of %s, a field on a type returned inside a list: one query per list element. '
            . 'Implement BatchResolverInterface, or collect the ids and load them once in a deferred value.',
            $query,
            $field,
        );
        $brand = 'ProductInterface.brand';

        $this->analyse([__DIR__ . '/../Fixtures/resolver-query.php'], [
            [$message('Magento\Catalog\Api\ProductRepositoryInterface::getById()', $brand), 32],
            [$message('Magento\Catalog\Api\ProductRepositoryInterface::get()', $brand), 33],
            [$message('Magento\Catalog\Api\ProductRepositoryInterface::getList()', $brand), 34],
            [$message('Magento\Catalog\Model\Product::load()', $brand), 35],
            [$message('Magento\Framework\DB\Adapter\AdapterInterface::fetchAll()', $brand), 36],
            [$message('Magento\Framework\Data\Collection\AbstractDb::getSize()', $brand), 37],
            [$message('Magento\Catalog\Api\ProductRepositoryInterface::getById()', $brand), 44],
            [$message('Magento\Framework\Model\ResourceModel\Db\AbstractDb::load()', $brand), 45],
            [$message('Magento\Framework\Data\Collection\AbstractDb::getSize()', 'ProductInterface.brand_deferred'), 132],
            [$message('Magento\Framework\Data\Collection\AbstractDb::load()', 'ProductInterface.brand_foreach_load'), 155],
            [$message('Magento\Catalog\Api\ProductRepositoryInterface::getById()', 'Shared.shared'), 196],
            [$message('Magento\Customer\Model\ResourceModel\Customer::load()', 'ProductInterface.brand_eav'), 223],
        ]);
    }
}
