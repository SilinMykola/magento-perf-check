<?php

declare(strict_types=1);

namespace MagentoPerfRules\Report;

use MagentoPerfRules\Rule\RuleRegistry;

final class TableReporter implements Reporter
{
    public function __construct(private readonly RuleRegistry $registry)
    {
    }

    public function render(AnalysisResult $result): string
    {
        $out = '';
        $byFile = [];
        foreach ($result->findings as $finding) {
            $byFile[$finding->file][] = $finding;
        }
        foreach ($byFile as $file => $findings) {
            $out .= $file . "\n";
            foreach ($findings as $finding) {
                $out .= sprintf(
                    "  %5d  %-7s  %s\n         %s\n",
                    $finding->line,
                    $finding->severity->value,
                    $finding->message,
                    $this->registry->docUrl($finding->ruleId),
                );
            }
            $out .= "\n";
        }

        $counts = $result->countBySeverity();
        $out .= sprintf(
            "%d finding(s): %d error, %d warning, %d info. Analyzed %s in %.1f s.\n",
            count($result->findings),
            $counts['error'] ?? 0,
            $counts['warning'] ?? 0,
            $counts['info'] ?? 0,
            implode(', ', $result->paths),
            $result->durationSeconds,
        );
        if ($result->baselineHidden !== null) {
            $out .= sprintf(
                "Baseline hid %d known finding(s); %d baseline entr%s matched nothing and can be removed by regenerating it.\n",
                $result->baselineHidden,
                (int) $result->baselineUnmatched,
                $result->baselineUnmatched === 1 ? 'y' : 'ies',
            );
        }
        $notices = $result->countNoticesBySource();
        if ($notices !== []) {
            $parts = [];
            foreach ($notices as $source => $count) {
                $parts[] = $count . ' ' . $source;
            }
            $out .= sprintf(
                "%d analysis notice(s) not reported as findings (%s). Use --format=json to list them.\n",
                count($result->notices),
                implode(', ', $parts),
            );
        }

        return $out;
    }
}
