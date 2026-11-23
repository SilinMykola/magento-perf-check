# X-LAY-01: calibration grades, round 22

5 of 5 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Bonlineco/Checkout/view/frontend/layout/bonlineco_checkout.xml:25` | FP | An observer adds the handle only to `checkout_index_index`, and the block replaces core's checkout root, which is uncacheable already. |
| 2 | `app/code/Zwernemann/Withdrawal/view/frontend/layout/withdrawal_guest_search.xml:14` | TP-low | A static form page that could be cached, little visited and not linked by the module. |
| 3 | `app/code/Zwernemann/Withdrawal/view/frontend/layout/withdrawal_guest_view.xml:14` | FP | The page shows one order to the guest whose email the session holds: personal. |
| 4 | `app/code/Zwernemann/Withdrawal/view/frontend/layout/withdrawal_index_success.xml:15` | FP | The page reads an order id from the customer session and redirects without it: personal. |
| 5 | `app/code/Zwernemann/Withdrawal/view/frontend/layout/withdrawal_index_view.xml:14` | FP | The logged-in customer's own order page: personal. |
