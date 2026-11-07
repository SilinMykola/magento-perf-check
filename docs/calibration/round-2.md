# Calibration, round 2

Round 1 ([`docs/calibration.md`](../calibration.md)) measured the rules and listed recurring false positives. This round changes the rules with those patterns and measures them again, together with the new rules. A decision here replaces the one in round 1. Rules not listed did not change and keep their round 1 result.

## Runs

The corpus and the method are those of round 1. Both parts ran on 2026-11-07, one after the other, at low priority, with the experimental rules enabled:

| Part | Paths | Duration reported by the tool |
|---|---|---|
| core | `vendor/magento` | 83 s |
| packagist | `app/code` | 11 s |

The machine was busy; an earlier run of the same code took 37 s and 5 s. Tool state: branch `develop`, uncommitted, on top of commit `30ee35a`. `*-sample-data` modules are excluded from the counts.

## How findings were graded

- **Findings that round 1 graded and the rule still reports** keep their round 1 verdict. A sampled finding the rule no longer reports is dropped, so Sampled can be lower than the number drawn.
- **New findings** were graded once, by one grader who did not write the rule, with the same verdicts. Their tables are below.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [X-LAY-03](../rules/X-LAY-03.md) | error | 0 | 0 | 0 | 0 | 0 | 0 | – | – | no findings in the corpus |
| [X-LAY-01](../rules/X-LAY-01.md) | warning | 4 | 4 | 1 | 1 | 2 | 0 | 50 % | 50 % | experimental |
| [P-COL-01](../rules/P-COL-01.md) | warning | 0 | 0 | 0 | 0 | 0 | 0 | – | – | no findings in the corpus |
| [P-COL-02](../rules/P-COL-02.md) | error | 16 | 14 | 3 | 2 | 9 | 0 | 36 % | 60 % | experimental |
| [P-LOOP-01](../rules/P-LOOP-01.md) | error | 112 | 27 | 14 | 13 | 0 | 0 | 100 % | 52 % | kept |
| [P-LOOP-02](../rules/P-LOOP-02.md) | error | 3 | 3 | 0 | 3 | 0 | 0 | 100 % | 0 % | insufficient data |
| [P-LOOP-05](../rules/P-LOOP-05.md) | warning | 0 | 0 | 0 | 0 | 0 | 0 | – | – | no findings in the corpus |
| [P-LOOP-09](../rules/P-LOOP-09.md) | warning | 3 | 3 | 0 | 3 | 0 | 0 | 100 % | 0 % | insufficient data |
| [P-LOOP-10](../rules/P-LOOP-10.md) | warning | 1 | 1 | 0 | 1 | 0 | 0 | 100 % | 0 % | insufficient data |
| [P-IDX-01](../rules/P-IDX-01.md) | warning | 2 | 2 | 0 | 2 | 0 | 0 | 100 % | 0 % | insufficient data |
| [P-FPC-01](../rules/P-FPC-01.md) | info | 61 | 26 | 1 | 25 | 0 | 0 | 100 % | 4 % | kept |
| [P-PLG-01](../rules/P-PLG-01.md) | warning | 11 | 11 | 9 | 2 | 0 | 0 | 100 % | 82 % | kept |
| [X-SETUP-01](../rules/X-SETUP-01.md) | warning | 1 | 1 | 0 | 1 | 0 | 0 | 100 % | 0 % | insufficient data |
| [X-DB-02](../rules/X-DB-02.md) | info | 7 | 7 | 0 | 7 | 0 | 0 | 100 % | 0 % | insufficient data |
| [X-AREA-01](../rules/X-AREA-01.md) | info | 16 | 16 | 0 | 16 | 0 | 0 | 100 % | 0 % | kept |
| [X-GQL-03](../rules/X-GQL-03.md) | info | 0 | 0 | 0 | 0 | 0 | 0 | – | – | no findings in the corpus |

The thresholds are those of round 1. X-LAY-01 stays experimental at 2 of 4, although that is too few findings for a measured decision. Two of the 16 P-COL-02 findings are new and not graded, which cannot change its decision.

P-PLG-01 returns to the default set. In round 1 it had 18 false positives of 29, all with state carried across `$proceed`; the rule now skips those.

## What the changes cost

- P-COL-01 no longer reports anything in this corpus. Its one correct finding in round 1, a getter that builds a new collection only to count it, is lost too.
- P-PLG-01 no longer reports 2 findings that round 1 graded as correct.
- P-LOOP-02 no longer reports 1 finding that round 1 graded as correct, because it duplicated another finding on the same collection.

## Limitations

- The rules were changed by looking at these same findings, so the precision above is optimistic. It says the known false positives are gone, not how a rule does on code it has not seen.
- Most findings of the new rules are low impact: in the corpus they sit in admin code or in small, capped loops.
- X-LAY-03, P-LOOP-05, P-COL-01 and X-GQL-03 have no findings in the corpus, so their precision is unmeasured. X-LAY-03 and P-LOOP-05 are covered only by fixtures.
- A grader who reads the code can still miss a caller. For X-AREA-01, dispatchers outside the named files were not searched.

## Grading tables for new findings

### P-LOOP-09

