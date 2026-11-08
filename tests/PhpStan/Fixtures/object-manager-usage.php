<?php

declare(strict_types=1);

namespace ObjectManagerUsageFixture;

use Magento\Framework\App\ObjectManager;

final class Helper
{
    private object $logger;

    public function __construct(?object $logger = null)
    {
        $this->logger = $logger ?? ObjectManager::getInstance()->get(\stdClass::class);
    }

    public function run(): object
    {
        return ObjectManager::getInstance()->get(\stdClass::class);
    }

    public function __wakeup(): void
    {
        $this->logger = ObjectManager::getInstance()->get(\stdClass::class);
    }
}

final class WithoutFallback
{
    private object $logger;

    public function __construct()
    {
        $this->logger = ObjectManager::getInstance()->get(\stdClass::class);
    }
}

final class ItemFactory
{
    public function create(): object
    {
        return ObjectManager::getInstance()->get(\stdClass::class);
    }
}

final class IfFallbacks
{
    private object $a;
    private object $b;
    private array $processors;
    private ?object $cached = null;

    public function __construct(?object $a = null, ?object $b = null, array $processors = [], ?object $c = null, ?object $d = null)
    {
        if (!isset($a)) {
            $a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->a = $a;
        if ($b === null) {
            $b = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->b = $b;
        if (empty($processors)) {
            $this->processors = ['one' => ObjectManager::getInstance()->get(\stdClass::class)];
        }
        if (is_null($d)) {
            $d = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if ($c === null || !$d) {
            $c = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if ($this->cached === null) {
            $this->cached = ObjectManager::getInstance()->get(\stdClass::class);
        }
    }
}

final class LocalObjectManager
{
    private object $a;
    private object $b;

    public function __construct(?object $a = null, ?object $b = null)
    {
        $objectManager = ObjectManager::getInstance();
        $this->a = $a ?: $objectManager->get(\stdClass::class);
        $this->b = $b ?? $objectManager->get(\stdClass::class);
    }
}

final class LocalObjectManagerUsedElsewhere
{
    private object $a;
    private object $b;

    public function __construct(?object $a = null)
    {
        $objectManager = ObjectManager::getInstance();
        $this->a = $a ?: $objectManager->get(\stdClass::class);
        $this->b = $objectManager->create(\stdClass::class);
    }
}

final class IfFallbackEdges
{
    private object $a;
    private object $b;
    private \Closure $later;

    public function __construct(?object $a = null, ?object $b = null, ?object $c = null)
    {
        if (null === $a) {
            $a = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!$b) {
            $b = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!isset($a, $c)) {
            $c = ObjectManager::getInstance()->get(\stdClass::class);
        }
        if (!$c) {
            $this->a = ObjectManager::getInstance()->get(\stdClass::class);
            $this->warmUp();
        }
        $this->later = function (?object $a = null): object {
            if (!$a) {
                $a = ObjectManager::getInstance()->get(\stdClass::class);
            }

            return $a;
        };
        $this->b = $b;
    }

    private function warmUp(): void
    {
    }
}

final class LocalObjectManagerHandedOn
{
    private object $a;

    public function __construct(?object $a = null)
    {
        $objectManager = ObjectManager::getInstance();
        $this->a = $a ?: $objectManager;
    }
}

final class FallbackBlockWithWork
{
    private object $a;
    private object $b;
    private mixed $config;

    public function __construct(private object $scopeConfig, ?object $a = null, ?object $b = null)
    {
        $objectManager = ObjectManager::getInstance();
        if (!isset($a)) {
            $a = $objectManager->get(\stdClass::class);
        }
        $this->a = $a;
        if (!$b) {
            $this->config = $this->scopeConfig->getValue('x');
            $b = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->b = $b;
    }
}

final class LocalObjectManagerCaptured
{
    private object $a;
    private \Closure $make;

    public function __construct(?object $a = null)
    {
        $objectManager = ObjectManager::getInstance();
        $this->a = $a ?: $objectManager->get(\stdClass::class);
        $this->make = fn (): object => $objectManager->create(\stdClass::class);
    }
}

final class LocalObjectManagerKeptInBlock
{
    private object $a;
    private object $b;

    public function __construct(?object $a = null)
    {
        $objectManager = ObjectManager::getInstance();
        if (!isset($a)) {
            $this->b = $objectManager;
        }
        $this->a = $a ?? $objectManager->get(\stdClass::class);
    }
}

final class FallbackBlockWithHiddenWork
{
    private object $a;
    private object $b;
    private mixed $config;
    private array $registry = [];

    public function __construct(?object $a = null, ?object $b = null)
    {
        if (!$a) {
            $this->config = ObjectManager::getInstance()->get(\stdClass::class)->getValue('x');
            $a = ObjectManager::getInstance()->create(\stdClass::class);
        }
        $this->a = $a;
        if (!$b) {
            $this->registry[$this->key()] = ObjectManager::getInstance()->get(\stdClass::class);
        }
        $this->b = $b;
    }

    private function key(): string
    {
        return 'b';
    }
}
