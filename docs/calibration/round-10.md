# Calibration, round 10

This round measures X-LAY-04, the first rule of the Luma half of SPEC milestone M6: a script added to the page head by layout XML without `async` or `defer`. The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** the Magento 2.4.9 core (`vendor/magento`), the 25 modules of round 1 (`app/code`) and the extended part (`app/code`, `app/design`) of the calibration corpus ([`corpus.json`](corpus.json)). Run on 2026-11-11 with the code of 0.6.0-dev, through the XML analyzer with X-LAY-04 only; the rule reads layout files alone, so the other analyzers were not needed.
- **Grading:** every finding was graded. The verdicts are in [`graded/round-10/`](graded/round-10/). Each was graded once, by one grader. As for T-05 in [round 9](round-9.md), `TP` means a script on every page or on product, category or home pages, and `TP-low` a script on the cart, the checkout, an account page or a page few visitors open.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [X-LAY-04](../rules/X-LAY-04.md) | info | 14 | 14 | 1 | 13 | 0 | 0 | 100 % | 7 % | experimental |

**X-LAY-04.** The core gives 3 findings, the 25 modules none and the extended part 11.
- TP, 1: Buckaroo's Apple Pay SDK from Apple's CDN on every product page.
- TP-low, 13: the same SDK on the cart and the checkout; core's scripts for editing a cart item; Mageplaza Blog's editor and tree select scripts on the author pages; Mollie's components script on the Hyvä checkout.
- No false positive. Whether the payment providers' documentation asks to load their scripts synchronously was not checked.

The rule leaves out 4 head scripts in the corpus that it would otherwise report: core's `require.js` and `sri.js` (twice), which core never defers, and an IE 11 polyfill behind `ie_condition`.

## Decision

X-LAY-04 stays experimental. Its precision is 100 %, but only 1 of 14 findings matters, below the impact share of 50 % that an experimental rule needs to enter the default set (round 6). As round 6 applies the impact share, it reports at `info`, not `warning`. Most head scripts in the corpus are on pages without full page cache. More Luma code, as SPEC milestone M8 plans, may change that.
