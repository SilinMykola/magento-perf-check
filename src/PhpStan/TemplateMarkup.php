<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

/**
 * The HTML of a storefront template, for rules that look at its tags. PHP parts become a run of
 * "\x01", HTML comments and the bodies of <script> elements become spaces; newlines are kept, so
 * offsets and line numbers stay those of the file.
 */
final class TemplateMarkup
{
    private const PHP = "\x01";

    /** The JavaScript MIME types of the HTML standard: a script of one of them runs as a classic script. */
    private const JAVASCRIPT_TYPES = [
        'application/ecmascript', 'application/javascript', 'application/x-ecmascript', 'application/x-javascript',
        'text/ecmascript', 'text/javascript', 'text/javascript1.0', 'text/javascript1.1', 'text/javascript1.2',
        'text/javascript1.3', 'text/javascript1.4', 'text/javascript1.5', 'text/jscript', 'text/livescript',
        'text/x-ecmascript', 'text/x-javascript',
    ];

    private function __construct(private readonly string $html)
    {
    }

    /**
     * A template of the storefront: in a module's view/frontend or view/base, or in a frontend theme.
     */
    public static function isStorefrontTemplate(string $file): bool
    {
        $file = str_replace('\\', '/', $file);

        return str_ends_with($file, '.phtml')
            && (str_contains($file, '/view/frontend/') || str_contains($file, '/view/base/') || str_contains($file, '/design/frontend/'));
    }

    public static function fromFile(string $file): ?self
    {
        $source = @file_get_contents($file);
        if ($source === false) {
            return null;
        }
        $html = '';
        foreach (token_get_all($source) as $token) {
            if (is_array($token) && $token[0] === T_INLINE_HTML) {
                $html .= $token[1];
                continue;
            }
            $html .= self::blank(is_array($token) ? $token[1] : $token, self::PHP);
        }
        $html = (string) preg_replace_callback(
            '/(<script\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*>)(.*?)(?=<\/script\s*>|$)/is',
            static fn (array $m): string => $m[1] . self::blank($m[2], ' '),
            $html,
        );
        // After the script bodies, so that "<!--" in JavaScript does not start a comment.
        $html = (string) preg_replace_callback('/<!--.*?(?:-->|$)/s', static fn (array $m): string => self::blank($m[0], ' '), $html);

        return new self($html);
    }

    /**
     * Opening tags with this name outside <template> elements, whose content the browser does not render.
     *
     * @return list<array{attributes: array<string, string>, line: int}>
     */
    public function tags(string $name): array
    {
        $inert = $this->templateRanges();
        $tags = [];
        if (preg_match_all('/<' . preg_quote($name, '/') . '\b((?:"[^"]*"|\'[^\']*\'|[^>"\'])*)>/i', $this->html, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER) > 0) {
            foreach ($matches as [[, $at], [$attributes]]) {
                foreach ($inert as [$from, $to]) {
                    if ($at > $from && $at < $to) {
                        continue 2;
                    }
                }
                $tags[] = ['attributes' => self::attributes($attributes), 'line' => substr_count($this->html, "\n", 0, $at) + 1];
            }
        }

        return $tags;
    }

    /**
     * Whether PHP prints part of the tag outside its quoted attribute values, so that it may add
     * attributes the rule cannot see.
     *
     * @param array<string, string> $attributes
     */
    public static function printsAttributes(array $attributes): bool
    {
        foreach (array_keys($attributes) as $attribute) {
            if (str_contains($attribute, self::PHP)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the attribute is written, or bound with Alpine.js (`:name` or `x-bind:name`).
     *
     * @param array<string, string> $attributes
     */
    public static function hasAttribute(array $attributes, string $name): bool
    {
        return isset($attributes[$name]) || self::isBound($attributes, $name);
    }

    /**
     * @param array<string, string> $attributes
     */
    public static function isBound(array $attributes, string $name): bool
    {
        return isset($attributes[':' . $name]) || isset($attributes['x-bind:' . $name]);
    }

    /**
     * Whether a written type attribute names JavaScript; a missing or empty one does too.
     */
    public static function isJavaScriptType(?string $type): bool
    {
        $essence = strtolower(trim(explode(';', (string) $type)[0]));

        return $essence === '' || in_array($essence, self::JAVASCRIPT_TYPES, true);
    }

    /**
     * Whether PHP prints part of an attribute value.
     */
    public static function isPrinted(string $value): bool
    {
        return str_contains($value, self::PHP);
    }

    /**
     * @return array<string, string> attribute names in lower case, with their values
     */
    private static function attributes(string $text): array
    {
        $attributes = [];
        preg_match_all('/([^\s=\/>"\']+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>"\']+)))?/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $attributes[strtolower($match[1])] ??= ($match[2] ?? '') . ($match[3] ?? '') . ($match[4] ?? '');
        }

        return $attributes;
    }

    /**
     * @return list<array{int, int}> start and end offsets of the outermost <template> elements
     */
    private function templateRanges(): array
    {
        $ranges = [];
        $depth = 0;
        $start = 0;
        preg_match_all('/<(\/?)template\b[^>]*>/i', $this->html, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($matches as [[$tag, $at], [$closing]]) {
            if ($closing === '') {
                if ($depth++ === 0) {
                    $start = $at;
                }
            } elseif ($depth > 0 && --$depth === 0) {
                $ranges[] = [$start, $at + strlen($tag)];
            }
        }
        if ($depth > 0) {
            $ranges[] = [$start, strlen($this->html)];
        }

        return $ranges;
    }

    private static function blank(string $text, string $with): string
    {
        return (string) preg_replace('/[^\n]/', $with, $text);
    }
}
