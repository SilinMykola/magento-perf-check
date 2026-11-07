<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use FilesystemIterator;
use Silin\MagentoPerfCheck\Project\TestCode;
use SplFileInfo;

/**
 * Finds the Magento XML files that XML rules look at and works out their area,
 * module and layout handle from the path.
 */
final class FileDiscovery
{
    /** Core packages are analyzed only when a path points inside them explicitly. */
    private const CORE_VENDOR_DIRS = ['vendor/magento', 'vendor/mage-os'];

    private const MODULE_NAME = '[A-Z][A-Za-z0-9]*_[A-Z][A-Za-z0-9]*';

    /** @var array<string, ?string> */
    private array $moduleByDir = [];

    /** @var array<string, ?string> */
    private array $themeAreaByDir = [];

    public function __construct(
        private readonly string $projectRoot,
        private readonly bool $includeTests = false,
    ) {
    }

    /**
     * @param list<string> $paths paths relative to the project root
     * @return list<XmlFile>
     */
    public function discover(array $paths): array
    {
        $files = [];
        foreach ($paths as $path) {
            $path = trim($path, '/');
            $skipCore = !$this->isInsideCoreVendor($path);
            $skipTests = !$this->includeTests && !TestCode::contains($path);
            $absolute = $path === '.' ? $this->projectRoot : $this->projectRoot . '/' . $path;
            foreach ($this->xmlFilesUnder($absolute) as $file) {
                $relative = substr($file, strlen($this->projectRoot) + 1);
                if ($skipCore && $this->isInsideCoreVendor($relative)) {
                    continue;
                }
                if ($skipTests && TestCode::contains($relative)) {
                    continue;
                }
                $xmlFile = $this->describe($file, $relative);
                if ($xmlFile !== null) {
                    $files[$relative] = $xmlFile;
                }
            }
        }
        ksort($files);

        return array_values($files);
    }

