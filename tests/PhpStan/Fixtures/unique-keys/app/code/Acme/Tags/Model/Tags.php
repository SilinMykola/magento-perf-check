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

        /**
         * Joins the labels table, like Order\Status\Collection::joinStates().
         *
         * @return $this
         */
        public function joinLabels()
        {
            return $this;
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

        public function keyFilteredOnlyInABranch(OrderCollection $orderTags, ?int $id, int $orderId): void
        {
            if ($id !== null) {
                $orderTags->addFieldToFilter('id', $id);
            }
            $orderTags->addFieldToFilter('order_id', $orderId);
            $orderTags->getFirstItem();
        }

        public function keyFilteredInTheSameBranch(OrderCollection $orderTags, ?int $id): void
        {
            if ($id !== null) {
                $orderTags->addFieldToFilter('id', $id);
                $orderTags->getFirstItem();
            }
        }

        public function keyFilteredBeforeTheBranch(OrderCollection $orderTags, int $id, bool $load): void
        {
            $orderTags->addFieldToFilter('id', $id);
            if ($load) {
                $orderTags->getFirstItem();
            }
        }

        public function keyFilteredInTheOtherBranch(OrderCollection $orderTags, ?int $id): void
        {
            if ($id !== null) {
                $orderTags->addFieldToFilter('id', $id);
            } else {
                $orderTags->getFirstItem();
            }
        }

        public function keyWithAJoin(OrderCollection $orderTags, int $id): void
        {
            $orderTags->addFieldToFilter('id', $id);
            $orderTags->getSelect()->joinLeft(['label' => 'acme_label'], 'label.order_tag_id = main_table.id', []);
            $orderTags->getFirstItem();
        }

        public function keyWithAJoinInTheChain(OrderCollection $orderTags, int $id): void
        {
            $orderTags->addFieldToFilter('id', $id)->joinLabels()->getFirstItem();
        }

        public function keyFilteredOnlyInTheElseBranch(OrderCollection $orderTags, ?int $id, int $orderId): void
        {
            if ($id === null) {
                $orderTags->addFieldToFilter('order_id', $orderId);
            } else {
                $orderTags->addFieldToFilter('id', $id);
            }
            $orderTags->getFirstItem();
        }
    }
}

