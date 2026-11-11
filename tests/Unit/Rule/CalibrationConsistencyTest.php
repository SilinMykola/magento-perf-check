<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Rule;

use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
use PHPUnit\Framework\TestCase;

/**
 * The registry applies every decision in docs/calibration.md (change m2-calibration, design D4)
 * and in later rounds, whose decisions replace the earlier ones.
 */
final class CalibrationConsistencyTest extends TestCase
{
    public function testRegistryMatchesTheCalibrationReport(): void
    {
        $rows = [];
        foreach ([__DIR__ . '/../../../docs/calibration.md', __DIR__ . '/../../../docs/calibration/round-2.md', __DIR__ . '/../../../docs/calibration/round-3.md', __DIR__ . '/../../../docs/calibration/round-4.md', __DIR__ . '/../../../docs/calibration/round-5.md', __DIR__ . '/../../../docs/calibration/round-6.md', __DIR__ . '/../../../docs/calibration/round-7.md', __DIR__ . '/../../../docs/calibration/round-8.md', __DIR__ . '/../../../docs/calibration/round-9.md', __DIR__ . '/../../../docs/calibration/round-10.md', __DIR__ . '/../../../docs/calibration/round-11.md'] as $file) {
            preg_match_all('/^\| \[([A-Z0-9-]+)\]\((?:\.\.\/)?rules\/[^)]+\) \| (error|warning|info) \|.*\| ([^|]+) \|$/m', (string) file_get_contents($file), $matches, PREG_SET_ORDER);
            self::assertNotEmpty($matches, $file);
            foreach ($matches as $match) {
                $rows[$match[1]] = $match;
            }
        }

        $registry = new RuleRegistry();
        foreach ($rows as [, $id, $measuredSeverity, $decision]) {
            $rule = $registry->find($id);
            self::assertNotNull($rule, $id);
            if ($decision === 'experimental') {
                self::assertSame(RuleStatus::Experimental, $rule->status, $id);
                continue;
            }
            self::assertSame(RuleStatus::Default, $rule->status, $id);
            $expected = preg_match('/^(?:downgraded|raised) to (\w+)$/', $decision, $changed) === 1 ? $changed[1] : $measuredSeverity;
            self::assertSame($expected, $rule->severity->value, sprintf('%s: %s', $id, $decision));
        }
    }
}
