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

final class SameArgumentsEveryIteration
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly Product $product,
    ) {
    }

    /**
     * @param list<object> $stores
     * @param list<int> $ids
     * @param list<string> $lines
     */
    public function run(int $id, array $stores, array $ids, array $lines): void
    {
        foreach ($stores as $store) {
            $this->productRepository->getById($id); // not reported: the repository keeps the product it loaded
            $this->productRepository->getById(self::DEFAULT_ID);
            $this->product->load($id); // reported: a model keeps nothing
            foreach ($ids as $other) {
                $this->productRepository->getById($id);
            }
        }
        foreach ($ids as $each) {
            $this->productRepository->getById($each); // reported: the loop variable
            $next = $each + 1;
            $this->productRepository->getById($next); // reported: assigned in the loop
            foreach ($stores as $store) {
                $this->productRepository->getById($each); // reported: the outer loop changes it
            }
        }
        foreach ($lines as $line) {
            sscanf($line, '%d', $found);
            $this->productRepository->getById($found); // reported: a function may write its argument
            $this->apply(fn (int $x) => $this->productRepository->getById($x), $ids); // reported: a closure parameter
        }
    }

    /**
     * @param list<int> $ids
     */
    private function apply(callable $callback, array $ids): void
    {
    }

    private const DEFAULT_ID = 1;
}

final class ChangedOutsideTheArgument
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly IdParser $parser,
    ) {
    }

    /**
     * @param list<string> $lines
     */
    public function forceReload(string $sku, array $lines): void
    {
        foreach ($lines as $line) {
            $this->productRepository->get($sku, false, null, true); // reported: forceReload loads again
        }
    }

    /**
     * @param list<string> $lines
     */
    public function byReference(int $id, array $lines): void
    {
        foreach ($lines as $line) {
            $this->parser->parse($line, $id);
            $this->productRepository->getById($id); // reported: a method may take it by reference
        }
    }

    /**
     * @param list<array<string, int>> $rows
     */
    public function extracted(array $rows): void
    {
        $x = 0;
        foreach ($rows as $row) {
            extract($row);
            $this->productRepository->getById($x); // reported: extract() may set it
        }
    }

    /**
     * @param list<array<string, int>> $rows
     */
    public function variableVariable(array $rows): void
    {
        $y = 0;
        foreach ($rows as $row) {
            $name = 'y';
            $$name = $row['y'];
            $this->productRepository->getById($y); // reported: a variable variable may be it
        }
    }

    /**
     * @param list<int> $ids
     */
    public function referenced(int $id, array $ids): void
    {
        $ref = &$id;
        foreach ($ids as $other) {
            $ref = $other;
            $this->productRepository->getById($id); // reported: changed through a reference
        }
    }

    /**
     * @param list<int> $stores
     */
    public function saved(string $sku, array $stores): void
    {
        foreach ($stores as $store) {
            $product = $this->productRepository->get($sku); // reported: save() makes it load again
            $this->productRepository->save($product);
        }
    }

    /**
     * @param list<int> $stores
     */
    public function called(int $id, array $stores, callable $next): void
    {
        foreach ($stores as $store) {
            $next();
            $this->productRepository->getById($id); // reported: a callable may change anything
        }
    }
}

final class IdParser
{
    public function parse(string $line, int &$id): void
    {
        $id = (int) $line;
    }

    public static function parseStatic(string $line, int &$id): void
    {
        $id = (int) $line;
    }

    public function run(callable $callback): void
    {
        $callback();
    }
}

final class ChangedElsewhere
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly IdParser $parser,
    ) {
    }

    /**
     * @param list<string> $lines
     */
    public function byStaticReference(int $id, array $lines): void
    {
        foreach ($lines as $line) {
            IdParser::parseStatic($line, $id);
            $this->productRepository->getById($id); // reported: a static method may take it by reference
        }
    }

    /**
     * @param list<string> $lines
     */
    public function byClosure(array $lines): void
    {
        $n = 0;
        $increment = function () use (&$n): void {
            ++$n;
        };
        foreach ($lines as $line) {
            $this->parser->run($increment);
            $this->productRepository->getById($n); // reported: a closure changes it by reference
        }
    }

    /**
     * @param list<int> $ids
     * @param list<string> $stores
     */
    public function byCallback(array $ids, array $stores): void
    {
        array_map(function (int $id) use ($stores): void {
            foreach ($stores as $store) {
                $this->productRepository->getById($id); // reported: array_map() passes another id each time
            }
        }, $ids);
        array_map(fn (int $id) => $this->productRepository->get('sku-1'), $ids); // not reported: the same SKU each time, which the repository keeps
    }

    /**
     * @param list<string> $stores
     */
    public function byNamedForceReload(string $sku, array $stores): void
    {
        foreach ($stores as $store) {
            $this->productRepository->get($sku, forceReload: true); // reported: it loads again every time
        }
    }
}

final class FirstClassCallables
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * @param list<string> $lines
     */
    public function run(int $id, array $lines): void
    {
        foreach ($lines as $line) {
            $lengths = array_map(strlen(...), $lines);
            $load = $this->productRepository->getById(...);
            $this->productRepository->getById($id); // not reported: strlen(...) and getById(...) pass nothing
        }
    }
}
