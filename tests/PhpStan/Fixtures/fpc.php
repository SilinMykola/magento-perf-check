<?php

declare(strict_types=1);

namespace Acme\Promo\Block {
    use Magento\Customer\Model\Session;
    use Magento\Framework\View\Element\AbstractBlock;

    final class PromoBlock extends AbstractBlock
    {
        protected $_isScopePrivate = true;

        public function __construct(private readonly Session $customerSession)
        {
        }

        public function getGreeting(): string
        {
            $this->_isScopePrivate = false;

            return 'Hello ' . (string) $this->customerSession->getCustomerId();
        }
    }

    final class NotABlock
    {
        /** @var bool */
        protected $_isScopePrivate = true;
    }
}

namespace Acme\Promo\ViewModel {
    use Magento\Checkout\Model\Session;
    use Magento\Framework\View\Element\Block\ArgumentInterface;

    final class MiniCart implements ArgumentInterface
    {
        public function __construct(private readonly Session $checkoutSession)
        {
        }

        public function getQuote(): object
        {
            return $this->checkoutSession->getQuote();
        }
    }
}

namespace Acme\Promo\Model\ResourceModel\Promo {
    use Magento\Customer\Model\Session;

    final class Collection extends \Magento\Framework\Data\Collection implements \Magento\Framework\View\Element\Block\ArgumentInterface
    {
        public function __construct(private readonly Session $customerSession)
        {
        }

        public function forCustomer(): int
        {
            return (int) $this->customerSession->getCustomerId();
        }
    }
}

namespace Acme\Promo\Controller\Promo {
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Catalog\Model\Product;
    use Magento\Checkout\Model\Cart;
    use Magento\Customer\Model\Session;
    use Magento\Framework\App\Action\HttpGetActionInterface;
    use Magento\Framework\App\Action\HttpPostActionInterface;
    use Magento\Framework\App\ActionInterface;
    use Magento\Framework\App\Response\Http;

    final class Add implements HttpGetActionInterface
    {
        public function __construct(
            private readonly Http $response,
            private readonly Cart $cart,
            private readonly ProductRepositoryInterface $productRepository,
            private readonly Session $customerSession,
            private readonly Product $product,
        ) {
        }

        public function execute(): mixed
        {
            $this->response->setNoCacheHeaders();
            $this->cart->addProduct($this->product);
            $this->productRepository->save($this->product);
            $this->customerSession->getCustomerId();

            return null;
        }
    }

    final class AddPost implements HttpPostActionInterface
    {
        public function __construct(private readonly Http $response, private readonly Cart $cart, private readonly Product $product)
        {
        }

        public function execute(): mixed
        {
            $this->response->setNoCacheHeaders();
            $this->cart->addProduct($this->product);

            return null;
        }
    }

    final class AddBoth implements HttpGetActionInterface, HttpPostActionInterface
    {
        public function __construct(private readonly Cart $cart, private readonly Product $product)
        {
        }

        public function execute(): mixed
        {
            $this->cart->addProduct($this->product);

            return null;
        }
    }

    final class Legacy implements ActionInterface
    {
        public function __construct(private readonly Http $response)
        {
        }

        public function execute(): mixed
        {
            $this->response->setHeader('Cache-Control', 'no-store');
            $this->response->setHeader('X-Acme', '1');

            return null;
        }
    }
}

namespace Acme\Promo\Controller\Adminhtml\Promo {
    use Magento\Framework\App\Action\HttpGetActionInterface;
    use Magento\Framework\App\Response\Http;

    final class Grid implements HttpGetActionInterface
    {
        public function __construct(private readonly Http $response)
        {
        }

        public function execute(): mixed
        {
            $this->response->setPrivateHeaders(0);

            return null;
        }
    }
}

namespace Acme\Promo\Model\Resolver {
    use Magento\Framework\App\Response\Http;
    use Magento\Framework\GraphQl\Query\ResolverInterface;

    final class Banners implements ResolverInterface
    {
        public function __construct(private readonly Http $response)
        {
        }

        public function resolve(): void
        {
            $this->response->setHeader('Pragma', 'no-cache');
        }
    }
}

namespace Acme\Promo\Observer {
    use Magento\Framework\App\Cache\TypeListInterface;
    use Magento\Framework\App\CacheInterface;
    use Magento\Framework\Event\Observer;
    use Magento\Framework\Event\ObserverInterface;
    use Magento\Framework\Indexer\IndexerInterface;
    use Magento\PageCache\Model\Cache\Type;

    final class ProductSaved implements ObserverInterface
    {
        public function __construct(
            private readonly TypeListInterface $typeList,
            private readonly CacheInterface $cache,
            private readonly IndexerInterface $indexer,
        ) {
        }

