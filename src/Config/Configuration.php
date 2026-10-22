<?php

declare(strict_types=1);

namespace MagentoPerfRules\Config;

use MagentoPerfRules\Report\Finding;

/**
 * Settings from magento-perf-rules.neon. Null means "not set in the file".
 */
final class Configuration
{
    /**
     * @param ?list<string> $paths
     * @param array<string, RuleSettings> $rules rule ID => settings
     * @param list<string> $allowlistHandles extra X-LAY-01 allowlist prefixes
     */
    public function __construct(
        public readonly ?array $paths = null,
        public readonly ?bool $includeTests = null,
        public readonly array $rules = [],
        public readonly array $allowlistHandles = [],
        public readonly bool $replaceAllowlist = false,
    ) {
    }

    /**
     * Drops findings of disabled rules and applies severity overrides.
     *
     * @param list<Finding> $findings
     * @return list<Finding>
     */
    public function apply(array $findings): array
    {
        $result = [];
        foreach ($findings as $finding) {
            $settings = $this->rules[$finding->ruleId] ?? null;
            if ($settings === null) {
                $result[] = $finding;
            } elseif ($settings->enabled) {
                $result[] = $settings->severity !== null ? $finding->withSeverity($settings->severity) : $finding;
            }
        }

        return $result;
    }
}
