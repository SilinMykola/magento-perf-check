<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Project;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Silin\MagentoPerfCheck\Xml\LayoutHandles;
use Silin\MagentoPerfCheck\Xml\Rule\NonCacheableBlockRule;

/**
 * Builds the ProjectIndex from app/code and every Composer package that is a Magento
 * module, regardless of which paths are analyzed. Reads etc/ files and the frontend layouts of modules and themes,
 * and writes nothing into the project; the result is cached in the system temp directory.
 */
final class ProjectIndexBuilder
{
    private const XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    /** Indexed files, relative to a module's etc/ directory; * is one area directory. */
    /** Part of the cache key: raise it when the same files start to give a different index. */
    private const INDEX_VERSION = 6;

    private const MODULE_CONFIG = 'app/etc/config.php';

    private const FILES = ['di.xml', '*/di.xml', 'events.xml', '*/events.xml', 'mview.xml', 'db_schema.xml', 'schema.graphqls', 'crontab.xml', 'webapi.xml'];

    /** Areas whose di.xml preferences apply to web API requests. */
    private const WEBAPI_AREAS = ['global', 'webapi_rest', 'webapi_soap'];

    private const COMMAND_LISTS = ['magento\\framework\\console\\commandlist', 'magento\\framework\\console\\commandlistinterface'];

    public function __construct(private readonly ?string $cacheDir = null)
    {
    }

    public function build(string $projectRoot): ProjectIndex
    {
        $modules = $this->moduleRoots($projectRoot);
        $files = $this->indexedFiles($projectRoot, $modules);
        $layouts = LayoutHandles::layoutFiles($projectRoot, $modules);

        $cacheFile = $this->cacheFile($projectRoot, $this->fingerprintOf($modules, $files, [...$layouts, ...self::routeFiles($modules)]));
        if (is_file($cacheFile)) {
            $data = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($data)) {
                /** @var array<string, mixed> $data */
                return ProjectIndex::fromArray($data);
            }
        }

        $index = $this->index(count($modules), $files, $this->layoutPlacements($projectRoot, $modules, $layouts));
        $this->writeCache($cacheFile, $index);

