<?php

declare(strict_types=1);

namespace BatchQueriesFixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Api\StoreRepositoryInterface;

interface OptionRepositoryInterface
{
    /** @return list<object> */
    public function getList(string $sku): array;
}

final class Example
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
        private readonly StoreRepositoryInterface $storeRepository,
        private readonly OptionRepositoryInterface $optionRepository,
        private readonly ProductResource $resource,
        private readonly SearchCriteriaInterface $criteria,
    ) {
    }

    /**
     * @param list<int> $ids
     */
    public function listPerItem(array $ids): void
    {
        foreach ($ids as $id) {
            $this->repository->getList($this->criteria);
        }
        array_map(fn (int $id) => $this->repository->getList($this->criteria), $ids);
    }

    /**
     * @param list<int> $ids
     */
    public function listAllowed(array $ids): void
    {
        $this->repository->getList($this->criteria);
        foreach ($this->repository->getList($this->criteria)->getItems() as $product) {
            $product->getSku();
        }
        // Paging through one result.
        do {
            $page = $this->repository->getList($this->criteria);
        } while ($page->getItems() !== []);
        // One query per batch is the fix itself.
        foreach (array_chunk($ids, 100) as $batch) {
            $this->repository->getList($this->criteria);
        }
        // Reads cached configuration, and takes no criteria.
        foreach ($ids as $id) {
            $this->storeRepository->getList();
        }
        // One SKU per call: no filter can replace the loop.
        foreach ($ids as $id) {
            $this->optionRepository->getList((string) $id);
        }
    }

    /**
     * @param list<int> $ids
     */
    public function resourceLoad(array $ids, Product $product, object $unknown): void
    {
        foreach ($ids as $id) {
            $this->resource->load($product, $id);
        }
        while ($ids !== []) {
            array_pop($ids);
            $this->resource->load($product, 1);
        }
        $this->resource->load($product, 2);
        foreach ($ids as $id) {
            $unknown->load($product, $id);
        }
    }

    /**
     * @param list<Product> $products
     */
    public function categoryIds(array $products, Product $single): void
    {
        foreach ($products as $product) {
            $product->getCategoryIds();
        }
        $single->getCategoryIds();
    }

    public function categoryIdsOfCollectionItems(Collection $collection): void
    {
        foreach ($collection as $product) {
            $product->getCategoryIds();
        }
        foreach ($collection->getItems() as $item) {
            $item->getCategoryIds();
        }
    }

    public function categoryIdsAdded(Collection $collection): void
    {
        $collection->load();
        $collection->addCategoryIds();
        /** @var Product $product */
        foreach ($collection as $product) {
            $product->getCategoryIds();
        }
    }

    /**
     * @param list<object> $items
     */
    public function categoryIdsOnSomethingElse(array $items): void
    {
        foreach ($items as $item) {
            $item->getCategoryIds();
        }
    }

    /**
     * @param list<int> $ids
     */
    public function eavResourceModel(array $ids, \Magento\Customer\Model\ResourceModel\Customer $resource, Product $product): void
    {
        foreach ($ids as $id) {
            $resource->load($product, $id);
        }
    }
}
