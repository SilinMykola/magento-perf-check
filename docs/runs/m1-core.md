# M1 acceptance run on Magento 2.4.9

- **Date:** 2026-10-22.
- **Tool:** `magento-perf-check` from change m1-mvp, with 29 rules and PHPStan 2.3.1.
- **Host:** Apple M4, 10 cores, 16 GB, macOS, PHP 8.5.11.
- **Project:** `~/projects/magento-2.4.9.ddev` (`magento/magento2-base` 2.4.9, with `generated/code`, without bitexpert/phpstan-magento).

This is the M1 smoke test, not a precision measurement; calibration with thresholds is M2. The spot checks decide which rules need changes before or during M2.

## Runs

| Run | Exit | Wall time | Peak RSS | Findings | Notices |
|---|---|---|---|---|---|
| Default paths (`app/code`, `app/design`; 4 custom modules), cold | 0 | 5 s | 0.3 GB | 3 (all warning) | 0 |
| `--paths=vendor/magento`, cold (PHPStan and index caches deleted) | 1 | 118 s | 3.9 GB | 2870 | 22 |
| `--paths=vendor/magento`, after the fixes below (partly warm cache) | 1 | 76 s | 4.2 GB | 2811 | 9 |

- **Peak RSS** is the sum over the command and all PHPStan worker processes, sampled every second.
- **Project index:** a cold build took 1.67 s for 390 modules, and loading it from the cache took 0.04 s ([ADR 4](../adr/0004-project-index.md)).
- **Project left unchanged:** after each run, `find ~/projects/magento-2.4.9.ddev -newer <marker>` found no files.
- **Faster than M0:** the M0 cold run took 227 s. Test code is now skipped by default, which removes most of `dev/tests` and every `Test/` directory.

### Problems found by this run and fixed in M1

| Problem | Effect | Fix |
|---|---|---|
| 14 core `schema.graphqls` files did not parse | X-GQL-01 skipped them (14 `graphql` notices) | Magento's schema dialect (empty types, `implements A, B`) is normalized first, as Magento's own `GraphQlReader` does. All 61 non-test core schemas now parse. |
| Type checks accepted PHPStan's `never` type | P-COL-01 reported `count()` on arrays after `is_array()` | `MagentoTypes::isObjectOf()` requires a real object type in all rules |
| A limit set in an assigned chain was not seen | P-COL-02 reported `$c = $f->create()->...->setPageSize(1); $c->getFirstItem()` | Assignments carry chain facts to the variable; reassignment resets them |
| An early `return` before `$proceed` was not seen | P-PLG-01 reported around plugins that skip the original conditionally (80 → 33 findings) | A return before the call makes it conditional |
| `AbstractBlock` itself was reported | P-FPC-01 flagged the framework's own declaration | Only subclasses are checked |
| Collections counted as view models | P-FPC-02 flagged collections (they implement `ArgumentInterface` through `CollectionDataSourceInterface`) | Collections are excluded |

### Findings on core after the fixes

| Rule | Findings | Severity |
|---|---|---|
| P-DI-02 | 1391 | warning |
| P-DI-01 | 579 | error (session) / warning (cookie manager) |
| P-DI-06 | 343 | warning |
| P-LOOP-03 | 127 | warning |
| P-LOOP-01 | 107 | error |
| P-FPC-01 | 70 | error |
| P-FPC-02 | 48 | warning |
| P-PLG-01 | 33 | warning |
| P-COL-02 | 21 | error |
| T-01 | 17 | warning |
| P-LOOP-02 | 11 | error |
| X-EVT-01 | 10 | warning |
| X-SETUP-01 | 8 | warning |
| P-COL-01, P-FPC-05, P-FPC-06, P-LOOP-07 | 7 each | |
| P-FPC-09 | 6 | error |
| X-LAY-01 | 5 | |
| X-SEC-01 | 4 | |
| P-DI-03, X-DB-01, X-EVT-02 | 1 each | |
| X-LAY-02, X-EVT-03, X-MQ-01, X-IDX-01, X-GQL-01, P-FPC-10 | 0 | |

The remaining notices are 9 `untyped-receiver` (P-COL-02 candidates whose receiver type PHPStan cannot resolve).

## Spot checks

Up to 10 findings per rule were drawn with seed 20261022 from the run before the fixes and graded by reading the code. The full tables are in [m1-spot-checks/](m1-spot-checks/): [group A](m1-spot-checks/group-a.md), [group B](m1-spot-checks/group-b.md), [group C](m1-spot-checks/group-c.md). The bug claims behind the fixes above were re-checked by hand against the code before fixing.

- **TP:** the rule's claim holds.
- **TP-low:** it holds, but the impact is negligible.
- **FP:** the claim does not hold.

