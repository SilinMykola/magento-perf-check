# X-GQL-02: calibration grades, round 15

All 7 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-12 by one grader; see [round 15](../../round-15.md). The stores are private, so each is named by a letter and each finding is described by what the code does, without its path.

| # | store | what | verdict | reason |
|---|---|---|---|---|
| 1 | C | the resolver of a category field | TP | Calls `CategoryRepository::get()` for each category of the list. |
| 2 | C | the resolver of a product field: the product itself | TP | Calls `ProductRepositoryInterface::get()` for each product of the list; products loaded by a collection are not in the repository's cache. |
| 3 | C | the same resolver: the parent product | TP | A second `ProductRepositoryInterface::get()` per product. |
| 4 | C | the resolver of a product field: a first lookup | TP | A collection's `getFirstItem()` per product. |
| 5 | C | the same resolver: a second lookup | TP | A second collection query per product. |
| 6 | C | the same resolver: a third lookup | TP | A third collection query per product. |
| 7 | C | the resolver of a custom option value field | TP | `fetchRow()` for each option value of the list. |