| Location | Verdict | Reason |
|---|---|---|
| vendor/magento/module-inventory-admin-ui/Controller/Adminhtml/Source/MassDelete.php:170 | TP-low | One query per selected source in an admin mass action; sources are few, and an "in" filter would load all their items to count them. |
| vendor/magento/module-inventory-in-store-pickup-sales/Model/NotifyOrdersAreReadyForPickup.php:112 | TP-low | One query per order is real, but the admin caller passes one ID and order load, shipment creation and email dominate REST batches. |
| vendor/magento/module-inventory-in-store-pickup-sales/Model/NotifyOrdersAreReadyForPickupEmailSender.php:103 | TP-low | One query per order in cron, capped by `sending_limit` (default 50), only after an email send that costs far more. |

### P-LOOP-10

| Location | Verdict | Reason |
|---|---|---|
| vendor/magento/module-sales-sequence/Model/Sequence/DeleteByStore.php:68 | TP-low | `load()` per meta row is real, but it runs on store deletion, for one store's sequence rows, next to `dropTable` on each. |

### P-IDX-01

| Location | Verdict | Reason |
|---|---|---|
| vendor/magento/module-customer/Model/Plugin/CustomerGridIndexAfterWebsiteDelete.php:55 | TP-low | No mode check, but the plugin is declared in `etc/adminhtml/di.xml` for rare website deletion; an `isScheduled()` skip would likely lose the update, because `website_id` is nulled by FK `SET NULL`, which does not fire the mview triggers. |
| app/code/Algolia/AlgoliaSearch/Observer/ReindexProductOnLastItemPurchase.php:70 | TP-low | No mode check and the queue is off by default, so it can index synchronously, but it is wired only in `etc/adminhtml/events.xml` and fires only for zero-quantity items on a new shipment. |

### X-DB-02

| Location | Verdict | Reason |
|---|---|---|
| vendor/magento/module-adobe-stock-asset/etc/db_schema.xml:16 | TP-low | `ADB_STK_AST` on `id` duplicates PRIMARY; writes occur only on admin asset licensing or saving. |
| vendor/magento/module-adobe-stock-asset/etc/db_schema.xml:35 | TP-low | `ADB_STK_CT` on `id` duplicates PRIMARY on a tiny lookup table. |
| vendor/magento/module-adobe-stock-asset/etc/db_schema.xml:45 | TP-low | `ADB_STK_KB_INDEX` on `id` duplicates PRIMARY on a tiny lookup table. |
| vendor/magento/module-media-gallery/etc/db_schema.xml:25 | TP-low | `MEDIA_GALLERY_ID` on `id` duplicates PRIMARY; writes come from admin and gallery sync, not storefront traffic. |
| vendor/magento/module-media-gallery/etc/db_schema.xml:38 | TP-low | `MEDIA_GALLERY_KEYWORD` on `id` duplicates PRIMARY on a small table with rare writes. |
| vendor/magento/module-swatches/etc/db_schema.xml:36 | TP-low | `EAV_ATTRIBUTE_OPTION_SWATCH_SWATCH_ID` on `swatch_id` duplicates PRIMARY; writes occur on attribute saves, not storefront traffic. |
| app/code/Snowdog/Menu/etc/db_schema.xml:41 | TP-low | `SNOWMENU_NODE_NODE_ID` on `node_id` duplicates PRIMARY; writes occur on admin menu saves. |

### X-AREA-01

| Location | Verdict | Reason |
|---|---|---|
| vendor/magento/module-admin-adobe-ims/etc/events.xml:10 | TP-low | `adminhtml_auth_logout` is admin-only and the observer never runs elsewhere, so only the config entry costs. |
| vendor/magento/module-cache-invalidate/etc/events.xml:37 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| vendor/magento/module-cache-invalidate/etc/events.xml:40 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| vendor/magento/module-cache-invalidate/etc/events.xml:43 | TP-low | Event never fires: Magento_Catalog's admin route id is `catalog`, so moving it achieves nothing and the entry is dead. |
| vendor/magento/module-cache-invalidate/etc/events.xml:46 | TP-low | Event never fires for the same reason (route id `catalog`), so the entry is dead. |
| vendor/magento/module-cache-invalidate/etc/events.xml:49 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| vendor/magento/module-inventory-shipping-admin-ui/etc/events.xml:10 | TP-low | `adminhtml_order_shipment_new` is admin-only; the only cost is one config entry. |
| vendor/magento/module-page-cache/etc/events.xml:37 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| vendor/magento/module-page-cache/etc/events.xml:40 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| vendor/magento/module-page-cache/etc/events.xml:46 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| app/code/Fastly/Cdn/etc/events.xml:53 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| app/code/Fastly/Cdn/etc/events.xml:56 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| app/code/Fastly/Cdn/etc/events.xml:59 | TP-low | Event never fires: Magento_Catalog's admin route id is `catalog`, so the entry is dead. |
| app/code/Fastly/Cdn/etc/events.xml:62 | TP-low | Event never fires for the same reason (route id `catalog`), so the entry is dead. |
| app/code/Fastly/Cdn/etc/events.xml:65 | TP-low | Route `adminhtml` makes this event admin-only; the only cost is one config entry. |
| app/code/Mollie/Payment/etc/events.xml:59 | TP-low | Event is dispatched by the admin order create controller; no other dispatcher was searched for. |

### P-PLG-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/JustBetter/Sentry/Plugin/CronScheduleCheckIn.php:30 | TP-low | No state, try/catch or argument change, so `afterSetData` is equivalent; the code runs in cron processing. |
| app/code/Mageplaza/Smtp/Plugin/Model/Resolver/Products/DataProvider/ProductSearch/ProductSearchCriteriaBuilder.php:77 | TP-low | `afterBuild` is equivalent and the call is once per search; the target class file is absent at its PSR-4 path in this corpus, so the plugin looks dead. |
