# S-PERF-01: calibration grades, round 20

12 of 17 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Bodak/CheckoutCustomForm/Model/Checkout/LayoutProcessor/Plugin.php:96` | FP | `array_merge($formField['config'], $field['config'])` merges into `$formField`, which is rebuilt in every iteration over three fixed fields, so nothing grows across iterations. |
| 2 | `app/code/Bodak/CheckoutCustomForm/Model/Checkout/LayoutProcessor/Plugin.php:100` | FP | `$formField['validation']` starts as `[]` in each iteration and gets one merge, so the merge is not quadratic and the loop has at most three fields. |
| 3 | `app/code/MagePal/CustomShippingRate/Helper/Data.php:183` | FP | `$row = array_merge($requiredFields, $row)` applies fixed defaults to each row by reference and does not grow a result across iterations. |
| 4 | `app/code/Swissup/SearchMysqlLegacy/Model/Indexer/IndexerHandler.php:214` | TP-low | `$this->fields` grows by merge over the indexer's fieldsets, but this runs once per handler construction over a handful of configured fieldsets. |
| 5 | `app/code/Swissup/SearchMysqlLegacy/Model/Search/FiltersExtractor.php:38` | TP-low | `$filters` grows by merge over the should-clauses of a search request query, which hold only a few filters per request. |
| 6 | `app/code/Swissup/SearchMysqlLegacy/Model/Search/FiltersExtractor.php:41` | TP-low | `$filters` grows by merge over the must-not clauses of a search query tree that is small and bounded by the request config. |
| 7 | `app/code/Swissup/SearchMysqlLegacy/Model/Search/FiltersExtractor.php:73` | TP-low | The recursive merge of nested bool filters grows `$filters`, but a search request has only a few nested filters. |
| 8 | `app/code/Swissup/SearchMysqlLegacy/Model/Search/FiltersExtractor.php:80` | TP-low | The recursive merge over should filters grows `$filters` over a short, request-config-bounded list. |
| 9 | `app/code/Swissup/SearchMysqlLegacy/Model/Search/FiltersExtractor.php:87` | TP-low | The recursive merge over must-not filters grows `$filters` over a short, request-config-bounded list. |
| 10 | `app/code/Swissup/SearchMysqlLegacy/SearchAdapter/Mysql/Aggregation/Builder/Dynamic.php:80` | FP | `array_merge(['value' => $rangeName], $value)` writes into the keyed slot `$resultData[$rangeName]` per item, so no result is merged back into itself. |
| 11 | `app/code/Swissup/SearchMysqlLegacy/SearchAdapter/Mysql/Aggregation/Builder/Range.php:93` | TP-low | `$casesResults` grows by merge per range, but a range bucket has only a few configured ranges. |
| 12 | `app/code/Swissup/SearchMysqlLegacy/SearchAdapter/Mysql/Aggregation/Builder/Range.php:100` | TP-low | `$casesResults` grows by merge per range of a bucket with only a few configured ranges, so the cost is negligible. |
