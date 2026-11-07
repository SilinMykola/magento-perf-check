<?php

declare(strict_types=1);

namespace Acme\Promo\Observer {
    use Magento\Framework\Event\Observer;
    use Magento\Framework\Event\ObserverInterface;
    use Magento\Framework\Indexer\IndexerInterface;

    final class Unchecked implements ObserverInterface
    {
        public function __construct(private readonly IndexerInterface $indexer)
        {
        }

        public function execute(Observer $observer): void
        {
            $this->indexer->reindexRow(1);
            $this->indexer->reindexList([1, 2]);
        }
    }

    final class Checked implements ObserverInterface
    {
        public function __construct(private readonly IndexerInterface $indexer)
        {
        }

        public function execute(Observer $observer): void
        {
            if (!$this->indexer->isScheduled()) {
                $this->indexer->reindexRow(1);
            }
        }
    }

    final class CheckedElsewhere implements ObserverInterface
    {
        public function __construct(private readonly IndexerInterface $indexer)
        {
        }

        public function execute(Observer $observer): void
        {
            $this->indexer->isScheduled();
        }

        public function unchecked(): void
        {
            $this->indexer->reindexRow(1);
        }
    }
}

namespace Acme\Promo\Controller\Index {
    use Magento\Framework\App\ActionInterface;
    use Magento\Framework\Indexer\IndexerInterface;

    final class Reindex implements ActionInterface
    {
        public function __construct(private readonly IndexerInterface $indexer)
        {
        }

        public function execute(): mixed
        {
            $this->indexer->reindexRow(1);

            return null;
        }
    }
}

namespace Acme\Promo\Cron {
    use Magento\Framework\Indexer\IndexerInterface;

    final class Reindex
    {
        public function __construct(private readonly IndexerInterface $indexer)
        {
        }

        public function execute(): void
        {
            $this->indexer->reindexRow(1);
        }
    }
}

namespace Acme\Promo\Other {
    final class Processor
    {
        public function reindexRow(int $id): void
        {
        }
    }

    final class NotAnIndexer implements \Magento\Framework\Event\ObserverInterface
    {
        public function __construct(private readonly Processor $processor)
        {
        }

        public function execute(\Magento\Framework\Event\Observer $observer): void
        {
            $this->processor->reindexRow(1);
        }
    }
}

namespace Acme\Promo\Plugin {
    use Magento\Framework\Indexer\IndexerInterface;

    final class CartPlugin
    {
        public function __construct(private readonly IndexerInterface $indexer)
        {
        }

        public function afterSave(object $subject, mixed $result): mixed
        {
            $this->indexer->reindexList([1]);

            return $result;
        }
    }
}
