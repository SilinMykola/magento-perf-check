<?php

declare(strict_types=1);

namespace MagentoPerfRules\Baseline;

use MagentoPerfRules\Report\Finding;

final class BaselineResult
{
    /**
     * @param list<Finding> $findings findings the baseline did not cover
     * @param int $unmatched baseline occurrences that matched no finding (fixed issues)
     */
    public function __construct(
        public readonly array $findings,
        public readonly int $hidden,
        public readonly int $unmatched,
    ) {
    }
}
