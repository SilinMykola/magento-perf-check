# M0 acceptance run on Magento 2.4.9

- **Date:** 2026-10-21.
- **Tool:** `magento-perf-check` from change m0-skeleton, running PHPStan 2.3.1.
- **Host:** Apple M4, 10 cores, 16 GB, macOS, PHP 8.5.11.
- **Project:** `/path/to/magento-2.4.9` (`magento/magento2-base` 2.4.9). It has a `generated/code` directory, which the runner scans. bitexpert/phpstan-magento is not installed.

This is a smoke test of the architecture, not a precision measurement: calibration is M2. The spot checks below decide only what has to change before M1.

## Runs

| Command | Exit | Wall time | Peak RSS | Findings | Notices |
|---|---|---|---|---|---|
| `php bin/magento-perf-check --project=/path/to/magento-2.4.9` (default `app/code`, `app/design`; 4 custom modules) | 0 | 4 s | 0.4 GB | 0 | 0 |
| `php bin/magento-perf-check --project=/path/to/magento-2.4.9 --paths=vendor/magento` (about 29,700 PHP files) | 1 | 227 s | 4.5 GB | 513 | 175 |

- **How peak RSS was measured:** the summed RSS of the command and all its child processes (PHPStan's parallel workers), sampled every second. Memory per worker was not measured separately. The run used the default `--memory-limit=2G` and finished without running out of memory.
- **Project left unchanged:** `find /path/to/magento-2.4.9 -newer <marker created before the runs>` returned no files.

### Findings on core (`--paths=vendor/magento`)

| Rule | Findings | In test code¹ | Severity |
|---|---|---|---|
| P-LOOP-01 | 343 | 236 (69%) | 343 error |
| P-COL-02 | 150 | 117 (78%) | 150 error |
| X-LAY-01 | 20 | 3 | 1 error, 19 warning |

¹ Path contains `/Test/` or `/dev/tests/` (integration tests, fixtures, `_files`).

### Core without test code (rerun on 2026-10-22)

After test code was excluded by default, `--paths=vendor/magento` was run again. Exit code 1. The project was again unchanged.

| Rule | Findings | Before (with tests) |
|---|---|---|
| P-LOOP-01 | 107 | 343 |
| P-COL-02 | 33 | 150 |
| X-LAY-01 | 17 | 20 |
| **Total** | **157** (141 error, 16 warning) | 513 |

`untyped-receiver` notices dropped from 175 to 13, so most untyped `getFirstItem()` receivers were in test code. Wall time was 30 s, but PHPStan reused its result cache from the first run, so this time cannot be compared with the 227 s cold run.

### Notices in the first run (with tests)

- **175 `untyped-receiver`:** `getFirstItem()` calls whose receiver type PHPStan could not resolve, even with `generated/code` scanned. That is more than the 150 reported P-COL-02 findings, so the rule currently sees less than half of the candidate calls. Typical causes:
  - collections returned as `mixed` or `AbstractCollection` from untyped methods;
  - `getCollection()` on models.
- **0 parse errors.**

## Spot checks

Random sample with seed 20261021, up to 10 findings per rule. **TP** means the code does what the rule says and the cost is real. **FP** means the rule's claim does not hold for this code.

### P-LOOP-01: 10 of 10 TP

| # | Location | Verdict |
|---|---|---|
| 1 | `module-inventory-catalog/Test/Integration/.../AssignStatusToProductTest.php:118` | TP, test code: `productRepository->get($sku, …, forceReload: true)` per SKU |
| 2 | `module-catalog-rule-configurable/Plugin/CatalogRule/Model/Rule/Validation.php:52` | TP: `getById()` for every configurable parent of a product, inside a rule-validation plugin |
| 3 | `module-inventory-in-store-pickup-sales-api/Test/_files/create_in_store_pickup_quote_on_eu_website_guest.php:63` | TP, test fixture |
| 4 | `magento2-base/dev/tests/api-functional/.../ProductPriceTest.php:468` | TP, test code |
| 5 | `module-catalog-inventory-graph-ql/Model/Resolver/StockStatusProvider.php:95` | TP: `stockStatusRepository->get()` per bundle option in a GraphQL resolver |
| 6 | `module-catalog-widget/Model/Rule/Condition/Product/CategoryConditionProcessor.php:57` | TP: `categoryRepository->get()` per category id |
| 7 | `magento2-base/dev/tests/integration/.../product_grouped_with_simple_out_of_stock_rollback.php:21` | TP, test fixture |
| 8 | `module-sales-rule/Model/RuleRepository.php:151` | TP: `getList()` loads the collection, then calls `load()` again on every rule in it |
| 9 | `module-sales/Test/Fixture/PlaceOrderWithCustomerOrGuest.php:137` | TP, test fixture |
| 10 | `magento2-base/dev/tests/integration/.../product_configurable_two_options_rollback.php:34` | TP, test fixture |

Detection is precise. 6 of the 10 findings are in test code, where per-iteration loads do not matter.

### P-COL-02: 5 of 10 TP, 5 FP

| # | Location | Verdict |
|---|---|---|
| 1 | `module-two-factor-auth/Test/Integration/UserConfigManagerTest.php:51` | FP: filter on `username`, which is unique (at most one row) |
| 2 | `magento2-base/dev/tests/integration/.../Report/Rule/CreatedatTest.php:266` | TP, test code: unfiltered report collection |
| 3 | `module-widget-sample-data/Model/CmsBlock.php:189` | TP: `url_key` filter can match several categories |
| 4 | `magento2-base/dev/tests/api-functional/.../CategoryTreeTest.php:636` | TP, test code: filter on `name` |
| 5 | `magento2-base/dev/tests/integration/.../Report/Rule/CreatedatTest.php:102` | TP, test code |
| 6 | `magento2-base/dev/tests/integration/.../Tax/Model/TaxRuleCollectionTest.php:30` | FP: `count()` on line 23 already loaded the collection, so `getFirstItem()` runs no query |
| 7 | `magento2-base/dev/tests/integration/.../Billing/Agreement/ViewTest.php:32` | TP, test code: unfiltered collection |
| 8 | `magento2-base/dev/tests/integration/.../SpecialPriceIndexerWithDimensionTest.php:84` | FP: filter on `sku`, which is unique |
| 9 | `module-catalog/Model/CategoryManagement.php:105` | FP in practice: `level = 0` matches only the tree root |
| 10 | `magento2-base/dev/tests/integration/.../Tax/Model/TaxRateCollectionTest.php:20` | FP: `count()` on line 19 already loaded the collection |

At `error` this is far below the 90% threshold (SPEC section 7). Two FP patterns can be fixed statically:
- A collection already loaded earlier in the method by `count()`, `load()`, `getItems()` or `foreach`.
- A filter on the primary key or a known unique column (`entity_id`, `sku`, `username`, `email` with website).

### X-LAY-01: all 20 checked

Every finding is a real `<block cacheable="false">` in a frontend layout, so detection has no FP. But in core almost all of them are intentional, on personal pages that the default allowlist does not cover yet:
- `customer_address_form`, `customer_address_index` (2), `downloadable_customer_products`, `newsletter_manage_index`, `review_customer_index`, `review_customer_view`, `vault_cards_listaction` (2), `paymentservicespaypal_vault_addcard`, `loginascustomer_login_index`;
- `contact_index_index` (captcha), `sendfriend_product_send`, `productalert_unsubscribe_email`, `shipping_tracking_popup`;
- `catalog_product_compare_index` is reported as `error` because of the `catalog_` prefix, but the compare page is per customer;
- `search_term_popular` (`seo.searchterm`) is the only one that looks like a candidate for review;
- 3 findings are integration-test modules.

## Conclusions for M1

1. **Test code dominates the findings** (69% of P-LOOP-01, 78% of P-COL-02). Done in M0 after this run: `Test/` and `dev/tests/` are skipped by default, and `--include-tests` brings them back.
2. **P-COL-02 needs two precision fixes before it can stay at `error`:**
   - Recognise a collection that is already loaded in the method.
   - Recognise filters on the primary key or unique columns.

   Its coverage is also limited by untyped receivers (175 untyped against 150 found). Measure again with bitexpert/phpstan-magento installed.
3. **X-LAY-01 allowlist needs to grow** to the personal handles core itself makes non-cacheable (list above). `catalog_product_compare_*` must not be `error`.
4. **P-LOOP-01 detection works as intended.** Reporting test fixtures is the main source of noise.
5. **Run time and memory are acceptable for CI on a project's own code.** 4 s on `app/code` here. A full core run takes about 4 minutes and needs about 4.5 GB in total across PHPStan workers.
