<?php

declare(strict_types=1);

namespace ModelLoadInLoopFixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

final class Example
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductRepository $concreteRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly RequestInterface $request,
        private readonly ProductResource $productResource,
    ) {
    }

    /**
     * @param list<int> $ids
     * @param list<string> $emails
     * @param mixed $unknown
     */
    public function violations(array $ids, array $emails, Product $product, $unknown): void
    {
        foreach ($ids as $id) {
            $product->load($id);
        }

        for ($i = 0; $i < count($ids); $i++) {
            $this->productRepository->getById($ids[$i]);
        }

        array_map(fn (string $email) => $this->customerRepository->get($email), $emails);

        foreach ($ids as $id) {
            $this->concreteRepository->getById($id);
        }
    }

    /**
     * @param list<int> $ids
     * @param mixed $unknown
     */
    public function allowed(array $ids, SearchCriteriaInterface $criteria, Collection $collection, Product $product, $unknown): void
    {
        $this->productRepository->getById(1);

        foreach ($this->productRepository->getList($criteria)->getItems() as $item) {
            $item->getId();
        }

        foreach ($ids as $id) {
            $collection->load();
            $this->productResource->load($product, $id);
            $this->scopeConfig->getValue('catalog/seo/product_url_suffix');
            $this->request->get('id');
            $unknown->load($id);
            $unknown->getById($id);
        }
    }

    /**
     * These read a field or cached configuration, not the database.
     *
     * @param list<int> $ids
     */
    public function loadsThatRunNoQuery(
        array $ids,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Api\StoreRepositoryInterface $stores,
        \Magento\Store\Api\WebsiteRepositoryInterface $websites,
        \Magento\Store\Api\GroupRepositoryInterface $groups,
    ): void {
        foreach ($ids as $id) {
            $currency->load((string) $id);
            $stores->getById($id);
            $websites->getById($id);
            $groups->get($id);
        }
    }
}

final class LeavesLoop
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * @param list<int> $ids
     */
    public function first(array $ids, Product $product): ?Product
    {
        foreach ($ids as $id) {
            $product->load($id);

            return $product;
        }
        foreach ($ids as $id) {
            return $this->productRepository->getById($id);
        }

        return null;
    }
}

final class TryAroundLoop
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * @param list<int> $ids
     */
    public function leavesLoop(array $ids, bool $skip): ?object
    {
        try {
            foreach ($ids as $id) {
                return $this->productRepository->getById($id);
            }
        } catch (\Throwable $e) {
            return null;
        }
        try {
            foreach ($ids as $id) {
                if ($skip) {
                    return null;
                } else {
                    return $this->productRepository->getById($id);
                }
            }
        } finally {
            foreach ($ids as $id) {
                $this->productRepository->getById($id);
                break;
            }
        }
        try {
            $this->productRepository->getById(0);
        } catch (\Throwable $e) {
            foreach ($ids as $id) {
                return $this->productRepository->getById($id);
            }
        }

        return null;
    }

    /**
     * @param list<int> $ids
     */
    public function stillInLoop(array $ids): ?object
    {
        foreach ($ids as $id) {
            try {
                return $this->productRepository->getById($id);
            } catch (\Throwable $e) {
                continue;
            }
        }
        try {
            foreach ($ids as $id) {
                try {
                    return $this->productRepository->getById($id);
                } catch (\Throwable $e) {
                    continue;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }
        foreach ($ids as $id) {
            try {
                foreach ($ids as $other) {
                    return $this->productRepository->getById($other);
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
}

final class MagicRepository extends ProductRepository
{
    /**
     * @param array<mixed> $args
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return null;
    }
}

final class MagicSetterOutsideDataObject
{
    /**
     * @param list<int> $ids
     */
    public function notReported(MagicRepository $repository, array $ids): void
    {
        foreach ($ids as $id) {
            $repository->setStoreId(1)->getById($id);
        }
    }
}
