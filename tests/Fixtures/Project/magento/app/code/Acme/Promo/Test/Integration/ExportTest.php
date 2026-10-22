<?php

declare(strict_types=1);

namespace Acme\Promo\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;

final class ExportTest
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * @param list<int> $ids
     */
    public function testExport(array $ids): void
    {
        foreach ($ids as $id) {
            $this->productRepository->getById($id);
        }
    }
}
