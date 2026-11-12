# J-DEP-01: calibration grades, round 15

30 of 45 findings (seed 20261112). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-12 by one grader; rows 25 and 28 to 30, first graded FP, were changed to TP-low by the owner's ruling of the same day; see [round 15](../../round-15.md). The stores are private, so each is named by a letter and each finding is described by what the code does, without its path.

| # | store | what | verdict | reason |
|---|---|---|---|---|
| 1 | A | a product page widget | TP | Needs only `$.widget` and runs on product pages. |
| 2 | D | a script of one CMS page | TP-low | Uses no jQuery UI widget at all, on one CMS page. |
| 3 | D | another script of the same CMS page | TP-low | Needs only `$.widget`, on one CMS page. |
| 4 | D | a sticky filter sidebar of category pages | TP | Needs only `$.widget`, on every category page. |
| 5 | D | a script of the customer account forms | TP-low | Needs only `$.widget`, on the account and address forms. |
| 6 | D | a product page controller | TP | Needs only `$.widget`, on product pages. |
| 7 | D | a mixin of that controller | TP | Needs only `$.widget`, on product pages. |
| 8 | D | a mixin of core's product custom options | TP | Needs only `$.widget`, on product pages. |
| 9 | D | a product page widget | TP | Needs only `$.widget`, on product pages. |
| 10 | D | a product page widget | TP | Needs only `$.widget`, on product pages. |
| 11 | D | a product page widget | TP | Needs only `$.widget`, on product pages. |
| 12 | D | a product page widget | TP | Needs only `$.widget`, on product pages. |
| 13 | D | a product page widget | TP | Needs only `$.widget`, on product pages. |
| 14 | D | a mixin of core's collapsible widget | TP | Needs only `$.widget`; the collapsible widget runs on product pages and more. |
| 15 | D | a product page widget | TP | Needs only `$.widget`, on product pages. |
| 16 | D | a product page service | TP | Uses no jQuery UI widget at all, on product pages. |
| 17 | D | a product page widget | TP | Needs only `$.widget`, on product pages. |
| 18 | D | a replacement of core's price box | TP | Needs only `$.widget`, on product and category pages. |
| 19 | D | a theme script of blog pages | TP-low | Needs only `$.widget`, on blog pages. |
| 20 | D | a theme mixin of core's menu widget | TP | Uses no jQuery UI widget itself, and the menu runs on every page. |
| 21 | D | a theme script of the top menu | TP | Needs only `$.widget`, on every page. |
| 22 | D | a theme script of the store switcher dropdown | TP | Needs only `$.widget`, on every page. |
| 23 | D | a theme script of the sticky header | TP | Needs only `$.widget`, on every page. |
| 24 | D | a theme script of the secondary menu | TP | Needs only `$.widget`, on every page. |
| 25 | E | a `shim` entry in a third-party module's `requirejs-config.js` | TP-low | The shimmed library is required by nothing in the store, so its dependency is not loaded now; the entry still names the whole library (owner's ruling, 2026-11-12). |
| 26 | E | the swatch script of a third-party product slider template | TP | Needs only the swatch renderer, on every page that shows the slider. |
| 27 | E | a theme override of core's add-to-wishlist script | TP | Needs only `$.widget`, on product and category pages. |
| 28 | E | a theme override of a layered navigation script | TP-low | The module it overrides is not installed in the store, so the file is not loaded now; the file still depends on the whole library (owner's ruling, 2026-11-12). |
| 29 | E | a theme override of another layered navigation script | TP-low | The same: the module is not installed (owner's ruling). |
| 30 | E | a theme override of a third layered navigation slider | TP-low | The same: the module is not installed (owner's ruling). |
