<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Report;

use Silin\MagentoPerfCheck\Rule\Severity;

final class AnalysisResult
{
    /** @var list<Finding> */
    public readonly array $findings;

    /**
     * @param list<Finding> $findings
     * @param list<Notice> $notices
     * @param list<string> $paths
     */
    public function __construct(
        array $findings,
        public readonly array $notices,
        public readonly string $projectRoot,
        public readonly array $paths,
        public readonly float $durationSeconds,
        public readonly ?int $baselineHidden = null,
        public readonly ?int $baselineUnmatched = null,
    ) {
        usort(
            $findings,
            static fn (Finding $a, Finding $b): int => [$a->file, $a->line, $a->ruleId] <=> [$b->file, $b->line, $b->ruleId],
        );
        $this->findings = $findings;
    }

    /**
     * @return array<string, int> count per severity, all severities present
     */
    public function countBySeverity(): array
    {
        $counts = [];
        foreach (Severity::cases() as $severity) {
            $counts[$severity->value] = 0;
        }
        foreach ($this->findings as $finding) {
            ++$counts[$finding->severity->value];
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    public function countByRule(): array
    {
        $counts = [];
        foreach ($this->findings as $finding) {
            $counts[$finding->ruleId] = ($counts[$finding->ruleId] ?? 0) + 1;
        }
        ksort($counts);

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    public function countNoticesBySource(): array
    {
        $counts = [];
        foreach ($this->notices as $notice) {
            $counts[$notice->source] = ($counts[$notice->source] ?? 0) + 1;
        }
        ksort($counts);

        return $counts;
    }

    /**
     * @return list<string>
     */
    public function analysisFailures(): array
    {
        $failures = [];
        foreach ($this->notices as $notice) {
            if ($notice->source === Notice::SOURCE_ANALYSIS_FAILURE) {
                $failures[] = $notice->message;
            }
        }

        return $failures;
    }

    /**
     * One line for the reader, or null when there are no notices.
     */
    public function noticeSummary(): ?string
    {
        $parts = [];
        foreach ($this->countNoticesBySource() as $source => $count) {
            $parts[] = $count . ' ' . $source;
        }
        if ($parts === []) {
            return null;
        }

        return sprintf(
            '%d analysis notice(s) not reported as findings (%s). Use --format=json to list them.',
            count($this->notices),
            implode(', ', $parts),
        );
    }

    public function hasFindingsAtLeast(Severity $threshold): bool
    {
        foreach ($this->findings as $finding) {
            if ($finding->severity->isAtLeast($threshold)) {
                return true;
            }
        }

        return false;
    }
}
