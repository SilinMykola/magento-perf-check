<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Release;

use PHPUnit\Framework\TestCase;

/**
 * resources/calibration-summary.php, which contributors run on their report (docs/calibration-reports.md).
 */
final class CalibrationSummaryTest extends TestCase
{
    private const SCRIPT = __DIR__ . '/../../../resources/calibration-summary.php';

    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/mpc-summary-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dir);
    }

    public function testTheSharedFileHasNoPathsNamesOrMessages(): void
    {
        $findings = [];
        for ($i = 1; $i <= 35; ++$i) {
            $findings[] = ['ruleId' => 'P-DI-01', 'file' => "app/code/ClientName/Secret/Model/M$i.php", 'line' => $i, 'severity' => 'warning', 'message' => '[P-DI-01] ClientName\\Secret\\Model\\M uses the session'];
        }
        $findings[] = ['ruleId' => 'X-LAY-01', 'file' => 'app/design/frontend/ClientName/theme/Magento_Theme/layout/default.xml', 'line' => 7, 'severity' => 'warning', 'message' => '[X-LAY-01] Block "client.block"'];
        $report = [
            'summary' => [
                'incomplete' => false,
                'project' => '/home/someone/projects/client-name',
                'findings' => 36,
                'byRule' => ['P-DI-01' => 35, 'X-LAY-01' => 1],
                'notices' => ['disabled-module' => 2],
                'durationSeconds' => 12.5,
            ],
            'findings' => $findings,
            'notices' => [['source' => 'disabled-module', 'message' => 'ClientName_Secret is disabled', 'file' => 'app/code/ClientName/Secret']],
        ];
        file_put_contents($this->dir . '/report.json', json_encode($report));

        exec(sprintf('%s %s %s %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg(self::SCRIPT), escapeshellarg($this->dir . '/report.json'), escapeshellarg('--out=' . $this->dir)), $output, $exit);
        self::assertSame(0, $exit, implode("\n", $output));

        $shared = (string) file_get_contents($this->dir . '/calibration-summary.md');
        self::assertStringNotContainsString('ClientName', $shared);
        self::assertStringNotContainsString('client', strtolower($shared));
        self::assertStringNotContainsString('app/', $shared);
        self::assertStringContainsString('| P-DI-01 | 35 |', $shared);
        self::assertStringContainsString('- disabled-module: 2', $shared);
        self::assertStringContainsString('12.5 s', $shared);
        // At most 30 findings of a rule to grade, as in the calibration rounds.
        self::assertSame(31, preg_match_all('/^\| \d+ \| [A-Z]/m', $shared));

        $local = (string) file_get_contents($this->dir . '/calibration-findings-local.md');
        self::assertStringContainsString('app/design/frontend/ClientName/theme/Magento_Theme/layout/default.xml:7', $local);
        self::assertSame(31, preg_match_all('/^\| \d+ \| [A-Z]/m', $local));
    }

    public function testSeveralProjectsGiveOneFileThatDoesNotNameThem(): void
    {
        $report = static fn (string $client, int $count): string => (string) json_encode([
            'summary' => ['byRule' => ['P-LOOP-01' => $count], 'notices' => [], 'durationSeconds' => 2.0, 'project' => "/srv/$client"],
            'findings' => array_map(static fn (int $i): array => ['ruleId' => 'P-LOOP-01', 'file' => "app/code/$client/M/F$i.php", 'line' => $i, 'message' => "[P-LOOP-01] $client"], range(1, $count)),
            'notices' => [],
        ]);
        file_put_contents($this->dir . '/acme-shop.json', $report('AcmeShop', 2));
        file_put_contents($this->dir . '/globex-store.json', $report('GlobexStore', 3));

        exec(sprintf(
            '%s %s %s %s %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(self::SCRIPT),
            escapeshellarg($this->dir . '/acme-shop.json'),
            escapeshellarg($this->dir . '/globex-store.json'),
            escapeshellarg('--out=' . $this->dir),
        ), $output, $exit);
        self::assertSame(0, $exit, implode("\n", $output));

        $shared = (string) file_get_contents($this->dir . '/calibration-summary.md');
        self::assertStringContainsString('2 projects', $shared);
        self::assertStringContainsString('| P-LOOP-01 | 5 |', $shared);
        self::assertStringContainsString('run time 4 s', $shared);
        foreach (['acme', 'globex', '/srv', 'app/'] as $name) {
            self::assertStringNotContainsString($name, strtolower($shared));
        }
        // The local file says which report each row comes from.
        self::assertStringContainsString('globex-store.json', (string) file_get_contents($this->dir . '/calibration-findings-local.md'));
    }

    public function testRejectsAMissingOutputDirectory(): void
    {
        exec(sprintf('%s %s %s %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg(self::SCRIPT), escapeshellarg($this->dir . '/none.json'), escapeshellarg('--out=' . $this->dir . '/missing')), $output, $exit);

        self::assertSame(2, $exit);
        self::assertStringContainsString('output directory', implode("\n", $output));
    }

    public function testRejectsAFileThatIsNotAReport(): void
    {
        file_put_contents($this->dir . '/report.json', '{"findings": "no"}');
        exec(sprintf('%s %s %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg(self::SCRIPT), escapeshellarg($this->dir . '/report.json')), $output, $exit);

        self::assertSame(2, $exit);
    }
}
