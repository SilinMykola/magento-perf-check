<?php

declare(strict_types=1);

namespace LoopCallQueryFixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Api\SearchCriteriaInterface;

abstract class Base
{
    public function __construct(protected readonly ProductRepositoryInterface $productRepository)
    {
    }

    protected function inherited(int $id): string
    {
        return (string) $this->productRepository->getById($id)->getName();
    }
}

final class StockReader
{
    public function __construct(private readonly Product $product)
    {
    }

    public function qty(int $id): float
    {
        return (float) $this->product->load($id)->getData('qty');
    }
}

interface NameReaderInterface
{
    public function name(int $id): string;
}

final class NameReader implements NameReaderInterface
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    public function name(int $id): string
    {
        return (string) $this->productRepository->getById($id)->getName();
    }
}

interface LocalRepositoryInterface
{
    public function getById(int $id): Product;
}

final class LocalRepository implements LocalRepositoryInterface
{
    public function __construct(private readonly Product $product)
    {
    }

    public function getById(int $id): Product
    {
        return $this->product->load($id);
    }
}

final class BackupReader
{
    public function __construct(private readonly Product $product)
    {
    }

    public function qty(int $id): float
    {
        return (float) $this->product->load($id)->getData('qty');
    }
}

final class CachedReader
{
    /** @var array<int, string> */
    private array $names = [];

    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    public function name(int $id): string
    {
        return $this->names[$id] ??= (string) $this->productRepository->getById($id)->getName();
    }
}

final class OrderLine
{
    private ?Product $product = null;

    public function __construct(private readonly ProductRepositoryInterface $productRepository, private readonly int $productId)
    {
    }

    public function product(): Product
    {
        return $this->product ??= $this->productRepository->getById($this->productId);
    }

    public function reader(): CachedReader
    {
        return new CachedReader($this->productRepository);
    }
}

final class CachedReaderFactory
{
    public function create(): CachedReader
    {
        return new CachedReader(new \Magento\Catalog\Model\ProductRepository());
    }
}

final class Example extends Base
{
    use NameLookup;

    /** @var array<int, Product> */
    private array $products = [];

    /** @var array<int, string> */
    private static array $names = [];

    private ?Product $last = null;

    private ?Product $guarded = null;

    /** @var list<string> */
    private array $logged = [];

    /** @var array<int, string> */
    private array $labels = [];

    public function __construct(
        ProductRepositoryInterface $productRepository,
        private readonly ProductRepository $concreteRepository,
        private readonly ProductResource $productResource,
        private readonly StockReader $stockReader,
        private readonly ?StockReader $optionalReader,
        private readonly NameReaderInterface $nameReader,
        private readonly SearchCriteriaInterface $criteria,
        private readonly LocalRepository $localRepository,
        private readonly CachedReader $cachedReader,
        private readonly CachedReaderFactory $readerFactory,
    ) {
        parent::__construct($productRepository);
    }

    /**
     * @param list<int> $ids
     */
    public function violations(array $ids, Product $product): void
    {
        foreach ($ids as $id) {
            $this->productName($id);
        }
        foreach ($ids as $id) {
            $this->stockReader->qty($id);
        }
        foreach ($ids as $id) {
            $this->loadInto($product, $id);
        }
        array_map(fn (int $id) => $this->search(), $ids);
        foreach ($ids as $id) {
            $this->twoQueries($id);
        }
        foreach ($ids as $id) {
            $this->inherited($id);
        }
        foreach ($ids as $id) {
            // A plain property is not a cache per key.
            $this->remember($id);
        }
        foreach ($ids as $id) {
            // The if tests another property than the one it assigns.
            $this->rememberWhenEnabled($id);
            // A lazy getter for another value; the query itself is not kept.
            $this->counted($id);
            $this->loadThenCache($id);
            $this->labelled($id);
            // An if on the property that neither returns nor assigns it.
            $this->reload($id);
            // An append keeps nothing for a later call.
            $this->log($id);
            self::staticName($this->productRepository, $id);
            $this->optionalReader?->qty($id);
            // Declared in a trait, in another file.
            $this->traitName($id);
            parent::inherited($id);
            Example::staticName($this->productRepository, $id);
            // The callee runs the query in its own array_map().
            $this->namesOf([$id]);
        }
    }

    /**
     * @param list<OrderLine> $lines
     */
    public function newObjectEachTime(array $lines): void
    {
        foreach ($lines as $line) {
            // A lazy getter, but on a new object in each iteration.
            $line->product();
            // A cache per key, on an object reached from the item, built with new or by a factory.
            $line->reader()->name(1);
            (new CachedReader($this->productRepository))->name(1);
            $this->readerFactory->create()->name(1);
        }
    }

