<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Rule;

use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
use PHPUnit\Framework\TestCase;

/**
 * The registry applies every decision in docs/calibration.md (change m2-calibration, design D4).
 */
final class CalibrationConsistencyTest extends TestCase
{
    public function testRegistryMatchesTheCalibrationReport(): void
    {
        $report = (string) file_get_contents(__DIR__ . '/../../../docs/calibration.md');
        preg_match_all('/^\| \[([A-Z0-9-]+)\]\(rules\/[^)]+\) \| (error|warning|info) \|.*\| ([^|]+) \|$/m', $report, $rows, PREG_SET_ORDER);
        self::assertNotEmpty($rows);

        $registry = new RuleRegistry();
        foreach ($rows as [, $id, $measuredSeverity, $decision]) {
            $rule = $registry->find($id);
            self::assertNotNull($rule, $id);
            if ($decision === 'experimental') {
                self::assertSame(RuleStatus::Experimental, $rule->status, $id);
                continue;
            }
            self::assertSame(RuleStatus::Default, $rule->status, $id);
            $expected = str_starts_with($decision, 'downgraded to ') ? substr($decision, strlen('downgraded to ')) : $measuredSeverity;
            self::assertSame($expected, $rule->severity->value, sprintf('%s: %s', $id, $decision));
        }
    }
}
