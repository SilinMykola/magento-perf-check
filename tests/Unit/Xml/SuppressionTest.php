<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\Rule\NonCacheableBlockRule;
use Silin\MagentoPerfCheck\Xml\XmlAnalysisResult;
use Silin\MagentoPerfCheck\Xml\XmlAnalyzer;
use PHPUnit\Framework\TestCase;

final class SuppressionTest extends TestCase
{
    private function analyze(): XmlAnalysisResult
    {
        $root = realpath(__DIR__ . '/../../Fixtures/Xml/suppression');
        self::assertIsString($root);

        return (new XmlAnalyzer(new FileDiscovery($root), [new NonCacheableBlockRule()]))->analyze(['app/code']);
    }

    /**
     * @return list<string>
     */
    private function reportedBlocks(): array
    {
        return array_map(
            static fn (Finding $f): string => (string) $f->element?->getAttribute('name'),
            $this->analyze()->findings,
        );
    }

    public function testSuppressedWithReason(): void
    {
        self::assertNotContains('suppressed', $this->reportedBlocks());
        self::assertNotContains('two.comments', $this->reportedBlocks());
    }

    public function testCommentForAnotherRule(): void
    {
        self::assertContains('other.rule', $this->reportedBlocks());
    }

    public function testMissingReasonReportsFindingAndNotice(): void
    {
        $result = $this->analyze();
        $names = array_map(static fn (Finding $f): string => (string) $f->element?->getAttribute('name'), $result->findings);

        self::assertContains('no.reason', $names);
        self::assertCount(1, $result->notices);
        self::assertSame('suppression', $result->notices[0]->source);
        self::assertSame(10, $result->notices[0]->line);
    }

    public function testCommentNotAdjacent(): void
    {
        self::assertContains('not.adjacent', $this->reportedBlocks());
    }
}
