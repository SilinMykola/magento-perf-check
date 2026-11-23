# P-FPC-02: calibration grades, round 22

3 of 3 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Adyen/ExpressCheckout/Block/Buttons/AbstractButton.php:168` | TP-low | On the cacheable product page the button reads the depersonalized, empty quote and the result is cached for everyone, but its script loads the real state over AJAX. |
| 2 | `app/code/Adyen/ExpressCheckout/Block/Buttons/AbstractButton.php:178` | TP-low | The claim holds in code, but nothing calls `getAmount()`. |
| 3 | `app/code/Adyen/ExpressCheckout/Block/Buttons/AbstractButton.php:230` | TP-low | On a depersonalized page the empty quote falls back to the store's base currency, and the script takes the currency from its AJAX response. |
