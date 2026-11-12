<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Xml\Script\ScriptSource;

/**
 * A rule over the JavaScript of storefront files (XmlFile::KIND_SCRIPT).
 */
interface ScriptRule
{
    public function ruleId(): string;

    /**
     * @return list<Finding>
     */
    public function check(XmlFile $file, ScriptSource $script): array;
}
