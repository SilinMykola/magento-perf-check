<?php

declare(strict_types=1);

namespace Acme\Promo\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;

final class PriceExporter
{
    public function __construct(private readonly ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * @param list<int> $ids
     * @return list<string>
     */
    public function export(array $ids): array
    {
        $rows = [];
        foreach ($ids as $id) {
            $rows[] = $this->productRepository->getById($id)->getSku();
        }

        return $rows;
    }
}
