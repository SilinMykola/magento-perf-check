<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Report;

use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\Severity;

/**
 * The report for people: one block per rule, errors first, with the rule's title and documentation link
 * once and the places as "file:line, line". Info rules list a few files; --verbose lists every file and
 * the message of each finding. Colors and terminal links only when $ansi is set.
 */
final class TableReporter implements Reporter
{
    /** Files listed for an info rule without --verbose. */
    public const INFO_FILES = 5;

    private const ORDER = ['error' => 0, 'warning' => 1, 'info' => 2];
    private const COLORS = ['error' => '1;37;41', 'warning' => '1;30;43', 'info' => '1;30;46'];
    private const TEXT_COLORS = ['error' => '1;31', 'warning' => '1;33', 'info' => '36'];
    private const INDENT = '        ';
    /** Rule IDs are padded to this width, so the titles of all blocks start in one column. */
    private const ID_WIDTH = 10;

    public function __construct(
        private readonly RuleRegistry $registry,
        private readonly bool $ansi = false,
        private readonly bool $verbose = false,
    ) {
    }

    public function render(AnalysisResult $result): string
    {
        $groups = [];
        foreach ($result->findings as $finding) {
            $groups[$finding->severity->value . ' ' . $finding->ruleId][] = $finding;
        }
        uksort($groups, static function (string $a, string $b) use ($groups): int {
            [$severityA, $idA] = explode(' ', $a, 2);
            [$severityB, $idB] = explode(' ', $b, 2);

            return [self::ORDER[$severityA] ?? 3, -count($groups[$a]), $idA] <=> [self::ORDER[$severityB] ?? 3, -count($groups[$b]), $idB];
        });

        $out = '';
        $hidden = false;
        foreach ($groups as $findings) {
            $out .= $this->group($findings, $hidden) . "\n";
        }

        $counts = $result->countBySeverity();
        $out .= sprintf(
            "%d finding(s): %s, %s, %s. Analyzed %s in %.1f s.\n",
            count($result->findings),
            $this->paint(($counts['error'] ?? 0) . ' error', ($counts['error'] ?? 0) > 0 ? self::TEXT_COLORS['error'] : ''),
            $this->paint(($counts['warning'] ?? 0) . ' warning', ($counts['warning'] ?? 0) > 0 ? self::TEXT_COLORS['warning'] : ''),
            $this->paint(($counts['info'] ?? 0) . ' info', ($counts['info'] ?? 0) > 0 ? self::TEXT_COLORS['info'] : ''),
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
        $notices = $result->noticeSummary();
        if ($notices !== null) {
            $out .= $notices . "\n";
        }
        if ($result->findings !== [] && !$this->verbose) {
            $out .= $this->paint(
                'Run with --verbose for the message of each finding' . ($hidden ? ' and every file' : '') . ', or use --format=json.',
                '2',
            ) . "\n";
        }

        return $out;
    }

    /**
     * @param non-empty-list<Finding> $findings of one rule at one severity
     * @param bool $hidden set when files were left out
     */
    private function group(array $findings, bool &$hidden): string
    {
        $first = $findings[0];
        $severity = $first->severity;
        $url = $this->registry->docUrl($first->ruleId);
        $title = $this->registry->find($first->ruleId)?->title;

        $count = count($findings);
        $out = sprintf(
            "%s %s%s  %s  %s\n%s%s\n",
            $this->paint(str_pad(strtoupper($severity->value), 7), self::COLORS[$severity->value]),
            $this->link($this->paint($first->ruleId, '1'), $url),
            str_repeat(' ', max(0, self::ID_WIDTH - strlen($first->ruleId))),
            $title ?? '',
            $this->paint(sprintf('%d finding%s', $count, $count === 1 ? '' : 's'), self::TEXT_COLORS[$severity->value]),
            self::INDENT,
            $this->paint($url, '2'),
        );

        $byFile = [];
        foreach ($findings as $finding) {
            $byFile[$finding->file][] = $finding;
        }
        // Hiding a single file would take the line it needs.
        $limit = !$this->verbose && $severity === Severity::Info && count($byFile) > self::INFO_FILES + 1 ? self::INFO_FILES : count($byFile);
        foreach (array_slice($byFile, 0, $limit, true) as $file => $inFile) {
            if ($this->verbose) {
                $out .= self::INDENT . $file . "\n";
                foreach ($inFile as $finding) {
                    // The block names the rule already.
                    $message = preg_replace('/^\[' . preg_quote($finding->ruleId, '/') . '\] /', '', $finding->message) ?? $finding->message;
                    $out .= sprintf("%s  %s %s\n", self::INDENT, $this->paint(str_pad($finding->line > 0 ? (string) $finding->line : '', 5, ' ', STR_PAD_LEFT), '2'), $message);
                }
                continue;
            }
            // A line with two findings shows "5 (2x)", so the places add up to the count above.
            $places = [];
            foreach (array_count_values(array_map(static fn (Finding $f): int => $f->line, $inFile)) as $line => $times) {
                if ($line > 0) {
                    $places[] = $times > 1 ? sprintf('%d (%dx)', $line, $times) : (string) $line;
                }
            }
            $out .= self::INDENT . $file . ($places === [] ? '' : $this->paint(':' . implode(', ', $places), '2')) . "\n";
        }
        if ($limit < count($byFile)) {
            $hidden = true;
            $rest = count($byFile) - $limit;
            $out .= self::INDENT . $this->paint(sprintf('... and %d more files', $rest), '2') . "\n";
        }

        return $out;
    }

    private function paint(string $text, string $code): string
    {
        return $this->ansi && $code !== '' ? "\e[" . $code . 'm' . $text . "\e[0m" : $text;
    }

    /**
     * An OSC 8 terminal link: terminals that know it make the text clickable, the others show the text.
     */
    private function link(string $text, string $url): string
    {
        return $this->ansi ? "\e]8;;" . $url . "\e\\" . $text . "\e]8;;\e\\" : $text;
    }
}
