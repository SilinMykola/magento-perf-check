<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Ci;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class WorkflowsTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    /**
     * @return list<string>
     */
    private static function uses(string $file): array
    {
        preg_match_all('/^\s*(?:-\s*)?uses:\s*(\S+)/m', (string) file_get_contents($file), $m);

        return $m[1];
    }

    public function testProjectExampleRunsBothFormatsAndPinsActions(): void
    {
        $file = self::ROOT . '/docs/ci/github-actions.yml';
        $workflow = Yaml::parseFile($file);
        self::assertIsArray($workflow);
        $runs = implode("\n", array_column($workflow['jobs']['magento-perf']['steps'], 'run'));

        self::assertStringContainsString('magento-perf-check --format=sarif', $runs);
        self::assertStringContainsString('magento-perf-check --format=github', $runs);
        $this->assertPinned($file);
    }

    public function testRepositoryCiCoversPhpAndPhpStanVersions(): void
    {
        $file = self::ROOT . '/.github/workflows/ci.yml';
        $workflow = Yaml::parseFile($file);
        self::assertIsArray($workflow);

        self::assertSame(['8.1', '8.2', '8.3', '8.4', '8.5'], $workflow['jobs']['tests']['strategy']['matrix']['php']);
        self::assertContains('bin/test-phpstan1', array_column($workflow['jobs']['phpstan-1-12']['steps'], 'run'));
        $staticRuns = array_column($workflow['jobs']['static-analysis']['steps'], 'run');
        self::assertContains('composer analyse', $staticRuns);
        self::assertNotEmpty(array_filter($staticRuns, static fn (string $run): bool => str_contains($run, 'openspec') && str_contains($run, 'validate --specs')));
        $this->assertPinned($file);
    }

    private function assertPinned(string $file): void
    {
        $uses = self::uses($file);
        self::assertNotEmpty($uses);
        foreach ($uses as $action) {
            self::assertMatchesRegularExpression('/^[\w.-]+\/[\w.\/-]+@[0-9a-f]{40}$/', $action, $file);
        }
    }

    public function testGrumphpExampleRunsTheTool(): void
    {
        $config = Yaml::parseFile(self::ROOT . '/docs/ci/grumphp.yml');
        self::assertIsArray($config);
        $scripts = $config['grumphp']['tasks']['shell']['scripts'];

        self::assertSame([['-c', 'vendor/bin/magento-perf-check --fail-on=error']], $scripts);
    }
}
