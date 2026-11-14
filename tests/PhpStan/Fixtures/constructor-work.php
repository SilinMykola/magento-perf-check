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

final class ReturnsParentCall extends Base
{
    public function __construct()
    {
        // The parent call written with a return, as some controllers do.
        return parent::__construct();
    }
}

final class CoalesceAssignFallback
{
    public function __construct(?object $resolver = null)
    {
        $resolver ??= ObjectManager::getInstance()->get(\stdClass::class);
        $this->resolver = $resolver;
    }

    public object $resolver;
}

final class CoalesceAssignWork
{
    public function __construct(?ManagerInterface $eventManager = null)
    {
        $eventManager ??= $this->build();
    }

    private function build(): ManagerInterface
    {
        throw new \LogicException();
    }
}

final class TernaryFallback
{
    public object $config;
    public object $other;

    public function __construct(?object $config = null, ?object $other = null)
    {
        $this->config = $config !== null ? $config : ObjectManager::getInstance()->get(\stdClass::class);
        $this->other = $other === null ? ObjectManager::getInstance()->get(\stdClass::class) : $other;
    }
}

final class TernaryWithWork
{
    public object $config;

    public function __construct(?object $config = null)
    {
        $this->config = $config !== null ? $config : $this->build();
    }

    private function build(): object
    {
        return new \stdClass();
    }
}

final class FallbackWithWork
{
    public object $a;
    public object $b;
    public object $d;

    public function __construct(?object $a = null, ?object $b = null, ?object $d = null)
    {
        $this->a = $a ?? ObjectManager::getInstance()->get(\stdClass::class, ['data' => ['x' => 1]]);
        $this->d = $d ?? ObjectManager::getInstance()->create(\stdClass::class);
        $this->b = $b ?? ObjectManager::getInstance()->get(\stdClass::class)->load(1);
    }
}

final class FallbackArgumentWork
{
    public object $c;

    public function __construct(?object $c = null)
    {
        $this->c = $c ?? ObjectManager::getInstance()->get($this->resolve());
    }

    private function resolve(): string
    {
        return \stdClass::class;
    }
}

final class TernaryConditionWork
{
    public object $config;

    public function __construct(?object $config = null)
    {
        $this->config = $this->isOn() ? $config : ObjectManager::getInstance()->get(\stdClass::class);
    }

    private function isOn(): bool
    {
        return true;
    }
}

final class FallbackOtherCall
{
    public mixed $e;

    public function __construct(mixed $e = null)
    {
        $this->e = $e ?? ObjectManager::getInstance()->configure(['preferences' => []]);
    }
}
