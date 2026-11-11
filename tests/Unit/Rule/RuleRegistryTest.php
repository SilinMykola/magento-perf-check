<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Rule;

use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
use PHPUnit\Framework\TestCase;

final class RuleRegistryTest extends TestCase
{
    public function testEveryEnabledRuleHasAPrimarySource(): void
    {
        foreach ((new RuleRegistry())->all() as $rule) {
            if ($rule->status === RuleStatus::Experimental) {
                continue;
            }
            self::assertTrue($rule->hasPrimarySource(), $rule->id . ' needs a level A or B source');
        }
    }

    public function testCodingStandardRulesThatRarelyMatterAreRecommendations(): void
    {
        $registry = new RuleRegistry();
        $severities = [];
        foreach (['S-PERF-01', 'S-TPL-01', 'S-DI-01', 'S-TPL-02', 'S-FN-01'] as $id) {
            $severities[$id] = $registry->find($id)?->severity->value;
        }

        self::assertSame(
            ['S-PERF-01' => 'info', 'S-TPL-01' => 'info', 'S-DI-01' => 'info', 'S-TPL-02' => 'warning', 'S-FN-01' => 'warning'],
            $severities,
        );
    }

    public function testRulesAreRegistered(): void
    {
        $ids = array_map(static fn ($rule): string => $rule->id, (new RuleRegistry())->all());

        self::assertContains('P-LOOP-01', $ids);
        self::assertContains('P-COL-02', $ids);
        self::assertContains('X-LAY-01', $ids);
    }

    public function testDocUrlEndsWithRulePage(): void
    {
        self::assertStringEndsWith('docs/rules/P-COL-02.md', (new RuleRegistry())->docUrl('P-COL-02'));
    }

    public function testDocUrlPointsAtReleaseTag(): void
    {
        self::assertSame(
            'https://github.com/SilinMykola/magento-perf-check/blob/v0.1.0/docs/rules/X-LAY-01.md',
            (new RuleRegistry())->docUrl('X-LAY-01', '0.1.0'),
        );
    }

    public function testDevelopmentDocUrlPointsAtMain(): void
    {
        self::assertSame(
            'https://github.com/SilinMykola/magento-perf-check/blob/main/docs/rules/X-LAY-01.md',
            (new RuleRegistry())->docUrl('X-LAY-01', '0.2.0-dev'),
        );
    }

    public function testIdentifierVariantsCarryTheirSeverity(): void
    {
        $registry = new RuleRegistry();

        self::assertSame(\Silin\MagentoPerfCheck\Rule\Severity::Warning, $registry->findByIdentifier('magentoPerf.sessionWithoutProxy')[1] ?? null);
        self::assertSame(\Silin\MagentoPerfCheck\Rule\Severity::Info, $registry->findByIdentifier('magentoPerf.cookieManagerWithoutProxy')[1] ?? null);
        self::assertNull($registry->findByIdentifier('magentoPerf.unknown'));
    }
}
