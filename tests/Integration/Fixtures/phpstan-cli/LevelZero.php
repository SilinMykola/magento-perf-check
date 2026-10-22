<?php

declare(strict_types=1);

namespace PhpStanCliFixture;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

final class LevelZero
{
    public function first(Collection $collection): void
    {
        $collection->getFirstItem();
    }
}
