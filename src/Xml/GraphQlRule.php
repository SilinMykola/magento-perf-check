<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use GraphQL\Language\AST\DocumentNode;
use Silin\MagentoPerfCheck\Report\Finding;

interface GraphQlRule
{
    public function ruleId(): string;

    /**
     * @return list<Finding>
     */
    public function check(XmlFile $file, DocumentNode $schema): array;
}
