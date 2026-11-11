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
     * @param array<string, true> $consoleCommands classes in the commands argument of CommandList
     * @param array<string, true> $cronJobs classes that crontab.xml runs
     * @param array<string, list<string>> $webapiMethods service class => methods that webapi.xml routes call
     * @param array<string, list<string>> $webapiPreferences type => classes di.xml prefers for it in the global and web API areas
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
        private readonly array $consoleCommands = [],
        private readonly array $cronJobs = [],
        private readonly array $webapiMethods = [],
        private readonly array $webapiPreferences = [],
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

    public function isConsoleCommand(string $class): bool
    {
        return isset($this->consoleCommands[self::normalizeClass($class)]);
    }

    public function isCronJob(string $class): bool
    {
        return isset($this->cronJobs[self::normalizeClass($class)]);
    }

    /**
     * Normalized service class => methods that webapi.xml routes call.
     *
     * @return array<string, list<string>>
     */
    public function webapiMethods(): array
    {
        return $this->webapiMethods;
    }

    /**
     * Normalized type => normalized classes that di.xml prefers for it in the global, webapi_rest or
     * webapi_soap area; several when modules disagree, since the index does not know their order.
     *
     * @return array<string, list<string>>
     */
    public function webapiPreferences(): array
    {
        return $this->webapiPreferences;
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
            'consoleCommands' => $this->consoleCommands,
            'cronJobs' => $this->cronJobs,
            'webapiMethods' => $this->webapiMethods,
            'webapiPreferences' => $this->webapiPreferences,
        ];
    }

    /**
     * @param array<string, mixed> $data output of toArray()
     */
    public static function fromArray(array $data): self
    {
        /** @var array{moduleCount: int, proxyArguments: array<string, array<string, true>>, pluginClasses: array<string, true>, observers: array<string, list<array{class: string, area: string, module: string}>>, mviewViews: array<string, true>, tablesWithPrimaryKey: array<string, true>, listFieldResolvers: array<string, string>, pluginTargets: array<string, list<string>>, consoleCommands: array<string, true>, cronJobs: array<string, true>, webapiMethods: array<string, list<string>>, webapiPreferences: array<string, list<string>>} $data */
        return new self(
            $data['moduleCount'],
            $data['proxyArguments'],
            $data['pluginClasses'],
            $data['observers'],
            $data['mviewViews'],
            $data['tablesWithPrimaryKey'],
            $data['listFieldResolvers'],
            $data['pluginTargets'],
            $data['consoleCommands'],
            $data['cronJobs'],
            $data['webapiMethods'],
            $data['webapiPreferences'],
        );
    }
}
