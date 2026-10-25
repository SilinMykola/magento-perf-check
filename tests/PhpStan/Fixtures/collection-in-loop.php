<?php

declare(strict_types=1);

namespace CollectionInLoopFixture;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

final class Example
{
    public function __construct(private readonly CollectionFactory $collectionFactory)
    {
    }

    /**
     * @param list<int> $ids
     */
    public function violations(array $ids): void
    {
        foreach ($ids as $id) {
            $this->collectionFactory->create()->addFieldToFilter('parent_id', $id)->getItems();
        }

        foreach ($ids as $id) {
            $children = $this->collectionFactory->create();
            $children->addFieldToFilter('parent_id', $id);
            $children->getSize();
        }

        array_map(fn (int $id) => $this->collectionFactory->create()->addFieldToFilter('parent_id', $id)->getFirstItem(), $ids);

        // The iterated expression of an inner loop runs once per outer iteration.
        foreach ($ids as $id) {
            foreach ($this->collectionFactory->create()->addFieldToFilter('parent_id', $id)->load() as $child) {
                $child->getId();
            }
        }
    }

    /**
     * @param list<int> $ids
     */
    public function allowed(array $ids): void
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('parent_id', ['in' => $ids]);
        $collection->load();
        foreach ($ids as $id) {
            foreach ($collection as $item) {
                $item->getId();
            }
            $collection->getSize();
        }

        $outside = $this->collectionFactory->create();
        $outside->getItems();

        // The iterated expression of foreach and the init of for run once.
        foreach ($this->collectionFactory->create()->load() as $item) {
            $item->getId();
        }
        for ($i = 0, $size = $this->collectionFactory->create()->getSize(); $i < $size; $i++) {
            $outside->getItems();
        }
    }
}
