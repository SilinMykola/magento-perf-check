<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use DOMDocument;
use Silin\MagentoPerfCheck\Project\ProjectIndex;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\XmlFile;
use Silin\MagentoPerfCheck\Xml\XmlRule;
use Silin\MagentoPerfCheck\Xml\XPath;

/**
 * X-DB-01: a db_schema.xml table that no module gives a primary key.
 */
final class TableWithoutPrimaryKeyRule implements XmlRule
{
    public const ID = 'X-DB-01';

    public function __construct(private readonly ProjectIndex $index)
    {
    }

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return !$this->index->isEmpty()
            && $file->kind === XmlFile::KIND_CONFIG
            && basename($file->relativePath) === 'db_schema.xml';
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '/schema/table') as $table) {
            $name = $table->getAttribute('name');
            if ($table->getAttribute('disabled') === 'true' || $this->index->tableHasPrimaryKey($name)) {
                continue;
            }
            $findings[] = new Finding(self::ID, Severity::Error, $file->relativePath, $table->getLineNo(), sprintf(
                '[%s] Table "%s" has no primary key in any db_schema.xml of the project. '
                . 'Add <constraint xsi:type="primary">: replication and clusters need one.',
                self::ID,
                $name,
            ), $table);
        }

        return $findings;
    }
}
