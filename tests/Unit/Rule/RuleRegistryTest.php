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

    public function testIdentifierVariantsCarryTheirSeverity(): void
    {
        $registry = new RuleRegistry();

        self::assertSame(\Silin\MagentoPerfCheck\Rule\Severity::Warning, $registry->findByIdentifier('magentoPerf.sessionWithoutProxy')[1] ?? null);
        self::assertSame(\Silin\MagentoPerfCheck\Rule\Severity::Info, $registry->findByIdentifier('magentoPerf.cookieManagerWithoutProxy')[1] ?? null);
        self::assertNull($registry->findByIdentifier('magentoPerf.unknown'));
    }
}
