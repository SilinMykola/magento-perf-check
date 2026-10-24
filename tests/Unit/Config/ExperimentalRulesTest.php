<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Config;

use Silin\MagentoPerfCheck\Config\Configuration;
use Silin\MagentoPerfCheck\Config\RuleSettings;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\RuleDefinition;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Rule\Source;
use Silin\MagentoPerfCheck\Rule\SourceLevel;
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
