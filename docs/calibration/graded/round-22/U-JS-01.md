# U-JS-01: calibration grades, round 22

1 of 1 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/MagestyApps/FixRules/view/frontend/web/js/action/select-payment-method-mixin.js:42` | TP | `mage/storage.put()` returns a jqXHR, and without jquery-migrate `.success()` throws on every payment method selection, leaving the checkout behind its loader. |
