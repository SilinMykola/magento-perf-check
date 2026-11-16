# P-LOOP-04: calibration grades, round 22

2 of 2 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/ElNino/ProductLinksNavigator/Helper/ProductUrlGenerator.php:91` | TP-low | One URL rewrite lookup per store for one product, in admin only; the full product load per store costs more. |
| 2 | `app/code/Magefan/HtmlSitemap/Block/Catalog/AbstractProducts.php:115` | TP | The product sitemap page loads every visible product without `addUrlRewrite()`, one URL rewrite query per product on each cache miss. |
