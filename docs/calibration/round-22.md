# Calibration, round 22

This round grades the experimental rules on the `github2` part of the calibration corpus: 100 more GitHub modules with the topic `magento2-module` ([`corpus.json`](corpus.json), section `github2`; the [fourth held-out check](held-out-4.md) graded the default set on the same modules). It also grades the 16 X-GQL-02 findings of the Magento 2.4.9 core, which no round had graded, and re-checks the 7 X-GQL-02 verdicts of [round 15](round-15.md). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** the 100 modules of the `github2` part, analyzed alone, with every experimental rule enabled, on 2026-11-15 with the code of commit 7975005 ([`runs/github2/round-22.json`](runs/github2/round-22.meta.json)): 468 findings, `incomplete: false`.
- **Sample:** up to 12 findings per experimental rule, seed 20261115, drawn as in [round 20](round-20.md). P-DI-02, P-DI-04 and S-TPL-01 were left out: earlier rounds gave each at least 10 graded findings, almost all correct and none mattering, and more of the same cannot change their decision. The graded files are in [`graded/round-22/`](graded/round-22/).
- **Core:** the 16 X-GQL-02 findings of the core run with every experimental rule on, at the same commit ([`runs/core-x-gql-02-round-22.json`](runs/core-x-gql-02-round-22.json)), all graded, in the same file as X-GQL-02's `github2` findings.
- **Grading:** each finding was graded once, by one grader, from the code of the corpus; the false positives were checked again against the code. `TP-low` means the claim holds but the cost is negligible.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [P-COL-02](../rules/P-COL-02.md) | error | 9 | 9 | 0 | 2 | 7 | 0 | 22 % | 0 % | experimental |
| [P-COL-03](../rules/P-COL-03.md) | info | 1 | 1 | 0 | 1 | 0 | 0 | 100 % | 0 % | experimental |
| [P-COL-04](../rules/P-COL-04.md) | warning | 1 | 1 | 1 | 0 | 0 | 0 | 100 % | 100 % | experimental |
| [P-FPC-02](../rules/P-FPC-02.md) | warning | 3 | 3 | 0 | 3 | 0 | 0 | 100 % | 0 % | experimental |
| [P-LOOP-04](../rules/P-LOOP-04.md) | warning | 2 | 2 | 1 | 1 | 0 | 0 | 100 % | 50 % | experimental |
| [P-LOOP-12](../rules/P-LOOP-12.md) | warning | 6 | 6 | 0 | 6 | 0 | 0 | 100 % | 0 % | experimental |
| [S-FN-01](../rules/S-FN-01.md) | warning | 1 | 1 | 0 | 1 | 0 | 0 | 100 % | 0 % | experimental |
| [S-PERF-01](../rules/S-PERF-01.md) | info | 8 | 8 | 0 | 2 | 6 | 0 | 25 % | 0 % | experimental |
| [S-TPL-02](../rules/S-TPL-02.md) | warning | 6 | 6 | 0 | 6 | 0 | 0 | 100 % | 0 % | experimental |
| [T-04](../rules/T-04.md) | warning | 4 | 4 | 0 | 4 | 0 | 0 | 100 % | 0 % | experimental |
| [T-06](../rules/T-06.md) | info | 17 | 12 | 1 | 6 | 5 | 0 | 58 % | 14 % | experimental |
| [U-JS-01](../rules/U-JS-01.md) | error | 1 | 1 | 1 | 0 | 0 | 0 | 100 % | 100 % | experimental |
| [X-GQL-02](../rules/X-GQL-02.md) | warning | 19 | 19 | 6 | 11 | 2 | 0 | 89 % | 35 % | experimental |
| [X-LAY-01](../rules/X-LAY-01.md) | warning | 5 | 5 | 0 | 1 | 4 | 0 | 20 % | 0 % | experimental |
| [X-PLG-02](../rules/X-PLG-02.md) | info | 2 | 2 | 0 | 1 | 1 | 0 | 50 % | 0 % | experimental |

X-GQL-02's row counts its 3 `github2` findings and the 16 of the core.

- **X-GQL-02:** the three `github2` findings are one blog module's resolvers of a post's author, categories and tags, each loading per post of a paged list, without a cache: TP. In the core, 3 of 16 matter: a `COUNT` per category of a category list, an order load per order of `customer.orders` for its gift message, and a review summary per product of a listing. Eleven are TP-low: five stock fields that `Magento_InventoryGraphQl` redefines with its own resolvers in this install, `quantity`, whose resolver returns before its query under the default setting, gift messages of items, which are off by default, a downloadable link query, and the wishlists of a customer, one in Open Source. The two false positives are a resolver that reads the cart of each cart item, which all share one cart that `QuoteRepository` keeps, and the category tree, whose query runs only for the top-level `category` field because the nodes of `children` come with their children set.
- **Round 15's X-GQL-02 findings,** 7 in store C first graded TP, were re-checked against the store's storefront code before a decision, as the re-check of [round 19](round-19.md) taught: the per-element queries are real, but the storefront asks for these fields for one product or category, for a few cart items, or not at all. All seven are TP-low now. With them, X-GQL-02 has 26 graded findings: 6 TP, 18 TP-low and 2 FP, a precision of 92 % and an impact share of 25 %.
- **P-COL-02:** 7 of 9 are `getFirstItem()` after a filter on a column that the module keeps unique, a URL key, a slug, a subscriber or coupon id, which the rule cannot see.
- **T-06:** 5 of 12 are false: three images whose size a stylesheet of the module fixes, in a CSS or LESS file rather than the template (the known false positive of the rule page), an image in an email template, and one in a template that fails before it is output. One matters: the QR code that is the main content of a payment's success page.
- **S-PERF-01:** 6 of 8 are `array_merge()` calls that grow nothing, the known false positive of the sniff the rule runs.
- **X-LAY-01:** 4 of 5 are pages that are personal by design but whose handles the allowlist does not name: a checkout page added by an observer, and a module's guest, success and order pages that read the session.
- **U-CLI-01 and U-CLI-02** are in the default set since [round 21](round-21.md) and [round 17](round-17.md); their `github2` findings are graded in the held-out check.
- **The other rules:** one to six findings each, mostly TP-low; U-JS-01's one finding is a TP, a checkout mixin that calls `.success()` on a jqXHR and leaves the checkout behind its loader.

## Decision

No rule enters the default set. X-GQL-02 has the data and a precision of 92 %, but an impact share of 25 %, below the 50 % an experimental rule needs to enter the default set ([round 6](round-6.md)). The other rules have fewer than 10 graded findings in all rounds together, or a precision or an impact share below the threshold.

One rule changed after grading, without a change of status: X-GQL-02 leaves out `$cartRepository->get($cartItem->getQuoteId())` on a cart item, the false positive of `BundlePriceDetails`.
