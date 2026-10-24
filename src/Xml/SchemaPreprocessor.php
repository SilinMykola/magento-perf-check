<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

/**
 * Brings Magento's schema.graphqls dialect to standard SDL before parsing, the way
 * Magento\Framework\GraphQlSchemaStitching\GraphQlReader does: empty types and enums
 * get a placeholder member, and comma-separated interface lists ("implements A, B")
 * become "implements A & B". Line numbers are preserved: nothing adds or removes a
 * line break.
 */
final class SchemaPreprocessor
{
    private const PLACEHOLDER = 'mpr_placeholder_field';

    public static function normalize(string $schema): string
    {
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
