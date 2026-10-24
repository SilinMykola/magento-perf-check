<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Rule;

final class Source
{
    public function __construct(
        public readonly SourceLevel $level,
        public readonly string $url,
        public readonly string $confirms,
    ) {
    }
}