| Rule | Sample | TP | TP-low | FP | Recurring false-positive or low-impact pattern |
|---|---|---|---|---|---|
| P-COL-01 | 10 | 0 | 0 | 10 | 4 were the `never`-type bug (fixed). Others: the collection is loaded later in the same function, or loaded in another method. |
| P-COL-02 | 10 | 1 | 4 | 5 | 1 was the assigned-chain bug (fixed). Limit or primary-key filter inside a collection helper method (`filterLastItem()`, `setIdFilter()`). Sample-data modules. |
| P-LOOP-02 | 10 | 1 | 5 | 4 | Several findings for one loop on an already loaded collection. Batched loops over `array_chunk(..., 1000)`. Small fixed bounds. |
| P-LOOP-03 | 10 | 1 | 8 | 1 | Saves bounded by one order or product. A loop that breaks after the first save. |
| P-LOOP-07 | 7 | 1 | 5 | 1 | A loop over product type classes, not over products. |
| P-DI-01 | 10 | 3 | 7 | 0 | No Proxy anywhere in each case. Low impact in admin code and in cart/checkout code where the session is started anyway. |
| P-DI-02 | 10 | 1 | 4 | 5 | Cheap statements (`$factory->create()`, casts, `setType()`), backward-compatible `??` fallbacks assigned to locals, composer plugins and coding-standard code that the object manager never builds. |
| P-DI-03 | 1 | 0 | 1 | 0 | `customer_session_init` has no core observer. |
| P-DI-06 | 10 | 0 | 8 | 2 | Deprecated getters that fetch a shared object once. A fallback through a helper (`$arg ?: $this->getX()`). An optional dependency loaded by name. |
| P-FPC-01 | 10 | 1 | 7 | 2 | 1 was `AbstractBlock` itself (fixed). A redeclaration with the default `false`. Flags on pages that are never cached. |
| P-FPC-02 | 10 | 0 | 2 | 8 | Collections as view models (fixed). `getCustomerGroupId()`, which depersonalization restores. Adminhtml blocks. Blocks rendered only on cart/checkout or inside the minicart section. |
| P-FPC-05 | 7 | 0 | 1 | 6 | `Pragma: public` reported as no-cache. One finding per header line. Endpoints private by design (`customer/section/load`, `page_cache/block/render`, downloadable links, search). |
| P-FPC-06 | 7 | 0 | 0 | 7 | Adminhtml controllers, never page-cached. GET controllers that only redirect (Varnish and the built-in cache store only 200/404). |
| P-FPC-09 | 6 | 0 | 0 | 6 | CLI commands, setup code, the deploy config importer, and a store-URL setting backend, where a full flush is right. |
| P-PLG-01 | 10 | 1 | 3 | 6 | 5 were the early-return bug (fixed). State captured before `$proceed` and used after it. |
| T-01 | 10 | 2 | 8 | 0 | One block per uncached page. The 2 TPs create a block inside a loop. |
| X-SETUP-01 | 8 | 1 | 2 | 5 | Loops over literal table lists or in-memory config, with no query inside. |
| X-DB-01 | 1 | 0 | 1 | 0 | `queue_poison_pill`, a one-row table that core lists in its primary-key exemption list. |
| X-EVT-01 | 10 | 1 | 2 | 7 | `Magento\Framework\EntityManager\Observer\*` plumbing on `*_api_data_*interface_load_after`. `*_collection_load_after` fires once per collection, not per entity. |
| X-EVT-02 | 1 | 0 | 0 | 1 | `Magento\PageCache\Observer\FlushCacheByTags` needs every model on purpose. |
| X-SEC-01 | 4 | 0 | 0 | 4 | Store and currency switch actions (`stores/store/switch*`, `directory/currency/switch`). `<action name="*">` with only `messages`, which is how core shows flash messages. |

The M0 rules were rechecked in M0 (P-LOOP-01, X-LAY-01) and are not repeated here. On the 4 custom modules of the project, the 3 findings were 1 TP (P-DI-01, cookie manager without Proxy) and 2 TP-low (P-DI-02: reading `$_SERVER`, `rtrim(getRoot())`).

## Conclusions for M2

The spot checks only cover core code, which is the hardest case: core is old, uses its own exceptions, and many findings are intentional. They are still a clear signal.

**Detection is precise but impact is often low.** For these rules, either the severity or the status needs a decision at calibration: P-DI-01, P-DI-06, P-LOOP-03, P-LOOP-07, T-01, P-FPC-01.

**Not fit for the default set in their current form:**

| Rule | FP in sample | Smallest change that could fix it |
|---|---|---|
| P-DI-02 | 5 of 10, 1391 findings | Allow casts, `setType()`-style setters, `??` fallbacks to locals; skip non-runtime packages (composer plugins, coding standard). Otherwise ⏳ or `info`. |
| P-FPC-06 | 7 of 7 | Skip Adminhtml controllers and actions whose `execute()` only returns redirects. |
| P-FPC-09 | 6 of 6 | Skip CLI commands, setup code and config backend models. |
| P-FPC-05 | 6 of 7 | Check the header value (`no-cache`, `no-store`, `private`), report once per statement, allowlist core private endpoints. |
| P-FPC-02 | 8 of 10 before the fix | Skip `getCustomerGroupId()` and Adminhtml blocks. |
| X-SEC-01 | 4 of 4 | Add the store and currency switch actions to the exception list; do not report `name="*"` that only lists `messages`. |
| X-EVT-01 | 7 of 10 | Skip `Framework\EntityManager\Observer\*` and `*_collection_load_after`. |
| X-SETUP-01 | 5 of 8 | Count a loop as heavy only when it loads, saves or queries inside. |
| P-COL-01 | 10 of 10 before the fix | Treat a load later in the same function as "loaded anyway". |

All of these change behaviour that the M1 specs define. They need the owner's decision, either as a follow-up change or as part of M2 calibration.

**Other options:**
- Skip `*-sample-data` modules by default, the way test code is skipped (6 of the low-impact loop/collection findings).
- X-EVT-02 could allowlist `FlushCacheByTags`.
