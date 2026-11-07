<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Config;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;

/**
 * Settings from magento-perf-check.neon. Null means "not set in the file".
 */
final class Configuration
{
    /**
     * @param ?list<string> $paths
     * @param array<string, RuleSettings> $rules rule ID => settings
     * @param list<string> $allowlistHandles extra allowlist prefixes of X-LAY-01 and X-LAY-03
     */
    public function __construct(
        public readonly ?array $paths = null,
        public readonly ?bool $includeTests = null,
        public readonly array $rules = [],
        public readonly array $allowlistHandles = [],
        public readonly bool $replaceAllowlist = false,
    ) {
    }

    public function isEnabled(string $ruleId, RuleRegistry $registry): bool
    {
        return $this->rules[$ruleId]->enabled ?? $registry->find($ruleId)?->status !== RuleStatus::Experimental;
    }

    /**
     * Drops findings of disabled rules (experimental rules are disabled unless enabled
     * explicitly) and applies severity overrides.
     *
     * @param list<Finding> $findings
     * @return list<Finding>
     */
    public function apply(array $findings, RuleRegistry $registry): array
    {
        $result = [];
        foreach ($findings as $finding) {
            if ($this->isEnabled($finding->ruleId, $registry)) {
                $severity = $this->rules[$finding->ruleId]->severity ?? null;
                $result[] = $severity !== null ? $finding->withSeverity($severity) : $finding;
            }
        }

        return $result;
    }
}
