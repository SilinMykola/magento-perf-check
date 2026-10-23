<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Config;

use MagentoPerfRules\Config\Configuration;
use MagentoPerfRules\Config\RuleSettings;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Rule\RuleDefinition;
use MagentoPerfRules\Rule\RuleRegistry;
use MagentoPerfRules\Rule\RuleStatus;
use MagentoPerfRules\Rule\Severity;
use MagentoPerfRules\Rule\Source;
use MagentoPerfRules\Rule\SourceLevel;
use PHPUnit\Framework\TestCase;

final class ExperimentalRulesTest extends TestCase
{
    private function registry(): RuleRegistry
    {
        $source = [new Source(SourceLevel::B, 'https://example.invalid', 'test')];

        return new RuleRegistry([
            new RuleDefinition('X-LAY-01', 'stable', Severity::Warning, RuleStatus::Default, $source),
            new RuleDefinition('X-EVT-01', 'demoted', Severity::Warning, RuleStatus::Experimental, $source),
        ]);
    }

    /**
     * @return list<Finding>
     */
    private function findings(): array
    {
        return [
            new Finding('X-LAY-01', Severity::Warning, 'a.xml', 1, 'stable'),
            new Finding('X-EVT-01', Severity::Warning, 'b.xml', 2, 'demoted'),
        ];
    }

    public function testExperimentalRuleSilentByDefault(): void
    {
        $kept = (new Configuration())->apply($this->findings(), $this->registry());

        self::assertSame(['X-LAY-01'], array_map(static fn (Finding $f): string => $f->ruleId, $kept));
    }

    public function testExperimentalRuleEnabledInConfiguration(): void
    {
        $config = new Configuration(rules: ['X-EVT-01' => new RuleSettings(true)]);

        $kept = $config->apply($this->findings(), $this->registry());

        self::assertSame(['X-LAY-01', 'X-EVT-01'], array_map(static fn (Finding $f): string => $f->ruleId, $kept));
    }
}
