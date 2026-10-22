<?php

declare(strict_types=1);

namespace MagentoPerfRules\Report;

use MagentoPerfRules\Rule\RuleRegistry;

final class JsonReporter implements Reporter
{
    public function __construct(private readonly RuleRegistry $registry)
    {
    }

    public function render(AnalysisResult $result): string
    {
        $findings = array_map(fn (Finding $f): array => [
            'ruleId' => $f->ruleId,
            'severity' => $f->severity->value,
            'file' => $f->file,
            'line' => $f->line,
            'message' => $f->message,
            'docUrl' => $this->registry->docUrl($f->ruleId),
        ], $result->findings);

        $notices = array_map(static fn (Notice $n): array => [
            'source' => $n->source,
            'message' => $n->message,
            'file' => $n->file,
            'line' => $n->line,
        ], $result->notices);

        return json_encode([
            'summary' => [
                'project' => $result->projectRoot,
                'paths' => $result->paths,
                'findings' => count($result->findings),
                'bySeverity' => $result->countBySeverity(),
                'byRule' => (object) $result->countByRule(),
                'notices' => (object) $result->countNoticesBySource(),
                'durationSeconds' => round($result->durationSeconds, 2),
                'baseline' => $result->baselineHidden === null ? null : [
                    'hidden' => $result->baselineHidden,
                    'unmatched' => $result->baselineUnmatched,
                ],
            ],
            'findings' => $findings,
            'notices' => $notices,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
    }
}
