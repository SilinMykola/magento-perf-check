<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Xml\Rule;

use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Xml\FileDiscovery;
use MagentoPerfRules\Xml\Rule\CacheIdentityRule;
use MagentoPerfRules\Xml\XmlAnalysisResult;
use MagentoPerfRules\Xml\XmlAnalyzer;
use PHPUnit\Framework\TestCase;

final class CacheIdentityRuleTest extends TestCase
{
    private function analyze(): XmlAnalysisResult
    {
        $root = realpath(__DIR__ . '/../../../Fixtures/Rules/GraphQL/project');
        self::assertIsString($root);

        return (new XmlAnalyzer(new FileDiscovery($root), [new CacheIdentityRule()]))->analyze(['app/code']);
    }

    public function testReportsQueryFieldsWithoutIdentity(): void
    {
        $findings = array_map(
            static fn (Finding $f): string => sprintf('%s %s %s:%d', $f->ruleId, $f->severity->value, $f->file, $f->line),
            $this->analyze()->findings,
        );

        // promoBanners (multi-line field, reported at its first line) and promoTags (extend type Query).
        self::assertSame([
            'X-GQL-01 error app/code/Acme/Gql/etc/schema.graphqls:2',
            'X-GQL-01 error app/code/Acme/Gql/etc/schema.graphqls:11',
        ], $findings);
    }

    public function testMessageNamesTheField(): void
    {
        self::assertStringContainsString('"promoBanners"', $this->analyze()->findings[0]->message);
    }

    public function testUnparsableSchemaIsANotice(): void
    {
        $notices = $this->analyze()->notices;

        self::assertCount(1, $notices);
        self::assertSame('graphql', $notices[0]->source);
        self::assertSame('app/code/Acme/Broken/etc/schema.graphqls', $notices[0]->file);
    }
}
