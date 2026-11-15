# X-PLG-02: calibration grades, round 22

2 of 2 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Backendorf/Installment/Plugin/Block/Price.php:19` | TP-low | A frontend plugin on `AbstractBlock` that acts only for `product.info.price`; for every other block it costs a string comparison. |
| 2 | `app/code/Yireo/NewRelic2/Model/Plugin/AbstractBlock.php:54` | FP | A New Relic profiler that must see every block, the rule's known false positive; it returns quickly when off. |
