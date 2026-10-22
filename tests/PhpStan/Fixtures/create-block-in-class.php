<?php

declare(strict_types=1);

namespace CreateBlockFixture;

final class Renderer
{
    public function __construct(private readonly object $layoutOwner)
    {
    }

    public function render(): mixed
    {
        return $this->layoutOwner->getLayout()->createBlock('Acme\Promo\Block\Badge');
    }
}
