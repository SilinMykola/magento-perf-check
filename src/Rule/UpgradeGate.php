<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Rule;

/**
 * Decides which upgrade rules run (change add-upgrade-check-mode): a rule runs when the installed
 * Magento version or the target version has the change it checks, each on its own release line.
 * A rule with appliesFrom is silent when the installed version is below it: it describes the
 * store as it is, so the target version does not count. Other rules always run.
 */
final class UpgradeGate
{
    public function __construct(
        public readonly ?MagentoVersion $installed,
        public readonly ?MagentoVersion $target,
    ) {
    }

    public function runs(?RuleDefinition $rule): bool
    {
        if ($rule === null) {
            return true;
        }
        if ($rule->appliesFrom !== null && $this->installed !== null && !$this->installed->has([$rule->appliesFrom])) {
            return false;
        }
        if (!$rule->isUpgradeRule()) {
            return true;
        }

        return ($this->installed?->has($rule->changedIn) ?? false) || ($this->target?->has($rule->changedIn) ?? false);
    }

    public function knowsAVersion(): bool
    {
        return $this->installed !== null || $this->target !== null;
    }
}
