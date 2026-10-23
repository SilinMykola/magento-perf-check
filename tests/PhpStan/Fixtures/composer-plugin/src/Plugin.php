<?php

declare(strict_types=1);

namespace ComposerPluginFixture;

final class Plugin
{
    private array $options;

    public function __construct(object $composer)
    {
        $this->options = $composer->getPackage()->getExtra();
    }
}
