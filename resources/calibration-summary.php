<?php

declare(strict_types=1);

/*
 * Turns one or more magento-perf-check JSON reports into a calibration report that can be shared
 * (docs/calibration-reports.md): counts per rule and a verdict table without paths, module names,
 * messages or project names, and a local list with the paths that stays with whoever ran it.
 * Several reports, one per project, give one combined file, which does not say which project a
 * finding came from.
 *
 * Usage: php calibration-summary.php report.json [more-report.json ...] [--out=directory]
 */

const SAMPLE_PER_RULE = 30;
const SEED = 20261023;

$reports = [];
$outDir = '.';
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--out=')) {
        $outDir = substr($argument, strlen('--out='));
    } else {
        $reports[] = $argument;
    }
}
if ($reports === []) {
    fwrite(STDERR, "Usage: php calibration-summary.php report.json [more-report.json ...] [--out=directory]\n");
    exit(2);
}
if (!is_dir($outDir)) {
    fwrite(STDERR, "The output directory $outDir does not exist.\n");
    exit(2);
}

require_once __DIR__ . '/../src/Version.php';

$findings = [];
$byRule = [];
$notices = [];
$seconds = 0.0;
$incomplete = false;
foreach ($reports as $number => $report) {
    $data = is_file($report) ? json_decode((string) file_get_contents($report), true) : null;
    if (!is_array($data) || !is_array($data['findings'] ?? null) || !is_array($data['summary'] ?? null)) {
        fwrite(STDERR, "$report is not a magento-perf-check JSON report (--format=json).\n");
        exit(2);
    }
    $summary = $data['summary'];
    foreach ($summary['byRule'] ?? [] as $rule => $count) {
        $byRule[$rule] = ($byRule[$rule] ?? 0) + (int) $count;
    }
    foreach ($summary['notices'] ?? [] as $source => $count) {
        $notices[$source] = ($notices[$source] ?? 0) + (int) $count;
    }
    $seconds += (float) ($summary['durationSeconds'] ?? 0);
    $incomplete = $incomplete || ($summary['incomplete'] ?? false) === true;
    foreach ($data['findings'] as $finding) {
        $finding['report'] = $report;
        $finding['reportNumber'] = $number;
        $findings[(string) ($finding['ruleId'] ?? '?')][] = $finding;
    }
}
ksort($byRule);
ksort($notices);
ksort($findings);

// Up to 30 findings per rule, drawn with a fixed seed, as the calibration rounds draw them.
$picked = [];
foreach ($findings as $ofRule) {
    usort($ofRule, static fn (array $a, array $b): int => [$a['reportNumber'], $a['file'] ?? '', $a['line'] ?? 0] <=> [$b['reportNumber'], $b['file'] ?? '', $b['line'] ?? 0]);
    mt_srand(SEED);
    for ($i = count($ofRule) - 1; $i > 0; --$i) {
        $j = mt_rand(0, $i);
        [$ofRule[$i], $ofRule[$j]] = [$ofRule[$j], $ofRule[$i]];
    }
    array_push($picked, ...array_slice($ofRule, 0, SAMPLE_PER_RULE));
}

$share = "# Calibration report\n\n"
    . 'magento-perf-check ' . \Silin\MagentoPerfCheck\Version::VERSION . ', ' . count($reports) . (count($reports) === 1 ? ' project' : ' projects')
    . ', run time ' . round($seconds, 1) . ' s' . ($incomplete ? ', incomplete (exit code 2)' : '') . ".\n\n"
    . "Magento versions and editions: \nStorefront (Luma, Hyvä, other): \n\n"
    . "## Findings per rule\n\n| Rule | Findings |\n|---|---|\n";
foreach ($byRule as $rule => $count) {
    $share .= "| $rule | $count |\n";
}
$share .= "\n## Notices per source\n\n";
$share .= $notices === [] ? "None.\n" : implode('', array_map(static fn (string $source, int $count): string => "- $source: $count\n", array_keys($notices), $notices));
$share .= "\n## Verdicts\n\nTP, TP-low, FP or unclear, and a short reason without names or paths. Row numbers match calibration-findings-local.md, which stays with you.\n\n"
    . "| # | Rule | Verdict | Why |\n|---|---|---|---|\n";
$local = "# Findings to grade (keep this file: it has your paths)\n\n"
    . "Row numbers match the verdict table of calibration-summary.md. Label a code piece you send for a row with its number.\n\n"
    . "| # | Rule | Report | File | Message | Rule page |\n|---|---|---|---|---|---|\n";
foreach ($picked as $n => $finding) {
    $row = $n + 1;
    $share .= sprintf("| %d | %s |  |  |\n", $row, $finding['ruleId'] ?? '?');
    $local .= sprintf(
        "| %d | %s | %s | %s:%d | %s | %s |\n",
        $row,
        $finding['ruleId'] ?? '?',
        $finding['report'],
        $finding['file'] ?? '?',
        $finding['line'] ?? 0,
        str_replace(['|', "\n"], ['\|', ' '], (string) ($finding['message'] ?? '')),
        $finding['docUrl'] ?? '',
    );
}

file_put_contents($outDir . '/calibration-summary.md', $share);
file_put_contents($outDir . '/calibration-findings-local.md', $local);
printf(
    "Wrote %s/calibration-summary.md, to fill in and send (no paths or names in it), and %s/calibration-findings-local.md, which stays with you.\n",
    $outDir,
    $outDir,
);
