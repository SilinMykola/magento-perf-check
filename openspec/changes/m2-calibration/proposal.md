# Proposal

## Why

The 29 default rules have never been measured. The M1 spot checks on Magento core (`docs/runs/m1-core.md`) showed that several of them would make the tool noisy:
- 9 rules had most or all of their sampled findings false;
- others are precise but report low-impact cases at a high severity.

SPEC section 7 makes calibration mandatory before a public release. Each rule's precision is measured on a fixed corpus and published in `docs/calibration.md`; a rule below its threshold is downgraded or made experimental. M2 also adds the CI integrations the release needs: GitHub Actions and GrumPHP.

## What Changes

- **Corpus**, fixed and reproducible. It does not touch the test Magento project.
  - Magento 2.4.9 core, analyzed in a copy of `~/projects/magento-2.4.9.ddev` under `var/corpus/` (git-ignored).
  - The 25 most-downloaded `magento2-module` packages from Packagist that install on Magento 2.4.9. Each is pinned to a version and placed in the copy's `app/code`.
  - Mage-OS: planned as a Composer install of `mage-os/project-community-edition`. Dropped on 2026-10-24 by the owner after three installs hung; recorded in the manifest and the report.
  - The package list, versions and checksums are committed as `docs/calibration/corpus.json`.
- **Rule fixes first**, from the M1 spot-check conclusions. Each fix is limited to the false-positive pattern the spot checks found:
  - P-COL-01: a collection loaded later in the same function counts as loaded.
  - P-DI-02: casts, assignments to locals, and `??`/`?:` fallbacks whose default is `new` or the ObjectManager are allowed; code that the object manager never builds (composer plugins, the coding standard) is skipped.
  - P-FPC-02: `getCustomerGroupId()` and Adminhtml blocks are skipped.
  - P-FPC-05: only headers whose value disables caching count; one finding per statement; core private endpoints are allowlisted.
  - P-FPC-06: Adminhtml controllers and redirect-only actions are skipped.
  - P-FPC-09: CLI commands, setup code and configuration backend models are skipped.
  - X-SEC-01: store and currency switch actions are allowed; `<action name="*">` that only lists `messages` is not reported.
  - X-EVT-01: EntityManager plumbing observers and `*_collection_load_after` events are skipped.
  - X-SETUP-01: a loop is heavy only if it loads, saves or queries inside.
- **Calibration**:
  - Each default rule runs on the corpus, and up to 30 findings per rule are sampled with a fixed seed.
  - A single grader grades every sampled finding by reading the code; the owner re-checks about 3 per rule and corrects the grades.
  - Precision = (TP + TP-low) / (TP + TP-low + FP). Low-impact findings count as correct for precision; the impact share TP / (TP + TP-low) lowers the severity one more step when it is below 50 %.
  - Thresholds (SPEC section 7), applied after the fixes:
    - an `error` rule stays at ≥ 90 %, becomes `warning` at 75–90 %, and moves to 🧪 below 75 %;
    - a `warning` rule stays at ≥ 75 % and moves to 🧪 below;
    - `info` rules have no threshold;
    - fewer than 10 graded findings means "insufficient data", and the rule keeps its severity.
  - 🧪 rules are off by default; the configuration file can enable them (today the runner does not filter by status).
- **Results**: `docs/calibration.md` holds the corpus, version, per-rule counts, precision and the decision for each rule. The README links it. The SPEC catalog and the rule registry take the new severities and statuses.
- **CI integrations**:
  - a GitHub Actions workflow: SARIF to code scanning, plus annotations on pull requests through a new `--format=github`;
  - a documented GrumPHP `shell` task.
- **Spec hygiene**: the two over-long requirements (X-LAY-01 default allowlist, P-COL-02 scope) are split, one behavior per requirement, with meaning unchanged.

Out of M2:
- GitLab Code Quality format (M3 if requested);
- PRs to module authors (M3);
- ⏳ and 🧪 rules (M4/M5).

## Capabilities

### New Capabilities

- `calibration`: the corpus definition, the sampling and grading procedure, the precision thresholds and the published calibration report.
- `ci-integration`: the GitHub Actions workflow and the GrumPHP task, as delivered and documented artifacts.

### Modified Capabilities

- `cli`: the output formats requirement adds `--format=github` (GitHub Actions workflow commands).
- `configuration`: experimental rules are off unless enabled.
- `collection-rules`: P-COL-01 treats a later load as loaded; the P-COL-02 scope requirement is split.
- `layout-rules`: the X-LAY-01 default allowlist requirement is split.
- `di-rules`: the P-DI-02 allowed statements and skipped code are extended.
- `fpc-rules`: P-FPC-02, P-FPC-05, P-FPC-06 and P-FPC-09 get the exclusions above.
- `customer-data-rules`: X-SEC-01 exceptions are extended.
- `event-rules`: X-EVT-01 skips EntityManager plumbing and collection load events.
- `setup-rules`: X-SETUP-01 counts only loops with database work inside.
- Severity and status of any rule may change as a calibration outcome. Such changes are added to the affected rule-family delta specs during apply.

## Impact

- **Code:** fixes in 9 rules; a GitHub reporter; scripts under `bin/` for corpus setup, sampling and the report.
- **Data:** `var/corpus/` (git-ignored, several GB); `docs/calibration/` with the corpus manifest, samples and graded tables; `docs/calibration.md`.
- **CI:** `.github/workflows/` and an example `grumphp.yml`. The repository has no remote yet, so the workflow is validated locally and runs for the first time after M3 publication.
- **Owner time:** re-checking about 3 graded findings per rule with findings (roughly 60–90 findings).
- The test Magento project stays read-only. The corpus copy is made with rsync from it, and the original is never written to.
