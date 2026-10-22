<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml;

use DOMDocument;
use MagentoPerfRules\Report\Finding;

interface XmlRule
{
    public function ruleId(): string;

    public function appliesTo(XmlFile $file): bool;

    /**
     * @return list<Finding>
     */
    public function check(XmlFile $file, DOMDocument $document): array;
}
