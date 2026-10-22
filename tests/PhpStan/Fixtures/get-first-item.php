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
        $collection->addFieldToFilter('type_id', $sku)->getFirstItem();
    }

    public function fromFactory(): void
    {
        $this->collectionFactory->create()->addFieldToFilter('type_id', 'simple')->getFirstItem();
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

    /**
     * The M0 core run found this pattern in TaxRuleCollectionTest and TaxRateCollectionTest:
     * count() already loaded the collection, so getFirstItem() runs no query.
     */
    public function alreadyLoadedByCount(Collection $collection): void
    {
        $count = $collection->count();
        $collection->getFirstItem();
    }

    public function alreadyLoadedByCountFunction(Collection $collection): void
    {
        if (count($collection) > 0) {
            $collection->getFirstItem();
        }
    }

    public function alreadyIterated(Collection $collection): void
    {
        foreach ($collection as $item) {
            $item->getId();
        }
        $collection->getFirstItem();
    }

    public function uniqueKeyFilter(Collection $collection, string $sku): void
    {
        $collection->addFieldToFilter('sku', $sku)->getFirstItem();
        $this->collectionFactory->create()->addAttributeToFilter('main_table.entity_id', ['eq' => 5])->getFirstItem();
        $this->collectionFactory->create()->addIdFilter(5)->getFirstItem();
    }

    public function uniqueKeyFilterEarlier(Collection $collection, string $sku): void
    {
        $collection->addFieldToFilter('increment_id', $sku);
        $collection->getFirstItem();
    }

    public function nonEqualityOnUniqueKey(Collection $collection): void
    {
        $collection->addFieldToFilter('sku', ['like' => 'A%'])->getFirstItem();
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
