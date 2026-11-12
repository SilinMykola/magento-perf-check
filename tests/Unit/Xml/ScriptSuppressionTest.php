<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\Rule\JqueryUiBundleRule;
use Silin\MagentoPerfCheck\Xml\XmlAnalyzer;
use PHPUnit\Framework\TestCase;

final class ScriptSuppressionTest extends TestCase
{
    public function testJavaScriptCommentsSuppressWithAReason(): void
    {
        $root = realpath(__DIR__ . '/../../Fixtures/Xml/suppression-script');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new JqueryUiBundleRule()]))->analyze(['app/code']);

        // Line 3: comment on the line before; line 4: comment on the same line. Both are suppressed.
        // Line 6: no reason; line 8: another rule; line 11: a blank line in between. These are reported.
        self::assertSame([6, 8, 11], array_map(static fn (Finding $f): int => $f->line, $result->findings));
        self::assertCount(1, $result->notices);
        self::assertSame('suppression', $result->notices[0]->source);
        self::assertSame(5, $result->notices[0]->line);
    }
}
