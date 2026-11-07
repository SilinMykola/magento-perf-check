<?php

declare(strict_types=1);

namespace Acme\Factories;


final class ExistingFactory
{
    public function create(): \stdClass
    {
        return new \stdClass();
    }
}

// Removing "Factory" from the name above gives this class, but ExistingFactory has its own create().
final class Existing extends \Magento\Framework\Model\AbstractModel
{
}

// Cutting the seven characters of a suffix from NotAFactoryLoaders would give this class, if the suffix were not checked.
final class NotAFactory extends \Magento\Framework\Model\AbstractModel
{
}

final class Uses
{
    public function __construct(
        private readonly \Magento\Catalog\Model\ProductFactory $products,
        private readonly NoSuchThingFactory $nothing,
        private readonly ExistingFactory $existing,
        private readonly NotAFactoryLoaders $loader,
    ) {
    }

    /** @param list<int> $ids */
    public function run(array $ids): void
    {
        foreach ($ids as $id) {
            $this->products->create()->load($id);
            $this->products->other()->load($id);
            $this->nothing->create()->load($id);
            $this->existing->create()->load($id);
            $this->loader->create()->load($id);
        }
    }
}
