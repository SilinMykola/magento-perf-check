<?php

declare(strict_types=1);

namespace CollectionCountFixture;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

final class Example
{
    public function countBeforeLoading(Collection $collection): bool
    {
        return count($collection) > 0;
    }

    public function countAfterIteration(Collection $collection): int
    {
        foreach ($collection as $item) {
            $item->getId();
        }

        return count($collection);
    }

    /**
     * @param list<int> $items
     */
    public function countOfArray(array $items): int
    {
        return count($items) + sizeof($items);
    }

    public function sizeofBeforeLoading(Collection $collection): int
    {
        return sizeof($collection);
    }

    public function getSizeIsFine(Collection $collection): int
    {
        return $collection->getSize();
    }
}
