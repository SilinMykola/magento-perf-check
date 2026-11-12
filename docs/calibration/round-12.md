# Calibration, round 12

This round measures J-DEP-01, the first rule on storefront JavaScript ([ADR 9](../adr/0009-storefront-javascript-scanned-by-the-xml-analyzer.md)) and the third of the Luma half of SPEC milestone M6: a dependency on `jquery/ui`, the whole jQuery UI library. The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** the Magento 2.4.9 core (`vendor/magento`), the 25 modules of round 1 (`app/code`) and the extended part (`app/code`, `app/design`) of the calibration corpus ([`corpus.json`](corpus.json)). Run on 2026-11-12 with the code of 0.6.0-dev, through the XML analyzer with J-DEP-01 only. Reading the storefront scripts took about a second per part.
- **Grading:** every finding was graded. The verdicts are in [`graded/round-12/`](graded/round-12/). Each was graded once, by one grader. As for T-05 in [round 9](round-9.md), `TP` means code on every page or on product, category or home pages, and `TP-low` code on the checkout, an account page or a page few visitors open.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [J-DEP-01](../rules/J-DEP-01.md) | warning | 5 | 5 | 2 | 2 | 1 | 0 | 80 % | 50 % | experimental |

**J-DEP-01.** The core gives no finding, the 25 modules 1 and the extended part 4.
- TP, 2: Clerk's live search script on every page, which uses no jQuery UI widget at all; Algolia's price slider on category and search pages.
- TP-low, 2: Taxjar's address suggestion modal on the customer address form; the grid library of Mageplaza Reports' dashboard page.
- FP, 1: Tweakwise's navigation slider fallback, which the module loads only on Magento older than 2.3.3; from 2.3.3 it uses `jquery-ui-modules/slider`.

Two scripts in `view/base` are also used by the admin, where the whole library is expected; on the storefront the finding still holds.

The rule leaves out core's two object keys named `jquery/ui`: the storefront map entry to `jquery/compat` and the base area shim entry.

## Decision

J-DEP-01 stays experimental, at `warning`. Its precision is 80 % and its impact share 50 %, but 5 graded findings are fewer than the 10 that round 1 needs for a decision. More Luma code, as SPEC milestone M8 plans, may give enough.
