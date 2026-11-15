# S-FN-01: calibration grades, round 22

1 of 1 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/OxCom/MagentoCurrencyServices/Model/Currency/Import/AbstractSource.php:79` | TP-low | `sleep()` of a configured delay before each currency pair, only in the admin rate import and its cron, never in a storefront request. |
