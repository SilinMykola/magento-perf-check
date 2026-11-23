# P-LOOP-12: calibration grades, round 22

6 of 6 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Crealoz/EasyAudit/Cron/Audit.php:39` | TP-low | One primary-key load per pending audit request, next to a whole-codebase audit per request. |
| 2 | `app/code/Gaiterjones/BuyXGetY/Model/BuyXGetY.php:247` | TP-low | A cached product load per qualifying rule of a few, followed by a cart save that costs far more. |
| 3 | `app/code/O2TI/SigepWebCarrier/Cron/PlpCompleteProcessCron.php:133` | TP-low | Re-reads a shipping manifest after a stage wrote it; the class is not registered as a cron, so it never runs. |
| 4 | `app/code/O2TI/SigepWebCarrier/Cron/PlpFinalizationCron.php:122` | TP-low | Re-reads each of a few open manifests after a stage wrote it, next to remote API calls. |
| 5 | `app/code/O2TI/SigepWebCarrier/Cron/PlpProcessingCron.php:113` | TP-low | The same pattern over a handful of open manifests. |
| 6 | `app/code/RicardoMartins/PagSeguro/Cron/UpdatePendingOrderStatus.php:97` | TP-low | An order load only for cancelled transactions whose state changed, each already behind a remote call. |
