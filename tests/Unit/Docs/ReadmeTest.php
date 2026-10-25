<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;

/**
 * The README stays short and links the reference pages, and every finding it shows as a
 * real problem was graded TP in calibration.
 */
final class ReadmeTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    private const REFERENCE_PAGES = ['docs/rules.md', 'docs/configuration.md', 'docs/baseline.md', 'docs/ci.md', 'docs/agents.md'];

    public function testReferencePagesAreLinkedAndExist(): void
    {
        $readme = $this->readme();
        foreach (self::REFERENCE_PAGES as $page) {
            self::assertStringContainsString('](' . $page . ')', $readme, 'README must link ' . $page);
            self::assertFileExists(self::ROOT . '/' . $page);
        }
    }

    public function testReferenceMaterialIsNotInTheReadme(): void
    {
        $readme = $this->readme();

        self::assertStringNotContainsString('```neon', $readme, 'the configuration example belongs in docs/configuration.md');
        self::assertStringNotContainsString('| ID | Finds | Severity | Sources |', $readme, 'the rule tables belong in docs/rules.md');
    }

    public function testExampleOutputShowsOnlyGradedTruePositives(): void
    {
        self::assertSame(1, preg_match('/```text\n(.*?)```/s', $this->readme(), $m));
        $file = null;
        $findings = [];
        foreach (explode("\n", $m[1]) as $line) {
            if (preg_match('#^((?:app|vendor)/\S+\.(?:php|xml|phtml|graphqls))$#', $line, $f) === 1) {
                $file = $f[1];
            } elseif (preg_match('/^\s+(\d+)\s+\w+\s+\[([A-Z0-9-]+)\]/', $line, $f) === 1) {
                self::assertNotNull($file);
                $findings[] = [$f[2], $file . ':' . $f[1]];
            }
        }

        self::assertNotEmpty($findings);
        foreach ($findings as [$rule, $location]) {
            self::assertTrue($this->gradedTruePositive($rule, $location), $rule . ' at ' . $location . ' is not a TP row in docs/calibration/graded/' . $rule . '.md');
        }
    }

    public function testRealFindingsTableShowsOnlyGradedTruePositives(): void
    {
        preg_match_all('/^\| ([A-Z0-9-]+) \| `([^`]+:\d+)`/m', $this->readme(), $rows, PREG_SET_ORDER);

        self::assertNotEmpty($rows);
        foreach ($rows as [, $rule, $location]) {
            self::assertTrue($this->gradedTruePositive($rule, $location), $rule . ' at ' . $location . ' is not a TP row in docs/calibration/graded/' . $rule . '.md');
        }
    }

    private function gradedTruePositive(string $rule, string $location): bool
    {
        $graded = self::ROOT . '/docs/calibration/graded/' . $rule . '.md';
        if (!is_file($graded)) {
            return false;
        }

        return str_contains((string) file_get_contents($graded), '| `' . $location . '` | TP |');
    }

    private function readme(): string
    {
        return (string) file_get_contents(self::ROOT . '/README.md');
    }
}
