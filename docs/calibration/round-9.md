# Calibration, round 9

This round measures T-05 and T-06, the first rules of SPEC milestone M6 (storefront code, Hyvä first). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** the Magento 2.4.9 core (`--paths=vendor/magento`), the 25 modules of round 1 (`--paths=app/code`) and the extended part (`--paths=app/code,app/design`) of the calibration corpus ([`corpus.json`](corpus.json)), with T-05 and T-06 enabled. Run on 2026-11-11 with the code of 0.5.0-dev.
- **Grading:** every T-05 finding was graded. For T-06, 30 findings were sampled with `random.seed(20261111)` over the 106 findings of a first run, sorted by part, file and line. A review then made the rules skip tags in HTML comments, in the bodies of scripts and in nested `<template>` elements; that removed 3 T-06 findings, none of them sampled. The counts below are those of the final run. The verdicts are in [`graded/round-9/`](graded/round-9/). Each was graded once, by one grader. `TP` means the finding matters: for T-05 a script on every page or on product, category or home pages; for T-06 a large image in the content of a page. `TP-low` means the claim holds but the cost is small: a script only on checkout, a blog page or a page a few modules include; a small icon, logo, avatar or loader.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [T-05](../rules/T-05.md) | warning | 18 | 18 | 13 | 5 | 0 | 0 | 100 % | 72 % | default set |
| [T-06](../rules/T-06.md) | info | 103 | 30 | 5 | 21 | 4 | 0 | 87 % | 19 % | experimental |

**T-05.** The core gives 1 finding, the 25 modules 4 and the extended part 13.
- TP, 13:
  - on every page, through `default.xml`: Adyen's data collection script, Mailchimp's pixel consent loader, Reviews.co.uk's rating snippet and Tweakwise's analytics;
  - on product, category, cart and checkout pages: PayU's credit widget;
  - on product pages: six scripts of the Reviews.co.uk product and question widgets;
  - in Mageplaza Product Slider's Hyvä templates, a slider library from a CDN, twice.
- TP-low, 5: Magefan Blog's tag cloud in the blog sidebar; Mageplaza Core's loader for the pages that include its `mp_add_js_css` handle; Payone's two client scripts on the checkout page; the core Google AdWords conversion script on the order success page.
- No false positive. Whether the payment providers' documentation asks to load their scripts synchronously was not checked; a script that must block the page belongs in the baseline (rule page, known false positives).

**T-06.** The core gives 11 findings, the 25 modules 17 and the extended part 76.
- TP, 5: a Mageplaza Banner Slider banner, the featured image of a blog post in Mageplaza Blog's Hyvä template and in Magefan Blog's modern template, Tweakwise's visual navigation images in the product list, and the header image of a Smile Store Locator store page.
- TP-low, 21: payment marks and logos of PayPal and Payment Services, Luma's CSS preloader and Mageplaza Ajax Layer's loader, Clerk's popup image, card type icons in the customer account, blog author avatars, RSS, share and writing icons, comment avatars, gallery thumbnails, related and recent post images.
- FP, 4:
  - two Dotdigital templates that render content for an email, where layout shift does not apply;
  - a Mageplaza Blog widget whose image has `style="height: 220px"`, so its box does not change;
  - a Yireo Webp2 template under `view/frontend/templates/test`, used only by the module's tests.

## Decisions

- T-05 joins the default set at `warning`: 18 graded findings, 100 % precision and an impact share of 72 %. Its sources are the Hyvä documentation (level A).
- T-06 stays experimental. It passes the precision threshold, but with an impact share of 19 % it would be an `info` rule whose findings are mostly icons and logos, which adds work to every report for little gain (round 6).
