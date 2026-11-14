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
     * In a try inside the loop, a call counts as running once only when no catch can go on with the loop.
     *
     * @param list<int> $ids
     */
    public function leavesLoopThroughTry(array $ids): ?object
    {
        foreach ($ids as $id) {
            try {
                $product = $this->productRepository->getById($id);
                break;
            } catch (\Throwable $e) {
                return null;
            }
        }
        foreach ($ids as $id) {
            foreach ($ids as $other) {
                try {
                    $this->check($other);
                } catch (\Throwable $e) {
                    if ($id > 0) {
                        $this->productRepository->getById($id);
                    }
                    throw $e;
                }
            }
        }
        foreach ($ids as $id) {
            try {
                $this->check($id);
            } finally {
                return $this->productRepository->getById($id);
            }
        }
        foreach ($ids as $id) {
            try {
                $this->check($id);
            } catch (\Throwable $e) {
                $this->productRepository->getById($id);
            }

            return null;
        }
        foreach ($ids as $id) {
            try {
                $this->check($id);
            } finally {
                $this->productRepository->getById($id);
            }

            return null;
        }
        foreach ($ids as $id) {
            try {
                return $this->productRepository->getById($id);
            } finally {
                $this->check($id);
            }
        }

        return null;
    }

    /**
     * @param list<int> $ids
     * @return list<object>
     */
    public function stillInLoop(array $ids): array
    {
        $products = [];
        foreach ($ids as $id) {
            try {
                return [$this->productRepository->getById($id)]; // reported: a missing id goes on with the loop
            } catch (\Throwable $e) {
                continue;
            }
        }
        foreach ($ids as $id) {
            if ($id > 0) {
                try {
                    $product = $this->productRepository->getById($id); // reported: the empty catch goes on
                    break;
                } catch (\Throwable $e) {
                }
            }
        }
        foreach ($ids as $id) {
            try {
                $product = $this->productRepository->getById($id); // reported: its own catch takes the throw
                if ($id > 1) {
                    throw new \RuntimeException();
                }

                return [$product];
            } catch (\RuntimeException $e) {
                continue;
            }
        }
        foreach ($ids as $id) {
            try {
                foreach ($ids as $other) {
                    return [$this->productRepository->getById($other)]; // reported: the catch goes on with the outer loop
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
        foreach ($ids as $id) {
            try {
                $products[] = $this->productRepository->getById($id); // reported: the loop goes on
            } catch (\Throwable $e) {
                return [];
            }
        }
        foreach ($ids as $id) {
            try {
                $this->check($id);
            } catch (\Throwable $e) {
                $this->productRepository->getById($id); // reported: the catch goes on with the loop
            }
        }
        foreach ($ids as $id) {
            try {
                if ($id < 0) {
                    continue;
                }
                $this->check($id);
            } finally {
                $this->productRepository->getById($id); // reported: the finally also runs after continue
            }

            return [];
        }
        foreach ($ids as $id) {
            foreach ($ids as $other) {
                try {
                    $this->productRepository->getById($other); // reported: break leaves the inner loop only
                    break;
                } catch (\Throwable $e) {
                    return [];
                }
            }
        }
        try {
            foreach ($ids as $id) {
                try {
                    return [$this->productRepository->getById($id)]; // reported: the inner catch goes on
                } catch (\Throwable $e) {
                    continue;
                }
            }
        } catch (\Throwable $e) {
            return [];
        }

        return $products;
    }

    private function check(int $id): void
    {
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
