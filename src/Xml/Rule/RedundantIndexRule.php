<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use DOMDocument;
use DOMElement;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\XmlFile;
use Silin\MagentoPerfCheck\Xml\XmlRule;
use Silin\MagentoPerfCheck\Xml\XPath;

/**
 * X-DB-02: a db_schema.xml index with the same columns, in the same order, as the primary
 * key, a unique constraint or another index of that table in the same file.
 */
final class RedundantIndexRule implements XmlRule
{
    public const ID = 'X-DB-02';

    private const XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_CONFIG && basename($file->relativePath) === 'db_schema.xml';
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '/schema/table') as $table) {
            if ($table->getAttribute('disabled') === 'true') {
                continue;
            }
            // Column list => what already covers it. Keys come first, so an index is always the later one.
            $covered = [];
            foreach ($table->childNodes as $key) {
                if ($key instanceof DOMElement && $key->localName === 'constraint' && $key->getAttribute('disabled') !== 'true'
                    && in_array($key->getAttributeNS(self::XSI, 'type'), ['primary', 'unique'], true)
                ) {
                    $covered[$this->columns($key)] = $key->getAttribute('referenceId');
                }
            }
            foreach ($table->childNodes as $index) {
                if (!$index instanceof DOMElement || $index->localName !== 'index' || $index->getAttribute('disabled') === 'true'
                    || !in_array($index->getAttribute('indexType'), ['', 'btree'], true)
                ) {
                    continue;
                }
                $columns = $this->columns($index);
                if ($columns === '') {
                    continue;
                }
                if (isset($covered[$columns])) {
                    $findings[] = new Finding(self::ID, Severity::Info, $file->relativePath, $index->getLineNo(), sprintf(
                        '[%s] Index "%s" on table "%s" has the same columns (%s) as "%s". Remove it: it serves no query that the other one does not, and every index adds work to writes.',
                        self::ID,
                        $index->getAttribute('referenceId'),
                        $table->getAttribute('name'),
                        $columns,
                        $covered[$columns],
                    ), $index);
                    continue;
                }
                $covered[$columns] = $index->getAttribute('referenceId');
            }
        }

        return $findings;
    }

    private function columns(DOMElement $key): string
    {
        $names = [];
        foreach ($key->childNodes as $column) {
            if ($column instanceof DOMElement && $column->localName === 'column') {
                $names[] = $column->getAttribute('name');
            }
        }

        return implode(', ', $names);
    }
}
