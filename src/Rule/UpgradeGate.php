<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Rule;

/**
 * Decides which upgrade rules run (change add-upgrade-check-mode): a rule runs when the installed
 * Magento version or the target version has the change it checks, each on its own release line.
 * Other rules always run.
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
        if ($rule === null || !$rule->isUpgradeRule()) {
            return true;
        }

        return ($this->installed?->has($rule->changedIn) ?? false) || ($this->target?->has($rule->changedIn) ?? false);
    }

    public function knowsAVersion(): bool
    {
        return $this->installed !== null || $this->target !== null;
    }
}