    private function isInsideCoreVendor(string $relativePath): bool
    {
        foreach (self::CORE_VENDOR_DIRS as $dir) {
            if ($relativePath === $dir || str_starts_with($relativePath, $dir . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return iterable<string>
     */
    private function xmlFilesUnder(string $absolute): iterable
    {
        if (is_file($absolute)) {
            if (str_ends_with($absolute, '.xml') || str_ends_with($absolute, '.graphqls')) {
                yield $absolute;
            }

            return;
        }
        if (!is_dir($absolute)) {
            return;
        }
        yield from $this->xmlFilesIn($absolute, [(string) realpath($absolute)]);
    }

    /**
     * Follows symlinks, because modules are often symlinked into app/code, but not a link
     * that leads back up the path taken: that would be a loop, or a way around the core skip.
     *
     * @param list<string> $ancestors real paths of the directories on the way here
     * @return iterable<string>
     */
    private function xmlFilesIn(string $directory, array $ancestors): iterable
    {
        /** @var SplFileInfo $info */
        foreach (new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS) as $info) {
            if ($info->isFile()) {
                if (in_array($info->getExtension(), ['xml', 'graphqls'], true)) {
                    yield $info->getPathname();
                }
            } elseif ($info->isDir()) {
                $real = (string) $info->getRealPath();
                if (!$info->isLink() || !self::leadsBack($real, $ancestors)) {
                    yield from $this->xmlFilesIn($info->getPathname(), [...$ancestors, $real]);
                }
            }
        }
    }

    /**
     * @param list<string> $ancestors
     */
    private static function leadsBack(string $target, array $ancestors): bool
    {
        foreach ($ancestors as $ancestor) {
            if ($ancestor === $target || str_starts_with($ancestor, $target . '/')) {
                return true;
            }
        }

        return false;
    }

    private function describe(string $absolute, string $relative): ?XmlFile
    {
        $handle = basename($absolute, '.xml');

        // Module layout: <module>/view/<area>/layout/[...]/<handle>.xml
        if (preg_match('#/view/([a-z_]+)/layout/#', $absolute, $m) === 1) {
            return new XmlFile(
                $absolute,
                $relative,
                XmlFile::KIND_LAYOUT,
                $m[1],
                $this->moduleFromModuleXml(dirname($absolute)),
                $handle,
            );
        }

        // Theme layout: <theme>/<Module_Name>/layout/[...]/<handle>.xml
        if (preg_match('#^(.*)/(' . self::MODULE_NAME . ')/layout/#', $absolute, $m) === 1) {
            $area = $this->themeArea($m[1], $relative);
            if ($area === null) {
                return null;
            }

            return new XmlFile($absolute, $relative, XmlFile::KIND_LAYOUT, $area, $m[2], $handle);
        }

        // GraphQL schema: <module>/etc/<file>.graphqls
        if (preg_match('#/etc/[^/]+\.graphqls$#', $absolute) === 1) {
            $module = $this->moduleFromModuleXml(dirname($absolute, 2));

            return $module === null
                ? null
                : new XmlFile($absolute, $relative, XmlFile::KIND_GRAPHQL, 'graphql', $module, null);
        }
        if (str_ends_with($absolute, '.graphqls')) {
            return null;
        }

        // Module config: <module>/etc/<file>.xml or <module>/etc/<area>/<file>.xml
        if (preg_match('#/etc/(?:([a-z_]+)/)?[^/]+\.xml$#', $absolute, $m) === 1) {
            $area = $m[1] ?? '';
            $etcDir = $area !== '' ? dirname($absolute, 2) : dirname($absolute);
            $module = $this->moduleFromModuleXml(dirname($etcDir));
            if ($module === null) {
                return null;
            }

            return new XmlFile(
                $absolute,
                $relative,
                XmlFile::KIND_CONFIG,
                $area !== '' ? $area : 'global',
                $module,
                null,
            );
        }

        return null;
    }

    /**
     * Walks up from $dir to the nearest etc/module.xml and returns its module name.
     */
    private function moduleFromModuleXml(string $dir): ?string
    {
        $visited = [];
        $module = null;
        while (str_starts_with($dir, $this->projectRoot) && strlen($dir) >= strlen($this->projectRoot)) {
            if (array_key_exists($dir, $this->moduleByDir)) {
                $module = $this->moduleByDir[$dir];
                break;
            }
            $visited[] = $dir;
            $moduleXml = $dir . '/etc/module.xml';
            if (is_file($moduleXml)) {
                $module = $this->readModuleName($moduleXml);
                break;
            }
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }
        foreach ($visited as $visitedDir) {
            $this->moduleByDir[$visitedDir] = $module;
        }

        return $module;
    }

    private function readModuleName(string $moduleXml): ?string
    {
        $content = @file_get_contents($moduleXml);
        if ($content === false) {
            return null;
        }
        if (preg_match('#<module\s[^>]*\bname\s*=\s*"(' . self::MODULE_NAME . ')"#', $content, $m) === 1) {
            return $m[1];
        }

        return null;
    }

    /**
     * Area of a theme: from the app/design/<area>/ path, otherwise from the
     * theme's registration.php ('frontend/Vendor/theme').
     */
    private function themeArea(string $themeDir, string $relative): ?string
    {
        if (preg_match('#^app/design/([a-z_]+)/#', $relative, $m) === 1) {
            return $m[1];
        }
        if (array_key_exists($themeDir, $this->themeAreaByDir)) {
            return $this->themeAreaByDir[$themeDir];
        }
        $area = null;
        $registration = @file_get_contents($themeDir . '/registration.php');
        if ($registration !== false
            && preg_match("#ComponentRegistrar::THEME\s*,\s*['\"]([a-z_]+)/#", $registration, $m) === 1
        ) {
            $area = $m[1];
        }

        return $this->themeAreaByDir[$themeDir] = $area;
    }
}
