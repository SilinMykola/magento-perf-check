# P-COL-03: calibration grades, round 22

1 of 1 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Magefan/HtmlSitemap/Block/Catalog/AbstractCategories.php:88` | TP-low | Loads every attribute of every active category where three are used, on a cacheable sitemap page, only on a cache miss. |
