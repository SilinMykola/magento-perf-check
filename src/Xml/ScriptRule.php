<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Xml\Script\ScriptSource;

/**
 * A rule over the JavaScript of .js files and templates (XmlFile::KIND_SCRIPT).
 */
interface ScriptRule
{
    public function ruleId(): string;

    /**
     * The areas whose scripts the rule reads: "frontend" and "base" for the storefront, "adminhtml" for the admin.
     *
     * @return list<string>
     */
    public function areas(): array;

    /**
     * @return list<Finding>
     */
    public function check(XmlFile $file, ScriptSource $script): array;
}
