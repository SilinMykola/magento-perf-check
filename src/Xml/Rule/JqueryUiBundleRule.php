<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\Script\ScriptSource;
use Silin\MagentoPerfCheck\Xml\ScriptRule;
use Silin\MagentoPerfCheck\Xml\XmlFile;

/**
 * J-DEP-01: storefront JavaScript that depends on "jquery/ui", the whole jQuery UI library.
 */
final class JqueryUiBundleRule implements ScriptRule
{
    public const ID = 'J-DEP-01';

    public function ruleId(): string
    {
        return self::ID;
    }

    public function check(XmlFile $file, ScriptSource $script): array
    {
        $findings = [];
        foreach ($script->strings as $string) {
            // An object key, such as core's map entry 'jquery/ui': 'jquery/compat', names the module but does not load it.
            if ($string->value !== 'jquery/ui' || $string->isKey) {
                continue;
            }
            $findings[] = new Finding(
                self::ID,
                Severity::Warning,
                $file->relativePath,
                $string->line,
                sprintf(
                    '[%s] "jquery/ui" loads every jQuery UI widget: on the storefront core maps it to jquery/compat, '
                    . 'which imports them all. Depend only on the widgets this code uses, such as '
                    . '"jquery-ui-modules/widget" or "jquery-ui-modules/slider" (Magento 2.3.3 and later).',
                    self::ID,
                ),
            );
        }

        return $findings;
    }
}
