<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

/**
 * Brings Magento's schema.graphqls dialect to standard SDL before parsing, the way
 * Magento\Framework\GraphQlSchemaStitching\GraphQlReader does: empty types and enums
 * get a placeholder member, and comma-separated interface lists ("implements A, B")
 * become "implements A & B". The reader picks types out with a regular expression, so
 * it also ignores comments between definitions and a stray "extends" before "type".
 * Comments inside a definition, which Magento rejects, are blanked as well: finding
 * mistakes in them is not this tool's job. Line numbers are preserved: nothing adds or
 * removes a line break.
 */
final class SchemaPreprocessor
{
    private const PLACEHOLDER = 'mpr_placeholder_field';

    public static function normalize(string $schema): string
    {
        // Strings and "#" comments are matched only so that nothing inside them is changed.
        // On a regular expression failure the parser gets the file as it is and reports what it finds there.
        $schema = preg_replace_callback(
            '~"""(?:\\\\"""|[\s\S])*?"""|"[^"\\\\\n]*+(?:\\\\.[^"\\\\\n]*+)*+"|#[^\n]*|/\*[\s\S]*?\*/|//[^\n]*|\bextends(?=\s+(?:type|interface|input|enum|union|scalar)\b)~',
            static fn (array $m): string => match (true) {
                $m[0] === 'extends' => 'extend',
                $m[0][0] === '/' => (string) preg_replace('/[^\n]/', ' ', $m[0]),
                default => $m[0],
            },
            $schema,
        ) ?? $schema;
        $schema = (string) preg_replace(
            '/\b(type|interface|input)(\s+[_A-Za-z]\w*[^{}]*)\{(\s*)\}/',
            '$1$2{$3' . self::PLACEHOLDER . ': String }',
            $schema,
        );
        $schema = (string) preg_replace(
            '/\b(enum)(\s+[_A-Za-z]\w*[^{}]*)\{(\s*)\}/',
            '$1$2{$3' . strtoupper(self::PLACEHOLDER) . ' }',
            $schema,
        );

        return (string) preg_replace_callback(
            '/\bimplements(\s+)([_A-Za-z]\w*(?:\s*,\s*[_A-Za-z]\w*)+)/',
            static fn (array $m): string => 'implements' . $m[1] . preg_replace('/\s*,(\s*)/', ' &$1', $m[2]),
            $schema,
        );
    }
}
