<?php

declare(strict_types=1);

namespace ObjectManagerInLoopFixture;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

final class Builder
{
    public function __construct(private readonly ObjectManagerInterface $objectManager)
    {
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function reported(array $rows): void
    {
        foreach ($rows as $row) {
            $this->objectManager->create(\stdClass::class, ['data' => $row]);
            ObjectManager::getInstance()->get(\stdClass::class);
        }
        array_map(fn (array $row): object => $this->objectManager->create(\stdClass::class), $rows);
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function notReported(array $rows, \Magento\Framework\DataObject $data): ?object
    {
        $this->objectManager->create(\stdClass::class);
        foreach ($rows as $row) {
            $this->objectManager->configure($row);
            $data->get('x');
        }
        foreach ($rows as $row) {
            return $this->objectManager->get(\stdClass::class);
        }

        return null;
    }
}

final class ThingFactory
{
    public function __construct(private readonly ObjectManagerInterface $objectManager)
    {
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<object>
     */
    public function createMany(array $rows): array
    {
        $things = [];
        foreach ($rows as $row) {
            $things[] = $this->objectManager->create(\stdClass::class, $row);
        }

        return $things;
    }
}

namespace ObjectManagerInLoopFixture\Thing;

final class Proxy
{
    public function __construct(private readonly \Magento\Framework\ObjectManagerInterface $objectManager)
    {
    }

    /**
     * @param list<string> $types
     */
    public function warmUp(array $types): void
    {
        foreach ($types as $type) {
            $this->objectManager->get($type);
        }
    }
}
