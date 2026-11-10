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
    interface BatchServiceContractResolverInterface
    {
    }
}

namespace Magento\Framework\DB\Adapter {
    interface AdapterInterface
    {
        /** @return list<array<string, mixed>> */
        public function fetchAll(string $sql): array;
    }
}

namespace Magento\Framework\App\Http {
    class Context
    {
        /**
         * @param mixed $value
         * @param mixed $default
         * @return $this
         */
        public function setValue(string $name, $value, $default)
        {
            return $this;
        }
    }
}

namespace Magento\Customer\Api\Data {
    interface CustomerInterface
    {
        public function getId(): ?int;

        public function getEmail(): string;

        public function getGroupId(): int;
    }
}

namespace Magento\Quote\Api\Data {
    interface CartInterface
    {
        public function getId(): ?int;
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

namespace Symfony\Component\Console\Input {
    interface InputInterface
    {
    }
}

namespace Symfony\Component\Console\Output {
    interface OutputInterface
    {
    }
}

namespace Symfony\Component\Console\Command {
    // Symfony Console 7.4, as Magento 2.4.9 requires.
    class Command
    {
        protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
        {
            return 0;
        }
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