        return $index;
    }

    /**
     * Module directories: app/code/<Vendor>/<Module> and Composer packages with etc/module.xml.
     *
     * @return array<string, string> module root => module name
     */
    public function moduleRoots(string $projectRoot): array
    {
        $disabled = self::disabledModules($projectRoot);

        return array_filter($this->allModuleRoots($projectRoot), static fn (string $name): bool => !isset($disabled[$name]));
    }

    /**
     * Directories of the modules set to 0 in app/etc/config.php: Magento loads neither their code
     * nor their configuration. A Composer package is listed both as installed (vendor/..., which
     * may be a symlink) and resolved, since files are found under either.
     *
     * @return array<string, string> directory => module name
     */
    public function disabledModuleRoots(string $projectRoot): array
    {
        $disabled = self::disabledModules($projectRoot);
        $roots = [];
        foreach ($this->allModuleRoots($projectRoot, $asInstalled) as $root => $name) {
            if (isset($disabled[$name])) {
                $roots[$root] = $name;
                $roots[$asInstalled[$root]] = $name;
            }
        }
        ksort($roots);

        return $roots;
    }

    /**
     * Module names set to 0 or false in the modules list of app/etc/config.php. The file is read
     * as text, never run; a module missing from the list counts as enabled.
     *
     * @return array<string, true>
     */
    public static function disabledModules(string $projectRoot): array
    {
        $content = @file_get_contents($projectRoot . '/' . self::MODULE_CONFIG);
        if (!is_string($content)) {
            return [];
        }
        // Tokenizing does not run the file; comments go, so a bracket or a module name in one does not count.
        $code = '';
        foreach (token_get_all($content) as $token) {
            $code .= is_array($token) ? (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true) ? ' ' : $token[1]) : $token;
        }
        if (preg_match('/([\'"])modules\1\s*=>\s*(?:array\s*\(|\[)([^\])]*)/i', $code, $list) !== 1) {
            return [];
        }
        preg_match_all('/([\'"])([A-Za-z0-9]+_[A-Za-z0-9]+)\1\s*=>\s*(?:0|false)\b/i', $list[2], $matches);

        return array_fill_keys($matches[2], true);
    }

    /**
     * @param ?array<string, string> $asInstalled
     * @param-out array<string, string> $asInstalled module root => the same directory as Composer installed it, before symlinks
     * @return array<string, string> module root => module name
     */
    private function allModuleRoots(string $projectRoot, ?array &$asInstalled = null): array
    {
        $roots = [];
        $asInstalled = [];
        foreach (glob($projectRoot . '/app/code/*/*/etc/module.xml') ?: [] as $moduleXml) {
            $roots[dirname($moduleXml, 2)] = $moduleXml;
            $asInstalled[dirname($moduleXml, 2)] = dirname($moduleXml, 2);
        }

        $installed = $projectRoot . '/vendor/composer/installed.json';
        $data = is_file($installed) ? json_decode((string) file_get_contents($installed), true) : null;
        // Composer 2 wraps the list in "packages"; Composer 1 stored the list itself.
        $packages = is_array($data) ? ($data['packages'] ?? $data) : [];
        foreach (is_array($packages) ? $packages : [] as $package) {
            if (!is_array($package) || !is_string($package['install-path'] ?? null)) {
                continue;
            }
            $path = self::normalize($projectRoot . '/vendor/composer/' . $package['install-path']);
            $root = realpath($path);
            if ($root === false) {
                continue;
            }
            foreach (['', '/src'] as $suffix) {
                if (is_file($root . $suffix . '/etc/module.xml')) {
                    $roots[$root . $suffix] = $root . $suffix . '/etc/module.xml';
                    $asInstalled[$root . $suffix] = $path . $suffix;
                }
            }
        }

        $modules = [];
        foreach ($roots as $root => $moduleXml) {
            $content = (string) @file_get_contents($moduleXml);
            $modules[$root] = preg_match('#<module\s[^>]*\bname\s*=\s*"([^"]+)"#', $content, $m) === 1 ? $m[1] : '';
        }
        ksort($modules);

        return $modules;
    }

    /**
     * Resolves "." and ".." in an absolute path without following symlinks.
     */
    private static function normalize(string $path): string
    {
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '..') {
                array_pop($parts);
            } elseif ($part !== '.' && $part !== '') {
                $parts[] = $part;
            }
        }

        return '/' . implode('/', $parts);
    }

    /**
     * @param array<string, string> $modules
     * @return list<array{path: string, module: string, area: string, name: string}>
     */
    private function indexedFiles(string $projectRoot, array $modules): array
    {
        $files = [];
        foreach ($modules as $root => $module) {
            foreach (self::FILES as $pattern) {
                foreach (glob($root . '/etc/' . $pattern) ?: [] as $path) {
                    $relative = substr($path, strlen($root) + 5);
                    $area = str_contains($relative, '/') ? dirname($relative) : 'global';
                    $files[] = ['path' => $path, 'module' => $module, 'area' => $area, 'name' => basename($path)];
                }
            }
        }
        if (is_file($projectRoot . '/app/etc/di.xml')) {
            $files[] = ['path' => $projectRoot . '/app/etc/di.xml', 'module' => '', 'area' => 'global', 'name' => 'di.xml'];
        }

        return $files;
    }

    /**
     * Changes whenever an XML file behind the index changes. PHP rules read the index, so
     * PHPStan results cached for another fingerprint are out of date.
     */
    public function fingerprint(string $projectRoot): string
    {
        $modules = $this->moduleRoots($projectRoot);

        return $this->fingerprintOf($modules, $this->indexedFiles($projectRoot, $modules), [...LayoutHandles::layoutFiles($projectRoot, $modules), ...self::routeFiles($modules)]);
    }

    /**
     * @param array<string, string> $modules module root => module name
     * @param list<array{path: string, module: string, area: string, name: string}> $files
     * @param list<string> $layouts frontend layout files and the storefront routes that tell which handles are pages
     */
    private function fingerprintOf(array $modules, array $files, array $layouts): string
    {
        // The module list counts too: a module added, enabled or disabled changes the index without changing a file.
        $fingerprint = self::INDEX_VERSION . "\n" . implode("\n", array_keys($modules)) . "\n";
        foreach ([...array_column($files, 'path'), ...$layouts] as $path) {
            $fingerprint .= $path . '|' . (int) @filesize($path) . '|' . (int) @filemtime($path) . "\n";
        }

        return md5($fingerprint);
    }

    /**
     * @param array<string, string> $modules
     * @return list<string>
     */
    private static function routeFiles(array $modules): array
    {
        return array_values(array_filter(array_map(static fn (string $root): string => $root . '/etc/frontend/routes.xml', array_keys($modules)), 'is_file'));
    }

    private function cacheFile(string $projectRoot, string $fingerprint): string
    {
        $dir = $this->cacheDir !== null ? $this->cacheDir . '/' . md5($projectRoot) : WorkDirectory::forProject($projectRoot);

        return $dir . '/index-' . $fingerprint . '.json';
    }

    private function writeCache(string $cacheFile, ProjectIndex $index): void
    {
        $dir = dirname($cacheFile);
        if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '/index-*.json') ?: [] as $stale) {
            @unlink($stale);
        }
        $temp = $cacheFile . '.' . getmypid();
        if (@file_put_contents($temp, json_encode($index->toArray(), JSON_THROW_ON_ERROR)) !== false) {
            @rename($temp, $cacheFile);
        }
    }

    /**
     * Where frontend layouts place each block class and object argument (view models), and which of those handles
     * no cacheable page uses: account pages that include customer_account, handles whose files hold a block with
     * cacheable="false" that no ifconfig switches and no file of the handle removes, with the elements around it,
     * and handles only such handles include. Handles X-LAY-03 reports do not count that way: their cacheable="false"
     * block is an error to fix. A route's own handle counts as a page, whatever includes it (LayoutHandles).
     * X-LAY-01's allowlist does not count either: it names pages where being uncacheable is acceptable, not pages
     * that are never cached.
     *
     * @param array<string, string> $modules
     * @param list<string> $layouts
     * @return array{array<string, list<string>>, array<string, true>}
     */
    private function layoutPlacements(string $projectRoot, array $modules, array $layouts): array
    {
        $placements = [];
        $names = [];
        $nonCacheable = [];
        $removed = [];
        foreach ($layouts as $file) {
            $xpath = $this->load($file);
            if ($xpath === null) {
                continue;
            }
            $handle = basename($file, '.xml');
            $classes = array_map(static fn (DOMElement $block): string => $block->getAttribute('class'), $this->elements($xpath, '//block[@class] | //referenceBlock[@class]'));
            foreach ($this->elements($xpath, '//argument | //item') as $argument) {
                if ($this->xsiType($argument) === 'object' && trim($argument->textContent) !== '') {
                    $classes[] = $argument->textContent;
                }
            }
            foreach ($classes as $class) {
                $key = ProjectIndex::normalizeClass($class);
                $placements[$key][$handle] = true;
                $names[$key] ??= ltrim(trim($class), '\\');
            }
            // Magento reads remove as a boolean (Layout\Reader\Block), so "1" removes too.
            $removing = "[translate(@remove, 'TRUE', 'true') = 'true' or @remove = '1']";
            foreach ($this->elements($xpath, '//referenceBlock' . $removing . ' | //referenceContainer' . $removing . ' | //remove[@name]') as $remove) {
                $removed[$handle][$remove->getAttribute('name')] = true;
            }
            // On a handle X-LAY-03 reports, the block is an error to fix, not a sign of a personal page.
            if (NonCacheableBlockRule::isShopWide($handle) && !NonCacheableBlockRule::isDefaultAllowlisted($handle)) {
                continue;
            }
            foreach ($this->elements($xpath, "//block[@cacheable='false']") as $block) {
                // The block, or an element around it, that has an ifconfig or that a file removes may not be on the page.
                $chain = [];
                for ($node = $block; $node instanceof DOMElement; $node = $node->parentNode) {
                    $chain[] = $node;
                }
                $nonCacheable[$handle][] = $chain;
            }
        }
        $private = [];
        foreach ($nonCacheable as $handle => $blocks) {
            foreach ($blocks as $chain) {
                $present = true;
                foreach ($chain as $element) {
                    $present = $present && !$element->hasAttribute('ifconfig')
                        && !isset($removed[$handle][$element->getAttribute('name')]);
                }
                if ($present) {
                    $private[$handle] = true;
                }
            }
        }
        $handles = LayoutHandles::fromProject($projectRoot, $modules);
        $personal = static fn (string $handle): bool => isset($private[$handle])
            || isset($private[str_starts_with($handle, 'hyva_') ? substr($handle, 5) : $handle]);
        $uncacheable = [];
        foreach (array_unique(array_merge(...array_map('array_keys', array_values($placements)))) as $handle) {
            if ($handles->isPersonal((string) $handle, $personal)) {
                $uncacheable[(string) $handle] = true;
            }
        }

        $byName = [];
        foreach ($placements as $key => $in) {
            $byName[$names[$key]] = array_map('strval', array_keys($in));
        }

        return [$byName, $uncacheable];
    }

    /**
     * @param list<array{path: string, module: string, area: string, name: string}> $files
     * @param array{array<string, list<string>>, array<string, true>} $layout
     */
    private function index(int $moduleCount, array $files, array $layout = [[], []]): ProjectIndex
    {
        $proxies = [];
        $plugins = [];
        $observers = [];
        $views = [];
        $tables = [];
        $virtualTypes = [];
        $virtualProxies = [];
        $schemas = [];
        $pluginsByName = [];
        $disabledPlugins = [];
        $commands = [];
        $cronJobs = [];
        $webapiMethods = [];
        $preferences = [];

        foreach ($files as $file) {
            if ($file['name'] === 'schema.graphqls') {
                $schemas[] = $file['path'];
                continue;
            }
            $xpath = $this->load($file['path']);
            if ($xpath === null) {
                continue;
            }
            switch ($file['name']) {
                case 'di.xml':
                    if (in_array($file['area'], self::WEBAPI_AREAS, true)) {
                        foreach ($this->elements($xpath, '/config/preference') as $preference) {
                            if ($preference->getAttribute('for') !== '' && $preference->getAttribute('type') !== '') {
                                $preferences[ProjectIndex::normalizeClass($preference->getAttribute('for'))][ProjectIndex::normalizeClass($preference->getAttribute('type'))] = true;
                            }
                        }
                    }
                    foreach ($this->elements($xpath, '/config/type | /config/virtualType') as $type) {
                        $name = ProjectIndex::normalizeClass($type->getAttribute('name'));
                        if ($type->localName === 'virtualType' && $type->getAttribute('type') !== '') {
                            $virtualTypes[$name] = ProjectIndex::normalizeClass($type->getAttribute('type'));
                        }
                        foreach ($this->elements($xpath, 'arguments/argument', $type) as $argument) {
                            if ($this->xsiType($argument) === 'object' && str_ends_with(trim($argument->textContent), '\\Proxy')) {
                                $proxies[$name][$argument->getAttribute('name')] = true;
                                if ($type->localName === 'virtualType') {
                                    $virtualProxies[$name][] = $argument->getAttribute('name');
                                }
                            }
                        }
                        foreach ($this->elements($xpath, 'plugin', $type) as $plugin) {
                            if ($plugin->getAttribute('type') !== '') {
                                $class = ProjectIndex::normalizeClass($plugin->getAttribute('type'));
                                $plugins[$class] = true;
                                if ($file['area'] !== 'adminhtml') {
                                    $pluginsByName[$name][$plugin->getAttribute('name')] = $class;
                                }
                            }
                            if ($plugin->getAttribute('disabled') === 'true') {
                                $disabledPlugins[$name][$plugin->getAttribute('name')] = true;
                            }
                        }
                        if ($file['area'] === 'global' && in_array($name, self::COMMAND_LISTS, true)) {
                            foreach ($this->elements($xpath, 'arguments/argument[@name="commands"]/item', $type) as $item) {
                                if ($this->xsiType($item) === 'object') {
                                    $commands[ProjectIndex::normalizeClass($item->textContent)] = true;
                                }
                            }
                        }
                    }
                    break;
                case 'webapi.xml':
                    foreach ($this->elements($xpath, '/routes/route/service') as $service) {
                        if ($service->getAttribute('class') !== '' && $service->getAttribute('method') !== '') {
                            $webapiMethods[ProjectIndex::normalizeClass($service->getAttribute('class'))][$service->getAttribute('method')] = true;
                        }
                    }
                    break;
                case 'crontab.xml':
                    foreach ($this->elements($xpath, '/config/group/job') as $job) {
                        if ($job->getAttribute('instance') !== '') {
                            $cronJobs[ProjectIndex::normalizeClass($job->getAttribute('instance'))] = true;
                        }
                    }
                    break;
                case 'events.xml':
                    foreach ($this->elements($xpath, '/config/event/observer') as $observer) {
                        $event = $observer->parentNode instanceof DOMElement ? $observer->parentNode->getAttribute('name') : '';
                        if ($event !== '' && $observer->getAttribute('instance') !== '') {
                            $observers[$event][] = [
                                'class' => ltrim($observer->getAttribute('instance'), '\\'),
                                'area' => $file['area'],
                                'module' => $file['module'],
                            ];
                        }
                    }
                    break;
                case 'mview.xml':
                    foreach ($this->elements($xpath, '/config/view') as $view) {
                        $views[$view->getAttribute('id')] = true;
                    }
                    break;
                case 'db_schema.xml':
                    foreach ($this->elements($xpath, '/schema/table') as $table) {
                        foreach ($this->elements($xpath, 'constraint', $table) as $constraint) {
                            if ($this->xsiType($constraint) === 'primary') {
                                $tables[$table->getAttribute('name')] = true;
                            }
                        }
                    }
                    break;
            }
        }

        // A Proxy configured on a virtual type covers the class it is based on.
        foreach ($virtualProxies as $virtualType => $arguments) {
            $class = self::baseOfVirtualType($virtualType, $virtualTypes);
            foreach ($arguments as $argument) {
                $proxies[$class][$argument] = true;
            }
        }

        // Magento merges plugins by target and name, so another module can disable one by name alone.
        $pluginTargets = [];
        foreach ($pluginsByName as $target => $byName) {
            foreach (array_diff_key($byName, $disabledPlugins[$target] ?? []) as $class) {
                $pluginTargets[$class][] = $target;
            }
        }

        $listFields = ListFieldResolvers::fromSchemas($schemas);
        foreach ($listFields as $class => $field) {
            $listFields[self::baseOfVirtualType($class, $virtualTypes)] ??= $field;
        }

        foreach ([&$commands, &$cronJobs] as &$classes) {
            foreach (array_keys($classes) as $class) {
                $classes[self::baseOfVirtualType($class, $virtualTypes)] = true;
            }
        }
        unset($classes);

        return new ProjectIndex(
            $moduleCount,
            $proxies,
            $plugins,
            $observers,
            $views,
            $tables,
            $listFields,
            $pluginTargets,
            $commands,
            $cronJobs,
            array_map(static fn (array $methods): array => array_keys($methods), $webapiMethods),
            array_map(static fn (array $classes): array => array_keys($classes), $preferences),
            $layout[0],
            $layout[1],
        );
    }

    /**
     * @param array<string, string> $virtualTypes
     */
    private static function baseOfVirtualType(string $class, array $virtualTypes): string
    {
        $seen = [];
        while (isset($virtualTypes[$class]) && !isset($seen[$class])) {
            $seen[$class] = true;
            $class = $virtualTypes[$class];
        }

        return $class;
    }

    private function load(string $path): ?DOMXPath
    {
        $content = @file_get_contents($path);
        if ($content === false || $content === '') {
            return null;
        }
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $loaded = $document->loadXML($content, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $loaded ? new DOMXPath($document) : null;
    }

    /**
     * @return list<DOMElement>
     */
    private function elements(DOMXPath $xpath, string $query, ?DOMElement $context = null): array
    {
        $nodes = $xpath->query($query, $context);
        $elements = [];
        foreach ($nodes === false ? [] : $nodes as $node) {
            if ($node instanceof DOMElement) {
                $elements[] = $node;
            }
        }

        return $elements;
    }

    private function xsiType(DOMElement $element): string
    {
        $type = $element->getAttributeNS(self::XSI, 'type');

        return $type !== '' ? $type : $element->getAttribute('xsi:type');
    }
}
