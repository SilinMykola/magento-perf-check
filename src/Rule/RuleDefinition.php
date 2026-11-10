<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Rule;

final class RuleDefinition
{
    /**
     * @param non-empty-list<Source> $sources
     * @param array<string, Severity|null> $identifiers PHPStan identifiers of a PHP rule; null means the rule's severity
     * @param list<string> $changedIn for an upgrade rule: the first Magento version with the change it checks, one per release line
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly Severity $severity,
        public readonly RuleStatus $status,
        public readonly array $sources,
        public readonly array $identifiers = [],
        public readonly array $changedIn = [],
    ) {
    }

    public function isUpgradeRule(): bool
    {
        return $this->changedIn !== [];
    }

    public function hasPrimarySource(): bool
    {
        foreach ($this->sources as $source) {
            if ($source->level !== SourceLevel::C) {
                return true;
            }
        }

        return false;
    }

    public function docPath(): string
    {
        return 'docs/rules/' . $this->id . '.md';
    }
}
