<?php

declare(strict_types=1);

namespace MagentoPerfRules\Rule;

final class RuleDefinition
{
    /**
     * @param non-empty-list<Source> $sources
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly Severity $severity,
        public readonly RuleStatus $status,
        public readonly array $sources,
    ) {
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
