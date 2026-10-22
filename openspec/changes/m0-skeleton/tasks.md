# Tasks

## 1. Source audit

- [x] 1.1 Verify that commit `a385d02f` exists in magento/magento2 and resolve its full hash; record both in `docs/source-audit.md`. Verify: the pinned blob URL opens.
- [x] 1.2 Audit SPEC sections 4.1–4.2 (P-LOOP-*, P-COL-*). For each ✅/⏳ rule, open every cited source, record its level, what it confirms and the date, and pin core links to the commit. Resolve P-COL-03's 🔎 (A/B found, or demoted to 🧪). Check `setCurPage()` against `AbstractDb::_renderLimit()` (design D5). Verify: every rule ID from these sections is in the audit table.
- [x] 1.3 Audit SPEC sections 4.3–4.4 (P-DI-*, P-FPC-*) the same way. Verify: every ✅/⏳ rule ID from these sections is in the audit table.
- [x] 1.4 Audit SPEC sections 4.5–4.8 (P-PLG, X-PLG, P-EXT, X-*, T-*, C-01) the same way, including P-EXT-02's 🔎. Verify: every ✅/⏳ rule ID from these sections is in the audit table.
- [x] 1.5 Apply the audit to `docs/SPEC.md`: demote rules without A/B to 🧪, replace `2.4-develop` blob links with pinned ones, fix the `setCurPage` wording, and recount the totals in section 4. Verify: `grep -c 'blob/2.4-develop' docs/SPEC.md` is 0, and the totals match a recount of the table rows.

## 2. Package skeleton and PHPStan compatibility

- [x] 2.1 Create `composer.json` with the following, then run `composer install`. Verify: install succeeds on host PHP 8.5.
  - `type: phpstan-extension` and the working names from design D8.
  - `php: ^8.1` and `phpstan/phpstan: ^1.12 || ^2.0`.
  - `extra.phpstan.includes`; bitExpert in `suggest`.
  - Dev dependencies: PHPUnit and `phpstan/extension-installer`.
- [x] 2.2 Add `extension.neon` (empty rule list for now), `phpunit.xml.dist`, `phpstan.neon.dist` (level max on `src/`) and the Composer scripts `test` and `analyse`. Verify: `composer test` and `composer analyse` both run green on the empty skeleton.
- [x] 2.3 Add `bin/test-phpstan1`. It copies the repository into a temp directory, runs `composer require phpstan/phpstan:~1.12.0 -W` there and runs the test suite. Verify: the script exits 0 on the skeleton, or record the PHP 8.5 incompatibility and switch it to PHP 8.4 (design risk 3).
- [x] 2.4 Write the ADR for PHPStan 1.12 + 2.x support (D2) and the ADR for bitExpert as a suggestion (D3) in `docs/adr/`. Cite the Packagist constraints observed on 2026-10-21. Verify: both files exist, with Status, Context, Decision and Consequences.

## 3. Rule metadata and documentation checks

- [x] 3.1 Implement the rule metadata registry (design D7) with entries for X-LAY-01, P-COL-02 and P-LOOP-01, filled from the audit's verified sources. Verify: a unit test asserts each entry has severity, status and at least one A/B source.
- [x] 3.2 Add a documentation test. It fails when a registered rule has no `docs/rules/<ID>.md`, when the page has no `## Basis` section, or when the page contains a `magento2/blob/2.4-develop` link. Verify: it fails now (the pages are missing) and names the three missing pages.

## 4. XML analyzer and X-LAY-01

- [x] 4.1 Implement XML file discovery, area, module and handle resolution, and parse-error notices. Verify: unit tests on a fixture tree cover every scenario in `specs/xml-analysis/spec.md`.
- [x] 4.2 Implement X-LAY-01 with severity by handle and the default allowlist. Verify: golden tests over positive and negative fixtures cover every scenario in `specs/layout-rules/spec.md`; break the handle-severity mapping, watch the test fail, then restore it.
- [x] 4.3 Write `docs/rules/X-LAY-01.md` in the SPEC section 5 template, with a Basis from the audit. Verify: the documentation test passes for X-LAY-01.

## 5. PHPStan extension: loop context and P-LOOP-01

