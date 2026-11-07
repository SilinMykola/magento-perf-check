<?php

declare(strict_types=1);

namespace SaveInLoopFixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\Config\ScopeConfigInterface;

final class Example
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductResource $productResource,
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * @param list<Product> $products
     * @param list<string> $skus
     */
    public function violations(array $products, array $skus): void
    {
        foreach ($products as $product) {
            $this->productRepository->save($product);
        }

        foreach ($products as $product) {
            $product->save();
            $product->delete();
            $this->productResource->save($product);
        }

        foreach ($skus as $sku) {
            $this->productRepository->deleteById($sku);
        }
    }

    /**
     * @param list<Product> $products
     */
    public function allowed(array $products, Product $last): void
    {
        foreach ($products as $product) {
            $product->getSku();
        }
        $this->productRepository->save($last);
    }

    /**
     * @param list<Product> $products
     */
    public function eavResourceModel(array $products, \Magento\Customer\Model\ResourceModel\Customer $resource): void
    {
        foreach ($products as $product) {
            $resource->save($product);
        }
    }
}
