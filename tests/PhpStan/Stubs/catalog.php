<?php

declare(strict_types=1);

namespace Magento\Catalog\Model {
    class Product extends \Magento\Framework\Model\AbstractModel
    {
        public function getSku(): string
        {
            return '';
        }
    }

    class ProductRepository implements \Magento\Catalog\Api\ProductRepositoryInterface
    {
        public function get(string $sku): Product
        {
            return new Product();
        }

        public function getById(int $productId): Product
        {
            return new Product();
        }

        public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria): \Magento\Catalog\Api\Data\ProductSearchResultsInterface
        {
            throw new \LogicException();
        }
    }
}

namespace Magento\Catalog\Model\ResourceModel {
    class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
    }
}

namespace Magento\Catalog\Model\ResourceModel\Product {
    class Collection extends \Magento\Framework\Data\Collection\AbstractDb
    {
    }

    class CollectionFactory
    {
        public function create(): Collection
        {
            return new Collection();
        }
    }
}

namespace Magento\Catalog\Api {
    interface ProductRepositoryInterface
    {
        public function get(string $sku): \Magento\Catalog\Model\Product;

        public function getById(int $productId): \Magento\Catalog\Model\Product;

        public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria): \Magento\Catalog\Api\Data\ProductSearchResultsInterface;
    }
}

namespace Magento\Catalog\Api\Data {
    interface ProductSearchResultsInterface
    {
        /** @return list<\Magento\Catalog\Model\Product> */
        public function getItems(): array;
    }
}

namespace Magento\Framework\Api {
    interface SearchCriteriaInterface
    {
    }
}

namespace Magento\Customer\Api {
    interface CustomerRepositoryInterface
    {
        public function get(string $email): object;
    }
}
