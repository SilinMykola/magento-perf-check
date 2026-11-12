<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml;

use DOMComment;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use DOMDocument;
use DOMText;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Report\Notice;
use Silin\MagentoPerfCheck\Xml\Script\ScriptSource;

final class XmlAnalyzer
{
    private const PERF_IGNORE = '/^\s*perf-ignore\s+([A-Z]-[A-Z]+-\d+)\s*(?::\s*(.*?))?\s*$/s';

    /**
     * @param list<XmlRule|GraphQlRule|ScriptRule> $rules
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
        $files = $this->discovery->discover($paths);
        $scriptRules = array_values(array_filter($this->rules, static fn ($rule): bool => $rule instanceof ScriptRule));
        foreach ($files as $file) {
            if ($file->kind === XmlFile::KIND_GRAPHQL) {
                $graphQlRules = array_values(array_filter($this->rules, static fn ($rule): bool => $rule instanceof GraphQlRule));
                $schema = $graphQlRules === [] ? null : $this->parseSchema($file, $notices);
                if ($schema !== null) {
                    ++$analyzed;
                    foreach ($graphQlRules as $rule) {
                        array_push($findings, ...$rule->check($file, $schema));
                    }
                }
                continue;
            }
            if ($file->kind === XmlFile::KIND_SCRIPT) {
                if ($scriptRules === []) {
                    continue;
                }
                $script = ScriptSource::fromFile($file->absolutePath);
                if ($script === null) {
                    $notices[] = new Notice('script', 'File is not readable', $file->relativePath);
                    continue;
                }
                ++$analyzed;
                foreach ($scriptRules as $rule) {
                    foreach ($rule->check($file, $script) as $finding) {
                        if (!$this->isScriptSuppressed($finding, $script, $notices)) {
                            $findings[] = $finding;
                        }
                    }
                }
                continue;
            }
            $rules = array_values(array_filter(
                $this->rules,
                static fn ($rule): bool => $rule instanceof XmlRule && $rule->appliesTo($file),
            ));
            if ($rules === []) {
                continue;
            }
            $document = $this->load($file, $notices);
            if ($document === null) {
                continue;
            }
            ++$analyzed;
            foreach ($rules as $rule) {
                foreach ($rule->check($file, $document) as $finding) {
                    if (!$this->isSuppressed($finding, $notices)) {
                        $findings[] = $finding;
                    }
                }
            }
        }

        return new XmlAnalysisResult($findings, $notices, $analyzed, count($files));
    }

    /**
     * @param list<Notice> $notices
     */
    private function parseSchema(XmlFile $file, array &$notices): ?DocumentNode
    {
        $content = @file_get_contents($file->absolutePath);
        if ($content === false) {
            $notices[] = new Notice('graphql', 'File is not readable', $file->relativePath);

            return null;
        }
        try {
            return Parser::parse(SchemaPreprocessor::normalize($content));
        } catch (SyntaxError $e) {
            $location = $e->getLocations()[0] ?? null;
            $notices[] = new Notice('graphql', $e->getMessage(), $file->relativePath, $location?->line);

            return null;
        }
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

    /**
     * True when the element is directly preceded (ignoring whitespace and other
     * perf-ignore comments) by "<!-- perf-ignore RULE-ID: reason -->". A matching
     * comment without a reason suppresses nothing and is recorded as a notice.
     *
     * @param list<Notice> $notices
     */
    private function isSuppressed(Finding $finding, array &$notices): bool
    {
        $node = $finding->element?->previousSibling;
        while ($node !== null) {
            if ($node instanceof DOMText && trim($node->data) === '') {
                $node = $node->previousSibling;
                continue;
            }
            if (!$node instanceof DOMComment
                || preg_match(self::PERF_IGNORE, $node->data, $m) !== 1
            ) {
                return false;
            }
            if ($m[1] === $finding->ruleId) {
                if (($m[2] ?? '') !== '') {
                    return true;
                }
                $notices[] = new Notice(
                    'suppression',
                    sprintf('perf-ignore %s without a reason; the finding is still reported', $m[1]),
                    $finding->file,
                    $node->getLineNo(),
                );

                return false;
            }
            $node = $node->previousSibling;
        }

        return false;
    }

    /**
     * A script finding is suppressed by a JavaScript comment "perf-ignore RULE-ID: reason" that ends on
     * the line of the finding or on the line before it. Without a reason it is reported, with a notice.
     *
     * @param list<Notice> $notices
     */
    private function isScriptSuppressed(Finding $finding, ScriptSource $script, array &$notices): bool
    {
        foreach ($script->comments as $comment) {
            if ($comment->endLine !== $finding->line && $comment->endLine !== $finding->line - 1) {
                continue;
            }
            // A doc comment's leading "*" does not count.
            if (preg_match(self::PERF_IGNORE, ltrim($comment->text, '*'), $m) !== 1 || $m[1] !== $finding->ruleId) {
                continue;
            }
            if (($m[2] ?? '') !== '') {
                return true;
            }
            $notices[] = new Notice(
                'suppression',
                sprintf('perf-ignore %s without a reason; the finding is still reported', $m[1]),
                $finding->file,
                $comment->endLine,
            );
        }

        return false;
    }
}
