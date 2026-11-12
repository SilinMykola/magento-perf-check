<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Script;

/**
 * A comment in JavaScript source, without its delimiters.
 */
final class JsComment
{
    public function __construct(
        public readonly string $text,
        public readonly int $endLine,
    ) {
    }
}