namespace Acme\Tags\Model\ResourceModel {
    class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init('acme_tag_queue', 'queue_id');
        }
    }

    class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init('acme_tag_rule', 'rule_id');
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Queue {
    class Collection extends \Magento\Framework\Data\Collection\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init(\Acme\Tags\Model\Queue::class, \Acme\Tags\Model\ResourceModel\Queue::class);
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Rule {
    class Collection extends \Magento\Framework\Data\Collection\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init(\Acme\Tags\Model\Rule::class, \Acme\Tags\Model\ResourceModel\Rule::class);
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Order\Archive {
    /** Reads another table: its _construct() replaces the parent's without calling it. */
    class Collection extends \Acme\Tags\Model\ResourceModel\Order\Collection
    {
        protected function _construct(): void
        {
            $this->_setMainTable('acme_tag_archive');
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Order\Mapped {
    class Collection extends \Acme\Tags\Model\ResourceModel\Order\Collection
    {
        protected function _construct(): void
        {
            parent::_construct();
            $this->addFilterToMap('id', 'main_table.id');
        }
    }
}

namespace Acme\Tags\Model {
    use Acme\Tags\Model\ResourceModel\Order\Collection as OrderCollection;

    final class MoreTags
    {
        private OrderCollection $orderTags;

        public function __construct(private readonly \Acme\Tags\Model\ResourceModel\Order\CollectionFactory $orderTagsFactory)
        {
            $this->orderTags = $orderTagsFactory->create();
        }

        public function listOfIds(OrderCollection $orderTags, array $ids): void
        {
            // A list becomes IN (...).
            $orderTags->addFieldToFilter('id', $ids)->getFirstItem();
        }

        public function intOrListOfIds(OrderCollection $orderTags, int|array $id): void
        {
            $orderTags->addFieldToFilter('id', $id);
            $orderTags->getFirstItem();
        }

        public function eqCondition(OrderCollection $orderTags, int $id): void
        {
            $orderTags->addFieldToFilter('id', ['eq' => $id])->getFirstItem();
        }

        public function eqConditionWithAList(OrderCollection $orderTags, array $ids): void
        {
            // ['eq' => $ids] renders "= 1, 2", never IN (...).
            $orderTags->addFieldToFilter('id', ['eq' => $ids])->getFirstItem();
        }

        public function valueOfUnknownType(OrderCollection $orderTags, $id): void
        {
            $orderTags->addFieldToFilter('id', $id)->getFirstItem();
        }

        public function propertyReplacedByANewCollection(int $id): void
        {
            $this->orderTags->addFieldToFilter('id', $id);
            $this->orderTags = $this->orderTagsFactory->create();
            $this->orderTags->getFirstItem();
        }

        public function keyFilterInAnArrowFunction(OrderCollection $orderTags, int $id): void
        {
            $filter = fn () => $orderTags->addFieldToFilter('id', $id);
            $orderTags->getFirstItem();
        }

        public function keyFilterInAShortTernary(OrderCollection $orderTags, bool $skip, int $id): void
        {
            $skip ?: $orderTags->addFieldToFilter('id', $id);
            $orderTags->getFirstItem();
        }

        public function keyFilterInACoalesce(OrderCollection $orderTags, ?OrderCollection $given, int $id): void
        {
            $given ?? $orderTags->addFieldToFilter('id', $id);
            $orderTags->getFirstItem();
        }

        public function keyFilterInAnAnd(OrderCollection $orderTags, ?int $id): void
        {
            $id !== null && $orderTags->addFieldToFilter('id', $id);
            $orderTags->getFirstItem();
        }

        public function keyFilterInAMatchArm(OrderCollection $orderTags, string $mode, int $id): void
        {
            match ($mode) {
                'id' => $orderTags->addFieldToFilter('id', $id),
                default => null,
            };
            $orderTags->getFirstItem();
        }

        public function keyFilterInASwitchCase(OrderCollection $orderTags, string $mode, int $id): void
        {
            switch ($mode) {
                case 'id':
                    $orderTags->addFieldToFilter('id', $id);
                    break;
            }
            $orderTags->getFirstItem();
        }

        public function keyFilterInACatch(OrderCollection $orderTags, int $id): void
        {
            try {
                $orderTags->setCurPage(1);
            } catch (\RuntimeException $e) {
                $orderTags->addFieldToFilter('id', $id);
            }
            $orderTags->getFirstItem();
        }

        public function keyFilterInALoop(OrderCollection $orderTags, int $times, int $id): void
        {
            for ($i = 0; $i < $times; $i++) {
                $orderTags->addFieldToFilter('id', $id);
            }
            $orderTags->getFirstItem();
        }

        public function keyThroughAddAttributeToFilter(OrderCollection $orderTags, int $id): void
        {
            $orderTags->addAttributeToFilter('id', $id);
            $orderTags->getFirstItem();
        }

        public function keyWithAnotherFrom(OrderCollection $orderTags, int $id): void
        {
            $orderTags->addFieldToFilter('id', $id);
            $orderTags->getSelect()->from(['label' => 'acme_label'], []);
            $orderTags->getFirstItem();
        }

        public function keyWithAUnion(OrderCollection $orderTags, int $id): void
        {
            $orderTags->addFieldToFilter('id', $id)->getSelect()->union(['SELECT 1']);
            $orderTags->getFirstItem();
        }

        public function collectionWithItsOwnMainTable(\Acme\Tags\Model\ResourceModel\Order\Archive\Collection $archive, int $id): void
        {
            $archive->addFieldToFilter('id', $id)->getFirstItem();
        }

        public function collectionThatCallsTheParentConstruct(\Acme\Tags\Model\ResourceModel\Order\Mapped\Collection $mapped, int $id): void
        {
            $mapped->addFieldToFilter('id', $id)->getFirstItem();
        }

        public function setupKeyOnADeclaredTable(OrderCollection $orderTags, int $labelId): void
        {
            $orderTags->addFieldToFilter('label_id', $labelId)->getFirstItem();
        }

        public function keyFromInstallSchema(\Acme\Tags\Model\ResourceModel\Rule\Collection $rules, string $code): void
        {
            $rules->addFieldToFilter('code', $code)->getFirstItem();
        }

        public function primaryKeyDroppedInTheSameScript(\Acme\Tags\Model\ResourceModel\Queue\Collection $queue, int $id): void
        {
            $queue->addFieldToFilter('queue_id', $id)->getFirstItem();
        }

        public function uniqueKeyDroppedByAnUnreadableName(\Acme\Tags\Model\ResourceModel\Queue\Collection $queue, string $code): void
        {
            $queue->addFieldToFilter('code', $code)->getFirstItem();
        }

        public function listReassignedBeforeGetFirstItem(OrderCollection $orderTags, array $ids): void
        {
            $orderTags->addFieldToFilter('id', $ids);
            $ids = 5;
            $orderTags->getFirstItem();
        }

        public function collectionThatCallsTheParentConstructOnlySometimes(\Acme\Tags\Model\ResourceModel\Order\Conditional\Collection $conditional, int $id): void
        {
            $conditional->addFieldToFilter('id', $id)->getFirstItem();
        }

        public function setupKeyOnATableDeclaredInAnotherCase(\Acme\Tags\Model\ResourceModel\Mixed\Collection $mixed, string $code): void
        {
            $mixed->addFieldToFilter('code', $code)->getFirstItem();
        }

        public function collectionThatReturnsTheParentConstruct(\Acme\Tags\Model\ResourceModel\Order\Returning\Collection $returning, int $id): void
        {
            $returning->addFieldToFilter('id', $id)->getFirstItem();
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Order\Conditional {
    class Collection extends \Acme\Tags\Model\ResourceModel\Order\Collection
    {
        private bool $archived = false;

        protected function _construct(): void
        {
            if ($this->archived) {
                $this->_setMainTable('acme_tag_archive');
            } else {
                parent::_construct();
            }
        }
    }
}

namespace Acme\Tags\Model\ResourceModel {
    class Mixed extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init('acme_tag_mixed', 'id');
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Mixed {
    class Collection extends \Magento\Framework\Data\Collection\AbstractDb
    {
        protected function _construct(): void
        {
            $this->_init(\Acme\Tags\Model\Mixed::class, \Acme\Tags\Model\ResourceModel\Mixed::class);
        }
    }
}

namespace Acme\Tags\Model\ResourceModel\Order\Returning {
    abstract class BaseCollection extends \Magento\Framework\Data\Collection\AbstractDb
    {
        protected function _construct()
        {
            $this->_init(\Acme\Tags\Model\Order::class, \Acme\Tags\Model\ResourceModel\Order::class);
        }
    }

    class Collection extends BaseCollection
    {
        protected function _construct()
        {
            return parent::_construct();
        }
    }
}
