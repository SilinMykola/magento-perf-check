<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Project;

/**
 * The literal values of the array that app/etc/config.php returns, read from its tokens: the file is never run
 * (ADR 11). A value or key that is not a literal, such as a constant or a function call, is left out with
 * everything under it; a key given twice keeps only its last value, as in PHP.
 */
final class ConfigPhpValues
{
    private const DOUBLE_QUOTE_ESCAPES = ['n' => "\n", 'r' => "\r", 't' => "\t", 'v' => "\v", 'e' => "\e", 'f' => "\f", '\\' => '\\', '$' => '$', '"' => '"'];

    /** @var list<string|array{int, string, int}> */
    private array $tokens = [];

    private int $position = 0;

    /** @var list<array{path: list<string>, value: string|int|float|bool|null, line: int}> */
    private array $values = [];

    /**
     * @return list<array{path: list<string>, value: string|int|float|bool|null, line: int}>
     */
    public static function read(string $file): array
    {
        $content = is_file($file) ? @file_get_contents($file) : false;
        if (!is_string($content)) {
            return [];
        }
        $reader = new self();
        // @: an invalid escape, such as "\400", makes the tokenizer warn, which would land in the report.
        foreach (@token_get_all($content) as $token) {
            if (!is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG], true)) {
                $reader->tokens[] = $token;
            }
        }
        // config.php is "return [...];": the first return outside any braces holds the configuration.
        $depth = 0;
        foreach ($reader->tokens as $i => $token) {
            if ($token === '{' || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
                ++$depth;
            } elseif ($token === '}') {
                --$depth;
            } elseif ($depth === 0 && is_array($token) && $token[0] === T_RETURN) {
                $reader->position = $i + 1;
                if ($reader->arrayOpens()) {
                    $reader->value([]);
                }
                break;
            }
        }

        return $reader->values;
    }

    /**
     * Reads one value at the current token; a literal is recorded, an array literal is walked, anything else is skipped.
     *
     * @param list<string> $path
     */
    private function value(array $path): void
    {
        if ($this->arrayOpens()) {
            $recorded = count($this->values);
            $this->arrayElements($path);
            // An array that is only part of an expression, such as ['a' => 1]['a'] ?? [], is not the value.
            if (!$this->endsExpression($this->position)) {
                $this->values = array_slice($this->values, 0, $recorded);
                $this->skipExpression();
            }

            return;
        }
        $literal = $this->literal();
        if ($literal !== null && $this->endsExpression($this->position + $literal[2])) {
            $this->values[] = ['path' => $path, 'value' => $literal[0], 'line' => $literal[1]];
            $this->position += $literal[2];

            return;
        }
        $this->skipExpression();
    }

    /**
     * Walks the elements of the array literal that opens at the current token, past its closing bracket.
     *
     * @param list<string> $path
     */
    private function arrayElements(array $path): void
    {
        $close = $this->tokens[$this->position] === '[' ? ']' : ')';
        $this->position += $close === ']' ? 1 : 2;
        // PHP 8.3 and later: the next index follows the largest integer key so far, even a negative one.
        $nextIndex = null;
        $seen = [];
        while ($this->position < count($this->tokens)) {
            if ($this->tokens[$this->position] === $close) {
                ++$this->position;

                return;
            }
            $start = $this->position;
            $key = $this->literal();
            if ($key !== null && $this->isDoubleArrow($this->position + $key[2])) {
                $this->position += $key[2] + 1;
                $name = self::arrayKey($key[0]);
                if (is_int($name)) {
                    $nextIndex = $nextIndex === null ? $name + 1 : max($nextIndex, $name + 1);
                }
            } else {
                // A value without a key; a key that is not a literal is read as a value that is not one, and left out.
                $name = $nextIndex ?? 0;
                $nextIndex = $name + 1;
            }
            if (isset($seen[$name])) {
                $this->forget([...$path, (string) $name]);
            }
            $seen[$name] = true;
            $this->value([...$path, (string) $name]);
            if (($this->tokens[$this->position] ?? null) === ',') {
                ++$this->position;
            } elseif ($this->position === $start) {
                ++$this->position;
            }
        }
    }

    /**
     * Drops the values recorded under a key that is given again: PHP keeps only the last one.
     *
     * @param list<string> $prefix
     */
    private function forget(array $prefix): void
    {
        $this->values = array_values(array_filter(
            $this->values,
            static fn (array $value): bool => array_slice($value['path'], 0, count($prefix)) !== $prefix,
        ));
    }

    /**
     * Moves to the "," or closing bracket that ends the current expression, without consuming it.
     */
    private function skipExpression(): void
    {
        $depth = 0;
        for (; $this->position < count($this->tokens); ++$this->position) {
            $token = $this->tokens[$this->position];
            if ($token === '[' || $token === '(' || $token === '{'
                || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES, T_ATTRIBUTE], true))
            ) {
                ++$depth;
            } elseif ($token === ']' || $token === ')' || $token === '}') {
                if ($depth === 0) {
                    return;
                }
                --$depth;
            } elseif ($depth === 0 && ($token === ',' || $token === ';')) {
                return;
            }
        }
    }

    private function arrayOpens(): bool
    {
        $token = $this->tokens[$this->position] ?? null;

        return $token === '['
            || (is_array($token) && $token[0] === T_ARRAY && ($this->tokens[$this->position + 1] ?? null) === '(');
    }

    /**
     * @return ?array{string|int|float|bool|null, int, int} the literal at the current token, its line and its number of tokens
     */
    private function literal(): ?array
    {
        $token = $this->tokens[$this->position] ?? null;
        $next = $this->tokens[$this->position + 1] ?? null;
        if (($token === '-' || $token === '+') && is_array($next) && in_array($next[0], [T_LNUMBER, T_DNUMBER], true)) {
            $number = self::number($next[1], $next[0]);

            return [$token === '-' ? -$number : $number, $next[2], 2];
        }
        if (!is_array($token)) {
            return null;
        }
        [$type, $text, $line] = $token;
        if ($type === T_CONSTANT_ENCAPSED_STRING) {
            // A binary string, b'...', is an ordinary string.
            $text = ltrim($text, 'bB');
            $inner = substr($text, 1, -1);

            return [$text[0] === "'" ? strtr($inner, ['\\\\' => '\\', "\\'" => "'"]) : self::doubleQuoted($inner), $line, 1];
        }
        if ($type === T_LNUMBER || $type === T_DNUMBER) {
            return [self::number($text, $type), $line, 1];
        }
        if ($type === T_STRING || $type === T_NAME_FULLY_QUALIFIED) {
            return match (strtolower(ltrim($text, '\\'))) {
                'true' => [true, $line, 1],
                'false' => [false, $line, 1],
                'null' => [null, $line, 1],
                default => null,
            };
        }

        return null;
    }

    private static function number(string $text, int $type): int|float
    {
        $digits = strtolower(str_replace('_', '', $text));
        // hexdec(), bindec() and octdec() give a float when the number overflows, as PHP does.
        return match (true) {
            str_starts_with($digits, '0x') => hexdec(substr($digits, 2)),
            str_starts_with($digits, '0b') => bindec(substr($digits, 2)),
            str_starts_with($digits, '0o') => octdec(substr($digits, 2)),
            preg_match('/^0[0-7]+$/', $digits) === 1 => octdec($digits),
            $type === T_LNUMBER => (int) $digits,
            default => (float) $digits,
        };
    }

    /**
     * The escape sequences of a double-quoted PHP string; an unknown one keeps its backslash, as in PHP.
     */
    private static function doubleQuoted(string $inner): string
    {
        return (string) preg_replace_callback(
            '/\\\\(?:([nrtvef\\\\$"])|([0-7]{1,3})|x([0-9A-Fa-f]{1,2})|u\{([0-9A-Fa-f]+)\})/',
            static function (array $m): string {
                if ($m[1] !== null) {
                    return self::DOUBLE_QUOTE_ESCAPES[$m[1]];
                }
                if ($m[2] !== null) {
                    return chr((int) octdec($m[2]) & 0xFF);
                }
                if ($m[3] !== null) {
                    return chr((int) hexdec($m[3]) & 0xFF);
                }

                return self::utf8((int) hexdec((string) $m[4]));
            },
            $inner,
            flags: PREG_UNMATCHED_AS_NULL,
        );
    }

    private static function utf8(int $codePoint): string
    {
        // The masks keep each byte in 0-255; for a valid code point they change nothing.
        return match (true) {
            $codePoint < 0x80 => chr($codePoint & 0x7F),
            $codePoint < 0x800 => chr(0xC0 | $codePoint >> 6 & 0x1F) . chr(0x80 | $codePoint & 0x3F),
            $codePoint < 0x10000 => chr(0xE0 | $codePoint >> 12 & 0x0F) . chr(0x80 | $codePoint >> 6 & 0x3F) . chr(0x80 | $codePoint & 0x3F),
            default => chr(0xF0 | $codePoint >> 18 & 0x07) . chr(0x80 | $codePoint >> 12 & 0x3F) . chr(0x80 | $codePoint >> 6 & 0x3F) . chr(0x80 | $codePoint & 0x3F),
        };
    }

    /**
     * The key PHP stores: a decimal integer string, a bool or a float becomes an integer, null an empty string.
     */
    private static function arrayKey(string|int|float|bool|null $key): string|int
    {
        if (is_string($key)) {
            return preg_match('/^(0|-?[1-9][0-9]*)$/', $key) === 1 && (string) (int) $key === $key ? (int) $key : $key;
        }

        // @: a float key too large for an integer makes PHP warn, which would land in the report; PHP's own value is kept.
        return $key === null ? '' : @(int) $key;
    }

    private function isDoubleArrow(int $index): bool
    {
        $token = $this->tokens[$index] ?? null;

        return is_array($token) && $token[0] === T_DOUBLE_ARROW;
    }

    private function endsExpression(int $index): bool
    {
        $token = $this->tokens[$index] ?? null;

        // A closing tag ends a statement, as ";" does.
        return $token === ',' || $token === ']' || $token === ')' || $token === ';'
            || (is_array($token) && $token[0] === T_CLOSE_TAG);
    }
}
