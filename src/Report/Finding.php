<?php

declare(strict_types=1);

namespace MagentoPerfRules\Report;

use MagentoPerfRules\Rule\Severity;

final class Finding
{
    /**
     * @param string $file path relative to the analyzed project
     */
    public function __construct(
        public readonly string $ruleId,
        public readonly Severity $severity,
        public readonly string $file,
        public readonly int $line,
        public readonly string $message,
    ) {
    }
}
