# P-COL-04: calibration grades, round 22

1 of 1 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/O2TI/SigepWebCarrier/Cron/UpdateTrackingStatus.php:76` | TP | A daily cron walks every completed order of the store's history without a page size, a set that only grows. |
