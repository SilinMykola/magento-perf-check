# P-COL-02: calibration grades, round 20

1 of 1 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Divante/PimcoreIntegration/Model/CategoryRepository.php:66` | FP | The collection is filtered on pimcore_id, the external PIM id that maps to one Magento category, so at most one row is loaded. |
