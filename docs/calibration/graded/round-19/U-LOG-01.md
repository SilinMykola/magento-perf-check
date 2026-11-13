# U-LOG-01: calibration grades, round 19

All 38 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-13 by one grader, and the `TP` verdicts re-checked the same day after 0.9.0, which changed rows 26 to 30; see [round 19](../../round-19.md). Private stores are named by their letter of [round 15](../../round-15.md), and their code is described, not listed. Every receiver is a logger class that extends `Monolog\Logger`, directly or through Magento's `Monolog`, and does not declare the method; in row 38 it is `Monolog\Logger` itself, reached through `parent::`.

| # | where | what | verdict | reason |
|---|---|---|---|---|
| 1 | store A | a custom company shipping delete service, when the shipping type is unknown, just before it throws | TP-low | the store is on 2.4.4 already: an Error replaces the intended exception, on a rare path |
| 2 | store A | the same service, when the entity is missing, just before it throws | TP-low | as above |
| 3 | store A | a custom ERP integration, logging a template error while rendering order data | TP | the store is on 2.4.4 already: the catch block turns a template error into a fatal Error while order data is built |
| 4 | store C | an ERP integration sending a credit memo, on its normal path | TP | `warn()` runs on every send, so each credit memo send fails |
| 5 | store C | an ERP integration sending a credit memo, on its normal path | TP | `warn()` runs on every send, so each credit memo send fails |
| 6 | store C | an ERP integration sending a credit memo, on its normal path | TP | `warn()` runs on every send, so each credit memo send fails |
| 7 | store C | an ERP integration sending a credit memo, on its normal path | TP | `warn()` runs on every send, so each credit memo send fails |
| 8 | store C | an ERP integration sending a credit memo, on its normal path | TP | `warn()` runs on every send, so each credit memo send fails |
| 9 | store C | an ERP integration sending an order, on its normal path | TP | `warn()` runs on every send, so each order send fails |
| 10 | store C | an ERP integration sending an order, on its normal path | TP | `warn()` runs on every send, so each order send fails |
| 11 | store C | an ERP integration sending an order, on its normal path | TP | `warn()` runs on every send, so each order send fails |
| 12 | store C | an ERP integration sending an order, on its normal path | TP | `warn()` runs on every send, so each order send fails |
| 13 | store C | a custom options import, run by a queue consumer, on a failure | TP-low | an admin import: a failure ends in a fatal Error instead of a log line |
| 14 | store C | a custom options import, run by a queue consumer, on a failure | TP-low | an admin import: a failure ends in a fatal Error instead of a log line |
| 15 | store C | a custom options import, run by a queue consumer, on a failure | TP-low | an admin import: a failure ends in a fatal Error instead of a log line |
| 16 | store C | a custom options import, run by a queue consumer, on a failure | TP-low | an admin import: a failure ends in a fatal Error instead of a log line |
| 17 | store C | a custom options import, run by a queue consumer, on a failure | TP-low | an admin import: a failure ends in a fatal Error instead of a log line |
| 18 | store C | a custom options import, run by a queue consumer, on a failure | TP-low | an admin import: a failure ends in a fatal Error instead of a log line |
| 19 | store C | a custom price tiers module, after an admin product, swatch or tier save, on a failure | TP-low | admin saves only: a failure ends in a fatal Error instead of a log line |
| 20 | store C | a custom price tiers module, after an admin product, swatch or tier save, on a failure | TP-low | admin saves only: a failure ends in a fatal Error instead of a log line |
| 21 | store C | a custom price tiers module, after an admin product, swatch or tier save, on a failure | TP-low | admin saves only: a failure ends in a fatal Error instead of a log line |
| 22 | store C | a custom newsletter integration, when the remote service refuses a subscribe or unsubscribe | TP | the services that call it catch only `Exception` and go on, so the `Error` turns a refusal into a fatal on a storefront newsletter action |
| 23 | store C | a custom newsletter integration, when the remote service refuses a subscribe or unsubscribe | TP | as above |
| 24 | store C | a custom integration observer after an order is placed from an e-mail quote | TP | runs after every order placed from an e-mail quote: the observer ends in a fatal Error |
| 25 | store C | a custom integration observer after an order is placed from an e-mail quote, when the remote call fails | TP | a failure of the remote call becomes a fatal Error after the order is placed |
| 26 | store C | a custom newsletter observer after a subscriber is saved, in a `catch` around an unsubscribe service | TP-low | re-checked, was TP: the service catches every exception itself, so the block is not reached |
| 27 | store C | a custom newsletter observer after a subscriber is saved, in a `catch` around a subscribe service | TP-low | re-checked, was TP: the service catches the exceptions of its remote call itself, so only a database error reaches the block |
| 28 | store C | a custom integration plugin after an admin controller sends a quote proposal | TP-low | re-checked, was TP: the plugin wraps an `Adminhtml` controller, so it runs on an admin action only |
| 29 | store C | a custom integration plugin after an admin controller sends a quote proposal, on a failure | TP-low | re-checked, was TP: as above |
| 30 | store C | a custom integration plugin after an admin controller sends a quote proposal, on a failure | TP-low | re-checked, was TP: as above |
| 31 | store C | a custom newsletter service, on subscribe and unsubscribe | TP | `addInfo()`/`addDebug()` on the normal path: every call fails |
| 32 | store C | a custom newsletter service, on subscribe and unsubscribe | TP | `addInfo()`/`addDebug()` on the normal path: every call fails |
| 33 | store C | a custom newsletter service, on subscribe and unsubscribe | TP | `addInfo()`/`addDebug()` on the normal path: every call fails |
| 34 | store C | a custom newsletter service, on subscribe and unsubscribe | TP | `addInfo()`/`addDebug()` on the normal path: every call fails |
| 35 | store D | a custom store setup service run by a setup patch, creating a store, on a failure | TP-low | runs once, during setup:upgrade |
| 36 | store D | a custom store setup service run by a setup patch, deleting default entities | TP-low | runs once, during setup:upgrade |
| 37 | store D | a custom store setup service run by a setup patch, deleting default entities | TP-low | runs once, during setup:upgrade |
| 38 | store B | a custom ERP integration's own logger, whose addDebug() override calls parent::addDebug() | TP-low | the store is on 2.4.5 already, so the override fails with an Error, but nothing in the store calls addDebug() on it |
