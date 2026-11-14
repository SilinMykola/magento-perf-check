# X-PLG-02: calibration grades, round 20

2 of 2 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Magefan/LazyLoad/Plugin/BlockPlugin.php:79` | FP | An HTML post-processor for a configurable list of block names, which cannot be targeted by class, and in the default "selected blocks" mode it returns after a few request checks, a strpos and an in_array over about 12 names (unsure: in the optional "all blocks" mode it runs preg_replace over every block's HTML on FPC misses). |
| 2 | `app/code/Siteation/DebugBar/Plugin/View/BlockPlugin.php:34` | FP | A profiler that must time every block and returns $proceed() after one boolean property check when the debug bar is not collecting. |
