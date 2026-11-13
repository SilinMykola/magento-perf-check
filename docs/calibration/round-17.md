# Calibration, round 17

This round measures U-CLI-02, an upgrade rule for the move to Symfony Console 5.4 in Magento 2.4.4-p12, 2.4.5-p11 and 2.4.6 (change add-console-execute-int-rule). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Projects:** the Magento 2.4.9 core (`vendor/magento`), the 25 modules of round 1 (`app/code`), the extended part (`app/code`, `app/design`) of the calibration corpus ([`corpus.json`](corpus.json)), and the five private stores of [round 15](round-15.md), with the same rules on what is published.
- **Code:** 0.8.0-dev, U-CLI-02 and U-CLI-01 enabled, `--target-version=2.4.6`, on 2026-11-13. The runs are not stored.
- **Grading:** every finding was graded. The verdicts are in [`graded/round-17/`](graded/round-17/). Each was graded once, by one grader. For each command, `di.xml` was checked for its registration: a command registered nowhere never runs.
- **Other rules:** they were not graded.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [U-CLI-02](../rules/U-CLI-02.md) | error | 21 | 21 | 21 | 0 | 0 | 0 | 100 % | 100 % | default set |

**U-CLI-02.** The core gives no finding, the 25 modules none, the extended part 5 in 4 modules, and the private stores 16 in four of the five stores. Every finding is a command registered in `di.xml` whose `execute()` ends without a `return` (18) or has a `return;` (3), so on 2.4.6 or later it fails with a `TypeError` each time it runs. Seven of them come from third-party modules, among them a cron runner, which would stop running the store's cron jobs. 9 of the 21 come from one private store.

## Decision

U-CLI-02 joins the default set at `error`: 21 graded findings, all correct and all mattering. It runs only on a project on 2.4.4-p12, 2.4.5-p11, 2.4.6 or later, or with such a `--target-version`, so on a project already there it reports commands that fail today.
