<?php

declare(strict_types=1);

namespace PhpStanCliFixture;

use Magento\Catalog\Api\ProductRepositoryInterface;

final class Suppression
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * @param list<int> $ids
     */
    public function run(array $ids): void
    {
        foreach ($ids as $id) {
            $this->productRepository->getById($id);
        }

        foreach ($ids as $id) {
            // @phpstan-ignore magentoPerf.loopModelLoad (ids are capped at 3 by the caller)
            $this->productRepository->getById($id);
        }
    }
}
