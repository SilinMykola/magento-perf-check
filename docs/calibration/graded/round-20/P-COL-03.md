# P-COL-03: calibration grades, round 20

1 of 1 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Divante/PimcoreIntegration/Model/CategoryRepository.php:59` | FP | getByPimId() is a repository method returning one full category to callers such as UpdateCategoryAction that update and save it, so all attributes are needed and only one row is read. |
