# Calibration, round 18

This round measures U-JS-01, an upgrade rule for the removal of jquery-migrate in Magento 2.4.6 (change add-jqxhr-migrate-rule). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Projects:** the Magento 2.4.9 core (`vendor/magento`), the 25 modules of round 1 (`app/code`), the extended part (`app/code`, `app/design`) of the calibration corpus ([`corpus.json`](corpus.json)), and the five private stores of [round 15](round-15.md), with the same rules on what is published.
- **Code:** 0.9.0-dev, U-JS-01 enabled, `--target-version=2.4.6`, on 2026-11-13, with the rule as committed: it was run again after the last change to the rule and the scanner (calls inside the arguments of another call, a `script` notice on a PCRE limit), with the same findings and no `script` notice. The runs are not stored.
- **Grading:** every finding was graded, once, by one grader. The verdicts are in [`graded/round-18/`](graded/round-18/).
- **Fix before grading:** the first run also reported a copy of jQuery 1.x that a private store's slider module ships for its own admin page. That copy has its own jqXHR, with `complete()`, so the finding was false. The rule now leaves out a file that defines jQuery itself (`jQuery.fn = jQuery.prototype`, in a template in any of its script blocks), and the run below has the fix.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [U-JS-01](../rules/U-JS-01.md) | error | 5 | 5 | 5 | 0 | 0 | 0 | 100 % | 100 % | experimental |

**U-JS-01.** The core and the 25 modules give no finding, the extended part 2 and the private stores 3, in three of the five stores. Every finding is a chain on an Ajax call whose `success()` or `complete()` throws from 2.4.6:
- two theme copies of an old core `Magento_Review/web/js/process-reviews.js`, whose `complete()` would stop the product reviews from appearing;
- a custom storefront script that validates a form through mage/storage;
- two admin scripts of extended-part modules, Mageplaza Blog (category path refresh) and KiwiCommerce AdminActivity (the revert dialog).

## Decision

U-JS-01 stays experimental at `error`: 5 graded findings, all correct and all breaking a feature, are fewer than the 10 a decision needs.
