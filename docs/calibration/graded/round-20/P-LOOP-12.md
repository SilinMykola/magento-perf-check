# P-LOOP-12: calibration grades, round 20

5 of 5 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Divante/PimcoreIntegration/Cron/ProductPublisher.php:82` | TP-low | getList() runs once per store in a 15-minute cron, a short bounded loop where each query is store-scoped after setCurrentStore() and cannot be merged into one. |
| 2 | `app/code/Divante/PimcoreIntegration/Cron/ProductPublisher.php:83` | TP-low | getList() runs once per store in a 15-minute cron, a short bounded loop where each query is store-scoped after setCurrentStore() and cannot be merged into one. |
| 3 | `app/code/Magiccart/Magicmenu/Block/Menu.php:402` | FP | The load() on line 898 is inside if ($count), and this call omits the $count parameter whose default is '', so no query runs. |
| 4 | `app/code/Magiccart/Magicmenu/Block/Menu.php:479` | FP | The load() on line 898 is inside if ($count), and this call omits the $count parameter whose default is '', so no query runs. |
| 5 | `app/code/Magiccart/Magicmenu/Block/Menu.php:905` | FP | The recursive call omits $count (default ''), so the load() under if ($count) on line 898 never runs, and no caller anywhere passes $count. |
