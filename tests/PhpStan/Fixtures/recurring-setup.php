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
