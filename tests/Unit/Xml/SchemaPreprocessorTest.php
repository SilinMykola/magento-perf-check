<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml;

use GraphQL\Language\Parser;
use Silin\MagentoPerfCheck\Xml\SchemaPreprocessor;
use PHPUnit\Framework\TestCase;

/**
 * Constructs found in Magento 2.4.9 core schemas that plain SDL rejects.
 */
final class SchemaPreprocessorTest extends TestCase
{
    private const SCHEMA = <<<'GRAPHQL'
        type Query {
            products: [ProductInterface] @cache(cacheIdentity: "Identity")
        }

        enum PriceAdjustmentCodesEnum @doc(description: "deprecated") {
        }

        type BundleProduct implements ProductInterface, RoutableInterface, PhysicalProductInterface @doc(description: "x") {
            price_view: String
        }

        type SwatchLayerFilterItem implements LayerFilterItemInterface, SwatchLayerFilterItemInterface
        {
            swatch_data: String
        }

        input EmptyInput {}

        extend type Query {
            promoTags: [String] @cache(cacheTag: "promo_tags")
        }
        GRAPHQL;

    public function testCoreDialectParses(): void
    {
        $document = Parser::parse(SchemaPreprocessor::normalize(self::SCHEMA));

        self::assertCount(6, $document->definitions);
    }

    public function testLineNumbersArePreserved(): void
    {
        $normalized = SchemaPreprocessor::normalize(self::SCHEMA);

        self::assertSame(substr_count(self::SCHEMA, "\n"), substr_count($normalized, "\n"));
        $lines = explode("\n", $normalized);
        self::assertStringContainsString('promoTags', $lines[19]);
    }

    public function testCommentsAndExtendsParseWithLinesKept(): void
    {
        $schema = "// @codingStandardsIgnoreFile\n/**\n * License\n */\nextends type Query {\n    a: String // note\n}\n";

        $normalized = SchemaPreprocessor::normalize($schema);

        self::assertCount(1, Parser::parse($normalized)->definitions);
        self::assertSame(substr_count($schema, "\n"), substr_count($normalized, "\n"));
        self::assertStringStartsWith('extend type Query', explode("\n", $normalized)[4]);
    }

    public function testCommentCharactersInStringsAreKept(): void
    {
        $sdl = "# it's a \"comment /* here */\ntype Query {\n    a: String @doc(description: \"see https://example.com/* extends type\")\n}\n"
            . "\"\"\"\nBlock // string\n\"\"\"\ntype B {\n    c: String @doc(description: \"a \\\" // b\")\n}\n";

        self::assertSame($sdl, SchemaPreprocessor::normalize($sdl));
    }

    public function testEscapedQuotesInABlockStringDoNotEndIt(): void
    {
        $sdl = "\"\"\"\na \\\"\"\" b // c\n\"\"\"\ntype Query {\n    a: String\n}\n";

        self::assertSame($sdl, SchemaPreprocessor::normalize($sdl));
    }

    public function testALongStringOnOneLineIsKept(): void
    {
        $sdl = 'type Query { a: String @doc(description: "' . str_repeat('x', 1000000) . '") }';

        self::assertSame($sdl, SchemaPreprocessor::normalize($sdl));
    }

    public function testPlainSdlIsUnchanged(): void
    {
        $sdl = "type Query {\n    a: String\n}\n\ntype B implements C & D {\n    e: Int\n}\n";

        self::assertSame($sdl, SchemaPreprocessor::normalize($sdl));
    }
}
