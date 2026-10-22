<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml\Rule;

use DOMDocument;
use MagentoPerfRules\Project\ProjectIndex;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Rule\Severity;
use MagentoPerfRules\Xml\XmlFile;
use MagentoPerfRules\Xml\XmlRule;
use MagentoPerfRules\Xml\XPath;

/**
 * X-IDX-01: an indexer whose view_id has no mview view anywhere in the project.
 */
final class IndexerWithoutMviewRule implements XmlRule
{
    public const ID = 'X-IDX-01';

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
            && basename($file->relativePath) === 'indexer.xml';
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '/config/indexer[@view_id]') as $indexer) {
            $view = $indexer->getAttribute('view_id');
            if ($this->index->hasMviewView($view)) {
                continue;
            }
            $findings[] = new Finding(self::ID, Severity::Warning, $file->relativePath, $indexer->getLineNo(), sprintf(
                '[%s] Indexer "%s" uses view_id "%s", but no mview.xml in the project declares that view. '
                . 'In "Update by Schedule" mode the indexer will not track changes.',
                self::ID,
                $indexer->getAttribute('id'),
                $view,
            ), $indexer);
        }

        return $findings;
    }
}
