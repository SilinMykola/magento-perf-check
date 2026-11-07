<?php

declare(strict_types=1);

namespace Magento\Framework\View\Element {
    abstract class AbstractBlock
    {
        /** @var bool */
        protected $_isScopePrivate = false;
    }
}

namespace Magento\Framework\View\Element\Block {
    interface ArgumentInterface
    {
    }
}

namespace Magento\Framework\App {
    interface ActionInterface
    {
        public function execute(): mixed;
    }

    interface ResponseInterface
    {
    }

    interface CacheInterface
    {
        /** @param list<string> $tags */
        public function clean($tags = []): bool;
    }
}

namespace Magento\Framework\App\Action {
    interface HttpGetActionInterface extends \Magento\Framework\App\ActionInterface
    {
    }

    interface HttpPostActionInterface extends \Magento\Framework\App\ActionInterface
    {
    }
}

namespace Magento\Framework\App\Response {
    class Http implements \Magento\Framework\App\ResponseInterface
    {
        public function setNoCacheHeaders(): void
        {
        }

        public function setPrivateHeaders(int $ttl): void
        {
        }

        public function setHeader(string $name, string $value, bool $replace = false): self
        {
            return $this;
        }
    }
}

namespace Magento\Framework\App\Cache {
    interface TypeListInterface
    {
        public function cleanType(string $typeCode): void;
    }
}

namespace Magento\PageCache\Model\Cache {
    class Type
    {
        public const TYPE_IDENTIFIER = 'full_page';
    }
}

namespace Magento\Framework\GraphQl\Query {
    interface ResolverInterface
    {
    }
}

namespace Magento\Framework\GraphQl\Query\Resolver {
    interface BatchResolverInterface
    {
    }
}

namespace Magento\Framework\Event {
    interface ObserverInterface
    {
        public function execute(\Magento\Framework\Event\Observer $observer): void;
    }

    class Observer
    {
    }
}

namespace Magento\Framework\Indexer {
    interface IndexerInterface
    {
        public function reindexAll(): void;

        public function reindexRow(int $id): void;

        public function isScheduled(): bool;

        /** @param list<int> $ids */
        public function reindexList(array $ids): void;
    }
}

namespace Magento\Checkout\Model {
    class Cart
    {
        /** @param array<string, mixed> $request */
        public function addProduct(\Magento\Catalog\Model\Product $product, array $request = []): self
        {
            return $this;
        }
    }
}

namespace Symfony\Component\Console\Command {
    class Command
    {
    }
}

namespace Magento\Framework\App\DeploymentConfig {
    interface ImporterInterface
    {
    }
}

namespace Magento\Framework\App\Config {
    class Value
    {
    }
}
