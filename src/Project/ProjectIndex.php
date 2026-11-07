<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Project;

/**
 * Magento configuration facts collected from every module of a project, for rules
 * that need to know what is configured outside the file they analyze.
 * Class names are stored normalized: no leading backslash, lower case.
 */
final class ProjectIndex
{
    /**
     * @param array<string, array<string, true>> $proxyArguments class => constructor argument names configured with a \Proxy
     * @param array<string, true> $pluginClasses
     * @param array<string, list<array{class: string, area: string, module: string}>> $observers event => observers
     * @param array<string, true> $mviewViews
     * @param array<string, true> $tablesWithPrimaryKey
     * @param array<string, string> $listFieldResolvers resolver class => a field on a list type it resolves
     * @param array<string, list<string>> $pluginTargets plugin class => classes it is configured on, outside the admin area
     */
    public function __construct(
        public readonly int $moduleCount,
        private readonly array $proxyArguments,
        private readonly array $pluginClasses,
        private readonly array $observers,
        private readonly array $mviewViews,
        private readonly array $tablesWithPrimaryKey,
        private readonly array $listFieldResolvers = [],
        private readonly array $pluginTargets = [],
    ) {
    }

    public static function normalizeClass(string $class): string
    {
        return strtolower(ltrim(trim($class), '\\'));
    }

    /** True when no Magento module was found: index-dependent rules must stay silent. */
    public function isEmpty(): bool
    {
        return $this->moduleCount === 0;
    }

    public function hasProxyArgument(string $class, string $argument): bool
    {
        return isset($this->proxyArguments[self::normalizeClass($class)][$argument]);
    }

    public function isPluginClass(string $class): bool
    {
        return isset($this->pluginClasses[self::normalizeClass($class)]);
    }

    /**
     * @return list<array{class: string, area: string, module: string}>
     */
    public function observersOf(string $event): array
    {
        return $this->observers[$event] ?? [];
    }

    public function hasMviewView(string $viewId): bool
    {
        return isset($this->mviewViews[$viewId]);
    }

    public function tableHasPrimaryKey(string $table): bool
    {
        return isset($this->tablesWithPrimaryKey[$table]);
    }

    /**
     * Normalized classes that $class is a plugin on, outside the admin area.
     *
     * @return list<string>
     */
    public function pluginTargetsOf(string $class): array
    {
        return $this->pluginTargets[self::normalizeClass($class)] ?? [];
    }

    /** "Type.field" when the class resolves a field of a type returned inside a list, else null. */
    public function listFieldResolvedBy(string $class): ?string
    {
        return $this->listFieldResolvers[self::normalizeClass($class)] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'moduleCount' => $this->moduleCount,
            'proxyArguments' => $this->proxyArguments,
            'pluginClasses' => $this->pluginClasses,
            'observers' => $this->observers,
            'mviewViews' => $this->mviewViews,
            'tablesWithPrimaryKey' => $this->tablesWithPrimaryKey,
            'listFieldResolvers' => $this->listFieldResolvers,
            'pluginTargets' => $this->pluginTargets,
        ];
    }

    /**
     * @param array<string, mixed> $data output of toArray()
     */
    public static function fromArray(array $data): self
    {
        /** @var array{moduleCount: int, proxyArguments: array<string, array<string, true>>, pluginClasses: array<string, true>, observers: array<string, list<array{class: string, area: string, module: string}>>, mviewViews: array<string, true>, tablesWithPrimaryKey: array<string, true>, listFieldResolvers: array<string, string>, pluginTargets: array<string, list<string>>} $data */
        return new self(
            $data['moduleCount'],
            $data['proxyArguments'],
            $data['pluginClasses'],
            $data['observers'],
            $data['mviewViews'],
            $data['tablesWithPrimaryKey'],
            $data['listFieldResolvers'],
            $data['pluginTargets'],
        );
    }
}
