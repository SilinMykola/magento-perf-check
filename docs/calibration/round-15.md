# Calibration, round 15

This round grades the experimental rules on five private Magento stores, which hold the custom storefront code the public corpus lacks (change promote-jquery-ui-rule). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Projects:** five Magento stores whose code is not public, the same as in [round 14](round-14.md), on Magento 2.3.3 to 2.4.5. Each store is named by a letter. Only counts and neutral descriptions of the graded code are published: no store, client or vendor name of custom code, path or file content. The runs and the verdicts with real paths stay local.
- **Code:** 0.7.0-dev at commit 522615c, every experimental rule enabled, default paths (`app/code`, `app/design`), on 2026-11-12.
- **Grading:** each finding was graded once, by one grader, by reading the code around it and following where it is used: which pages include a layout handle, which templates or configuration load a script, which resolvers return lists. J-DEP-01 gives 45 findings; 30 of them were drawn with the shuffle of `bin/calibration/sample` and the seed 20261112. X-LAY-01 and X-GQL-02 were graded in full. The verdicts are in [`graded/round-15/`](graded/round-15/).
- **Other rules:** they were not graded.
  - P-COL-04, P-LOOP-04 and P-LOOP-06 get 4, 2 and 1 findings, which leaves each under the 10 a decision needs; T-03 gets its first 2.
  - J-DEP-02 gets 2, which would bring it to 10, but its impact share could rise at most to 30 % (3 of 10), below the 50 % it needs to enter the default set, so its decision of round 13 stands.
  - C-02 gives the 2 findings already graded in round 14.
  - S-FN-01 and S-DI-01 give none; S-TPL-02 did not run, as the coding standard installed in the stores lacks its sniff, and U-CLI-01 did not run, as no store is on Magento 2.4.9.
  - The rest already have 10 or more graded findings and keep their decisions.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [J-DEP-01](../rules/J-DEP-01.md) | warning | 50 | 35 | 24 | 6 | 5 | 0 | 86 % | 80 % | default set |
| [X-LAY-01](../rules/X-LAY-01.md) | warning | 15 | 15 | 3 | 4 | 8 | 0 | 47 % | 43 % | experimental |
| [X-GQL-02](../rules/X-GQL-02.md) | warning | 7 | 7 | 7 | 0 | 0 | 0 | 100 % | 100 % | experimental |

The J-DEP-01 and X-LAY-01 rows add the grades of [round 12](round-12.md) and [round 8](round-8.md) to those of this round: the findings column counts the corpus findings of those rounds and the private ones.

**J-DEP-01.** 45 findings in three of the five stores: 38 in store D, 6 in E and 1 in A; B and C give none. The sample of 30 has 23 findings of D, 6 of E and 1 of A:
- TP, 22: scripts that need only the widget factory (`$.widget`), or no jQuery UI at all, and that run on product pages, category pages, or every page (the header, the menus, the store switcher, a mixin of the menu widget). Also a core wishlist script overridden by a theme, and a product slider's swatch script.
- TP-low, 4: scripts of a single CMS page (two), of the customer account forms and of blog pages.
- FP, 4, all in store E and all scripts that are never loaded: three theme overrides of layered navigation scripts of modules that the store does not have installed, and a `shim` entry of a library that nothing in the store asks for.

**X-LAY-01.** 10 findings in four stores:
- TP, 1: a custom module adds a block with `cacheable="false"` to the `requisition_list` handle. The store's installed Adobe Commerce requisition list module includes that handle on category, product and search pages (seen on 2026-11-12), so with requisition lists switched on none of those pages is cached.
- TP-low, 1: the captcha of the contact page, as in round 8.
- FP, 8: pages that are personal by design: handles that only the customer's order pages and order e-mails include (three), a print of the current cart, two account pages of commerce modules, a print page, and a payment redirect.

**X-GQL-02.** 7 findings, all in store C, all correct: the resolvers of fields on products, categories and custom option values call `ProductRepositoryInterface::get()`, `CategoryRepository::get()`, a collection's `getFirstItem()` or `fetchRow()` once per element of the list they are part of.

## Timing

J-DEP-01 in the default set means that a default run reads the storefront scripts. Measured on 2026-11-12 on a laptop (macOS, PHP 8.5), with `php bin/magento-perf-check --project=var/corpus/magento --paths=vendor/magento --format=json`, the Magento 2.4.9 core of the corpus, without and with `--config=<file>`, a file that sets `J-DEP-01: enabled: true`, run twice in turn:

| Run | Without J-DEP-01 | With J-DEP-01 |
|---|---|---|
| 1 (the first without the rule had a cold PHPStan cache) | 84.5 s | 4.4 s |
| 2 (warm cache) | 2.7 s | 3.2 s |

Both runs of the second pair report the same 964 findings: the core has no J-DEP-01 finding.

## Decision

- **J-DEP-01** joins the default set at `warning`: its precision of 86 % is above the 75 % a `warning` rule needs, and its impact share of 80 % above the 50 % an experimental rule needs to enter the default set ([round 6](round-6.md)).
  - 23 of its 35 graded findings come from store D, whose own scripts use the pattern throughout. Without them, the grades are 5 TP, 2 TP-low and 5 FP: a precision of 58 % on 12 findings.
  - Those 5 false positives are all scripts that are never loaded: four in store E and, in round 12, a fallback for Magento older than 2.3.3. Every script that is loaded and depends on `jquery/ui` was graded correct.
  - The rule page lists scripts that nothing loads as a known false positive.
- **X-LAY-01** stays experimental: a precision of 47 %. Its false positives are listed in the [follow-ups](follow-ups.md).
- **X-GQL-02** stays experimental: 7 graded findings, all correct, are fewer than the 10 a decision needs, and all come from one store.
