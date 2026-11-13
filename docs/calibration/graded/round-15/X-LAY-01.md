# X-LAY-01: calibration grades, round 15

All 10 findings of the private stores; the other 5 are in [round 8](../../round-8.md). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-12 by one grader; see [round 15](../../round-15.md). The stores are private, so each is named by a letter and each finding is described by what the code does, without its path.

| # | store | what | verdict | reason |
|---|---|---|---|---|
| 1 | A | a totals block in a handle that only the customer's order view, print and order e-mail include | FP | Personal pages, uncacheable anyway. |
| 2 | A | the print of the current cart | FP | A personal page. |
| 3 | A | order buttons in a handle that only the customer's order, invoice, shipment and credit memo pages include | FP | Personal pages. |
| 4 | A | a block of requisition lists added to the `requisition_list` handle | TP | The store's installed Adobe Commerce requisition list module includes the handle on category, product and search pages (seen on 2026-11-12), so with requisition lists switched on none of them is cached. |
| 5 | A | an account page of a commerce module | FP | A personal page. |
| 6 | A | another account page of a commerce module | FP | A personal page. |
| 7 | B | a totals block in a handle that only the customer's order view, print and order e-mail include | FP | Personal pages. |
| 8 | D | a print page | FP | A personal page. |
| 9 | E | the captcha of the contact page, from a third-party module | TP-low | A public page with little traffic, as in round 8. |
| 10 | E | a payment redirect page | FP | A personal page of the checkout. |
