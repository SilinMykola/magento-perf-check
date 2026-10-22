<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Integration;

use FilesystemIterator;
use MagentoPerfRules\Cli\Application;
use MagentoPerfRules\PhpStan\Runner\PhpStanRunner;
use MagentoPerfRules\Rule\RuleRegistry;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Runs the whole command against a Magento-like fixture project, with the real PHPStan.
 */
final class ApplicationTest extends TestCase
{
    private const PROJECT = __DIR__ . '/../Fixtures/Project/magento';

    /**
     * @param list<string> $argv
     * @return array{int, string, string}
     */
    private function runCommand(array $argv, ?PhpStanRunner $runner = null): array
    {
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);

        $project = realpath(self::PROJECT);
        self::assertIsString($project);
        $exitCode = (new Application($runner, $stdout, $stderr))->run(['--project=' . $project, ...$argv], __DIR__);

        rewind($stdout);
        rewind($stderr);

        return [$exitCode, (string) stream_get_contents($stdout), (string) stream_get_contents($stderr)];
    }

    /**
     * @return array{summary: array<string, mixed>, findings: list<array<string, mixed>>, notices: list<array<string, mixed>>}
     */
    private function json(string $stdout): array
    {
        /** @var array{summary: array<string, mixed>, findings: list<array<string, mixed>>, notices: list<array<string, mixed>>} */
        return json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
    }

    public function testJsonReportMergesXmlAndPhpFindings(): void
    {
        [$exitCode, $stdout, $stderr] = $this->runCommand(['--format=json']);

        self::assertSame('', $stderr);
        self::assertSame(Application::EXIT_FINDINGS, $exitCode);
        $report = $this->json($stdout);

        $found = array_map(
            static fn (array $f): string => sprintf('%s %s:%d %s', $f['ruleId'], $f['file'], $f['line'], $f['severity']),
            $report['findings'],
        );
        self::assertSame([
            'P-LOOP-01 app/code/Acme/Promo/Model/PriceExporter.php:23 error',
            'X-LAY-01 app/code/Acme/Promo/view/frontend/layout/default.xml:4 error',
            'X-LAY-01 app/code/Acme/Warn/view/frontend/layout/contact_index_index.xml:4 warning',
        ], $found);

        foreach ($report['findings'] as $finding) {
            self::assertIsString($finding['docUrl']);
            self::assertStringEndsWith('docs/rules/' . $finding['ruleId'] . '.md', $finding['docUrl']);
            self::assertIsString($finding['message']);
        }

        self::assertSame(['error' => 2, 'warning' => 1, 'info' => 0], $report['summary']['bySeverity']);
        self::assertIsFloat($report['summary']['durationSeconds'] + 0.0);
    }

    public function testNonRulePhpStanErrorsAreNoticesNotFindings(): void
    {
        [, $stdout] = $this->runCommand(['--format=json']);
        $report = $this->json($stdout);

        self::assertSame(['phpstan-parse' => 1], $report['summary']['notices']);
        self::assertSame('app/code/Acme/Promo/Model/Broken.php', $report['notices'][0]['file']);
    }

    public function testTableReport(): void
    {
        [$exitCode, $stdout] = $this->runCommand([]);

        self::assertSame(Application::EXIT_FINDINGS, $exitCode);
        self::assertStringContainsString("app/code/Acme/Promo/Model/PriceExporter.php\n     23  error    [P-LOOP-01]", $stdout);
        self::assertStringContainsString('3 finding(s): 2 error, 1 warning, 0 info.', $stdout);
        self::assertStringContainsString('1 analysis notice(s) not reported as findings (1 phpstan-parse)', $stdout);
    }

    public function testWarningsOnlyExitCleanByDefault(): void
    {
        [$exitCode] = $this->runCommand(['--paths=app/code/Acme/Warn']);

        self::assertSame(Application::EXIT_CLEAN, $exitCode);
    }

    public function testFailOnWarning(): void
    {
        [$exitCode] = $this->runCommand(['--paths=app/code/Acme/Warn', '--fail-on=warning']);

        self::assertSame(Application::EXIT_FINDINGS, $exitCode);
    }

    public function testInvalidInputExitsWithTwo(): void
    {
        [$exitCode, $stdout, $stderr] = $this->runCommand(['--format=xml']);

        self::assertSame(Application::EXIT_ERROR, $exitCode);
        self::assertSame('', $stdout);
        self::assertStringContainsString('Supported formats: table, json, sarif, checkstyle.', $stderr);
    }

    public function testPhpStanCrashExitsWithTwo(): void
    {
        $runner = new PhpStanRunner(new RuleRegistry(), __DIR__ . '/../Fixtures/Project/crashing-phpstan.php');

        [$exitCode, $stdout, $stderr] = $this->runCommand([], $runner);

        self::assertSame(Application::EXIT_ERROR, $exitCode);
        self::assertSame('', $stdout);
        self::assertStringContainsString('PHPStan failed with exit code 255', $stderr);
        self::assertStringContainsString('Allowed memory size', $stderr);
    }

    public function testProjectIsLeftUnchanged(): void
    {
        $before = $this->snapshot();
        $this->runCommand(['--format=json']);

        self::assertSame($before, $this->snapshot());
    }

    /**
     * @return array<string, string> path => size and mtime
     */
    private function snapshot(): array
    {
        clearstatcache();
        $snapshot = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator((string) realpath(self::PROJECT), FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );
        /** @var SplFileInfo $info */
        foreach ($iterator as $info) {
            $snapshot[$info->getPathname()] = $info->getSize() . '@' . $info->getMTime();
        }
        ksort($snapshot);

        return $snapshot;
    }

    /**
     * @param list<string> $argv
     * @return list<string> "RULE file:line" of every finding
     */
    private function findings(array $argv): array
    {
        [, $stdout] = $this->runCommand(['--format=json', ...$argv]);

        return array_map(
            static fn (array $f): string => sprintf('%s %s:%d', $f['ruleId'], $f['file'], $f['line']),
            $this->json($stdout)['findings'],
        );
    }

    public function testTestCodeIsSkippedByDefault(): void
    {
        foreach ($this->findings([]) as $finding) {
            self::assertStringNotContainsString('/Test/', $finding);
        }
    }

    public function testTestCodeIncludedOnRequest(): void
    {
        $findings = $this->findings(['--include-tests']);

        self::assertContains('P-LOOP-01 app/code/Acme/Promo/Test/Integration/ExportTest.php:21', $findings);
        self::assertContains('X-LAY-01 app/code/Acme/Promo/Test/Integration/_files/view/frontend/layout/default.xml:4', $findings);
    }

    public function testTestDirectoryGivenExplicitlyIsAnalyzed(): void
    {
        self::assertSame([
            'P-LOOP-01 app/code/Acme/Promo/Test/Integration/ExportTest.php:21',
            'X-LAY-01 app/code/Acme/Promo/Test/Integration/_files/view/frontend/layout/default.xml:4',
        ], $this->findings(['--paths=app/code/Acme/Promo/Test']));
    }

    public function testDisabledPhpRuleReportsNothing(): void
    {
        $findings = $this->findings(['--config=' . __DIR__ . '/../Fixtures/Config/disable-loop.neon']);

        self::assertSame([], array_values(array_filter($findings, static fn (string $f): bool => str_starts_with($f, 'P-LOOP-01'))));
        self::assertNotSame([], $findings);
    }

    public function testDowngradedRuleDoesNotFailTheRun(): void
    {
        [$exitCode, $stdout] = $this->runCommand(['--format=json', '--config=' . __DIR__ . '/../Fixtures/Config/downgrade-layout.neon']);

        self::assertSame(Application::EXIT_CLEAN, $exitCode);
        $report = $this->json($stdout);
        self::assertSame(['error' => 0, 'warning' => 1, 'info' => 0], $report['summary']['bySeverity']);
        self::assertSame('X-LAY-01', $report['findings'][0]['ruleId']);
    }

    public function testCommandLinePathsWinOverConfiguration(): void
    {
        [, $stdout] = $this->runCommand([
            '--format=json',
            '--config=' . __DIR__ . '/../Fixtures/Config/paths.neon',
            '--paths=app/code/Acme/Warn',
        ]);

        self::assertSame(['app/code/Acme/Warn'], $this->json($stdout)['summary']['paths']);
    }

    public function testBaselineHidesKnownFindings(): void
    {
        $file = sys_get_temp_dir() . '/mpr-app-baseline-' . bin2hex(random_bytes(4)) . '.json';
        [$exitCode, $stdout] = $this->runCommand(['--generate-baseline=' . $file]);

        self::assertSame(Application::EXIT_CLEAN, $exitCode);
        self::assertStringContainsString('Baseline with 3 finding(s) written to ' . $file, $stdout);

        [$exitCode, $stdout] = $this->runCommand(['--format=json', '--baseline=' . $file]);
        unlink($file);

        self::assertSame(Application::EXIT_CLEAN, $exitCode);
        $report = $this->json($stdout);
        self::assertSame([], $report['findings']);
        self::assertSame(['hidden' => 3, 'unmatched' => 0], $report['summary']['baseline']);
    }

    public function testMissingBaselineExitsWithTwo(): void
    {
        [$exitCode, , $stderr] = $this->runCommand(['--baseline=/nonexistent/baseline.json']);

        self::assertSame(Application::EXIT_ERROR, $exitCode);
        self::assertStringContainsString('does not exist', $stderr);
    }

    public function testSarifAndCheckstyleOnStdout(): void
    {
        [$exitCode, $stdout] = $this->runCommand(['--format=sarif']);
        self::assertSame(Application::EXIT_FINDINGS, $exitCode);
        $sarif = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($sarif);
        self::assertSame('2.1.0', $sarif['version']);

        [, $stdout] = $this->runCommand(['--format=checkstyle']);
        $document = new \DOMDocument();
        self::assertTrue($document->loadXML($stdout));
        self::assertSame(3, $document->getElementsByTagName('error')->length);
    }
}
