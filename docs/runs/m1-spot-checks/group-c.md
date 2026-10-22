> Appendix to [m1-core.md](../m1-core.md). Findings were sampled with seed 20261022 from the run before the spot-check fixes, then graded by reading the code in Magento 2.4.9. TP: the rule's claim holds. TP-low: it holds, but the impact is negligible. FP: the claim does not hold.

# Sample C: graded findings (Magento 2.4.9 core)

## T-01 (total 17, sample 10)

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-order-cancellation-ui/view/frontend/templates/order/history.phtml:64 | TP | `$this->getLayout()->createBlock(Template)` inside the order loop: a new block per order row, plus `getCancellationReasons()` per row. |
| 2 | module-customer/view/frontend/templates/form/register.phtml:66 | TP-low | Creates the Taxvat widget once per render; guideline violation, but one block on an uncached customer form. |
| 3 | module-customer/view/frontend/templates/form/register.phtml:39 | TP-low | Name widget created once; same as #2. |
| 4 | module-customer/view/frontend/templates/address/edit.phtml:17 | TP-low | Fax widget created once on the uncached address edit page. |
| 5 | module-customer/view/frontend/templates/form/register.phtml:71 | TP-low | Gender widget created once; same as #2. |
| 6 | module-customer/view/frontend/templates/address/edit.phtml:15 | TP-low | Company widget created once; same as #4. |
| 7 | module-customer/view/frontend/templates/form/register.phtml:85 | TP-low | Company widget created once; same as #2. |
| 8 | module-order-cancellation-ui/view/frontend/templates/order/recent.phtml:75 | TP | Same as #1: a block per order inside the loop of recent orders. |
| 9 | module-customer/view/frontend/templates/form/edit.phtml:26 | TP-low | Dob widget created once on the uncached account edit page. |
| 10 | module-customer/view/frontend/templates/form/edit.phtml:24 | TP-low | Name widget created once; same as #9. |

Summary: 2 TP, 8 TP-low, 0 FP, 0 unclear

Pattern: no FPs. The rule matches the guideline correctly. Its performance impact is real only when `createBlock()` is inside a loop (order-cancellation-ui). The Customer widget pattern is a one-off per page. Consider making a separate, higher-confidence message (or severity) when the call is inside a `foreach`/`for` loop.

## X-SETUP-01 (total 8, sample 8)

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-customer/Setup/RecurringData.php:44 | TP | Runs `customer_grid` `reindexAll()` whenever the grid table is missing or the indexer is invalid. The guard is cheap, but when it fires it reindexes every customer during the deploy. |
| 2 | module-reports/Setup/Recurring.php:57 | FP | Loop over a literal array of 5 table names that only calls `ExternalFKSetup::install()` (FK DDL check). No data volume. |
| 3 | module-product-alert/Setup/Recurring.php:54 | FP | Loop over a literal array of 2 tables, FK setup only; same as #2. |
| 4 | module-indexer/Setup/Recurring.php:93 | TP-low | Loops over all indexers (tens): loads/saves states, re-subscribes mview triggers. Bounded by the number of indexers, not data, and needed to sync config hashes. |
| 5 | module-mysql-mq/Setup/Recurring.php:40 | FP | In-memory loop over queue config names, then one SELECT and one bulk INSERT of missing names. Cheap. |
| 6 | module-data-exporter/Setup/Recurring.php:67 | FP | `getItems()` on the indexer collection plus a loop that only calls `setScheduled(true)` on feed indexers not yet scheduled; usually a no-op. |
| 7 | module-bundle/Setup/Recurring.php:54 | FP | Loop over a literal array of 2 tables, FK setup only; same as #2. |
| 8 | module-theme/Setup/RecurringData.php:51 | TP-low | Unconditional `design_config_grid` `reindexAll()` on every upgrade, which matches the rule exactly. The grid has one row per scope (website/store view), so the cost is small. `themeRegistration->register()` also scans themes. |

Summary: 1 TP, 2 TP-low, 5 FP, 0 unclear

Pattern: a `foreach` over a literal constant array (the `$listTables = [...]` + `addExternalForeignKeys` idiom used by 3 core modules), or over config/in-memory lists, is reported as heavy. Do not count loops whose subject is a literal array or a config getter. Consider counting only loops that call load/save/query inside them, or reindex calls. Also, `getItems()` on small system collections (indexers) is not heavy.

## X-DB-01 (total 1, sample 1)

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-message-queue/etc/db_schema.xml:24 | TP-low | `queue_poison_pill` really has no PK anywhere, and none is created in PHP. It is a one-row table (`PoisonPill::put()` does UPDATE without WHERE or INSERT), so replication scans cost nothing. Core lists it in `dev/tests/static/.../DBSchema/_files/primary_key_exemption_list.txt`. Group Replication and PXC strict mode still reject writes to it, so the finding is not wrong. |

