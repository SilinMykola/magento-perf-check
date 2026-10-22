<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml;

use GraphQL\Language\AST\DocumentNode;
use MagentoPerfRules\Report\Finding;

interface GraphQlRule
{
    public function ruleId(): string;

    /**
     * @return list<Finding>
     */
    public function check(XmlFile $file, DocumentNode $schema): array;
}
