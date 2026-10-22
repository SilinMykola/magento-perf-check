<?php

declare(strict_types=1);

namespace MagentoPerfRules\Report;

use XMLWriter;

/**
 * Checkstyle XML, as read by Jenkins, GitLab plugins and IDE importers.
 */
final class CheckstyleReporter implements Reporter
{
    public function render(AnalysisResult $result): string
    {
        $byFile = [];
        foreach ($result->findings as $finding) {
            $byFile[$finding->file][] = $finding;
        }

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('checkstyle');
        $xml->writeAttribute('version', '4.3');
        foreach ($byFile as $file => $findings) {
            $xml->startElement('file');
            $xml->writeAttribute('name', $file);
            foreach ($findings as $finding) {
                $xml->startElement('error');
                $xml->writeAttribute('line', (string) $finding->line);
                $xml->writeAttribute('severity', $finding->severity->value);
                $xml->writeAttribute('message', $finding->message);
                $xml->writeAttribute('source', $finding->ruleId);
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }
}
