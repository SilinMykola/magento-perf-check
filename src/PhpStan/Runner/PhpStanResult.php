<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Runner;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Report\Notice;

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
