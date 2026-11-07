<?php

declare(strict_types=1);

namespace Acme\Promo\Setup {
    use Magento\Framework\Indexer\IndexerInterface;

    final class Recurring
    {
        public function __construct(private readonly IndexerInterface $indexer)
        {
        }

        public function install(object $setup, object $context): void
        {
            $setup->startSetup();
            $this->indexer->reindexAll();
            $setup->endSetup();
        }
    }

    final class RecurringData
    {
        public function install(object $setup, object $context): void
        {
            if (!$setup->getConnection()->tableColumnExists('acme_promo', 'label')) {
                $setup->getConnection()->addColumn('acme_promo', 'label', []);
            }
        }
    }
}

namespace Acme\Promo\Setup\Patch\Data {
    final class AddRules
    {
        /** @param list<int> $ids */
        public function apply(array $ids): void
        {
            foreach ($ids as $id) {
                $id++;
            }
        }
    }
}

namespace Acme\Promo\Model {
    final class Recurring
    {
        /** @param list<int> $ids */
        public function install(array $ids): void
        {
            foreach ($ids as $id) {
                $id++;
            }
        }
    }
}

namespace Acme\Other\Setup {
    final class Recurring
    {
        public function install(object $setup, object $context): void
        {
            $tables = ['acme_promo_rule', 'acme_promo_rule_store'];
            foreach ($tables as $table) {
                $setup->getConnection()->addForeignKey('FK_' . $table, $table, 'rule_id', 'acme_promo', 'rule_id');
            }
        }
    }
}

namespace Acme\Third\Setup {
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Catalog\Model\Product;
    use Magento\Catalog\Model\ResourceModel\Product\Collection;

    final class RecurringData
    {
        /** @param list<Product> $items */
        public function __construct(
            private readonly array $items,
            private readonly Collection $collection,
            private readonly ProductRepositoryInterface $repository,
            private readonly Product $product,
        ) {
        }

        public function install(object $setup, object $context): void
        {
            foreach ($this->items as $item) {
                $item->save();
            }
            $this->collection->getItems();
            $this->collection->getSize();
            $this->product->load(1);
            $this->repository->save($this->product);
        }
    }
}

namespace Acme\Fourth\Setup {
    use Magento\Framework\Data\Collection as MemoryCollection;
    use Magento\Framework\Indexer\IndexerInterface;

    final class Recurring
    {
        public function __construct(
            private readonly MemoryCollection $configBased,
            private readonly IndexerInterface $indexer,
            private readonly object $unknown,
        ) {
        }

        public function install(object $setup, object $context): void
        {
            // Not a database collection, and not a typed model: nothing to report.
            $this->configBased->getItems();
            $this->configBased->load();
            $this->unknown->save();

            if ($this->needsReindex()) {
                $this->indexer->reindexAll();
            } else {
                $this->indexer->reindexList([1]);
            }
        }

        private function needsReindex(): bool
        {
            return false;
        }
    }
}
