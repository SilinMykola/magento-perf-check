<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml;

use DOMDocument;
use MagentoPerfRules\Report\Notice;

final class XmlAnalyzer
{
    /**
     * @param list<XmlRule> $rules
     */
    public function __construct(
        private readonly FileDiscovery $discovery,
        private readonly array $rules,
    ) {
    }

    /**
     * @param list<string> $paths paths relative to the project root
     */
    public function analyze(array $paths): XmlAnalysisResult
    {
        $findings = [];
        $notices = [];
        $analyzed = 0;
        foreach ($this->discovery->discover($paths) as $file) {
            $rules = array_values(array_filter($this->rules, static fn (XmlRule $rule): bool => $rule->appliesTo($file)));
            if ($rules === []) {
                continue;
            }
            $document = $this->load($file, $notices);
            if ($document === null) {
                continue;
            }
            ++$analyzed;
            foreach ($rules as $rule) {
                array_push($findings, ...$rule->check($file, $document));
            }
        }

        return new XmlAnalysisResult($findings, $notices, $analyzed);
    }

    /**
     * @param list<Notice> $notices
     */
    private function load(XmlFile $file, array &$notices): ?DOMDocument
    {
        $content = @file_get_contents($file->absolutePath);
        if ($content === false) {
            $notices[] = new Notice('xml', 'File is not readable', $file->relativePath);

            return null;
        }
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $document = new DOMDocument();
        // LIBXML_NONET: never fetch anything over the network; entities are not substituted.
        $loaded = $content !== '' && $document->loadXML($content, LIBXML_NONET);
        $error = libxml_get_last_error();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$loaded) {
            $notices[] = new Notice(
                'xml',
                $error !== false ? trim($error->message) : 'Empty or unparsable XML',
                $file->relativePath,
                $error !== false ? $error->line : null,
            );

            return null;
        }

        return $document;
    }
}