- [x] 5.1 Implement the loop-context NodeVisitor (design D4) and register it in `extension.neon`. Verify: tests cover every loop-context scenario in `specs/phpstan-extension/spec.md`, including the iterable expression and the stored closure.
- [x] 5.2 Implement P-LOOP-01 with identifier `magentoPerf.loopModelLoad` and a `[P-LOOP-01]` message prefix. Fixtures use stub Magento classes inside `tests/`. Verify: a `RuleTestCase` covers every scenario in `specs/loop-rules/spec.md` and passes on both PHPStan majors (`composer test` and `bin/test-phpstan1`); remove the loop-context check, watch the negative cases fail, then restore it.
- [x] 5.3 Add a test that P-LOOP-01 reports at level 0 and that `@phpstan-ignore magentoPerf.loopModelLoad (reason)` suppresses it. Verify: the test passes on both PHPStan majors.
- [x] 5.4 Write `docs/rules/P-LOOP-01.md` with a Basis from the audit. Verify: the documentation test passes for P-LOOP-01.

## 6. P-COL-02

- [x] 6.1 Implement P-COL-02 with chain and same-variable limit tracking (design D5), identifier `magentoPerf.getFirstItemWithoutLimit`, and a `[P-COL-02]` prefix. Verify: a `RuleTestCase` covers every scenario in `specs/collection-rules/spec.md` on both PHPStan majors; drop the earlier-statement lookup, watch "Limit earlier in method" fail, then restore it.
- [x] 6.2 Write `docs/rules/P-COL-02.md` with a Basis from the audit and the known false positive from the design risks. Verify: the documentation test passes for all three rules.

## 7. magento-perf-check runner

- [x] 7.1 Implement option parsing and validation (`--project`, `--paths`, `--format`, `--fail-on`, `--memory-limit`), with error exits with code 2. Verify: tests cover the "Path is not a Magento project", "Missing path" and "Unknown format" scenarios.
- [x] 7.2 Implement the PHPStan subprocess runner (design D1). It writes a temp config outside the project, uses the project's autoload as bootstrap, scans `generated/code` when present, includes bitExpert when installed, filters messages by rule-ID prefix and counts dropped notices. Check whether `customRulesetUsed` disables built-in rules on both majors and use it only if it does. Verify: an integration test on a fixture Magento-like project yields only rule findings and a notice count.
- [x] 7.3 Implement the merged result model, the table and JSON reporters, and the exit codes. Verify: tests cover every scenario in `specs/cli/spec.md` for formats and exit codes, including a PHPStan crash mapped to exit code 2.
- [x] 7.4 Add a test that a run leaves the project tree unchanged. It compares file list and mtimes before and after a run on the fixture project. Verify: the test passes.
- [x] 7.5 Write the ADR for the runner architecture (D1). Verify: the file exists in `docs/adr/` and links the result of the `customRulesetUsed` check.

## 8. Acceptance run and docs

- [x] 8.1 Run `php bin/magento-perf-check --project=$HOME/projects/magento-2.4.9.ddev` with the default paths, and record findings per rule and the run time. Verify: exit code 0 or 1 (not 2), and the test project's `git status` or file mtimes are unchanged.
- [x] 8.2 Run the same with `--paths=vendor/magento` (core). Record findings per rule, notice counts (including unknown-type `getFirstItem` receivers), wall time and peak memory in `docs/runs/m0-core.md`. Verify: the run completes with exit code 0 or 1 and the report file holds the numbers.
- [x] 8.3 Spot-check up to 10 core findings per rule by hand and note obvious false positives in `docs/runs/m0-core.md`. This is not calibration; it decides whether a rule needs a fix before M1. Verify: each spot-checked finding is marked TP or FP with a one-line reason.
- [x] 8.4 Update `README.md`: installation, the `magento-perf-check` usage with `--project`/`--paths`, the three rules with their source levels, and a link to `docs/source-audit.md`. Verify: the commands in the README run as written against the test project.
- [x] 8.5 Run the full check: `composer test`, `composer analyse`, `bin/test-phpstan1` and `openspec validate m0-skeleton --strict`. Verify: all four exit 0.

## 9. Test code exclusion

- [x] 9.1 Skip `Test` and `dev/tests` directories below the analyzed paths in XML discovery and in the PHPStan config, unless `--include-tests` is given or the path itself is inside test code. Verify: integration tests cover the three scenarios of "Test code excluded by default" in `specs/cli/spec.md`; turn the exclusion off in code, watch the default-run test fail, then restore it.
- [x] 9.2 Document `--include-tests` in `README.md` and in the usage text. Verify: `php bin/magento-perf-check --help` lists the option.

## Workflow follow-up

- Show the owner the changed files, the tests and the diff before any push.
- Archive the change with `/opsx:archive` after review, then start `m1-mvp`.
