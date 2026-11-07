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

    /**
     * After is_array() PHPStan narrows an object to "never"; that must not match.
     */
    public function arrayCheckOnObject(Collection $collection): int
    {
        return is_array($collection) ? count($collection) : 0;
    }

    public function iteratedAfterCount(Collection $collection): int
    {
        if (count($collection) === 0) {
            return 0;
        }
        foreach ($collection as $item) {
            $item->getId();
        }

        return 1;
    }

    public function countedTwice(Collection $collection): int
    {
        return count($collection) + count($collection);
    }

    public function getSizeIsFine(Collection $collection): int
    {
        return $collection->getSize();
    }
}

final class Memoized
{
    private ?Collection $rows = null;

    public function getRows(): Collection
    {
        return $this->rows ?? throw new \LogicException();
    }

    public function hasRows(): bool
    {
        return count($this->getRows()) > 0;
    }

    public function hasStoredRows(Collection $other): bool
    {
        return $this->rows !== null && count($this->rows) > 0;
    }

    public function otherObject(self $other): bool
    {
        return count($other->getRows()) > 0;
    }
}

abstract class LoadedInHook extends Collection
{
    protected function _afterLoad(): int
    {
        return count($this);
    }

    public function notInTheHook(): int
    {
        return count($this);
    }
}
