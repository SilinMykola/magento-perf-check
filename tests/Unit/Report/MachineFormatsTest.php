<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Report;

use DOMDocument;
use JsonSchema\Validator;
use Silin\MagentoPerfCheck\Report\AnalysisResult;
use Silin\MagentoPerfCheck\Report\CheckstyleReporter;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Report\SarifReporter;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\Severity;
use PHPUnit\Framework\TestCase;

final class MachineFormatsTest extends TestCase
{
    private function sample(): AnalysisResult
    {
        return new AnalysisResult(
            [
                new Finding('X-LAY-01', Severity::Error, 'app/code/Acme/Promo/view/frontend/layout/default.xml', 5, '[X-LAY-01] Block "a<b>&c" is uncacheable'),
                new Finding('P-LOOP-01', Severity::Info, 'app/code/Acme/My Module/Model/X.php', 0, '[P-LOOP-01] downgraded'),
                new Finding('X-LAY-01', Severity::Warning, 'app/code/Acme/Promo/view/frontend/layout/contact_index_index.xml', 7, '[X-LAY-01] other'),
            ],
            [],
            '/srv/shop',
            ['app/code'],
            1.0,
        );
    }

    public function testSarifValidatesAgainstTheOfficialSchema(): void
    {
        $sarif = json_decode((new SarifReporter(new RuleRegistry()))->render($this->sample()));
        $schema = json_decode((string) file_get_contents(__DIR__ . '/../../Fixtures/Sarif/sarif-schema-2.1.0.json'));

        $validator = new Validator();
        $validator->validate($sarif, $schema);

        self::assertTrue($validator->isValid(), (string) json_encode($validator->getErrors(), JSON_PRETTY_PRINT));
    }

    public function testSarifContent(): void
    {
        /** @var array{runs: list<array{tool: array{driver: array{rules: list<array{id: string, helpUri: string}>}}, results: list<array<string, mixed>>, originalUriBaseIds: array<string, array{uri: string}>}>} $sarif */
        $sarif = json_decode((new SarifReporter(new RuleRegistry()))->render($this->sample()), true);
        $run = $sarif['runs'][0];

        // Findings are sorted by file: "Acme/My Module" before "Acme/Promo".
        self::assertSame(['P-LOOP-01', 'X-LAY-01'], array_column($run['tool']['driver']['rules'], 'id'));
        self::assertStringEndsWith('docs/rules/P-LOOP-01.md', $run['tool']['driver']['rules'][0]['helpUri']);
        self::assertStringStartsWith('file://', $run['tool']['driver']['rules'][0]['helpUri']);
        self::assertSame('file:///srv/shop/', $run['originalUriBaseIds']['%SRCROOT%']['uri']);

        self::assertSame('note', $run['results'][0]['level']);
        self::assertSame('warning', $run['results'][1]['level']);
        self::assertSame('error', $run['results'][2]['level']);
        self::assertSame(
            ['artifactLocation' => ['uri' => 'app/code/Acme/My%20Module/Model/X.php', 'uriBaseId' => '%SRCROOT%']],
            $run['results'][0]['locations'][0]['physicalLocation'],
        );
        self::assertSame(
            ['artifactLocation' => ['uri' => 'app/code/Acme/Promo/view/frontend/layout/contact_index_index.xml', 'uriBaseId' => '%SRCROOT%'], 'region' => ['startLine' => 7]],
            $run['results'][1]['locations'][0]['physicalLocation'],
        );
        self::assertSame(1, $run['results'][1]['ruleIndex']);
    }

    public function testCheckstyle(): void
    {
        $document = new DOMDocument();
        self::assertTrue($document->loadXML((new CheckstyleReporter())->render($this->sample())));

        $root = $document->documentElement;
        self::assertNotNull($root);
        self::assertSame('checkstyle', $root->nodeName);
        self::assertSame(3, $document->getElementsByTagName('file')->length);

        $first = $document->getElementsByTagName('error')->item(2);
        self::assertInstanceOf(\DOMElement::class, $first);
        self::assertSame('5', $first->getAttribute('line'));
        self::assertSame('app/code/Acme/Promo/view/frontend/layout/default.xml', $first->parentNode instanceof \DOMElement ? $first->parentNode->getAttribute('name') : null);
        self::assertSame('error', $first->getAttribute('severity'));
        self::assertSame('X-LAY-01', $first->getAttribute('source'));
        self::assertSame('[X-LAY-01] Block "a<b>&c" is uncacheable', $first->getAttribute('message'));
    }
}
