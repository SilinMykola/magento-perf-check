<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Report;

use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Report\AnalysisResult;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Report\TableReporter;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\Severity;

final class TableReporterTest extends TestCase
{
    private function sample(): AnalysisResult
    {
        $findings = [
            new Finding('P-DI-06', Severity::Warning, 'app/code/Acme/A/Model/One.php', 12, 'ObjectManager in One.'),
            new Finding('P-LOOP-01', Severity::Error, 'app/code/Acme/A/Model/Two.php', 40, 'load() in a loop in Two.'),
            new Finding('P-LOOP-01', Severity::Error, 'app/code/Acme/A/Model/Two.php', 37, 'load() in a loop in Two.'),
            new Finding('P-LOOP-01', Severity::Error, 'app/code/Acme/A/Model/Three.php', 9, '[P-LOOP-01] load() in a loop in Three.'),
        ];
        for ($i = 1; $i <= TableReporter::INFO_FILES + 2; ++$i) {
            $findings[] = new Finding('T-01', Severity::Info, sprintf('app/design/t%d.phtml', $i), 3, sprintf('createBlock() in t%d.', $i));
        }

        return new AnalysisResult($findings, [], '/project', ['app/code', 'app/design'], 1.25);
    }

    public function testOneBlockPerRuleWithErrorsFirstAndLinesPerFile(): void
    {
        $registry = new RuleRegistry();
        $out = (new TableReporter($registry))->render($this->sample());

        $expected = "ERROR   P-LOOP-01   " . $registry->find('P-LOOP-01')?->title . "  3 findings\n"
            . '        ' . $registry->docUrl('P-LOOP-01') . "\n"
            . "        app/code/Acme/A/Model/Three.php:9\n"
            . "        app/code/Acme/A/Model/Two.php:37, 40\n\n"
            . "WARNING P-DI-06     " . $registry->find('P-DI-06')?->title . "  1 finding\n";
        self::assertStringStartsWith($expected, $out);
        self::assertLessThan(strpos($out, 'INFO    T-01   '), strpos($out, 'WARNING P-DI-06'));
        self::assertStringNotContainsString('load() in a loop in Two.', $out, 'messages only with --verbose');
        self::assertStringNotContainsString("\e", $out);
        self::assertStringContainsString("11 finding(s): 3 error, 1 warning, 7 info. Analyzed app/code, app/design in 1.2 s.\n", $out);
    }

    public function testInfoRuleListsAFewFilesUnlessVerbose(): void
    {
        $registry = new RuleRegistry();
        $short = (new TableReporter($registry))->render($this->sample());
        self::assertStringContainsString('app/design/t' . TableReporter::INFO_FILES . '.phtml:3', $short);
        self::assertStringNotContainsString('app/design/t' . (TableReporter::INFO_FILES + 1) . '.phtml', $short);
        self::assertStringContainsString("        ... and 2 more files\n", $short);
        self::assertStringContainsString('Run with --verbose for the message of each finding and every file, or use --format=json.', $short);

        $verbose = (new TableReporter($registry, verbose: true))->render($this->sample());
        self::assertStringContainsString('app/design/t' . (TableReporter::INFO_FILES + 2) . ".phtml\n", $verbose);
        self::assertStringContainsString("           37 load() in a loop in Two.\n", $verbose);
        self::assertStringContainsString("            9 load() in a loop in Three.\n", $verbose, 'the rule ID is not repeated before the message');
        self::assertStringNotContainsString('more files', $verbose);
        self::assertStringNotContainsString('--verbose', $verbose);
    }

    public function testColorsAndTerminalLinks(): void
    {
        $registry = new RuleRegistry();
        $out = (new TableReporter($registry, ansi: true))->render($this->sample());

        self::assertStringContainsString("\e[1;37;41mERROR  \e[0m", $out);
        self::assertStringContainsString("\e]8;;" . $registry->docUrl('P-LOOP-01') . "\e\\\e[1mP-LOOP-01\e[0m\e]8;;\e\\", $out);
        self::assertStringContainsString("\e[1;31m3 error\e[0m", $out);
    }

    public function testNoFindings(): void
    {
        $out = (new TableReporter(new RuleRegistry()))->render(new AnalysisResult([], [], '/project', ['app/code'], 0.5));

        self::assertSame("0 finding(s): 0 error, 0 warning, 0 info. Analyzed app/code in 0.5 s.\n", $out);
    }

    public function testRepeatedLineIsCountedAndOneHiddenFileIsShown(): void
    {
        $findings = [
            new Finding('T-01', Severity::Info, 'a/t.phtml', 5, 'first'),
            new Finding('T-01', Severity::Info, 'a/t.phtml', 5, 'second'),
            new Finding('T-01', Severity::Info, 'a/t.phtml', 0, 'no line'),
        ];
        for ($i = 2; $i <= TableReporter::INFO_FILES + 1; ++$i) {
            $findings[] = new Finding('T-01', Severity::Info, sprintf('a/t%d.phtml', $i), 1, 'x');
        }
        $out = (new TableReporter(new RuleRegistry()))->render(new AnalysisResult($findings, [], '/project', ['a'], 0.1));

        self::assertStringContainsString("        a/t.phtml:5 (2x)\n", $out);
        self::assertStringContainsString('a/t' . (TableReporter::INFO_FILES + 1) . ".phtml:1\n", $out, 'one file more is listed, not hidden');
        self::assertStringNotContainsString('more files', $out);
        self::assertStringContainsString((TableReporter::INFO_FILES + 3) . ' findings', $out);
    }
}
