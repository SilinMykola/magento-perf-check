<?php

declare(strict_types=1);

namespace Magento\Framework\Session {
    interface SessionManagerInterface
    {
    }

    class SessionManager implements SessionManagerInterface
    {
        public function getCustomerId(): ?int
        {
            return null;
        }

        public function getId(): string
        {
            return '';
        }

        public function getCurrencyCode(): string
        {
            return '';
        }
    }
}

namespace Magento\Customer\Model {
    class Session extends \Magento\Framework\Session\SessionManager
    {
        public function getCustomer(): object
        {
            return new \stdClass();
        }

        public function getCustomerGroupId(): int
        {
            return 0;
        }
    }
}

namespace Magento\Customer\Model\Session {
    class Proxy extends \Magento\Customer\Model\Session
    {
    }
}

namespace Magento\Checkout\Model {
    class Session extends \Magento\Framework\Session\SessionManager
    {
        public function getQuote(): object
        {
            return new \stdClass();
        }
    }
}

namespace Magento\Framework\Stdlib {
    interface CookieManagerInterface
    {
    }
}

namespace Magento\Framework\Event {
    interface ManagerInterface
    {
        /** @param array<string, mixed> $data */
        public function dispatch(string $eventName, array $data = []): void;
    }
}

namespace Magento\Framework\App {
    class ObjectManager
    {
        public static function getInstance(): self
        {
            return new self();
        }

        public function get(string $type): object
        {
            return new \stdClass();
        }
    }
}
