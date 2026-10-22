<?php

declare(strict_types=1);

namespace GetFirstItemFixture;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\Collection as MemoryCollection;

final class Example
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly Collection $collection,
    ) {
    }

    public function noLimit(Collection $collection, string $sku): void
    {
        $collection->addFieldToFilter('sku', $sku)->getFirstItem();
    }

    public function fromFactory(): void
    {
        $this->collectionFactory->create()->addFieldToFilter('sku', 'x')->getFirstItem();
    }

    public function curPageIsNotALimit(Collection $collection): void
    {
        $collection->setCurPage(1);
        $collection->getFirstItem();
    }

    public function limitInOtherMethodDoesNotCount(): void
    {
        $this->collection->getFirstItem();
    }

    public function limitInChain(Collection $collection): void
    {
        $collection->setPageSize(1)->getFirstItem();
        $this->collectionFactory->create()->setPageSize(1)->getFirstItem();
    }

    public function limitEarlier(Collection $collection): void
    {
        $collection->addFieldToFilter('sku', 'x');
        $collection->setPageSize(1);
        $collection->getFirstItem();
    }

    public function limitEarlierOnProperty(): void
    {
        $this->collection->setPageSize(1);
        $this->collection->getFirstItem();
    }

    public function limitThroughSelect(Collection $collection): void
    {
        $collection->getSelect()->limit(1);
        $collection->getFirstItem();
    }

    public function memoryCollection(MemoryCollection $collection): void
    {
        $collection->getFirstItem();
    }

    /**
     * @param mixed $unknown
     */
    public function unknownType($unknown): void
    {
        $unknown->getFirstItem();
    }

    public function unrelatedClass(Unrelated $unrelated): void
    {
        $unrelated->getFirstItem();
    }
}

final class Unrelated
{
    public function getFirstItem(): ?object
    {
        return null;
    }
}
