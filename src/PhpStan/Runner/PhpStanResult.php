<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Runner;

use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Report\Notice;

final class PhpStanResult
{
    /**
     * @param list<Finding> $findings
     * @param list<Notice> $notices
     */
    public function __construct(
        public readonly array $findings,
        public readonly array $notices,
    ) {
    }
}
