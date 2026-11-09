# Tasks

**Definition of done for every rule fix:**
- fixtures cover the new scenarios of the rule's delta spec;
- the fix is broken on purpose, the test is seen to fail, and the fix is restored;
- `docs/rules/<ID>.md` describes the new behaviour and moves the fixed false positives out of "Known false positives";
- `composer test`, `bin/test-phpstan1` and `composer analyse` pass.

Groups may be committed one at a time.

## 1. Spec hygiene and experimental rules

- [x] 1.1 Confirm that the split requirements (X-LAY-01 allowlist, P-COL-02 scope) keep their meaning. Verify: the existing X-LAY-01 and P-COL-02 tests pass unchanged, and `openspec validate m2-calibration --strict` passes.
- [x] 1.2 Turn experimental rules off by default (design D5): filter in `Configuration::apply()`, and add an empty `extension-experimental.neon` that the runner includes. Verify: tests cover both "Experimental rules are off by default" scenarios with a registry entry switched to experimental in the test.
- [x] 1.3 Add `--format=github` (design D7). Verify: the "GitHub output" scenario test passes, including the escaping of `%`, line breaks, `:` and `,`.

## 2. Rule fixes from the M1 spot checks

- [x] 2.1 P-COL-01: a collection loaded later in the same function counts as loaded. Verify: per definition of done.
- [x] 2.2 P-DI-02: casts, fallbacks to `new`/ObjectManager, assignments to locals; skip composer-plugin packages and `magento/magento-coding-standard`. Verify: per definition of done.
- [x] 2.3 P-FPC-02: drop `getCustomerGroupId()` and skip Adminhtml blocks. Verify: per definition of done.
- [x] 2.4 P-FPC-05: statement-level finding, header value check, allowlisted core endpoints. Verify: per definition of done.
- [x] 2.5 P-FPC-06: skip Adminhtml controllers and redirect-only `execute()`. Verify: per definition of done.
- [x] 2.6 P-FPC-09: skip CLI commands, setup code, config importers and config backend models. Verify: per definition of done.
- [x] 2.7 X-SEC-01: store and currency switch actions; `name="*"` with only `messages`. Verify: per definition of done.
- [x] 2.8 X-EVT-01: skip `*_collection_load_after` and EntityManager observers. Verify: per definition of done.
- [x] 2.9 X-SETUP-01: loops count only through heavy calls inside. Verify: per definition of done.

## 3. Corpus

- [x] 3.1 Add `composer/semver` as a dev dependency. Write `bin/calibration/build-corpus` for the Magento copy (design D1). Verify: `var/corpus/magento` exists, `var/` is git-ignored, and `find /path/to/magento-2.4.9 -newer <marker>` reports nothing.
- [x] 3.2 Select, download and place the 25 Packagist modules, recording skipped packages with reasons. Write `docs/calibration/corpus.json`. Verify: 25 modules under `var/corpus/magento/app/code`, each with name, version and SHA-256 in the manifest; `php bin/magento-perf-check --project=var/corpus/magento --paths=app/code` exits 0 or 1.
- [x] 3.3 Mage-OS: the install hung three times, and the owner decided on 2026-10-24 to calibrate without it. Record it in the manifest as not included, with the reason. Verify: `docs/calibration/corpus.json` has a `mage-os` entry with `included: false` and the reason; the calibration spec and design reflect the decision.

## 4. Runs and sampling

- [x] 4.1 Write `bin/calibration/run`. Run the three corpus runs and store the JSON with the tool commit in `docs/calibration/runs/`. Verify: three JSON files exist, and wall time and peak RSS for each are recorded for the report.
- [x] 4.2 Write `bin/calibration/sample` (seed 20261023, sample-data dropped, owner re-check rows marked). Verify: running it twice gives identical files, and every default rule with findings has a `docs/calibration/graded/<RULE>.md`.

## 5. Grading

- [x] 5.1 Grade every sampled finding by reading the code, in parallel per rule family, all in the shared table format. Verify: no row without a verdict and reason; `bin/calibration/report --check` passes.
- [x] 5.3 Fix the false positives found in grading that the specs already exclude (owner decision, 2026-10-24): P-FPC-06 redirects created with `ResultFactory::TYPE_REDIRECT`; P-PLG-01 `$proceed` in a ternary branch or after `&&`/`||`/`??`, and parameters reassigned before the call. Rerun the corpus, resample the affected rules, keep verdicts of findings still reported and grade the new ones. Verify: per definition of done; other rules' findings are unchanged between the runs.
- [x] 5.4 Add the impact share TP / (TP + TP-low) to the calibration spec and `bin/calibration/report` (owner decision, 2026-10-24). Verify: `bin/calibration/report --check` prints the decisions with the impact share applied.
- [x] 5.5 List the recurring false-positive patterns that need spec changes in `docs/calibration/follow-ups.md` and link it from the report. Correct the P-DI-01 page: `ContextPlugin` reads the customer session on every frontend action.
- [x] 5.2 Owner re-check: list the marked rows (about 3 per rule) with links to the graded files. The owner decided on 2026-10-24 not to re-check this round. Verify: the report shows 0 re-checked verdicts and states the single-grader limitation.

## 6. Report and decisions

- [x] 6.1 Write `bin/calibration/report`, which generates `docs/calibration.md` (design D3). Verify: the report lists every default rule with counts, precision, owner re-checks and a decision, and fails on an incomplete table.
- [x] 6.2 Apply the decisions:
  - update severity and status in `src/Rule/catalog.php`;
  - move PHP rules that become experimental to `extension-experimental.neon`;
  - update the SPEC catalog with its totals;
  - add MODIFIED requirements to this change's delta specs for every severity or status change.

  Verify: the consistency test (design D4) passes, and `openspec validate m2-calibration --strict` passes.
- [x] 6.3 Update `README.md`: link `docs/calibration.md` in "What our rules are based on", split the rule table into default and experimental, and remove the "not measured yet" note. Verify: the README tests pass.

## 7. CI integration

- [x] 7.1 Write `docs/ci/github-actions.yml` (SARIF upload plus GitHub annotations), with actions pinned to SHAs looked up through `gh api`. Verify: the test for "GitHub Actions example for projects" passes.
- [x] 7.2 Write `.github/workflows/ci.yml` (PHP 8.1–8.5, PHPStan 1.12 job, self-analysis, `openspec validate --specs`). Verify: the "Repository CI" test passes, and `actionlint` passes (1.7.12 with shellcheck, installed with the owner's agreement on 2026-10-24).
- [x] 7.3 Write `docs/ci/grumphp.yml` and check it in a scratch copy with `phpro/grumphp` (design D8). Document both integrations in `README.md`. Verify: GrumPHP fails on the fixture with findings and passes on a clean one.

## 8. Final check

- [x] 8.1 Run `composer test`, `bin/test-phpstan1`, `composer analyse`, `openspec validate m2-calibration --strict` and `openspec validate --specs`. Verify: all exit 0.

## Workflow follow-up

- List the changed files with links for the owner (no diffs in chat), commit, then archive with `/opsx:archive`.
