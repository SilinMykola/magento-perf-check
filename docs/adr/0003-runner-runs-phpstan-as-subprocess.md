# 3. magento-perf-check runs PHPStan as a subprocess and merges its JSON output

- Status: Accepted
- Date: 2026-10-21

## Context

`magento-perf-check` has to report XML findings (own DOM analyzer) and PHP findings (PHPStan rules) in one report, with one set of rule IDs and exit codes. It must work on a project it is not installed in (`--project`), and it must not write into that project (docs/SPEC.md section 9a).

PHPStan's internal API is not covered by its backward-compatibility promise and differs between 1.12 and 2.x ([ADR 1](0001-support-phpstan-1-12-and-2.md)). Its command line and `--error-format=json` output are stable in both.

## Decision

The runner runs the XML analyzer in-process and PHPStan as a child process. It then merges both into one result.

**Generated configuration.** The config lives in `<system temp>/magento-perf-check-<uid>/<md5 of project path>/`, together with PHPStan's result cache; nothing is written into the analyzed project. It contains:
- `includes`: this package's `extension.neon`, plus bitExpert's `extension.neon` when that package is installed ([ADR 2](0002-bitexpert-phpstan-magento-as-suggestion.md)).
- `paths`: the analyzed paths, as absolute paths.
- `bootstrapFiles`: the project's `vendor/autoload.php`.
- `scanDirectories`: `generated/code` when it exists.
- `customRulesetUsed: true`.
- `magentoPerf.reportUntypedReceivers: true`.

**Only our rules run.** Checked on 2026-10-21 with PHPStan 2.3.1 and 1.12.34:
- Without a level, both majors refuse to run ("No rules detected").
- With `customRulesetUsed: true` and no level, both majors run only the rules the config registers. A call to an undefined function or an unknown class produced no error.

**Mapping messages to rules.** Each rule message starts with `[RULE-ID]`, and the runner maps messages by that prefix. The JSON output of PHPStan 1.12.34 and 2.3.1 also contains `identifier`, which was observed on 2026-10-21. The runner uses it only to recognize parse errors (`phpstan.parse`). Any other message that does not carry a known prefix becomes an analysis notice, not a finding.

**Unparsable files.** When one analyzed file has a syntax error, PHPStan reports "Result is incomplete because of severe errors" and stops reporting rule errors for every other file. This was observed with PHPStan 2.3.1 on a fixture: the P-LOOP-01 violation in a sibling file was not reported. The runner therefore:
- adds the unparsable files to `excludePaths` and runs again, up to 5 passes;
- keeps their parse errors as `phpstan-parse` notices.

**Exit codes.**
- PHPStan exit code 0 or 1 means the analysis finished.
- Any other exit code (fatal error, out of memory), or output that is not JSON, is a tool failure: the command exits with 2.
- "No files found to analyse" (paths without PHP files) is an empty result, not a failure.

**Untyped receivers.** `reportUntypedReceivers` makes P-COL-02 emit a `[notice:P-COL-02]` message for each `getFirstItem()` whose receiver type is unknown. The runner counts these as `untyped-receiver` notices. In a user's own PHPStan run the parameter is off, so these messages never appear there.

## Consequences

- One code path works with both PHPStan majors.
- A file PHPStan cannot parse costs one extra PHPStan run, but it no longer hides findings in other files.
- Notices are counted in the summary and listed in JSON output. A run that skipped files is visible, not silently "clean".
- A crash of PHPStan always yields exit code 2, never a clean report.
- Process start-up and JSON parsing add a small fixed cost per run. This is negligible next to analysis time on a real project.
