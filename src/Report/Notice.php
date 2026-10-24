<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Report;

/**
 * Something the analysis could not handle and skipped: an unparsable file,
 * or a PHPStan error that does not come from one of our rules.
 */
final class Notice
{
    public function __construct(
        public readonly string $source,
        public readonly string $message,
        public readonly ?string $file = null,
        public readonly ?int $line = null,
    ) {
    }
}
