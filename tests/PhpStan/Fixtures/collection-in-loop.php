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
    }
}
