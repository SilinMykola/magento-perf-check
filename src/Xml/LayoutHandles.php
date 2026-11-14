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

    /** Router\ActionList::$reservedWords of Magento 2.4.9: an action with such a name lives in a class "<Word>Action". */
    private const RESERVED_WORDS = [
        'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const',
        'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare',
        'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
        'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'instanceof',
        'insteadof', 'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print', 'private', 'protected',
        'public', 'require', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'void',
        'while', 'xor', 'yield',
    ];

    /**
     * @param array<string, array<string, true>> $includedBy handle => handles whose layout files include it
     * @param array<string, true> $accountPages handles with a layout file that includes customer_account
     * @param array<string, true> $pages handles of storefront controller actions: route id, controller path, action
     * @param array<string, bool> $routes storefront route ids => true when their controllers were not found, so each of
     *     their handles counts as a page
     */
    public function __construct(
        private readonly array $includedBy = [],
        private readonly array $accountPages = [],
        private readonly array $pages = [],
        private readonly array $routes = [],
    ) {
    }

    /**
     * Reads the frontend and base layouts, the storefront routes and the Controller classes of the given modules, and the layouts of
     * the frontend themes under app/design/frontend and installed by Composer. As Magento does, a theme's
     * folder is read only for a module that is enabled (Module\Manager::isOutputEnabled()).
     *
     * @param array<string, string> $modules absolute directory => name of every enabled module
     */
    public static function fromProject(string $projectRoot, array $modules): self
    {
        // A page's handle is the full action name: route id, controller path and action, in lower case, as
        // App\Request\Http::getFullActionName() builds it. An action class named PrintAction serves "print".
        $actions = [];
        foreach ($modules as $root => $name) {
            $actions[$name] = self::controllerActions($root);
        }
        $pages = [];
        $opaque = [];
        foreach (array_keys($modules) as $root) {
            foreach (self::routes($root . '/etc/frontend/routes.xml') as $id => $names) {
                // A route without modules serves nothing.
                if ($names === []) {
                    continue;
                }
                $found = false;
                foreach ($names as $module) {
                    foreach ($actions[$module] ?? [] as $action) {
                        $pages[$id . '_' . $action] = true;
                        $found = true;
                    }
                }
                $opaque[$id] = ($opaque[$id] ?? true) && !$found;
            }
        }

        $includedBy = [];
        $accountPages = [];
        foreach (self::layoutFiles($projectRoot, $modules) as $file) {
            $handle = basename($file, '.xml');
            foreach (self::directUpdates($file) as $included) {
                if ($included === self::ACCOUNT_HANDLE) {
                    $accountPages[$handle] = true;
                }
                $includedBy[$included][$handle] = true;
            }
        }

        return new self($includedBy, $accountPages, $pages, $opaque);
    }

    /**
     * The PHP files under a module's Controller directory, absolute; a directory that cannot be read is skipped.
     *
     * @return list<string>
     */
    public static function controllerFiles(string $root): array
    {
        $base = $root . '/Controller';
        if (!is_dir($base)) {
            return [];
        }
        $files = [];
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::LEAVES_ONLY, \RecursiveIteratorIterator::CATCH_GET_CHILD);
            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && str_ends_with($file->getPathname(), '.php')) {
                    $files[] = $file->getPathname();
                }
            }
        } catch (\UnexpectedValueException) {
            return $files;
        }
        sort($files);

        return $files;
    }

    /**
     * The storefront actions of a module, as "controller_path_action" in lower case, as Router\ActionList finds
     * them: no controller path with "adminhtml" in it, and an action class named after a PHP reserved word plus
     * "Action" (PrintAction) serves that word.
     *
     * @return list<string>
     */
    private static function controllerActions(string $root): array
    {
        $actions = [];
        foreach (self::controllerFiles($root) as $file) {
            $relative = strtolower(substr($file, strlen($root . '/Controller/'), -strlen('.php')));
            if (!str_contains($relative, '/') || str_contains(dirname($relative), 'adminhtml')) {
                continue;
            }
            // Router\ActionList serves only a class it can create: no abstract class, interface or trait.
            if (preg_match('/^\s*(?:#\[.*\]\s*)*(?:final\s+|readonly\s+)*class\s/mi', (string) @file_get_contents($file)) !== 1) {
                continue;
            }
            $action = str_replace('/', '_', $relative);
            $actions[] = $action;
            $last = substr((string) strrchr('/' . $relative, '/'), 1);
            if (str_ends_with($last, 'action') && in_array(substr($last, 0, -strlen('action')), self::RESERVED_WORDS, true)) {
                $actions[] = substr($action, 0, -strlen('action'));
            }
        }

        return $actions;
    }

    /**
     * The frontend and base layout files of the given modules and of the frontend themes, as fromProject() reads them.
     *
     * @param array<string, string> $modules absolute directory => name of every enabled module
     * @return list<string>
     */
    public static function layoutFiles(string $projectRoot, array $modules): array
    {
        $files = [];
        $enabled = array_flip($modules);
        foreach (array_keys($modules) as $root) {
            foreach (['frontend', 'base'] as $area) {
                array_push($files, ...self::xmlFilesUnder($root . '/view/' . $area . '/layout'));
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

        return array_values(array_unique($files));
    }

    /**
     * Whether no cacheable page can use the handle. A page, the handle of a controller action, decides for itself:
     * $personalPage names it, or it includes customer_account; its hyva_ copy also needs every layout that includes
     * the copy to be personal. A handle that extends a page's name, such as catalog_product_view_type_simple, is
     * added by the controller: personal when it or its page is and every layout that includes it is personal. Every
     * handle of a route whose modules have no controllers counts as a page. Another handle is personal when only
     * personal handles include it; one that no layout includes is added by code, and $personalPage decides for it
     * as for a page.
     * $personalHandle names handles that are personal wherever they are used, such as one with a cacheable="false"
     * block. Without $followIncludes, only $personalPage and customer_account count.
     *
     * @param callable(string): bool $personalPage
     * @param ?callable(string): bool $personalHandle
     */
    public function isPersonal(string $handle, callable $personalPage, bool $followIncludes = true, ?callable $personalHandle = null): bool
    {
        if (!$followIncludes) {
            return $personalPage($handle) || isset($this->accountPages[$handle]) || isset($this->accountPages[self::withoutHyvaPrefix($handle)]);
        }

        return $this->state($handle, $personalPage, $personalHandle ?? static fn (string $handle): bool => false, []) === true;
    }

    /**
     * true: personal; false: used by a page that may be cached; null: included only along the current path,
     * so it adds no page of its own.
     *
     * @param callable(string): bool $personalPage
     * @param callable(string): bool $personalHandle
     * @param array<string, true> $visiting handles on the current include path
     */
    private function state(string $handle, callable $personalPage, callable $personalHandle, array $visiting): ?bool
    {
        $page = self::withoutHyvaPrefix($handle);
        if (isset($this->accountPages[$handle]) || $personalHandle($handle) || $personalHandle($page)) {
            return true;
        }
        // A route whose id starts with hyva_, such as Hyvä Admin's, has handles of its own: they are no hyva_ copies.
        if ($this->routeOf($handle) !== null && str_starts_with((string) $this->routeOf($handle), 'hyva_')) {
            $page = $handle;
        }
        $owner = $this->pageOf($page);
        if ($owner !== null && !($personalPage($handle) || $personalPage($owner) || $personalHandle($owner) || isset($this->accountPages[$owner]))) {
            return false;
        }
        $visiting[$handle] = true;
        // A Hyvä theme adds hyva_<handle> next to every handle of the page, so it is used where <handle> is too.
        // The copy of a handle that is no page of its own, or of an addition to a page, is used where that handle is.
        if ($owner !== $page && $page !== $handle && $this->state($page, $personalPage, $personalHandle, $visiting) !== true) {
            return false;
        }
        // A page decides for itself. Its hyva_ copy, and a handle its controller adds that layouts also include, as
        // checkout_cart_configure_type_simple includes catalog_product_view_type_simple, are used also where the
        // layouts that include them are.
        $includers = $owner === $handle ? [] : array_keys($this->includedBy[$handle] ?? []);
        if ($includers === []) {
            return $owner !== null || $personalPage($handle) || $page !== $handle;
        }
        $counted = 0;
        foreach ($includers as $includer) {
            $includer = (string) $includer;
            if (isset($visiting[$includer])) {
                continue;
            }
            $state = $this->state($includer, $personalPage, $personalHandle, $visiting);
            if ($state === false) {
                return false;
            }
            if ($state === true) {
                ++$counted;
            }
        }

        return $counted > 0 || $owner !== null || $page !== $handle ? true : null;
    }

    /**
     * The page whose handle this is: a controller action's own handle, or one that the controller adds to its
     * page and that extends it, as catalog_product_view_type_simple extends catalog_product_view. A handle of a
     * route whose controllers were not found counts as a page. Null for a handle that is no page.
     */
    private function pageOf(string $handle): ?string
    {
        if (isset($this->pages[$handle])) {
            return $handle;
        }
        $parts = explode('_', $handle);
        for ($length = count($parts) - 1; $length >= 2; --$length) {
            $prefix = implode('_', array_slice($parts, 0, $length));
            if (isset($this->pages[$prefix])) {
                return $prefix;
            }
        }
        $route = $this->routeOf($handle);

        return $route !== null && $this->routes[$route] ? $handle : null;
    }

    /**
     * The longest storefront route id the handle starts with, or null.
     */
    private function routeOf(string $handle): ?string
    {
        $found = null;
        foreach ($this->routes as $route => $opaque) {
            $route = (string) $route;
            if (str_starts_with($handle, $route . '_') && strlen($route) > strlen((string) $found)) {
                $found = $route;
            }
        }

        return $found;
    }

    /**
     * The routes of a routes.xml file: route id => names of its modules.
     *
     * @return array<string, list<string>>
     */
    private static function routes(string $file): array
    {
        $document = new DOMDocument();
        if (!is_file($file) || !@$document->load($file, LIBXML_NONET)) {
            return [];
        }
        $routes = [];
        foreach ($document->getElementsByTagName('route') as $route) {
            $id = $route->getAttribute('id');
            if ($id === '') {
                continue;
            }
            $routes[$id] ??= [];
            foreach ($route->getElementsByTagName('module') as $module) {
                if ($module->getAttribute('name') !== '') {
                    $routes[$id][] = $module->getAttribute('name');
                }
            }
        }

        return $routes;
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
