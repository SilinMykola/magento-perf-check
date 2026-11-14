# U-LOG-01: calibration grades, round 20

2 of 2 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Divante/PimcoreIntegration/Queue/Importer/AbstractImporter.php:109` | TP | success() runs on every accepted Pimcore push to the /V1/bridge/* REST endpoints, and the logger is the module's Stream or Graylog Logger extending Monolog\Logger (3.10 installed) without addInfo(), so the integration's normal path throws. |
| 2 | `app/code/Divante/PimcoreIntegration/Queue/Importer/AbstractImporter.php:149` | TP-low | critical() runs only when the bridge is disabled or the pushed data fails validation, a rare error path that already answers with a failure. |
