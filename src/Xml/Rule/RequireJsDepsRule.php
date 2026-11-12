<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\Script\ScriptSource;
use Silin\MagentoPerfCheck\Xml\ScriptRule;
use Silin\MagentoPerfCheck\Xml\XmlFile;

/**
 * J-DEP-02: a module in the top-level "deps" of a storefront requirejs-config.js, which loads it on every page.
 */
final class RequireJsDepsRule implements ScriptRule
{
    public const ID = 'J-DEP-02';

    /**
     * The files Magento_RequireJs merges (etc/di.xml, collectors Base, ThemeModular and Theme): a module's
     * view/<area>/requirejs-config.js, and a frontend theme's at its root or in a <Vendor_Module> folder.
     */
    private const MERGED = '#^/(?!app/design/).*/view/(?:frontend|base)/requirejs-config\\.js$|^/app/design/frontend/[^/]+/[^/]+/(?:'
        . FileDiscovery::MODULE_NAME . '/)?requirejs-config\\.js$#';

    public function ruleId(): string
    {
        return self::ID;
    }

    public function check(XmlFile $file, ScriptSource $script): array
    {
        // Only the merged files reach every page; a file of that name anywhere else is not merged.
        if (preg_match(self::MERGED, '/' . $file->relativePath) !== 1) {
            return [];
        }
        $findings = [];
        foreach ($script->strings as $string) {
            // "deps" under "shim" only orders the loading of a module that something else asks for.
            if ($string->isKey || $string->path !== ['deps']) {
                continue;
            }
            $findings[] = new Finding(
                self::ID,
                Severity::Info,
                $file->relativePath,
                $string->line,
                sprintf(
                    '[%s] "%s" is in the top-level deps of requirejs-config.js, so RequireJS loads it on every storefront page. '
                    . 'Unless every page needs it, start it with x-magento-init or data-mage-init in the templates that do.',
                    self::ID,
                    $string->value,
                ),
            );
        }

        return $findings;
    }
}
