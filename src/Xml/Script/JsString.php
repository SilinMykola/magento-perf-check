<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Script;

/**
 * A string literal in JavaScript source.
 */
final class JsString
{
    /**
     * @param bool $isKey the literal is an object key: it follows "{" or "," and the next token is ":"
     */
    public function __construct(
        public readonly string $value,
        public readonly int $line,
        public readonly bool $isKey,
    ) {
    }
}
