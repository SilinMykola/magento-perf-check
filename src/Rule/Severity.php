<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Rule;

enum Severity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';

    public function weight(): int
    {
        return match ($this) {
            self::Error => 3,
            self::Warning => 2,
            self::Info => 1,
        };
    }

    public function isAtLeast(self $threshold): bool
    {
        return $this->weight() >= $threshold->weight();
    }
}
