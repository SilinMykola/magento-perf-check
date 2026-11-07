<?php

// Minimal stand-ins for the Magento classes the rules look at. Only the type
// hierarchy and the methods the fixtures call matter.

declare(strict_types=1);

namespace Magento\Framework\Model {
    abstract class AbstractModel
    {
        /**
         * @param mixed $modelId
         * @return $this
         */
        public function load($modelId, ?string $field = null)
        {
            return $this;
        }

        /** @return $this */
        public function save()
        {
            return $this;
        }

        /** @return $this */
        public function delete()
        {
            return $this;
        }
    }
}

namespace Magento\Framework\Model\ResourceModel\Db {
    abstract class AbstractDb
    {
        /**
         * @param mixed $value
         * @return $this
         */
        public function load(\Magento\Framework\Model\AbstractModel $object, $value, ?string $field = null)
        {
            return $this;
        }

        /** @return $this */
        public function save(\Magento\Framework\Model\AbstractModel $object)
        {
            return $this;
        }

        /** @return $this */
        public function delete(\Magento\Framework\Model\AbstractModel $object)
        {
            return $this;
        }
    }
}

namespace Magento\Framework\Data {
    /**
     * @implements \IteratorAggregate<int, \Magento\Framework\DataObject>
     */
    class Collection implements \IteratorAggregate, \Countable
    {
        /** @return \ArrayIterator<int, \Magento\Framework\DataObject> */
        public function getIterator(): \ArrayIterator
        {
            return new \ArrayIterator([]);
        }

        public function count(): int
        {
            return 0;
        }

        /** @return list<\Magento\Framework\DataObject> */
        public function getItems(): array
        {
            return [];
        }

        /** @return int */
        public function getSize()
        {
            return 0;
        }

        /** @return $this */
        public function load(bool $printQuery = false, bool $logQuery = false)
        {
            return $this;
        }

        /** @return \Magento\Framework\DataObject */
        public function getFirstItem()
        {
            return new \Magento\Framework\DataObject();
        }

        /** @return $this */
        public function setPage(int $page, int $size)
        {
            return $this;
        }

        /**
         * @param int|list<int> $ids
         * @return $this
         */
        public function addIdFilter($ids)
        {
            return $this;
        }

        /** @return $this */
        public function setIdFilter(int $id)
        {
            return $this;
        }

        /** @return $this */
        public function setPageSize(?int $size)
        {
            return $this;
        }

        /** @return $this */
        public function setCurPage(int $page)
        {
            return $this;
        }

        /**
         * @param mixed $condition
         * @return $this
         */
        public function addFieldToFilter(string $field, $condition = null)
        {
            return $this;
        }
    }
}

namespace Magento\Framework\Data\Collection {
    abstract class AbstractDb extends \Magento\Framework\Data\Collection
    {
        public function getSelect(): \Magento\Framework\DB\Select
        {
            return new \Magento\Framework\DB\Select();
        }
    }
}

namespace Magento\Framework\DB {
    class Select
    {
        /** @return $this */
        public function limit(int $count = 0, int $offset = 0)
        {
            return $this;
        }
    }
}

namespace Magento\Framework {
    class DataObject
    {
        /** @return mixed */
        public function getId()
        {
            return null;
        }
    }
}

namespace Magento\Framework\App\Config {
    interface ScopeConfigInterface
    {
        /** @return mixed */
        public function getValue(string $path, string $scopeType = 'default', ?string $scopeCode = null);
    }
}

namespace Magento\Framework\App {
    interface RequestInterface
    {
        /**
         * @param mixed $default
         * @return mixed
         */
        public function getParam(string $key, $default = null);

        /** @return mixed */
        public function get(string $key);
    }
}
