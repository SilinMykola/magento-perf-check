<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Rule;

/**
 * Metadata of every rule, shared by the XML analyzer, the PHPStan extension and the report.
 * Sources come from docs/source-audit.md.
 */
final class RuleRegistry
{
    /**
     * Base for documentation links in findings. Points at this checkout until the
     * repository is published (change m0-skeleton, design D7).
     */
    public const DOCS_BASE = __DIR__ . '/../..';

    /** @var array<string, RuleDefinition> */
    private array $rules = [];

    /**
     * @param ?list<RuleDefinition> $definitions defaults to the catalog in catalog.php
     */
    public function __construct(?array $definitions = null)
    {
        foreach ($definitions ?? self::definitions() as $rule) {
            $this->rules[$rule->id] = $rule;
        }
    }

    /**
     * @return list<RuleDefinition>
     */
    public function all(): array
    {
        return array_values($this->rules);
    }

    public function find(string $id): ?RuleDefinition
    {
        return $this->rules[$id] ?? null;
    }

    /**
     * Rule and severity for a PHPStan identifier, or null when no rule emits it.
     *
     * @return array{RuleDefinition, Severity}|null
     */
    public function findByIdentifier(string $identifier): ?array
    {
        foreach ($this->rules as $rule) {
            if (array_key_exists($identifier, $rule->identifiers)) {
                return [$rule, $rule->identifiers[$identifier] ?? $rule->severity];
            }
        }

        return null;
    }

    public function docUrl(string $id): string
    {
        return (realpath(self::DOCS_BASE) ?: self::DOCS_BASE) . '/docs/rules/' . $id . '.md';
    }

    /**
     * @return list<RuleDefinition>
     */
    private static function definitions(): array
    {
        /** @var list<RuleDefinition> */
        return require __DIR__ . '/catalog.php';
    }
}
