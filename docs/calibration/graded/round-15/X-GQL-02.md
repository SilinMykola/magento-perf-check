# X-GQL-02: calibration grades, round 15

All 7 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-12 by one grader and re-checked against the store's storefront code on 2026-11-15 for [round 22](../../round-22.md), which changed all seven from TP to TP-low; see [round 15](../../round-15.md). The stores are private, so each is named by a letter and each finding is described by what the code does, without its path.

| # | store | what | verdict | reason |
|---|---|---|---|---|
| 1 | C | the resolver of a category field | TP-low | Calls `CategoryRepository::get()` for each category of a list, but the store's storefront asks for the field through one category only. Re-checked on 2026-11-15: TP-low (was TP). |
| 2 | C | the resolver of a product field: the product itself | TP-low | Calls `ProductRepositoryInterface::get()` for each product of a list, but the storefront asks for the field only in the product page query, one product. Re-checked on 2026-11-15: TP-low (was TP). |
| 3 | C | the same resolver: the parent product | TP-low | A second product load per product, reached only through the same one-product query. Re-checked on 2026-11-15: TP-low (was TP). |
| 4 | C | the resolver of a product field: a first lookup | TP-low | A collection's `getFirstItem()` per product, but the storefront asks for the field on the product page and on cart items, a few at most. Re-checked on 2026-11-15: TP-low (was TP). |
| 5 | C | the same resolver: a second lookup | TP-low | A second collection query per product, on the same one product or few cart items. Re-checked on 2026-11-15: TP-low (was TP). |
| 6 | C | the same resolver: a third lookup | TP-low | A third collection query per product, on the same tiny lists. Re-checked on 2026-11-15: TP-low (was TP). |
| 7 | C | the resolver of a custom option value field | TP-low | `fetchRow()` for each option value of the list, but the storefront never asks for the field. Re-checked on 2026-11-15: TP-low (was TP). |
