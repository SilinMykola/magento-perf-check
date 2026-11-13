# Calibration, round 19

This round measures U-LOG-01, an upgrade rule for the move to Monolog 2 in Magento 2.4.4 (change add-monolog-add-method-rule). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Projects:** the Magento 2.4.9 core (`vendor/magento`), the 25 modules of round 1 (`app/code`), the extended part (`app/code`, `app/design`) of the calibration corpus ([`corpus.json`](corpus.json)), and the five private stores of [round 15](round-15.md), with the same rules on what is published.
- **Code:** 0.9.0-dev, U-LOG-01 enabled, `--target-version=2.4.4`, on 2026-11-13. The runs are not stored.
- **Grading:** a finding is `TP` when it breaks a storefront, order or integration flow, on its normal path or when a remote call fails, and `TP-low` when it is only on an admin action, a setup patch, a queue import started from the admin, code nothing calls, a `catch` block that only an unexpected error such as a database error reaches, or a rare failure path just before the code throws an exception anyway. Every finding was graded by one grader, and the `TP` verdicts were re-checked after 0.9.0 (below). The verdicts are in [`graded/round-19/`](graded/round-19/). The results table counts 30 of the 38, drawn as the method asks, with the shuffle `bin/calibration/sample` uses (sorted by run, file, line and message, then a Fisher-Yates shuffle after `mt_srand(20261113)`), run on the local results, which are not stored, so the draw cannot be repeated from this repository: rows 1, 2, 5–8, 10, 13–25, 27, 28, 30, 31 and 33–38 of the grades file. Over all 38, the grades are 18 TP and 20 TP-low, after the re-check below. For each finding, the receiver's class was checked: it extends `Monolog\Logger`, directly or through Magento's `Monolog`, and does not declare the method, or the call is `parent::` to Monolog's own method.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [U-LOG-01](../rules/U-LOG-01.md) | error | 38 | 30 | 12 | 18 | 0 | 0 | 100 % | 40 % | experimental |

**U-LOG-01.** The core, the 25 modules and the extended part give no finding: one extended-part payment module calls `addError()` and `addDebug()` about 500 times, but on its own logger class, which declares them, so the rule leaves them out. The private stores give 38, in four of the five stores, all in custom modules:
- store A, already on 2.4.4, 3: these calls fail today, one of them while order data is built for an ERP integration;
- store C, on 2.4.2, 31: ERP integrations that send each order and credit memo, newsletter services and a checkout observer, which would fail on the normal path or whenever a remote call fails, and admin-only imports, saves, a plugin on an admin controller and `catch` blocks of a newsletter observer that a remote failure does not reach;
- store D, on 2.4.3, 3: a store setup service run once by a setup patch;
- store B, already on 2.4.5, 1: a logger's own `addDebug()` that calls `parent::addDebug()`, which nothing in the store calls.

31 of the 38 come from one store, and 24 of the 30 sampled. Without it, the rule has 7 findings, 1 of them TP. The TP-low findings are admin-only, setup-only or rare failure paths.

## Decision

The first grading gave 15 `TP` and 15 `TP-low` in the sample, an impact share of 50 %, and put U-LOG-01 in the default set, which 0.9.0 shipped. It said the margin was one verdict.

**Re-check after 0.9.0.** The `TP` verdicts were checked again against the code they name, and five of them were wrong:
- rows 28 to 30: the plugin wraps a controller in an `Adminhtml` namespace, so the calls run on an admin action, not on a storefront request;
- rows 26 and 27: the calls are in `catch` blocks of a newsletter observer around two services that catch the exceptions of their remote calls themselves: the block of row 26 is never reached, and that of row 27 only when the service's database query fails.

They are `TP-low` now. Rows 27, 28 and 30 are in the sample, which gives 12 `TP` and 18 `TP-low`, an impact share of 40 % (all 38: 18 and 20, 47 %). The other `TP` verdicts held: the ERP integrations send every order and credit memo through a queue consumer, the newsletter services fail on their normal path, the callers of the newsletter API catch only `Exception`, which the `Error` passes through, and the checkout observer runs for every order placed from an e-mail quote.

U-LOG-01 is experimental, at `error`: 30 sampled findings, all correct, but an impact share below the 50 % an experimental rule needs to enter the default set ([round 6](round-6.md)). The re-check redoes this round's decision on entry, so the drop to `warning` for a rule that stays in the default set does not apply. It runs only when it is enabled, on a project on 2.4.4 or later, or with such a `--target-version`, so on a project already there it reports calls that fail today.
