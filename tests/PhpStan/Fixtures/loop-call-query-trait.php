<?php

declare(strict_types=1);

namespace LoopCallQueryFixture;

trait NameLookup
{
    public function traitName(int $id): string
    {
        return (string) $this->productRepository->getById($id)->getName();
    }
}
