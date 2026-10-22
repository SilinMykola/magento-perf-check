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
