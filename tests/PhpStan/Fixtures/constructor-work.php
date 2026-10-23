<?php

declare(strict_types=1);

namespace ConstructorWorkFixture;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;

class Base
{
    public function __construct()
    {
    }
}

final class QueryInConstructor
{
    private mixed $config;

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
        $this->config = $this->scopeConfig->getValue('acme/promo/enabled');
    }
}

final class CallsInit
{
    public function __construct()
    {
        $this->init();
    }

    private function init(): void
    {
    }
}

final class Allowed extends Base
{
    /** @var list<object> */
    private array $handlers;

    private object $serializer;

    private string $mode;

    /** @var array<string, int> */
    private array $limits;

    /**
     * @param list<object> $handlers
     */
    public function __construct(array $handlers, ?object $serializer = null, private readonly int $size = 3)
    {
        parent::__construct();
        foreach ($handlers as $handler) {
            if (!$handler instanceof \Countable) {
                throw new \InvalidArgumentException('Handlers must be countable');
            }
        }
        if ($size < 1) {
            throw new \InvalidArgumentException('Size must be positive');
        }
        $this->handlers = $handlers;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(\stdClass::class);
        $this->mode = 'default';
        $this->limits = ['items' => 10];
    }
}

final class MoreAllowed
{
    private int $prefetchCount;

    private object $resolver;

    public function __construct(int|string $prefetchCount, ?object $resolver = null, ?object $formatter = null)
    {
        $this->prefetchCount = (int) $prefetchCount;
        $resolver = $resolver ?? ObjectManager::getInstance()->get(\stdClass::class);
        $this->resolver = $resolver;
        $formatter = $formatter ?: new \ArrayObject();
    }
}

final class Dispatching
{
    public function __construct(private readonly ManagerInterface $eventManager)
    {
        $this->eventManager->dispatch('acme_promo_init');
    }

    public function run(): void
    {
        $this->eventManager->dispatch('acme_promo_run');
    }
}