        public function execute(Observer $observer): void
        {
            $this->typeList->cleanType('full_page');
            $this->typeList->cleanType(Type::TYPE_IDENTIFIER);
            $this->typeList->cleanType('block_html');
            $this->cache->clean();
            $this->cache->clean([]);
            $this->cache->clean(['catalog_product_42']);
            $this->indexer->reindexAll();
            $this->indexer->reindexList([42]);
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

        public function afterSave(): void
        {
            $this->indexer->reindexAll();
        }
    }
}

namespace Acme\Promo\Model {
    use Magento\Framework\Indexer\IndexerInterface;

    final class Maintenance
    {
        public function __construct(private readonly IndexerInterface $indexer)
        {
        }

        public function run(): void
        {
            $this->indexer->reindexAll();
        }
    }
}

namespace Acme\Promo\Block\Group {
    use Magento\Customer\Model\Session;
    use Magento\Framework\View\Element\AbstractBlock;

    final class GroupPrice extends AbstractBlock
    {
        public function __construct(private readonly Session $customerSession)
        {
        }

        public function getGroup(): int
        {
            return $this->customerSession->getCustomerGroupId();
        }
    }
}

namespace Acme\Promo\Block\Adminhtml\Order {
    use Magento\Checkout\Model\Session;
    use Magento\Framework\View\Element\AbstractBlock;

    final class Totals extends AbstractBlock
    {
        public function __construct(private readonly Session $quoteSession)
        {
        }

        public function getQuote(): object
        {
            return $this->quoteSession->getQuote();
        }
    }
}

namespace Acme\Promo\Controller\Download {
    use Magento\Framework\App\Action\HttpGetActionInterface;
    use Magento\Framework\App\Response\Http;

    final class Link implements HttpGetActionInterface
    {
        public function __construct(private readonly Http $response)
        {
        }

        public function execute(): mixed
        {
            $this->response->setHeader('Pragma', 'public', true);
            $this->response->setHeader('Pragma', 'no-cache')->setHeader('Cache-Control', 'no-store');

            return null;
        }
    }
}

namespace Magento\Customer\Controller\Section {
    use Magento\Framework\App\Action\HttpGetActionInterface;
    use Magento\Framework\App\Response\Http;

    final class Load implements HttpGetActionInterface
    {
        public function __construct(private readonly Http $response)
        {
        }

        public function execute(): mixed
        {
            $this->response->setNoCacheHeaders();

            return null;
        }
    }
}

namespace Acme\Promo\Controller\Wishlist {
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Catalog\Model\Product;
    use Magento\Framework\App\Action\HttpGetActionInterface;

    final class Remove implements HttpGetActionInterface
    {
        public function __construct(
            private readonly ProductRepositoryInterface $productRepository,
            private readonly Product $product,
            private readonly object $resultRedirectFactory,
        ) {
        }

        public function execute(): mixed
        {
            $this->productRepository->save($this->product);
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('wishlist');
        }
    }

    final class Touch implements HttpGetActionInterface
    {
        public function __construct(private readonly ProductRepositoryInterface $productRepository, private readonly Product $product)
        {
        }

        public function execute(): mixed
        {
            $this->productRepository->save($this->product);
            if ($this->product->getSku() === '') {
                return $this->_redirect('*/*');
            }

            return null;
        }

        private function _redirect(string $path): mixed
        {
            return $path;
        }
    }
}

namespace Acme\Promo\Controller\Adminhtml\Rule {
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Catalog\Model\Product;
    use Magento\Framework\App\Action\HttpGetActionInterface;

    final class Apply implements HttpGetActionInterface
    {
        public function __construct(private readonly ProductRepositoryInterface $productRepository, private readonly Product $product)
        {
        }

        public function execute(): mixed
        {
            $this->productRepository->save($this->product);

            return null;
        }
    }
}

namespace Acme\Promo\Console\Command {
    use Magento\Framework\App\Cache\TypeListInterface;
    use Symfony\Component\Console\Command\Command;

    final class FlushPromo extends Command
    {
        public function __construct(private readonly TypeListInterface $typeList)
        {
        }

        public function run(): void
        {
            $this->typeList->cleanType('full_page');
        }
    }
}

namespace Acme\Promo\Model\Config\Backend {
    use Magento\Framework\App\Cache\TypeListInterface;
    use Magento\Framework\App\Config\Value;

    final class StoreCodeInUrl extends Value
    {
        public function __construct(private readonly TypeListInterface $typeList)
        {
        }

        public function afterSave(): void
        {
            $this->typeList->cleanType('full_page');
        }
    }
}

namespace Acme\Promo\Setup\Patch\Data {
    use Magento\Framework\App\CacheInterface;

    final class Install
    {
        public function __construct(private readonly CacheInterface $cache)
        {
        }

        public function apply(): void
        {
            $this->cache->clean();
        }
    }
}
