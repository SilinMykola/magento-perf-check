# Calibration, round 11

This round measures X-LAY-05, the second rule of the Luma half of SPEC milestone M6: a module stylesheet added to the head of every storefront page by layout XML. The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** the Magento 2.4.9 core (`vendor/magento`), the 25 modules of round 1 (`app/code`) and the extended part (`app/code`, `app/design`) of the calibration corpus ([`corpus.json`](corpus.json)). Run on 2026-11-11 with the code of 0.6.0-dev, through the XML analyzer with X-LAY-05 only; the rule reads layout files alone, so the other analyzers were not needed.
- **Grading:** every finding was graded. The verdicts are in [`graded/round-11/`](graded/round-11/). Each was graded once, by one grader. Every finding is on every page by construction, so `TP` means styles that most pages do not use, and `TP-low` styles for an element that is on every page, where moving them into the theme's stylesheet only saves one request. The size of each file was not weighed: how much one more small stylesheet delays the first render was not measured.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [X-LAY-05](../rules/X-LAY-05.md) | warning | 18 | 18 | 15 | 3 | 0 | 0 | 100 % | 83 % | default set |

**X-LAY-05.** The core gives 2 findings, the 25 modules 1 and the extended part 15.
- TP, 15: the styles of the UI form file uploader and of the date picker in the core; banner and product slider styles of Mageplaza, among them a 58 KB animation library; blog, payment and layered navigation styles; the popup shown after adding to the cart; Trustpilot styles that no template of the module uses.
- TP-low, 3: Magewire's component states, MageWorx's header search autocomplete and Mageplaza Share Cart's button in the mini cart.
- No false positive.

The rule leaves out 3 stylesheets in the corpus that a theme adds to every page: the blank theme's `styles-m.css` and `styles-l.css` (its `print.css` has `media="print"` and would be left out anyway) and the Hyvä default theme's `styles.css`.

## Decision

X-LAY-05 joins the default set at `warning`: 18 graded findings, 100 % precision and an impact share of 83 %. Its sources are the Adobe frontend documentation (level A) and core code (level B).
