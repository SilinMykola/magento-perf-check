<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Script;

/**
 * Splits JavaScript source into string literals and comments, which is all the script rules need. It is
 * a scanner, not a parser: it tells a regular expression literal from a division by the token before it.
 */
final class JsScanner
{
    /** Keywords after which "/" starts a regular expression, not a division. */
    private const BEFORE_REGEX = ['return', 'typeof', 'case', 'do', 'else', 'in', 'of', 'new', 'delete', 'void', 'throw', 'instanceof', 'yield', 'await'];

    private const ESCAPES = ['n' => "\n", 't' => "\t", 'r' => "\r", 'b' => "\x08", 'f' => "\f", 'v' => "\v", '0' => "\0"];

    /** @var list<JsString> */
    public array $strings = [];

    /** @var list<JsComment> */
    public array $comments = [];

    private string $source;

    private int $length;

    private int $line;

    private function __construct()
    {
    }

    public static function scan(string $source, int $firstLine = 1): self
    {
        $scan = new self();
        $scan->source = $source;
        $scan->length = strlen($source);
        $scan->line = $firstLine;
        $scan->run();

        return $scan;
    }

    private function run(): void
    {
        $source = $this->source;
        // What the last significant token was: "value" (identifier, number, string, ")" or "]") or "operator".
        $last = 'operator';
        // The last punctuator, or "" after another token: an object key follows "{" or ",", a property name ".".
        $punctuator = '';
        // Open braces inside each template literal substitution "${...}" that is being scanned, innermost last.
        $templates = [];
        $i = 0;
        while ($i < $this->length) {
            $char = $source[$i];
            $next = $source[$i + 1] ?? '';
            if ($char === "\n") {
                ++$this->line;
                ++$i;
            } elseif ($char === '/' && $next === '/') {
                $end = strpos($source, "\n", $i);
                $end = $end === false ? $this->length : $end;
                $this->comments[] = new JsComment(substr($source, $i + 2, $end - $i - 2), $this->line);
                $i = $end;
            } elseif ($char === '/' && $next === '*') {
                $end = strpos($source, '*/', $i + 2);
                $end = $end === false ? $this->length : $end;
                $text = substr($source, $i + 2, $end - $i - 2);
                $this->line += substr_count($text, "\n");
                $this->comments[] = new JsComment($text, $this->line);
                $i = $end + 2;
            } elseif ($char === '"' || $char === "'") {
                $i = $this->quoted($i, $punctuator === '{' || $punctuator === ',');
                [$last, $punctuator] = ['value', ''];
            } elseif ($char === '`' || ($char === '}' && $templates !== [] && end($templates) === 0)) {
                if ($char === '}') {
                    array_pop($templates);
                }
                [$i, $opensSubstitution] = $this->templatePart($i + 1, $char === '`');
                if ($opensSubstitution) {
                    $templates[] = 0;
                    [$last, $punctuator] = ['operator', '{'];
                } else {
                    [$last, $punctuator] = ['value', ''];
                }
            } elseif ($char === '/' && $last === 'operator') {
                $i = $this->regexEnd($i);
                [$last, $punctuator] = ['value', ''];
            } elseif (ord($char) >= 0xC2 && ($nonBreaking = self::nonBreakingSpace($source, $i)) > 0) {
                // A byte order mark or a no-break space is white space, not part of an identifier.
                $i += $nonBreaking;
            } elseif (($char === '+' || $char === '-') && $next === $char) {
                // "++" and "--" leave the token before them as it was: after a value they are postfix.
                $i += 2;
            } elseif (self::isIdentifierByte($char)) {
                $start = $i;
                while ($i < $this->length && self::isIdentifierByte($source[$i])) {
                    ++$i;
                }
                $keyword = $punctuator !== '.' && in_array(substr($source, $start, $i - $start), self::BEFORE_REGEX, true);
                [$last, $punctuator] = [$keyword ? 'operator' : 'value', ''];
            } else {
                if (!ctype_space($char)) {
                    if ($templates !== [] && ($char === '{' || $char === '}')) {
                        $templates[count($templates) - 1] += $char === '{' ? 1 : -1;
                    }
                    $last = $char === ')' || $char === ']' ? 'value' : 'operator';
                    $punctuator = $char;
                }
                ++$i;
            }
        }
    }

