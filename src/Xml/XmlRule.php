<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use DOMDocument;
use Silin\MagentoPerfCheck\Report\Finding;

interface XmlRule
{
    public function ruleId(): string;

    public function appliesTo(XmlFile $file): bool;

    /**
     * @return list<Finding>
     */
    public function check(XmlFile $file, DOMDocument $document): array;
}
