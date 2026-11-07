<?php

declare(strict_types=1);

namespace Acme\Promo\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Directory\Model\Currency;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchServiceContractResolverInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Api\StoreRepositoryInterface;

final class Brand implements ResolverInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly SearchCriteriaInterface $criteria,
        private readonly Product $product,
        private readonly AdapterInterface $connection,
        private readonly AbstractDb $collection,
        private readonly \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource,
    ) {
    }

    public function resolve(): void
    {
        $this->products->getById(1);
        $this->products->get('sku');
        $this->products->getList($this->criteria);
        $this->product->load(1);
        $this->connection->fetchAll('select 1');
        $this->collection->getSize();
        $this->collection->addFieldToFilter('a', 1);
        $this->helper();
    }

    private function helper(): void
    {
        $this->products->getById(2);
        $this->resource->load($this->product, 1);
    }
}

final class Rows implements ResolverInterface
{
    public function __construct(private readonly AbstractDb $collection)
    {
    }

    /** @param list<int> $plain */
    public function resolve(array $plain): void
    {
        foreach ($this->collection as $row) {
            unset($row);
        }
        foreach ($plain as $value) {
            unset($value);
        }
    }
}

final class Formatter implements ResolverInterface
{
    public function __construct(private readonly \Acme\Promo\Service\BrandLoader $loader)
    {
    }

    /** @param array<string, string> $value */
    public function resolve(array $value): string
    {
        return $this->loader->getById(1) . $value['brand'];
    }
}

final class BatchBrand implements BatchResolverInterface
{
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    public function resolve(): void
    {
        $this->products->getById(1);
    }
}

final class BatchContract implements BatchServiceContractResolverInterface
{
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    public function resolve(): void
    {
        $this->products->getById(1);
    }
}

final class Deferred implements ResolverInterface
{
    public function __construct(private readonly AbstractDb $collection)
    {
    }

    public function resolve(): \Closure
    {
        return function (): int {
            foreach ($this->collection as $row) {
                unset($row);
            }

            return (int) $this->collection->getSize();
        };
    }

    public function arrow(): \Closure
    {
        return fn (): int => (int) $this->collection->getSize();
    }

    /**
     * @param list<int> $ids
     * @return list<int>
     */
    public function perElementOfAnArray(array $ids): array
    {
        return array_map(fn (int $id): int => (int) $this->collection->getSize() + $id, $ids);
    }
}

final class LoadsInConstructor implements ResolverInterface
{
    public function __construct(AbstractDb $collection)
    {
        $collection->getSize();
        foreach ($collection as $row) {
            unset($row);
        }
    }
}

final class ForeachOverLoad implements ResolverInterface
{
    public function __construct(private readonly AbstractDb $collection)
    {
    }

    public function resolve(): void
    {
        foreach ($this->collection->load() as $row) {
            unset($row);
        }
    }
}

final class Configured implements ResolverInterface
{
    public function __construct(
        private readonly StoreRepositoryInterface $stores,
        private readonly Currency $currency,
    ) {
    }

    public function resolve(): void
    {
        $this->stores->getById(1);
        $this->currency->load('EUR');
    }
}

final class OnSingleType implements ResolverInterface
{
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    public function resolve(): void
    {
        $this->products->getById(1);
    }
}

final class OnInterfaceOfListedType implements ResolverInterface
{
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    public function resolve(): void
    {
        $this->products->getById(1);
    }
}

// In the schema, but it is not a resolver.
final class OnRecursiveType
{
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    public function resolve(): void
    {
        $this->products->getById(1);
    }
}

final class ThroughEavResource implements ResolverInterface
{
    public function __construct(
        private readonly \Magento\Customer\Model\ResourceModel\Customer $resource,
        private readonly Product $product,
    ) {
    }

    public function resolve(): void
    {
        $this->resource->load($this->product, 1);
    }
}

namespace Acme\Promo\Service;

final class BrandLoader
{
    public function getById(int $id): string
    {
        return (string) $id;
    }
}
