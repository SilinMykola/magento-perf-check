<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Project;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Builds the ProjectIndex from app/code and every Composer package that is a Magento
 * module, regardless of which paths are analyzed. Reads nothing but etc/ files and
 * writes nothing into the project; the result is cached in the system temp directory.
 */
final class ProjectIndexBuilder
{
    private const XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    /** Indexed files, relative to a module's etc/ directory; * is one area directory. */
    /** Part of the cache key: raise it when the same files start to give a different index. */
    private const INDEX_VERSION = 4;

    private const FILES = ['di.xml', '*/di.xml', 'events.xml', '*/events.xml', 'mview.xml', 'db_schema.xml', 'schema.graphqls', 'crontab.xml'];

    private const COMMAND_LISTS = ['magento\\framework\\console\\commandlist', 'magento\\framework\\console\\commandlistinterface'];

    public function __construct(private readonly ?string $cacheDir = null)
    {
    }

    public function build(string $projectRoot): ProjectIndex
    {
        $modules = $this->moduleRoots($projectRoot);
        $files = $this->indexedFiles($projectRoot, $modules);

        $cacheFile = $this->cacheFile($projectRoot, $this->fingerprintOf($files));
        if (is_file($cacheFile)) {
            $data = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($data)) {
                /** @var array<string, mixed> $data */
                return ProjectIndex::fromArray($data);
            }
        }

        $index = $this->index(count($modules), $files);
        $this->writeCache($cacheFile, $index);

        return $index;
    }

    /**
     * Module directories: app/code/<Vendor>/<Module> and Composer packages with etc/module.xml.
     *
     * @return array<string, string> module root => module name
     */
    private function moduleRoots(string $projectRoot): array
    {
        $roots = [];
        foreach (glob($projectRoot . '/app/code/*/*/etc/module.xml') ?: [] as $moduleXml) {
            $roots[dirname($moduleXml, 2)] = $moduleXml;
        }

        $installed = $projectRoot . '/vendor/composer/installed.json';
        $data = is_file($installed) ? json_decode((string) file_get_contents($installed), true) : null;
        // Composer 2 wraps the list in "packages"; Composer 1 stored the list itself.
        $packages = is_array($data) ? ($data['packages'] ?? $data) : [];
        foreach (is_array($packages) ? $packages : [] as $package) {
            if (!is_array($package) || !is_string($package['install-path'] ?? null)) {
                continue;
            }
            $root = realpath($projectRoot . '/vendor/composer/' . $package['install-path']);
            if ($root === false) {
                continue;
            }
            foreach ([$root, $root . '/src'] as $candidate) {
                if (is_file($candidate . '/etc/module.xml')) {
                    $roots[$candidate] = $candidate . '/etc/module.xml';
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
        return $this->fingerprintOf($this->indexedFiles($projectRoot, $this->moduleRoots($projectRoot)));
    }

    /**
     * @param list<array{path: string, module: string, area: string, name: string}> $files
     */
    private function fingerprintOf(array $files): string
    {
        $fingerprint = self::INDEX_VERSION . "\n";
        foreach ($files as $file) {
            $fingerprint .= $file['path'] . '|' . (int) @filesize($file['path']) . '|' . (int) @filemtime($file['path']) . "\n";
        }

        return md5($fingerprint);
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
     * @param list<array{path: string, module: string, area: string, name: string}> $files
     */
    private function index(int $moduleCount, array $files): ProjectIndex
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

        return new ProjectIndex($moduleCount, $proxies, $plugins, $observers, $views, $tables, $listFields, $pluginTargets, $commands, $cronJobs);
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
