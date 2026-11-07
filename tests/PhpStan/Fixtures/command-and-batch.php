<?php

declare(strict_types=1);

namespace Acme\Batch;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;

class Sync extends Command
{
    public function __construct(
        ProductRepositoryInterface $products,
        State $state,
        CollectionFactory $factory,
        string $name = 'sync',
    ) {
    }

    public function execute(CollectionFactory $factory): void
    {
        foreach ($factory->create() as $item) {
            unset($item);
        }
    }
}

class Proxied extends Command
{
    public function __construct(ProductRepositoryInterface $products)
    {
    }
}

class Light extends Command
{
    public function __construct(State $state, StoreManagerInterface $stores, CollectionFactory $factory, ?string $name = null)
    {
    }
}

class BaseOfVirtual extends Command
{
    public function __construct(ProductRepositoryInterface $products, ProductRepositoryInterface $others)
    {
    }
}

class Unregistered extends Command
{
    public function __construct(ProductRepositoryInterface $products)
    {
    }
}

class NightlyJob
{
    public function __construct(private readonly CollectionFactory $factory)
    {
    }

    public function reported(): void
    {
        $collection = $this->factory->create();
        $collection->addFieldToFilter('status', 'new');
        foreach ($collection as $item) {
            unset($item);
        }
        foreach ($this->factory->create()->addFieldToFilter('status', 'new')->getItems() as $item) {
            unset($item);
        }
    }

    public function paged(): void
    {
        $collection = $this->factory->create();
        $collection->setPageSize(100);
        foreach ($collection as $item) {
            unset($item);
        }
        foreach ($this->factory->create()->setPageSize(100) as $item) {
            unset($item);
        }
    }

    public function limited(): void
    {
        $collection = $this->factory->create();
        $collection->getSelect()->limit(10);
        foreach ($collection as $item) {
            unset($item);
        }
    }

    public function oneRow(string $sku): void
    {
        $collection = $this->factory->create()->addFieldToFilter('sku', $sku);
        foreach ($collection as $item) {
            unset($item);
        }
    }

    public function fromElsewhere(Collection $collection): void
    {
        foreach ($collection as $item) {
            unset($item);
        }
        $other = $this->build();
        foreach ($other as $item) {
            unset($item);
        }
    }

    private function build(): Collection
    {
        return $this->factory->create();
    }

    /** @param list<int> $ids */
    public function notACollection(array $ids): void
    {
        foreach ($ids as $id) {
            unset($id);
        }
    }
}

class BaseOfVirtualJob
{
    public function execute(CollectionFactory $factory): void
    {
        foreach ($factory->create() as $item) {
            unset($item);
        }
    }

    public function ownMethods(): void
    {
        foreach ($this->getCollection() as $item) {
            unset($item);
        }
        foreach ($this->create() as $item) {
            unset($item);
        }
    }

    public function collectionOfAnotherObject(OrderFactory $factory): void
    {
        $order = $factory->create();
        foreach ($order->getItemsCollection() as $item) {
            unset($item);
        }
    }

    public function limitOnAnotherCollection(CollectionFactory $factory): void
    {
        $first = $factory->create()->setPageSize(1);
        unset($first);
        foreach ($factory->create()->addFieldToFilter('status', 'new') as $item) {
            unset($item);
        }
    }

    /** @param list<Collection> $collections */
    public function reassignedByTheLoop(CollectionFactory $factory, array $collections): void
    {
        $collection = $factory->create();
        unset($collection);
        foreach ($collections as $collection) {
            foreach ($collection as $item) {
                unset($item);
            }
        }
    }

    public function itemsOfACollectionCreatedHere(CollectionFactory $factory): void
    {
        $collection = $factory->create();
        foreach ($collection->getItems() as $item) {
            unset($item);
        }
    }

    public function collectionFromAnotherObject(Order $order): void
    {
        foreach ($order->getCollection() as $item) {
            unset($item);
        }
    }

    private function getCollection(): Collection
    {
        throw new \LogicException();
    }

    private function create(): Collection
    {
        throw new \LogicException();
    }
}

class Order
{
    public function getCollection(): Collection
    {
        throw new \LogicException();
    }

    public function getItemsCollection(): Collection
    {
        throw new \LogicException();
    }
}

class OrderFactory
{
    public function create(): Order
    {
        return new Order();
    }
}

class RequestCode
{
    public function execute(CollectionFactory $factory): void
    {
        foreach ($factory->create() as $item) {
            unset($item);
        }
    }
}

class ProxiedInParent extends Command
{
    public function __construct(ProductRepositoryInterface $products)
    {
    }
}

class ProxiedInChild extends ProxiedInParent
{
    public function __construct(ProductRepositoryInterface $products)
    {
        parent::__construct($products);
    }
}

/** @implements \IteratorAggregate<int, int> */
class Rows implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([]);
    }
}

class RowsFactory
{
    public function create(): Rows
    {
        return new Rows();
    }
}

class CommandOverRows extends Command
{
    public function execute(RowsFactory $factory): void
    {
        foreach ($factory->create() as $row) {
            unset($row);
        }
    }
}

class OptionalDependency extends Command
{
    public function __construct(?ProductRepositoryInterface $products = null, ProductRepositoryInterface $others = null)
    {
    }
}

class NullableButRequired extends Command
{
    public function __construct(?ProductRepositoryInterface $products)
    {
    }
}
