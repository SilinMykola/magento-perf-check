# U-CLI-01: calibration grades, round 21

All 23 findings of the private stores of [round 15](../../round-15.md), with `--target-version=2.4.9`; the 13 findings of the corpus are graded in [round 7](../../round-7.md) and [round 20](../round-20/U-CLI-01.md). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 21](../../round-21.md). The stores are private, so each is named by a letter and each finding by what the command does.

Every command below extends `Symfony\Component\Console\Command\Command`, declares `execute()` without a return type, is registered in its module's `di.xml`, and its module is enabled in the store's `app/etc/config.php`. On Magento 2.4.9, whose Symfony Console 7.4 declares `execute(): int`, PHP refuses to declare such a class, so every `bin/magento` call fails, cron included.

| # | store | what | verdict |
|---|---|---|---|
| 1 | A | the store's own command: check whether a product configurator exists | TP |
| 2 | A | the store's own command: read a product configurator's data | TP |
| 3 | A | the store's own command: build a configurator page URL | TP |
| 4 | A | the store's own command: export an order to an ERP | TP |
| 5 | A | the store's own command: queue an order message | TP |
| 6 | A | the store's own command: check prices | TP |
| 7 | C | the store's own command: warm the GraphQL cache | TP |
| 8 | C | the store's own command: synchronize EAV attribute values | TP |
| 9 | C | the store's own command: send product alerts | TP |
| 10 | D | the store's own command: export the catalog to an ERP | TP |
| 11 | D | the store's own command: import credit memos | TP |
| 12 | D | the store's own command: import customers | TP |
| 13 | D | the store's own command: import invoices | TP |
| 14 | D | the store's own command: import order cancellations | TP |
| 15 | D | the store's own command: export orders | TP |
| 16 | D | the store's own command: import shipments | TP |
| 17 | D | the store's own command: warm the page cache | TP |
| 18 | D | the store's own command: compress images | TP |
| 19 | D | the store's own command: remove unused media | TP |
| 20 | E | a third-party reCAPTCHA module's command | TP |
| 21 | E | a third-party order deletion module's command | TP |
| 22 | E | the store's own command: add image alt texts | TP |
| 23 | E | a third-party command that generates product URL rewrites | TP |
