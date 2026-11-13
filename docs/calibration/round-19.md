# Calibration, round 19

This round measures U-LOG-01, an upgrade rule for the move to Monolog 2 in Magento 2.4.4 (change add-monolog-add-method-rule). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Projects:** the Magento 2.4.9 core (`vendor/magento`), the 25 modules of round 1 (`app/code`), the extended part (`app/code`, `app/design`) of the calibration corpus ([`corpus.json`](corpus.json)), and the five private stores of [round 15](round-15.md), with the same rules on what is published.
- **Code:** 0.9.0-dev, U-LOG-01 enabled, `--target-version=2.4.4`, on 2026-11-13. The runs are not stored.
- **Grading:** a finding is `TP` when it breaks a storefront, order or integration flow, on its normal path or when a remote call fails, and `TP-low` when it is only on an admin action, a setup patch, a queue import started from the admin, code nothing calls, or a rare failure path just before the code throws an exception anyway. Every finding was graded, once, by one grader. The verdicts are in [`graded/round-19/`](graded/round-19/). The results table counts 30 of the 38, drawn as the method asks, with the shuffle `bin/calibration/sample` uses (sorted by run, file, line and message, then a Fisher-Yates shuffle after `mt_srand(20261113)`), run on the local results, which are not stored, so the draw cannot be repeated from this repository: rows 1, 2, 5–8, 10, 13–25, 27, 28, 30, 31 and 33–38 of the grades file. Over all 38, the grades are 23 TP and 15 TP-low. For each finding, the receiver's class was checked: it extends `Monolog\Logger`, directly or through Magento's `Monolog`, and does not declare the method, or the call is `parent::` to Monolog's own method.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [U-LOG-01](../rules/U-LOG-01.md) | error | 38 | 30 | 15 | 15 | 0 | 0 | 100 % | 50 % | default set |

**U-LOG-01.** The core, the 25 modules and the extended part give no finding: one extended-part payment module calls `addError()` and `addDebug()` about 500 times, but on its own logger class, which declares them, so the rule leaves them out. The private stores give 38, in four of the five stores, all in custom modules:
- store A, already on 2.4.4, 3: these calls fail today, one of them while order data is built for an ERP integration;
- store C, on 2.4.2, 31: ERP integrations that send each order and credit memo, newsletter and checkout observers, which would fail on the normal path or whenever a remote call fails, and admin-only imports and saves;
- store D, on 2.4.3, 3: a store setup service run once by a setup patch;
- store B, already on 2.4.5, 1: a logger's own `addDebug()` that calls `parent::addDebug()`, which nothing in the store calls.

31 of the 38 come from one store, and 24 of the 30 sampled. Without it, the rule has 7 findings, 1 of them TP. The TP-low findings are admin-only, setup-only or rare failure paths.

## Decision

U-LOG-01 joins the default set at `error`: 30 sampled findings, all correct, and an impact share of 50 %, the lowest the default set takes (all 38: 61 %). The margin is one verdict: one sampled `TP` graded `TP-low` would give 47 % and leave the rule out. It runs only on a project on 2.4.4 or later, or with such a `--target-version`, so on a project already there it reports calls that fail today.
