<?php

declare(strict_types=1);

namespace Acme\Placed\Block {
    use Magento\Customer\Model\Session;
    use Magento\Framework\View\Element\AbstractBlock;

    abstract class SessionBlock extends AbstractBlock
    {
        public function __construct(protected readonly Session $customerSession)
        {
        }

        public function customerId(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // Placed only on the cart, which checkout_ makes personal.
    final class CartOnly extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // Placed on a page whose layout has a cacheable="false" block.
    final class OnNonCacheablePage extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // Placed on a page built on the customer account layout.
    final class AccountOnly extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // Placed on the cart and on the product page, which may be cached.
    final class Mixed extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // Placed in default.xml, whose cacheable="false" block is an X-LAY-03 error, not a personal page.
    final class OnEveryPage extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // Placed on the cart, but CartChild runs this code on the product page.
    class CartParent extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // CartChild, in fpc-placement-child.php outside the analyzed file, is placed on the product page and runs id().

    // On the cart, and given as class of a referenceBlock on the product page.
    final class ByReference extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // On the cart, and in checkout_acme_renderers, which default.xml includes.
    final class IncludedByDefault extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // On a page whose cacheable="false" block is in an XML comment.
    final class CommentedPage extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // On a page whose cacheable="false" block has an ifconfig.
    final class ConfigPage extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // On a page whose cacheable="false" block a theme removes.
    final class RemovedPage extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // On a page whose cacheable="false" block is removed with remove="1".
    final class RemovedByOne extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // On a page whose cacheable="false" block sits in a container a file removes.
    final class ParentRemoved extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // On a page whose cacheable="false" block sits in a block with an ifconfig.
    final class ParentIfconfig extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // Only on the compare page, which core makes uncacheable and X-LAY-03 does not report.
    final class CompareOnly extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }

    // No layout places it.
    final class Unplaced extends SessionBlock
    {
        public function id(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }
}

namespace Acme\Placed\ViewModel {
    use Magento\Checkout\Model\Session;
    use Magento\Framework\View\Element\Block\ArgumentInterface;

    final class CartModel implements ArgumentInterface
    {
        public function __construct(private readonly Session $checkoutSession)
        {
        }

        public function quote(): object
        {
            return $this->checkoutSession->getQuote();
        }
    }
    // On the cart, and as an <item> object in default.xml.
    final class ItemModel implements ArgumentInterface
    {
        public function __construct(private readonly Session $checkoutSession)
        {
        }

        public function quote(): object
        {
            return $this->checkoutSession->getQuote();
        }
    }
}
