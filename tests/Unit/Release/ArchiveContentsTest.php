<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Release;

use PHPUnit\Framework\TestCase;

/**
 * Every tracked top-level entry must be classified on purpose: shipped in the Composer
 * dist archive or excluded with export-ignore in .gitattributes.
 */
final class ArchiveContentsTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    private const SHIPPED = [
        'CHANGELOG.md',
        'LICENSE',
        'README.md',
        'bin/magento-perf-check',
        'composer.json',
        'docs/agents.md',
        'docs/baseline.md',
        'docs/calibration.md',
        'docs/calibration-reports.md',
        'docs/ci',
        'docs/ci.md',
        'docs/configuration.md',
        'docs/rules',
        'docs/rules.md',
        'extension-experimental.neon',
        'extension.neon',
        'resources',
        'src',
    ];

    private const EXCLUDED = [
        '.github',
        '.gitattributes',
        '.gitignore',
        'AGENTS.md',
        'bin/calibration',
        'bin/test-install',
        'bin/test-phpstan1',
        'docs/SPEC.md',
        'docs/adr',
        'docs/calibration',
        'docs/runs',
        'docs/source-audit.md',
        'openspec',
        'phpstan.neon.dist',
        'phpunit.xml.dist',
        'tests',
    ];

    protected function setUp(): void
    {
        // bin/test-phpstan1 runs the suite in a copy without .git; in a worktree .git is a file.
        if (!file_exists(self::ROOT . '/.git')) {
            self::markTestSkipped('needs a git checkout');
        }
    }

    public function testShippedPathsAreNotExportIgnored(): void
    {
        foreach (self::SHIPPED as $path) {
            self::assertFalse($this->exportIgnored($path), $path . ' must be in the archive');
        }
    }

    public function testDevelopmentPathsAreExportIgnored(): void
    {
        foreach (self::EXCLUDED as $path) {
            self::assertTrue($this->exportIgnored($path), $path . ' must be export-ignored');
        }
    }

    public function testEveryTrackedPathIsClassified(): void
    {
        $tracked = $this->git(['ls-files']);
        foreach ($tracked as $file) {
            $classified = false;
            foreach ([...self::SHIPPED, ...self::EXCLUDED] as $path) {
                if ($file === $path || str_starts_with($file, $path . '/')) {
                    $classified = true;
                    break;
                }
            }
            self::assertTrue($classified, $file . ' is neither shipped nor excluded: add it to this test and, if needed, to .gitattributes');
        }
    }

    private function exportIgnored(string $path): bool
    {
        // git archive skips a directory whose own entry is export-ignored.
        $output = $this->git(['check-attr', 'export-ignore', '--', $path]);

        return str_ends_with($output[0] ?? '', ': export-ignore: set');
    }

    /**
     * @param list<string> $arguments
     * @return list<string>
     */
    private function git(array $arguments): array
    {
        $command = 'git -C ' . escapeshellarg(self::ROOT) . ' ' . implode(' ', array_map('escapeshellarg', $arguments));
        exec($command, $output, $exitCode);
        self::assertSame(0, $exitCode, $command . ' failed');

        return $output;
    }
}
