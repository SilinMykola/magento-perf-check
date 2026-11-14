# X-LAY-01: calibration grades, round 20

1 of 1 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/FriendsOfHyva/ReactCheckout/view/frontend/layout/hyvareactcheckout_reactcheckout_index.xml:39` | FP | The handle is the module's own checkout page (route hyvareactcheckout), a personal page that is uncacheable by design like the allowlisted checkout_ and hyva_checkout_ handles. |
