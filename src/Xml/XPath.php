<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml;

use DOMDocument;
use DOMElement;
use DOMXPath;

final class XPath
{
    /**
     * @return list<DOMElement>
     */
    public static function elements(DOMDocument $document, string $query, ?DOMElement $context = null): array
    {
        $nodes = (new DOMXPath($document))->query($query, $context);
        $elements = [];
        foreach ($nodes === false ? [] : $nodes as $node) {
            if ($node instanceof DOMElement) {
                $elements[] = $node;
            }
        }

        return $elements;
    }
}
