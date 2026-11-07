<?php

declare(strict_types=1);

namespace Magento\Catalog\Model {
    class Product extends \Magento\Framework\Model\AbstractModel implements \Magento\Catalog\Api\Data\ProductInterface
    {
        public function getSku(): string
        {
            return '';
        }

        /** @return list<int> */
        public function getCategoryIds(): array
        {
            return [];
        }

        public function getProductUrl(): string
        {
            return '';
        }

        public function getUrlInStore(): string
        {
            return '';
        }

        public function getUrlModel(): \Magento\Catalog\Model\Product\Url
        {
            return new \Magento\Catalog\Model\Product\Url();
        }

        public function getAttributeText(string $code): string
        {
            return $code;
        }

        /** @return int|false */
        public function getIdBySku(string $sku)
        {
            return false;
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

        public function save(Product $product): Product
        {
            return $product;
        }

        public function delete(Product $product): bool
        {
            return true;
        }

        public function deleteById(string $sku): bool
        {
            return true;
        }
    }
}

namespace Magento\Catalog\Model\Product {
    class Url
    {
        public function getUrl(\Magento\Catalog\Model\Product $product): string
        {
            return '';
        }

        public function getProductUrl(\Magento\Catalog\Model\Product $product): string
        {
            return '';
        }

        public function getUrlInStore(\Magento\Catalog\Model\Product $product): string
        {
            return '';
        }
    }
}

namespace Magento\Catalog\Model\ResourceModel {
    class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        /** @return int|false */
        public function getIdBySku(string $sku)
        {
            return false;
        }
    }
}

namespace Magento\Catalog\Model\Product\Type {
    abstract class AbstractType
    {
        /** @return array<int, array<int, int>> */
        public function getChildrenIds(int $parentId, bool $required = true): array
        {
            return [];
        }

        /**
         * @param int|list<int> $childId
         * @return list<int>
         */
        public function getParentIdsByChild($childId): array
        {
            return [];
        }
    }
}

namespace Magento\ConfigurableProduct\Model\Product\Type {
    class Configurable extends \Magento\Catalog\Model\Product\Type\AbstractType
    {
        /** @return list<\Magento\Catalog\Model\Product> */
        public function getUsedProducts(\Magento\Catalog\Model\Product $product): array
        {
            return [];
        }
    }
}

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type {
    class Configurable extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        /**
         * @param int|list<int> $childId
         * @return list<int>
         */
        public function getParentIdsByChild($childId): array
        {
            return [];
        }
    }
}

namespace Magento\Eav\Model\Entity\Collection {
    abstract class AbstractCollection extends \Magento\Framework\Data\Collection\AbstractDb
    {
        /**
         * @param string|list<string> $attribute
         * @return $this
         */
        public function addAttributeToSelect($attribute)
        {
            return $this;
        }
    }
}

namespace Magento\Eav\Model\Entity\Attribute\Source {
    abstract class AbstractSource
    {
        /** @param string|int $value */
        public function getOptionText($value): string
        {
            return '';
        }
    }

    class Table extends AbstractSource
    {
    }

    class Boolean extends AbstractSource
    {
    }
}

namespace Magento\Catalog\Model\ResourceModel\Product {
    class Collection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
    {
        /** @return $this */
        public function addCategoryIds()
        {
            return $this;
        }

        /** @return $this */
        public function addUrlRewrite()
        {
            return $this;
        }

        /**
         * @param mixed $condition
         * @return $this
         */
        public function addAttributeToFilter(string $attribute, $condition = null)
        {
            return $this;
        }

        /**
         * @param int|list<int> $productId
         * @return $this
         */
        public function addIdFilter($productId)
        {
            return $this;
        }
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

        public function save(\Magento\Catalog\Model\Product $product): \Magento\Catalog\Model\Product;

        public function delete(\Magento\Catalog\Model\Product $product): bool;

        public function deleteById(string $sku): bool;
    }
}

namespace Magento\Catalog\Api\Data {
    interface ProductInterface
    {
    }

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

namespace Magento\Directory\Model {
    class Currency extends \Magento\Framework\Model\AbstractModel
    {
    }
}

namespace Magento\Store\Api {
    interface StoreRepositoryInterface
    {
        public function get(string $code): object;

        public function getById(int $id): object;

        public function getList(): array;
    }

    interface WebsiteRepositoryInterface
    {
        public function get(string $code): object;

        public function getById(int $id): object;
    }

    interface GroupRepositoryInterface
    {
        public function get(int $id): object;
    }
}
