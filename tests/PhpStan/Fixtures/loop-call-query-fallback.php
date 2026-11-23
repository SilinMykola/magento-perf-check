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
