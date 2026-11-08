<?php

declare(strict_types=1);

namespace CompositeTypeInLoopFixture;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;

final class Example
{
    public function __construct(
        private readonly Configurable $configurableType,
        private readonly ConfigurableResource $configurableResource,
        private readonly ProductResource $productResource,
    ) {
    }

    /**
     * @param list<Product> $products
     * @param list<string> $skus
     */
    public function violations(array $products, array $skus): void
    {
        foreach ($products as $product) {
            $this->configurableType->getUsedProducts($product);
        }

        foreach ($skus as $sku) {
            $this->productResource->getIdBySku($sku);
        }

        foreach ($products as $product) {
            $this->configurableResource->getParentIdsByChild((int) $product->getId());
        }
    }

    /**
     * @param list<int> $childIds
     */
    public function allowed(array $childIds): void
    {
        $parents = $this->configurableResource->getParentIdsByChild($childIds);
        foreach ($parents as $parentId) {
            $parentId++;
        }
    }
}

final class LeavesLoop
{
    public function __construct(private readonly Configurable $configurableType)
    {
    }

    /**
     * @param list<Product> $products
     * @return mixed
     */
    public function first(array $products)
    {
        foreach ($products as $product) {
            return $this->configurableType->getUsedProducts($product);
        }

        return null;
    }
}