    /**
     * The length of a UTF-8 byte order mark (U+FEFF) or no-break space (U+00A0) at $at, or 0.
     */
    private static function nonBreakingSpace(string $source, int $at): int
    {
        if (substr($source, $at, 3) === "\u{FEFF}") {
            return 3;
        }

        return substr($source, $at, 2) === "\u{A0}" ? 2 : 0;
    }

    private static function isIdentifierByte(string $char): bool
    {
        return ctype_alnum($char) || $char === '_' || $char === '$' || ord($char) >= 0x80;
    }

    /**
     * Records the quoted string that starts at $start and returns the offset after it.
     */
    private function quoted(int $start, bool $mayBeKey): int
    {
        $source = $this->source;
        $quote = $source[$start];
        $line = $this->line;
        $value = '';
        $i = $start + 1;
        while ($i < $this->length && $source[$i] !== $quote && $source[$i] !== "\n") {
            if ($source[$i] === '\\') {
                [$decoded, $i] = $this->escape($i);
                $value .= $decoded;
                continue;
            }
            $value .= $source[$i];
            ++$i;
        }
        // An unterminated string ends before the line break.
        $end = $i < $this->length && $source[$i] === $quote ? $i + 1 : $i;
        $after = $end;
        while ($after < $this->length && ctype_space($source[$after])) {
            ++$after;
        }
        $this->strings[] = new JsString($value, $line, $mayBeKey && ($source[$after] ?? '') === ':');

        return $end;
    }

    /**
     * Reads the text of a template literal from $start up to its closing backtick or to the next
     * substitution "${", and returns the offset after that and whether a substitution opens there.
     * Only a template without substitutions is recorded as a string: a part of one is not a whole value.
     *
     * @return array{int, bool}
     */
    private function templatePart(int $start, bool $opensTemplate): array
    {
        $source = $this->source;
        $line = $this->line;
        $value = '';
        $i = $start;
        while ($i < $this->length && $source[$i] !== '`' && !($source[$i] === '$' && ($source[$i + 1] ?? '') === '{')) {
            if ($source[$i] === '\\') {
                [$decoded, $i] = $this->escape($i);
                $value .= $decoded;
                continue;
            }
            if ($source[$i] === "\n") {
                ++$this->line;
            }
            $value .= $source[$i];
            ++$i;
        }
        $closes = $i >= $this->length || $source[$i] === '`';
        if ($closes && $opensTemplate) {
            $this->strings[] = new JsString($value, $line, false);
        }
        if ($i >= $this->length) {
            return [$i, false];
        }

        return $closes ? [$i + 1, false] : [$i + 2, true];
    }

    /**
     * Decodes the escape sequence at $at and returns it with the offset after it.
     *
     * @return array{string, int}
     */
    private function escape(int $at): array
    {
        $source = $this->source;
        $char = $source[$at + 1] ?? '';
        if ($char === "\n" || $char === "\r") {
            ++$this->line;

            return ['', $at + ($char === "\r" && ($source[$at + 2] ?? '') === "\n" ? 3 : 2)];
        }
        if ($char === 'x' && preg_match('/\G[0-9a-fA-F]{2}/', $source, $m, 0, $at + 2) === 1) {
            return [(string) mb_chr((int) hexdec($m[0]), 'UTF-8'), $at + 4];
        }
        if ($char === 'u' && preg_match('/\G(?:\{([0-9a-fA-F]{1,6})\}|[0-9a-fA-F]{4})/', $source, $m, 0, $at + 2) === 1) {
            $hex = $m[1] ?? $m[0];

            return [(string) mb_chr((int) hexdec($hex), 'UTF-8'), $at + 2 + strlen($m[0])];
        }

        return [self::ESCAPES[$char] ?? $char, $at + 2];
    }

    /**
     * The offset after a regular expression literal that starts at $start, character classes included.
     */
    private function regexEnd(int $start): int
    {
        $source = $this->source;
        $inClass = false;
        for ($i = $start + 1; $i < $this->length && $source[$i] !== "\n"; ++$i) {
            if ($source[$i] === '\\') {
                ++$i;
            } elseif ($source[$i] === '[') {
                $inClass = true;
            } elseif ($source[$i] === ']') {
                $inClass = false;
            } elseif ($source[$i] === '/' && !$inClass) {
                return $i + 1;
            }
        }

        return $i;
    }
}
