<?php

declare(strict_types=1);

namespace LoopCallQueryFixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

final class FallbackReader
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly Product $product,
    ) {
    }

    /**
     * @param list<int> $ids
     * @param array<int, Product> $known
     */
    public function run(array $ids, array $known): void
    {
        foreach ($ids as $id) {
            // The query runs only when the value on the left is empty.
            $this->found($id, $known[$id] ?? null);
            $this->known($id, $known);
            $this->nested($id, $known);
            // The query on the left of ?: or ?? runs on every call.
            $this->loadedFirst($id);
            $this->fetchedFirst($id);
            // A full ternary is a branch on the id, not a fallback.
            $this->byId($id);
        }
    }

    private function found(int $id, ?Product $product): Product
    {
        return $product ?: $this->product->load($id);
    }

    /**
     * @param array<int, Product> $known
     */
    private function known(int $id, array $known): Product
    {
        return $known[$id] ?? $this->productRepository->getById($id);
    }

    /**
     * @param array<int, Product> $known
     */
    private function nested(int $id, array $known): ?Product
    {
        return $known[$id] ?? ($known[0] ?: $this->product->load($id));
    }

    private function loadedFirst(int $id): ?Product
    {
        return $this->product->load($id) ?: null;
    }

    private function fetchedFirst(int $id): mixed
    {
        return $this->productRepository->getById($id)->getData('parent') ?? $id;
    }

    private function byId(int $id): ?Product
    {
        return $id <= 0 ? null : $this->product->load($id);
    }
}

abstract class FallbackBase
{
    /** @var array<int, Product> */
    protected array $inherited = [];
}

trait FallbackNames
{
    /** @var array<int, Product> */
    private array $traitProducts = [];

    private function fromTrait(int $id): mixed
    {
        return $this->traitProducts[$id] ?? $this->productRepository->getById($id);
    }
}

final class PropertyFallbackReader extends FallbackBase
{
    /** @var array<int, string> */
    private array $names = [];

    /** @var array<int, Product> */
    private array $cache = [];

    /** @var array<int, Product> */
    private array $products = [];

    /** @var array<int, Product> */
    private array $seen;

    /** @var array<int, string> */
    private array $labels = [1 => 'Default'];

    /** @var array<int, list<int>> */
    private array $groups = [];

    /** @var array<int, Product> */
    protected array $shared = [];

    /** @var array<int, Product>|null */
    private ?array $loaded = null;

    /** @var array<int, int> */
    private static array $parents = [];

    /** @var array<int, Product> */
    private static array $byKey = [];

    /**
     * @param array<int, Product> $injected
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly array $injected = [],
    ) {
        $this->seen = [];
    }

    /**
     * @param list<int> $ids
     */
    public function run(array $ids): void
    {
        foreach ($ids as $id) {
            // A property that the class fills, a default or injected map, or a property of the parent class.
            $this->cached($id);
            $this->memoized($id);
            $this->labelled($id);
            $this->fromInjected($id);
            $this->fromInherited($id);
            // A static property or an element filled elsewhere, an element set to [] or a protected property.
            $this->fromStatic($id);
            $this->fromGroups($id);
            $this->fromShared($id);
            // A property that the class never fills: the fallback runs on every call.
            $this->name($id);
            $this->fromSeen($id);
            $this->parentId($id);
            $this->fromLoaded($id);
        }
    }

    public function remember(Product $product): void
    {
        $this->cache[(int) $product->getId()] = $product;
        self::$byKey[(int) $product->getId()] = $product;
        $this->groups[(int) $product->getId()] = [];
    }

    public function reset(): void
    {
        $this->loaded = null;
    }

    private function cached(int $id): mixed
    {
        return $this->cache[$id] ?? $this->productRepository->getById($id);
    }

    private function memoized(int $id): mixed
    {
        return $this->products[$id] ?? ($this->products[$id] = $this->productRepository->getById($id));
    }

    private function labelled(int $id): mixed
    {
        return $this->labels[$id] ?? $this->productRepository->getById($id)->getName();
    }

    private function fromInjected(int $id): mixed
    {
        return $this->injected[$id] ?? $this->productRepository->getById($id);
    }

    private function fromInherited(int $id): mixed
    {
        return $this->inherited[$id] ?? $this->productRepository->getById($id);
    }

    private function fromStatic(int $id): mixed
    {
        return self::$byKey[$id] ?? $this->productRepository->getById($id);
    }

    private function fromGroups(int $id): mixed
    {
        return $this->groups[$id] ?? $this->productRepository->getById($id);
    }

    private function fromShared(int $id): mixed
    {
        return $this->shared[$id] ?? $this->productRepository->getById($id);
    }

    private function fromLoaded(int $id): mixed
    {
        return $this->loaded[$id] ?? $this->productRepository->getById($id);
    }

    private function name(int $id): mixed
    {
        return $this->names[$id] ?? $this->productRepository->getById($id)->getName();
    }

    private function fromSeen(int $id): mixed
    {
        return $this->seen[$id] ?? $this->productRepository->getById($id);
    }

    private function parentId(int $id): mixed
    {
        return self::$parents[$id] ?: $this->productRepository->getById($id)->getId();
    }
}

trait FallbackFiller
{
    public function fill(Product $product): void
    {
        $this->filled[(int) $product->getId()] = $product;
    }
}

final class TraitFallbackReader
{
    use FallbackNames;
    use FallbackFiller;

    /** @var array<int, Product> */
    private array $filled = [];

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * @param list<int> $ids
     */
    public function run(array $ids): void
    {
        foreach ($ids as $id) {
            // A property of a trait, or one that a method of a trait fills.
            $this->fromTrait($id);
            $this->fromFilled($id);
        }
    }

    private function fromFilled(int $id): mixed
    {
        return $this->filled[$id] ?? $this->productRepository->getById($id);
    }
}

final class FallbackRegistry
{
    /** @var array<int, Product> */
    public static array $items = [];
}

final class CountedFallbackReader
{
    /** @var array<int, Product> */
    private static array $items = [];

    /** @var array<int, Product> */
    private static array $own = [];

    /** @var array<int, Product> */
    private static array $named = [];

    private ?int $hits = null;

    private ?int $misses = null;

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    /**
     * @param list<int> $ids
     */
    public function run(array $ids): void
    {
        foreach ($ids as $id) {
            // A static property of another class with the same name, one filled through the class name,
            // and properties changed with ++ or --.
            $this->fromRegistry($id);
            $this->fromOwn($id);
            $this->counted($id);
            $this->uncounted($id);
            // A property the class never fills, read through the class name.
            $this->fromNamed($id);
        }
    }

    public function remember(Product $product): void
    {
        CountedFallbackReader::$own[(int) $product->getId()] = $product;
        $this->hits++;
        --$this->misses;
    }

    private function fromRegistry(int $id): mixed
    {
        return FallbackRegistry::$items[$id] ?? $this->productRepository->getById($id);
    }

    private function fromOwn(int $id): mixed
    {
        return self::$own[$id] ?? $this->productRepository->getById($id);
    }

    private function counted(int $id): mixed
    {
        return $this->hits ?: $this->productRepository->getById($id);
    }

    private function uncounted(int $id): mixed
    {
        return $this->misses ?: $this->productRepository->getById($id);
    }

    private function fromNamed(int $id): mixed
    {
        return CountedFallbackReader::$named[$id] ?? $this->productRepository->getById($id);
    }
}
