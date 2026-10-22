<?php

declare(strict_types=1);

namespace MagentoPerfRules\Config;

use MagentoPerfRules\Rule\Severity;

final class RuleSettings
{
    public function __construct(
        public readonly bool $enabled = true,
        public readonly ?Severity $severity = null,
    ) {
    }
}
