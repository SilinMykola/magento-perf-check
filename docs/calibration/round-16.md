# Calibration, round 16

This round measures X-LAY-01 again after it started to follow how layout handles include each other (change trace-layout-handle-includes). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Projects:** the calibration corpus ([`corpus.json`](corpus.json)) with the Magento 2.4.9 core, and the five private stores of [round 15](round-15.md), with the same rules on what is published.
- **Code:** 0.7.0-dev at commit f5c6fd3, X-LAY-01 enabled, on 2026-11-13.
- **Grading:** no new finding appeared, compared finding by finding with the runs before the change. Every remaining finding carries its verdict from [round 8](round-8.md) (corpus and core) or [round 15](round-15.md) (private stores, rows 2, 4, 8, 9 and 10 of its [grades](graded/round-15/X-LAY-01.md)). The runs are not stored: `runs/` keeps those of rounds 1 and 6.
- **Other rules:** they were not run; their earlier results stand.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [X-LAY-01](../rules/X-LAY-01.md) | warning | 10 | 10 | 3 | 4 | 3 | 0 | 70 % | 43 % | experimental |

**X-LAY-01.** The corpus and the core keep their 5 findings. The private stores go from 10 findings to 5: the three handles that only the customer's order pages include, and the theme files of two customer account pages, are no longer reported. What remains on the private stores is the TP and the TP-low of round 15, and three false positives: a print of the current cart, a print page and a payment redirect, pages that are personal by what they show but whose handles nothing marks as personal.

## Decision

X-LAY-01 stays experimental: a precision of 70 % on 10 graded findings is below the 75 % a `warning` rule needs, and its impact share of 43 % is below the 50 % an experimental rule needs to enter the default set ([round 6](round-6.md)). A project can add the handles of such pages to `layout.allowlistHandles`.
