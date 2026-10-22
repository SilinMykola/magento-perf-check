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
}
