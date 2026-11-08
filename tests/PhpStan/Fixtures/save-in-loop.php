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

final class LeavesLoop
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * @param list<Product> $products
     */
    public function allowed(array $products, int $type): ?Product
    {
        foreach ($products as $product) {
            if ($product->isSaleable()) {
                $this->productRepository->save($product);
                break;
            }
        }
        foreach ($products as $product) {
            if ($product->isSaleable()) {
                if ($product->hasDataChanges()) {
                    $product->save();
                }
                break;
            }
        }
        foreach ($products as $product) {
            $product->delete();
            throw new \RuntimeException('one');
        }
        foreach ($products as $product) {
            foreach ($product->getOptions() as $option) {
                if ($option) {
                    $product->save();

                    return $product;
                }
            }
        }
        foreach ($products as $product) {
            switch ($type) {
                case 1:
                    $product->save();
                    break;
            }
            break;
        }
        foreach ($products as $product) {
            switch ($type) {
                case 1:
                    $product->getSku();
                    break;
                default:
                    $product->save();
            }
            break;
        }
        foreach ($products as $product) {
            return $this->productRepository->save($product);
        }
        foreach ($products as $product) {
            if ($product->isSaleable()) {
                $product->getSku();
            } elseif ($product->hasDataChanges()) {
                $product->save();
                break;
            } else {
                $product->delete();
                break;
            }
        }
        foreach ($products as $product) {
            {
                $product->save();
            }
            break;
        }

        return null;
    }

    /**
     * @param list<Product> $products
     */
    public function reported(array $products, int $type): void
    {
        foreach ($products as $product) {
            $product->save();
            if ($product->isSaleable()) {
                continue;
            }
            break;
        }
        foreach ($products as $product) {
            foreach ($product->getOptions() as $option) {
                $product->save();
                break;
            }
        }
        foreach ($products as $product) {
            switch ($type) {
                case 1:
                    $product->save();
                    break;
            }
        }
        foreach ($products as $product) {
            switch ($type) {
                case 1:
                    $product->save();
                case 2:
                    continue 2;
            }
            break;
        }
        foreach ($products as $product) {
            try {
                $product->save();
                break;
            } catch (\Exception $e) {
            }
        }
        array_map(function (Product $product): Product {
            $product->save();

            return $product;
        }, $products);
        foreach ($products as $product) {
            if ($product->save()) {
                break;
            }
        }
        foreach ($products as $product) {
            array_walk($products, function (Product $other): void {
                $other->save();
            });
            break;
        }
        foreach ($products as $product) {
            array_map(fn (Product $other): Product => $other->save(), $products);
            break;
        }
        foreach ($products as $product) {
            foreach ($product->getOptions() as $option) {
                switch ($type) {
                    case 1:
                        $product->save();
                        break 2;
                }

                return;
            }
        }
        foreach ($products as $product) {
            foreach ($product->getOptions() as $option) {
                $product->save();
                if ($option) {
                    break;
                }

                return;
            }
        }
        foreach ($products as $product) {
            again:
            $product->save();
            if ($product->hasDataChanges()) {
                goto again;
            }
            break;
        }
    }
}
