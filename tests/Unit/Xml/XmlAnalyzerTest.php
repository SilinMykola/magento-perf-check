<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml;

use DOMDocument;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\XmlAnalyzer;
use Silin\MagentoPerfCheck\Xml\XmlFile;
use Silin\MagentoPerfCheck\Xml\XmlRule;
use PHPUnit\Framework\TestCase;

final class XmlAnalyzerTest extends TestCase
{
    public function testMalformedFileBecomesNoticeAndOthersAreAnalyzed(): void
    {
        $root = realpath(__DIR__ . '/../../Fixtures/Xml/project');
        self::assertIsString($root);

        $result = (new XmlAnalyzer(new FileDiscovery($root), [new EveryBlockRule()]))->analyze(['app/code']);

        self::assertCount(1, $result->notices);
        self::assertSame('app/code/Acme/Promo/view/frontend/layout/broken.xml', $result->notices[0]->file);
        self::assertNotSame('', $result->notices[0]->message);

        $files = array_map(static fn (Finding $f): string => $f->file, $result->findings);
        self::assertContains('app/code/Acme/Promo/view/frontend/layout/default.xml', $files);
    }

    public function testFindingLineIsElementLine(): void
    {
        $root = realpath(__DIR__ . '/../../Fixtures/Xml/project');
        self::assertIsString($root);

        $result = (new XmlAnalyzer(new FileDiscovery($root), [new EveryBlockRule()]))->analyze(['app/code']);

        self::assertCount(1, $result->findings);
        self::assertSame(5, $result->findings[0]->line);
    }
}

final class EveryBlockRule implements XmlRule
{
    public function ruleId(): string
    {
        return 'TEST-01';
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_LAYOUT;
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach ($document->getElementsByTagName('block') as $block) {
            $findings[] = new Finding('TEST-01', Severity::Info, $file->relativePath, $block->getLineNo(), 'block');
        }

        return $findings;
    }
}
