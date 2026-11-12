# Calibration, round 13

This round measures J-DEP-02, the second rule on storefront JavaScript: a module in the top-level `deps` of a storefront `requirejs-config.js`, which RequireJS loads on every page. The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** the Magento 2.4.9 core (`vendor/magento`), the 25 modules of round 1 (`app/code`) and the extended part (`app/code`, `app/design`) of the calibration corpus ([`corpus.json`](corpus.json)). Run on 2026-11-12 with the code of 0.6.0-dev, through the XML analyzer with J-DEP-02 only.
- **Grading:** every finding was graded. The verdicts are in [`graded/round-13/`](graded/round-13/). Each was graded once, by one grader. Every finding is on every page by construction, so `TP` means a module that most pages do not use, also one that is downloaded on every page but does nothing by default, and `TP-low` one that every page needs.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [J-DEP-02](../rules/J-DEP-02.md) | info | 8 | 8 | 1 | 7 | 0 | 0 | 100 % | 13 % | experimental |

**J-DEP-02.** The core gives 6 findings, the 25 modules 1 and the extended part 1.
- TP, 1: Algolia's click and conversion tracking, loaded with its analytics library on every page, while the features that use it are off by default.
- TP-low, 7: core's form key provider, `mage/common`, `mage/dataPost`, `mage/bootstrap`, translation dictionary and UI component runner; Yireo Webp2's WebP class on `body`. Every page needs them.
- No false positive. The rule leaves out the `deps` of `shim` entries, which load nothing by themselves.

It misses one top-level dependency: core Theme's `config.deps.push('mage/polyfill')`, added in code when the browser has no `localStorage`.

## Decision

J-DEP-02 stays experimental at `info`. Its precision is 100 %, but only 1 of its 8 findings is a module that most pages do not use: the corpus puts mostly global behavior in the top-level `deps`, as the Adobe page describes it. With an impact share of 13 %, it reports at `info`. It stays available as a check of a project's own modules.