    /**
     * @param list<int> $ids
     */
    public function allowed(array $ids, StockReader|BackupReader $readers, CachedReader $reader): void
    {
        // Outside a loop.
        $this->productName(1);
        foreach ($ids as $id) {
            // Cached per key.
            $this->cachedProduct($id);
            $this->cachedName($id);
            // Queries only through another method.
            $this->nameThroughHelper($id);
            // Through an interface.
            $this->nameReader->name($id);
            // A query itself: P-LOOP-01 reports it.
            $this->concreteRepository->getById($id);
            $this->localRepository->getById($id);
            // More than one possible class.
            $readers->qty($id);
        }
        foreach ($ids as $id) {
            if ($id > 10) {
                $this->productName($id);
                break;
            }
        }
        foreach ($ids as $id) {
            // Kept for later calls on the same object.
            $this->lastProduct($id);
            $this->guardedProduct($id);
            // The closure is returned, not run.
            $this->loader();
            // Kept for later calls, on a property of $this, with :: or on a parameter.
            $this->cachedReader->name($id);
            self::staticCached($this->productRepository, $id);
            $reader->name($id);
            // A lazy getter that returns early.
            $this->current($id);
        }
        $page = 1;
        while ($page < 10) {
            // Paging through one result: P-LOOP-09 leaves getList() in such a loop out.
            $this->search();
            ++$page;
        }
    }

    private function productName(int $id): string
    {
        return (string) $this->productRepository->getById($id)->getName();
    }

    private function loadInto(Product $product, int $id): void
    {
        $this->productResource->load($product, $id);
    }

    private function search(): int
    {
        return count($this->productRepository->getList($this->criteria)->getItems());
    }

    private function twoQueries(int $id): string
    {
        $first = $this->productRepository->getById($id);
        $second = $this->productRepository->getById($id + 1);

        return $first->getName() . $second->getName();
    }

    private function cachedProduct(int $id): Product
    {
        if (!isset($this->products[$id])) {
            $this->products[$id] = $this->productRepository->getById($id);
        }

        return $this->products[$id];
    }

    private function cachedName(int $id): string
    {
        return self::$names[$id] ??= (string) $this->productRepository->getById($id)->getName();
    }

    private function remember(int $id): void
    {
        $this->last = $this->productRepository->getById($id);
    }

    private function counted(int $id): Product
    {
        if ($this->guarded === null) {
            $this->guarded = $this->productRepository->getById(1);
        }

        return $this->productRepository->getById($id);
    }

    private function loadThenCache(int $id): Product
    {
        $product = $this->productRepository->getById($id);
        if ($this->guarded === null) {
            $this->guarded = $product;
        }

        return $product;
    }

    private function labelled(int $id): Product
    {
        $this->labels[$id] = (string) $id;

        return $this->productRepository->getById($id);
    }

    private function reload(int $id): ?Product
    {
        if ($this->last !== null) {
            $this->logged = [];
        }
        $this->last = $this->productRepository->getById($id);

        return $this->last;
    }

    private function current(int $id): Product
    {
        if ($this->last !== null) {
            return $this->last;
        }

        return $this->last = $this->productRepository->getById($id);
    }

    private function rememberWhenEnabled(int $id): void
    {
        if ($this->logged !== []) {
            $this->last = $this->productRepository->getById($id);
        }
    }

    private function log(int $id): void
    {
        $this->logged[] = (string) $this->productRepository->getById($id)->getName();
    }

    private static function staticName(ProductRepositoryInterface $repository, int $id): string
    {
        return (string) $repository->getById($id)->getName();
    }

    private function lastProduct(int $id): Product
    {
        return $this->last ??= $this->productRepository->getById($id);
    }

    private function guardedProduct(int $id): Product
    {
        if ($this->guarded === null) {
            $this->guarded = $this->productRepository->getById($id);
        }

        return $this->guarded;
    }

    private function loader(): \Closure
    {
        return fn (int $id): Product => $this->productRepository->getById($id);
    }

    /**
     * @param list<int> $ids
     * @return list<Product>
     */
    private function namesOf(array $ids): array
    {
        return array_map(fn (int $id): Product => $this->productRepository->getById($id), $ids);
    }

    private static function staticCached(ProductRepositoryInterface $repository, int $id): string
    {
        return self::$names[$id] ??= (string) $repository->getById($id)->getName();
    }

    private function nameThroughHelper(int $id): string
    {
        return $this->productName($id);
    }
}
