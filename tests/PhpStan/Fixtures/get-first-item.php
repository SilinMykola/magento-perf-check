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
        $this->collectionFactory->create()->addIdFilter(5)->getSize();
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

    public function limitInAssignedChain(): void
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('type_id', 'simple')
            ->setPageSize(1);
        $collection->getFirstItem();
    }

    public function reassignmentStartsOver(Collection $collection): void
    {
        $collection->setPageSize(1);
        $collection = $this->collectionFactory->create();
        $collection->getFirstItem();
    }

    public function reassignmentFromItself(Collection $collection): void
    {
        $collection->setPageSize(1);
        $collection = $collection->addFieldToFilter('type_id', 'simple');
        $collection->getFirstItem();
    }

    public function alreadyLoadedByGetItems(Collection $collection): void
    {
        $items = $collection->getItems();
        $collection->getFirstItem();
    }

    public function guardedBySize(Collection $collection): void
    {
        if ($collection->getSize() === 1) {
            $collection->getFirstItem();
        }
    }

    public function guardedByNegatedSize(Collection $collection): void
    {
        if ($collection->getSize() !== 1) {
            return;
        }
        $collection->getFirstItem();
    }

    public function sizeOfManyIsNoGuard(Collection $collection): void
    {
        if ($collection->getSize() > 0) {
            $collection->getFirstItem();
        }
    }

    public function sizeOfFiveIsNoGuard(Collection $collection): void
    {
        if ($collection->getSize() === 5) {
            $collection->getFirstItem();
        }
    }

    public function limitedBySetPage(Collection $collection): void
    {
        $collection->setPage(1, 1)->getFirstItem();
    }

    public function filteredById(Collection $collection): void
    {
        $collection->setIdFilter(5)->getFirstItem();
    }

    public function filteredByManyIds(Collection $collection): void
    {
        $collection->addIdFilter([1, 2, 3])->getFirstItem();
    }

    public function freshCollectionInSameVariable(Collection $collection, Collection $other): void
    {
        $collection->setPageSize(1);
        $collection = $other;
        $collection->getFirstItem();
    }

    public function rowsUsedLater(Collection $collection): void
    {
        if ($collection->getFirstItem()->getId() === null) {
            return;
        }
        $collection->getColumnValues('entity_id');
    }

    public function iteratedLater(Collection $collection): void
    {
        $collection->getFirstItem();
        foreach ($collection as $item) {
            $item->getId();
        }
    }

    public function otherCollectionUsedLater(Collection $collection, Collection $other): void
    {
        $collection->getFirstItem();
        $other->getColumnValues('entity_id');
    }

    public function calledTwice(Collection $collection): void
    {
        $collection->getFirstItem();
        $collection->getFirstItem();
    }

    public function inClosureAndUsedOutside(Collection $collection): void
    {
        $first = static function () use ($collection): object {
            return $collection->getFirstItem();
        };
        foreach ($collection as $item) {
            $item->getId();
        }
        $first();
    }

    public function unrelatedClass(Unrelated $unrelated): void
    {
        $unrelated->getFirstItem();
    }

    /**
     * @param list<int> $ids
     * @param list<string> $skus
     */
    public function uniqueColumnWithAList(Collection $a, Collection $b, Collection $c, Collection $d, Collection $e, Collection $f, array $ids, array $skus, int $id): void
    {
        $a->addFieldToFilter('entity_id', $ids)->getFirstItem();
        $b->addAttributeToFilter('sku', ['eq' => $skus])->getFirstItem();
        $c->addIdFilter($ids)->getFirstItem();
        $d->addFieldToFilter('entity_id', $id)->getFirstItem();
        $e->addFieldToFilter('entity_id', ['eq' => $id])->getFirstItem();
        // One of two filters on a single value is enough.
        $f->addFieldToFilter('entity_id', $id)->addFieldToFilter('sku', $skus)->getFirstItem();
    }

    /**
     * @param list<string> $skus
     */
    public function uniqueColumnWithAListEarlier(Collection $collection, array $skus): void
    {
        $collection->addFieldToFilter('sku', $skus);
        $collection->getFirstItem();
    }

    /**
     * @param list<int> $ids
     */
    public function uniqueColumnValueReassigned(Collection $collection, Collection $other, array $ids, int $id): void
    {
        $collection->addFieldToFilter('entity_id', $id);
        $id = $ids;
        $collection->getFirstItem();
        $other->addIdFilter($ids);
        $ids = 5;
        $other->getFirstItem();
    }

    public function uniqueColumnInAssignedChain(int $id): void
    {
        $collection = $this->collectionFactory->create()->addFieldToFilter('entity_id', $id);
        $collection->getFirstItem();
    }

    /**
     * @param int|list<int> $idOrIds
     */
    public function uniqueColumnWithAnUntypedValue(Collection $a, Collection $b, $untyped, int|array $idOrIds): void
    {
        $a->addFieldToFilter('entity_id', $untyped)->getFirstItem();
        $b->addIdFilter($idOrIds)->getFirstItem();
    }

    /**
     * @param list<int> $ids
     */
    public function uniqueColumnInConditionalCode(Collection $a, Collection $b, Collection $c, Collection $d, string $sku, ?int $id, array $ids): void
    {
        // Filters that may not run.
        if ($sku) {
            $a->addFieldToFilter('sku', $sku);
        }
        $a->getFirstItem();
        if ($id !== null) {
            $b->addIdFilter($id);
        }
        $b->getFirstItem();
        array_map(fn ($id) => $c->addFieldToFilter('entity_id', $id), $ids);
        $c->getFirstItem();
        // In the same branch.
        if ($id !== null) {
            $d->addIdFilter($id);
            $d->getFirstItem();
        }
    }

    public function uniqueColumnOnAnotherNewCollection(): void
    {
        $this->collectionFactory->create()->addIdFilter(5)->getSize();
        $this->collectionFactory->create()->getFirstItem();
        $this->collectionFactory->create()->addIdFilter(5)->getFirstItem();
        $this->collectionFactory->create()->getFirstItem();
        $this->collectionFactory->create()->setPageSize(1)->getFirstItem();
        $this->collectionFactory->create()->getFirstItem();
    }

    public function uniqueColumnWithAnUnresolvedValue(Collection $collection, \Acme\Missing\Transfer $transfer): void
    {
        // A value from a class PHPStan cannot find, as in old code with missing modules, counts as one value.
        $collection->addIdFilter($transfer->getWarehouseId())->getFirstItem();
    }

    /**
     * @param list<int> $ids
     * @param list<int> $more
     */
    public function uniqueColumnValueReassignedOtherwise(Collection $a, Collection $b, Collection $c, Collection $d, Collection $e, array $ids, array $more, int $id): void
    {
        $a->addIdFilter($ids);
        $ids = &$id;
        $a->getFirstItem();
        $b->addIdFilter($more);
        [$more] = [5];
        $b->getFirstItem();
        $list = [1, 2];
        $c->addFieldToFilter('entity_id', $list);
        foreach ([5] as $list) {
        }
        $c->getFirstItem();
        $keys = [1, 2];
        $d->addFieldToFilter('entity_id', $keys);
        foreach ([5 => true] as $keys => $_) {
        }
        $d->getFirstItem();
        $values = [[1, 2], 5];
        $n = 0;
        $e->addFieldToFilter('entity_id', $values[$n]);
        $n += 1;
        $e->getFirstItem();
    }

    public function iteratedOnAnotherNewCollection(): void
    {
        foreach ($this->collectionFactory->create() as $row) {
        }
        $this->collectionFactory->create()->getFirstItem();
    }

    public function countedOnAnotherNewCollection(): void
    {
        count($this->collectionFactory->create());
        $this->collectionFactory->create()->getFirstItem();
    }

    public function sizeComparedOnAnotherNewCollection(): void
    {
        if ($this->collectionFactory->create()->getSize() === 1) {
        }
        $this->collectionFactory->create()->getFirstItem();
    }

    public function rowsOfAnotherNewCollection(string $name): void
    {
        $all = $this->collectionFactory->create()->getItems();
        $one = $this->collectionFactory->create()->addFieldToFilter('name', $name)->getFirstItem();
    }

    /** @var array<string, int> */
    private array $hits = [];

    public function uniqueColumnValueUsedAsAKey(Collection $a, Collection $b, string $sku): void
    {
        // Writing an element keyed by the value does not change the value.
        $a->addFieldToFilter('sku', $sku);
        $this->hits[$sku] ??= 0;
        $a->getFirstItem();
        $count = [$sku => 0];
        $b->addFieldToFilter('sku', $sku);
        $count[$sku] += 1;
        $b->getFirstItem();
    }
}

final class Unrelated
{
    public function getFirstItem(): ?object
    {
        return null;
    }
}
