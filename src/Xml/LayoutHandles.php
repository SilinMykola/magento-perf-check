<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use DOMDocument;
use DOMElement;

/**
 * The storefront layout handles of a project and how they include each other. Magento merges every layout
 * file of a page's handles, and a handle named in <update handle="..."/> recursively, but only the
 * <update> elements that are direct children of a file's root (Layout\Merge::_fetchRecursiveUpdates()).
 */
final class LayoutHandles
{
    private const ACCOUNT_HANDLE = 'customer_account';

    /**
     * @param array<string, array<string, true>> $includedBy handle => handles whose layout files include it
     * @param array<string, true> $accountPages handles with a layout file that includes customer_account
     * @param array<string, true> $routes ids of the storefront routes, whose handles are pages
     */
    public function __construct(
        private readonly array $includedBy = [],
        private readonly array $accountPages = [],
        private readonly array $routes = [],
    ) {
    }

    /**
     * Reads the frontend and base layouts and the storefront routes of the given modules, and the layouts of
     * the frontend themes under app/design/frontend and installed by Composer. As Magento does, a theme's
     * folder is read only for a module that is enabled (Module\Manager::isOutputEnabled()).
     *
     * @param array<string, string> $modules absolute directory => name of every enabled module
     */
    public static function fromProject(string $projectRoot, array $modules): self
    {
        $files = [];
        $routes = [];
        $enabled = array_flip($modules);
        foreach (array_keys($modules) as $root) {
            foreach (['frontend', 'base'] as $area) {
                array_push($files, ...self::xmlFilesUnder($root . '/view/' . $area . '/layout'));
            }
            $routesXml = (string) @file_get_contents($root . '/etc/frontend/routes.xml');
            if (preg_match_all('#<route\s[^>]*\bid\s*=\s*"([^"]+)"#', $routesXml, $m) > 0) {
                $routes += array_fill_keys($m[1], true);
            }
        }
        $themes = [
            ...(glob($projectRoot . '/app/design/frontend/*/*', GLOB_ONLYDIR) ?: []),
            ...array_map('dirname', self::composerFrontendThemes($projectRoot)),
        ];
        foreach ($themes as $theme) {
            foreach (glob($theme . '/*_*/layout', GLOB_ONLYDIR) ?: [] as $layoutDir) {
                $module = basename(dirname($layoutDir));
                if (isset($enabled[$module])) {
                    array_push($files, ...self::xmlFilesUnder($layoutDir));
                }
            }
        }

        $includedBy = [];
        $accountPages = [];
        foreach (array_unique($files) as $file) {
            $handle = basename($file, '.xml');
            foreach (self::directUpdates($file) as $included) {
                if ($included === self::ACCOUNT_HANDLE) {
                    $accountPages[$handle] = true;
                }
                $includedBy[$included][$handle] = true;
            }
        }

        return new self($includedBy, $accountPages, $routes);
    }

    /**
     * Whether no cacheable page can use the handle: it is allowlisted or an account page in any of its files,
     * or, when includes are followed, it is not a route's own handle and only such handles include it.
     *
     * @param callable(string): bool $allowlisted
     */
    public function isPersonal(string $handle, callable $allowlisted, bool $followIncludes = true): bool
    {
        if (!$followIncludes) {
            return $allowlisted($handle) || isset($this->accountPages[$handle]) || isset($this->accountPages[self::withoutHyvaPrefix($handle)]);
        }

        return $this->state($handle, $allowlisted, []) === true;
    }

    /**
     * true: personal; false: used by a page that may be cached; null: included only along the current path,
     * so it adds no page of its own.
     *
     * @param callable(string): bool $allowlisted
     * @param array<string, true> $visiting handles on the current include path
     */
    private function state(string $handle, callable $allowlisted, array $visiting): ?bool
    {
        if ($allowlisted($handle) || isset($this->accountPages[$handle])) {
            return true;
        }
        $page = self::withoutHyvaPrefix($handle);
        $visiting[$handle] = true;
        if ($this->isRouteHandle($page)) {
            // A route's own handle is a page: a controller adds it, whatever else includes it.
            if ($page === $handle || !($allowlisted($page) || isset($this->accountPages[$page]))) {
                return false;
            }
        } elseif ($page !== $handle && $this->state($page, $allowlisted, $visiting) !== true) {
            // A Hyvä theme adds hyva_<handle> next to every handle of the page, so it is used where <handle> is too.
            return false;
        }
        $includers = array_keys($this->includedBy[$handle] ?? []);
        if ($includers === []) {
            return $page !== $handle;
        }
        $counted = 0;
        foreach ($includers as $includer) {
            $includer = (string) $includer;
            if (isset($visiting[$includer])) {
                continue;
            }
            $state = $this->state($includer, $allowlisted, $visiting);
            if ($state === false) {
                return false;
            }
            if ($state === true) {
                ++$counted;
            }
        }

        return $counted > 0 || $page !== $handle ? true : null;
    }

    private function isRouteHandle(string $handle): bool
    {
        foreach (array_keys($this->routes) as $route) {
            if (str_starts_with($handle, $route . '_')) {
                return true;
            }
        }

        return false;
    }

    private static function withoutHyvaPrefix(string $handle): string
    {
        return str_starts_with($handle, 'hyva_') ? substr($handle, strlen('hyva_')) : $handle;
    }

    /**
     * @return list<string>
     */
    private static function directUpdates(string $file): array
    {
        $document = new DOMDocument();
        if (!@$document->load($file, LIBXML_NONET) || $document->documentElement === null) {
            return [];
        }
        $handles = [];
        foreach ($document->documentElement->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'update' && $child->hasAttribute('handle')) {
                $handles[] = $child->getAttribute('handle');
            }
        }

        return $handles;
    }

    /**
     * Layout files under a directory, also in subfolders such as override/, as the tool's other layout readers do.
     *
     * @return list<string>
     */
    private static function xmlFilesUnder(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }
        $files = [];
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'xml') {
                    $files[] = $file->getPathname();
                }
            }
        } catch (\UnexpectedValueException) {
            // A folder that cannot be read adds nothing; the files found before it still count.
        }
        sort($files);

        return $files;
    }

    /**
     * registration.php files of Composer packages that register a frontend theme.
     *
     * @return list<string>
     */
    private static function composerFrontendThemes(string $projectRoot): array
    {
        $registrations = [];
        foreach ([...(glob($projectRoot . '/vendor/*/*/registration.php') ?: []), ...(glob($projectRoot . '/vendor/*/*/src/registration.php') ?: [])] as $registration) {
            if (preg_match("#ComponentRegistrar::THEME\s*,\s*['\"]frontend/#", (string) @file_get_contents($registration)) === 1) {
                $registrations[] = $registration;
            }
        }

        return $registrations;
    }
}
