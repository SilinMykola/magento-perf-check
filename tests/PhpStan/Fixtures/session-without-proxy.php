<?php

declare(strict_types=1);

namespace Acme\Promo\Block {
    use Magento\Customer\Model\Session;
    use Magento\Framework\Stdlib\CookieManagerInterface;

    class Banner
    {
        public function __construct(private readonly Session $customerSession)
        {
        }
    }

    final class Header
    {
        public function __construct(private readonly Session $customerSession)
        {
        }
    }

    final class Badge
    {
        public function __construct(
            private readonly Session $customerSession,
            private readonly CookieManagerInterface $cookieManager,
        ) {
        }
    }

    final class BigBanner extends Banner
    {
        public function __construct(Session $customerSession, private readonly int $size = 2)
        {
            parent::__construct($customerSession);
        }
    }

    final class ProxyHinted
    {
        public function __construct(private readonly Session\Proxy $customerSession)
        {
        }
    }
}

namespace Acme\Promo\Model {
    use Magento\Customer\Model\Session;

    final class PromoSession extends \Magento\Framework\Session\SessionManager
    {
        public function __construct(private readonly Session $customerSession)
        {
        }
    }

    final class ItemFactory
    {
        public function __construct(private readonly Session $customerSession)
        {
        }
    }
}

namespace Acme\Promo\Controller\Account {
    use Magento\Customer\Model\Session;
    use Magento\Framework\App\ActionInterface;
    use Magento\Framework\Stdlib\CookieManagerInterface;

    final class Edit implements ActionInterface
    {
        public function __construct(
            private readonly Session $customerSession,
            private readonly CookieManagerInterface $cookieManager,
        ) {
        }

        public function execute(): mixed
        {
            return null;
        }
    }
}
