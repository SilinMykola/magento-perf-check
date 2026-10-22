<?php

declare(strict_types=1);

namespace MagentoPerfRules\Rule;

/**
 * Source levels from docs/SPEC.md section 2.
 */
enum SourceLevel: string
{
    /** Official Adobe or Hyvä documentation. */
    case A = 'A';
    /** Magento core code pinned to a commit, or a magento/magento2 issue with measurements. */
    case B = 'B';
    /** Anything else; supplementary only. */
    case C = 'C';
}
