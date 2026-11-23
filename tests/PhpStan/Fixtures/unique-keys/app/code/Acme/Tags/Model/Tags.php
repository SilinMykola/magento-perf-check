<?php

// Unique keys: etc/db_schema.xml (acme_tag_order) and Setup/UpgradeSchema.php (acme_tag_stock, acme_tag_source).

declare(strict_types=1);

namespace Acme\Tags\Model\ResourceModel {
    class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init('acme_tag_order', 'id');
        }
    }

    class Stock extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        public const MAIN_TABLE = 'acme_tag_stock';

        protected function _construct(): void
        {
            $this->_init(self::MAIN_TABLE, 'stock_item_id');
        }
    }

    class Source extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init('acme_tag_source', 'id');
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Order {
    class Collection extends \Magento\Framework\Data\Collection\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init(\Acme\Tags\Model\Order::class, \Acme\Tags\Model\ResourceModel\Order::class);
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Order {
    class CollectionFactory
    {
        public function create(): Collection
        {
            return new Collection();
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Stock {
    class Collection extends \Magento\Framework\Data\Collection\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init('Acme\Tags\Model\Stock', 'Acme\Tags\Model\ResourceModel\Stock');
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Source {
    abstract class BaseCollection extends \Magento\Framework\Data\Collection\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init(\Acme\Tags\Model\Source::class, \Acme\Tags\Model\ResourceModel\Source::class);
        }
    }

    class Collection extends BaseCollection
    {
    }
}

namespace Acme\Tags\Model {
    use Acme\Tags\Model\ResourceModel\Order\Collection as OrderCollection;
    use Acme\Tags\Model\ResourceModel\Stock\Collection as StockCollection;
    use Acme\Tags\Model\ResourceModel\Source\Collection as SourceCollection;

    final class Tags
    {
        public function __construct(private readonly \Acme\Tags\Model\ResourceModel\Order\CollectionFactory $orderTagsFactory)
        {
        }

        public function keySplitOverTwoNewCollections(int $labelId, int $orderId): void
        {
            $this->orderTagsFactory->create()->addFieldToFilter('label_id', $labelId)->getSize();
            $this->orderTagsFactory->create()->addFieldToFilter('order_id', $orderId)->getFirstItem();
        }

        public function fullCompositeKey(OrderCollection $orderTags, int $labelId, int $orderId): void
        {
            $orderTags
                ->addFieldToFilter('label_id', $labelId)
                ->addFieldToFilter('order_id', $orderId);
            $orderTags->getFirstItem();
        }

        public function partOfTheKey(OrderCollection $orderTags, int $orderId): void
        {
            $orderTags->addFieldToFilter('order_id', $orderId)->getFirstItem();
        }

        public function primaryKeyUnderAnotherName(OrderCollection $orderTags, int $id): void
        {
            $orderTags->addFieldToFilter('id', $id)->getFirstItem();
        }

        public function notAnEquality(OrderCollection $orderTags, int $labelId, array $orderIds): void
        {
            $orderTags->addFieldToFilter('label_id', $labelId)
                ->addFieldToFilter('order_id', ['in' => $orderIds])
                ->getFirstItem();
        }

        public function mainTableAlias(OrderCollection $orderTags, int $labelId, int $orderId): void
        {
            $orderTags->addFieldToFilter('main_table.label_id', $labelId)
                ->addFieldToFilter('main_table.order_id', ['eq' => $orderId])
                ->getFirstItem();
        }

        public function joinedTableAlias(OrderCollection $orderTags, int $labelId, int $orderId): void
        {
            $orderTags->addFieldToFilter('joined.label_id', $labelId)
                ->addFieldToFilter('order_id', $orderId)
                ->getFirstItem();
        }

        public function keyFromNewTable(StockCollection $stock, int $productId, int $stockId): void
        {
            $item = $stock->addFieldToFilter('stock_id', $stockId)
                ->addFieldToFilter('product_id', $productId)
                ->getFirstItem();
        }

        public function plainIndexFromNewTable(StockCollection $stock, int $productId): void
        {
            $stock->addFieldToFilter('product_id', $productId)->getFirstItem();
        }

        public function primaryColumnFromNewTable(StockCollection $stock, int $id): void
        {
            $stock->addFieldToFilter('stock_item_id', $id)->getFirstItem();
        }

        public function keyAddedOnTheConnection(SourceCollection $sources, int $sourceId, int $productId): void
        {
            $sources->addFieldToFilter('source_id', $sourceId)
                ->addFieldToFilter('product_id', $productId)
                ->getFirstItem();
        }

        public function uniqueIndexDroppedLater(SourceCollection $sources, string $code): void
        {
            $sources->addFieldToFilter('source_code', $code)->getFirstItem();
        }

        public function plainIndexAddedOnTheConnection(SourceCollection $sources, int $productId): void
        {
            $sources->addFieldToFilter('product_id', $productId)->getFirstItem();
        }
    }
}
