<?php

declare(strict_types=1);

namespace MagentoPerfRules\Rule;

final class Source
{
    public function __construct(
        public readonly SourceLevel $level,
        public readonly string $url,
        public readonly string $confirms,
    ) {
    }
}