Summary: 0 TP, 1 TP-low, 0 FP, 0 unclear

Pattern: none. Optionally, read core's `primary_key_exemption_list.txt` and downgrade or annotate tables listed there as "core-exempted".

## X-EVT-01 (total 10, sample 10)

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-catalog/etc/events.xml:38 | FP | `Framework\EntityManager\Observer\AfterEntityLoad` on an EntityManager event (`magento_..._load_after`). This is EntityManager's own plumbing that runs the model's `afterLoad()`/`setOrigData()` for legacy models. It is required and adds no query. |
| 2 | module-catalog-inventory/etc/events.xml:13 | TP | `AddInventoryDataObserver` calls `assignStatusToProduct()` → `StockRegistryProvider::getStockStatus()`, which queries `cataloginventory_stock_status` per product (registry-cached per product id) on every product load. |
| 3 | module-cms/etc/events.xml:37 | FP | Same EntityManager `AfterEntityLoad` plumbing as #1 (CMS page). |
| 4 | module-catalog/etc/events.xml:53 | FP | Same as #1 (category tree). |
| 5 | module-customer/etc/events.xml:26 | TP-low | `catalog_rule_collection_load_after` is a collection event: it fires once per collection load, not per entity. The observer runs one `loadAllExcludedWebsites()` query (only if the collection is non-empty), then an in-memory loop. Catalog rule collections load rarely (indexer/admin). |
| 6 | module-catalog/etc/events.xml:22 | FP | Same as #1 (product). |
| 7 | module-sales-rule/etc/events.xml:25 | FP | Same as #1 (sales rule). |
| 8 | module-two-factor-auth/etc/adminhtml/events.xml:18 | TP-low | Loads `tfa_user_config` per admin user load (registry-cached per user id). Adminhtml area only, where few admin users are loaded per request. |
| 9 | module-catalog-rule/etc/events.xml:22 | FP | Same as #1 (catalog rule). |
| 10 | module-cms/etc/events.xml:22 | FP | Same as #1 (CMS block). |

Summary: 1 TP, 2 TP-low, 7 FP, 0 unclear

Pattern: (a) the `magento_<module>_api_data_<entity>interface_load_after` events with `Magento\Framework\EntityManager\Observer\AfterEntityLoad` (observer names `legacy_*`) are core EntityManager infrastructure. Do not report observers whose instance is `Magento\Framework\EntityManager\Observer\*`, or skip these events entirely. (b) `*_collection_load_after` events fire once per collection, not per entity. The message "runs on every load of this entity type" is wrong for them, so word it differently or treat these events separately.

## X-EVT-02 (total 1, sample 1)

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-page-cache/etc/events.xml:49 | FP | `FlushCacheByTags` is deliberately generic: it purges built-in FPC entries by the identity tags of whatever model was committed. This is the rule's own "really needs every model" exception. It returns immediately unless the built-in FPC is enabled. |

Summary: 0 TP, 0 TP-low, 1 FP, 0 unclear

Pattern: core's FPC tag-invalidation observer. Allowlist `Magento\PageCache\Observer\FlushCacheByTags` (or report core vendor findings only at info level).

## X-SEC-01 (total 4, sample 4)

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-store/etc/frontend/sections.xml:13 | FP | `stores/store/switchrequest` changes the store view. Cart, prices, translations and URLs in every section are store-scoped, so all sections must reload (a scope change, like core's identity-changing actions). |
| 2 | module-directory/etc/frontend/sections.xml:10 | FP | Currency switch changes displayed prices in cart, compare, wishlist and other sections, so reloading all sections is needed. |
| 3 | module-theme/etc/frontend/sections.xml:10 | FP | Core's `<action name="*">` invalidates only the small `messages` section. That is the designed mechanism for showing flash messages after any POST, not a broad invalidation. |
| 4 | module-store/etc/frontend/sections.xml:10 | FP | `stores/store/switch`: same as #1. |

Summary: 0 TP, 0 TP-low, 4 FP, 0 unclear

Pattern: the core exception list (logout/loginPost/createPost/editPost) is too narrow. Add the scope-changing core actions `stores/store/switch`, `stores/store/switchrequest` and `directory/currency/switch`, and core's `* → messages`. More generally, do not report `<action name="*">` when its only section is `messages`.

## X-LAY-02, X-EVT-03, X-MQ-01, X-IDX-01, X-GQL-01, P-FPC-10

0 findings on Magento 2.4.9 core. Nothing to grade.

## Overall

Summary: 4 TP, 13 TP-low, 17 FP, 0 unclear (34 findings graded)
