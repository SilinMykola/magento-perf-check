# Source audit

This audit checks the sources of every ✅ and ⏳ rule in the `docs/SPEC.md` catalog against the source policy in SPEC section 2.

- **Audit date:** 2026-10-21.
- **Core base:** [`magento/magento2@a385d02f60ee6dfa052f12cdc7d82e956581fba5`](https://github.com/magento/magento2/commit/a385d02f60ee6dfa052f12cdc7d82e956581fba5), the 2.4-develop head on 2026-10-16 ("Merge pull request #11139"). Short form in the SPEC: `a385d02f`.
- **How sources were checked:**
  - Core files were fetched at that commit, and every line range was checked against the fetched copy.
  - developer.adobe.com pages render only in the browser, so their Markdown source was read in the AdobeDocs repositories. The commits are given in each section.
  - experienceleague.adobe.com pages were fetched directly.
  - magento/magento2 issues were read with `gh`.
- **Spot-checked by hand:** for the three M0 rules (X-LAY-01, P-COL-02, P-LOOP-01), the core ranges and the Adobe page-cache quotes were re-checked against the live sources.
- **Not complete:** the magento/magento2 issue search for sections 4.5–4.8 hit the GitHub rate limit. Missing issue sources there mean "not found yet", not "none exist".

## Summary of verdicts

Rules whose status or severity the audit proposes to change. Every other ✅/⏳ rule keeps its status, though some get new or re-pinned sources. The SPEC catalog is updated only after the owner confirms these changes (change m0-skeleton, task 1.5).

| Rule | Now | Proposed | Reason |
|---|---|---|---|
| P-COL-03 | ✅ warning | ⏳ info | Only B source is the core mechanism; core itself uses `'*'` |
| P-COL-04 | ⏳ | ⏳ with new source, or 🧪 | The cited Technical guidelines 6.4.3.3 is about service-contract signatures, not iteration |
| P-FPC-10 | ✅ | ✅ for `reindexAll()` only | `reindexList()` from plugins and observers is the documented Update on Save pattern |
| P-DI-01 | ✅ error | ✅, CookieManager part as warning | The Set-Cookie claim is contradicted by core; CookieManager's constructor does no work |
| X-PLG-02 | ✅ | ⏳ (or 🧪 under a strict reading) | No source states that these plugins are slow |
| P-EXT-02 | ⏳ 🔎 | 🧪, or merge into P-EXT-01 | No A/B source specific to checkout; legitimate payment and tax calls |
| X-LAY-02 | ✅ error/warning | ✅ error only | The `default.xml` warning has no source, and core does it itself |
| X-SEC-01 | ✅ error | error for `name="*"`, warning otherwise | Core configures some actions to reload all sections |
| X-GQL-03 | ✅ | 🧪 or info | Adobe documents `idFactorProviders` as a supported extension point |
| X-MQ-02 | ⏳ | 🧪 or reworded | Contradicted by core: cron consumers get `--max-messages` 10000 by default |
| C-01 | 🧪 | ⏳ (env group, off by default) | Adobe and core sources found; "memory leak" has no source |

All three sections also list wording fixes and the core links to re-pin. Developer.adobe.com URLs with a trailing slash return 404 for some pages; each section names the working form.

## Source audit: SPEC 4.1 (P-LOOP-01..08) and 4.2 (P-COL-01..05)

Audit date: 2026-10-21. Core base: `magento/magento2@a385d02f60ee6dfa052f12cdc7d82e956581fba5`.
All core files were fetched from `raw.githubusercontent.com/magento/magento2/a385d02f.../<path>` and line numbers below were checked against those copies.

How the Adobe pages were read:
- developer.adobe.com pages are a JS single-page app, and a plain fetch returns only a 2.5 KB shell. Their Markdown source was read from `AdobeDocs/commerce-php` at commit `80d81d1922af3e1669a2535370a31138708cadff` (main, fetched 2026-10-21):
  - `src/pages/best-practices/extensions/security.md`
  - `src/pages/coding-standards/technical-guidelines.md`
- The experienceleague.adobe.com pages were fetched over HTTP (status 200), and their text was taken from the server-rendered HTML.

`<B>` below stands for `https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5`.

---

### P-LOOP-01: `load()` / repository `get()`/`getById()` in a loop (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security/), section "Use data collections" (source line 72) | A | Exact quotes: "Running SQL queries in a loop often results in a performance bottleneck." and "Instead of loading models in a loop, data collections can help..." The page names model loading in a loop directly. It does not name repositories. | verified, supports |
| [Adobe: Code review best practices](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review), "Performance" list | A | Exact quote: "Are queries executed in a loop? This loop can be outside of the edited files." It is a generic review checklist item that names neither `load()` nor repositories. | verified, supports (generic) |
| Same page, "Privacy and GDPR" list | A | Exact quote: "Adobe advises using factories or repositories to create models in the loop cycle". The context is isolating customer data between loop iterations. **It cuts against a blanket ban on repositories in loops**, so the rule message should not imply that Adobe forbids them. | verified, caveat |
| [`<B>/lib/internal/Magento/Framework/Model/AbstractModel.php#L564-L568`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L564-L568) | B | `load()` delegates to `_getResource()->load()`, so each call is one DB round trip. The method is `@deprecated 100.1.0` (L559-L562). | verified, supports |
| [`<B>/app/code/Magento/Catalog/Model/ProductRepository.php#L308-L328`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ProductRepository.php#L308-L328) | B | `getById()` calls `$product->load($productId)` unless the id is already in the `instancesById` cache. Each distinct id therefore costs a full EAV load. | verified, supports |

Verdict: keep ✅. Wording: the message can say Adobe guidance is to "avoid loading models in a loop; use collections". It should not claim that Adobe forbids repositories in loops. Mention in the docs that `ProductRepository` caches by id, so repeated calls for the same id are cheap. This is a possible FP note, but severity can stay `error`.

---

### P-LOOP-02: create and load a collection inside a loop (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| Adobe security/performance page, "Use data collections" | A | "Running SQL queries in a loop often results in a performance bottleneck." Loading a collection runs at least one SQL query. | verified, supports |
| Adobe code review page | A | "Are queries executed in a loop?" | verified, supports (generic) |
| [`<B>/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836) and [`#L920-L930`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L920-L930) | B | `load()` → `loadWithFilter()` → `getData()` → `_fetchAll($select)`, so each load runs a SELECT. | verified, supports |
| [`<B>/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441) | B | `getSize()` runs `fetchOne` on a COUNT select, which is one query per new collection. | verified, supports |

Verdict: keep ✅. The SPEC currently cites these sources only as "там же" (same as above). Replace that with explicit links.

---

### P-LOOP-03: `save()`/`delete()` in a loop (✅, warning)

| Source | Level | Confirms | Status |
|---|---|---|---|
| Adobe security/performance page, "Use data collections" | A | "Running SQL queries in a loop often results in a performance bottleneck." This is generic: the section is about loading, not saving. | verified, partial support |
| Adobe code review page | A | "Are queries executed in a loop?" | verified, supports (generic) |
| [Adobe Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines/) 6.4.3.2 | A | "Service contracts SHOULD support batch data processing." It also says a persisting method should accept an array of entities. | verified, supports intent |
| [`<B>/lib/internal/Magento/Framework/Model/AbstractModel.php#L680-L684`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L680-L684), [`#L881-L885`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L881-L885) | B | `save()`/`delete()` delegate to the resource model, so each call is a write per entity. Both are marked "we don't recommend this approach anymore". | verified, supports |

Verdict: keep ✅ at `warning`. No A source says to batch writes in loops specifically. TG 6.4.3.2 and the generic "queries in a loop" item are enough for `warning`. Note: `save()` in import/cron loops is often unavoidable when no batch API exists. Keep severity `warning`, not `error`.

---

### P-LOOP-04: `getProductUrl()` in a loop without `addUrlRewrite()` (⏳)

| Source | Level | Confirms | Status |
|---|---|---|---|
| SPEC link `https://github.com/magento/magento2/blob/2.4-develop/app/code/Magento/Catalog/Model/Product/Url.php` | B (but **branch, not pinned**) | Not acceptable as written: the source policy requires a commit link. | violates policy, replace |
| [`<B>/app/code/Magento/Catalog/Model/Product/Url.php#L154-L183`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/Product/Url.php#L154-L183) | B | If the product has no `request_path` (and no `url_data_object`), `getUrl()` calls `urlFinder->findOneByData()` (L174). `getProductUrl()` (L100-L107) calls `getUrl()`. | verified, supports |
| [`<B>/app/code/Magento/UrlRewrite/etc/di.xml#L10`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/UrlRewrite/etc/di.xml#L10) and [`<B>/app/code/Magento/UrlRewrite/Model/Storage/DbStorage.php#L107-L131`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/UrlRewrite/Model/Storage/DbStorage.php#L107-L131) | B | `UrlFinderInterface` resolves to `DbStorage`, which runs `fetchRow`/`fetchAll` per lookup. That makes one query per product. | verified, supports |
| [`<B>/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L1455-L1474`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L1455-L1474), [`#L1481-L1504`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L1481-L1504) | B | `addUrlRewrite()` uses one `findAllByData` for all ids and sets `request_path` on each item. This is the batch fix. | verified, supports |

Verdict: keep ⏳. Replace the branch link with the pinned links above. Wording: also mention `getUrlModel()->getUrl()`, which follows the same path. FP note: if `request_path` is already set (for example from a category listing), no query runs.

---

### P-LOOP-05: `getCategoryIds()` in a loop without `addCategoryIds()` (⏳)

| Source | Level | Confirms | Status |
|---|---|---|---|
| SPEC link `.../blob/2.4-develop/app/code/Magento/Catalog/Model/Product.php` | B (branch, **not pinned**) | Not acceptable as written. | violates policy, replace |
| [`<B>/app/code/Magento/Catalog/Model/Product.php#L753-L769`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/Product.php#L753-L769) | B | If `category_ids` is not set, the method calls `_getResource()->getCategoryIds($this)`. | verified, supports |
| [`<B>/app/code/Magento/Catalog/Model/ResourceModel/Product.php#L277-L281`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product.php#L277-L281) → [`<B>/app/code/Magento/Catalog/Model/ResourceModel/Product/CategoryLink.php#L54-L69`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/CategoryLink.php#L54-L69) | B | Each call runs one `fetchAll` with `where('product_id = ?')`, so there is one query per product. | verified, supports |
| [`<B>/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L2163-L2200`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L2163-L2200) | B | `addCategoryIds()` runs one `IN (?)` query for all loaded items. **Note:** it only works on already-loaded items. If `_items` is empty it returns without setting the flag (L2168-L2171). | verified, supports; affects detection |

Verdict: keep ⏳. Replace the branch link. Detection: `addCategoryIds()` called before `load()` does nothing, so the rule must not treat "addCategoryIds appears in the method" as a fix unless it is called after load or iteration. Otherwise it produces FNs.

---

### P-LOOP-06: `getAttributeText()` / `getFrontend()->getValue()` in a loop (⏳)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [magento2#38934](https://github.com/magento/magento2/issues/38934) | B | Labels: "Issue: Confirmed" and "Reproduced on 2.4.x". It was confirmed "Looking at the codebase", and the issue contains no timings. Paraphrase: `Table::getSpecificOptions` "loads data from Database every time attribute value is requested". The issue is OPEN. | verified, supports (confirmed, no measurements) |
| [`<B>/app/code/Magento/Eav/Model/Entity/Attribute/Source/Table.php#L106-L119`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Attribute/Source/Table.php#L106-L119), [`#L139-L165`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Attribute/Source/Table.php#L139-L165) | B | At the pinned commit, `getSpecificOptions()` still creates and `load()`s an option collection on each call, and `getOptionText()` calls it (L147). The issue is still current. | verified, supports |
| [`<B>/app/code/Magento/Catalog/Model/Product.php#L1796-L1801`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/Product.php#L1796-L1801), [`<B>/app/code/Magento/Eav/Model/Entity/Attribute/Frontend/AbstractFrontend.php#L160-L185`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Attribute/Frontend/AbstractFrontend.php#L160-L185), [`#L332-L339`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Attribute/Frontend/AbstractFrontend.php#L332-L339) | B | `getAttributeText()` → `getSource()->getOptionText()`. `getFrontend()->getValue()` → `getOption()` → `getOptionText()`, but only for `select`/`boolean`/`multiselect` input. | verified, supports |

Verdict: keep ⏳. Add the pinned core links next to the issue, because the issue alone has no measurements. FP note: the cost applies only when the source model is `Eav\...\Source\Table` (the default for select/multiselect without a custom `source_model`). Custom or Boolean source models may not query the DB. `getFrontend()->getValue()` on text inputs does no lookup.

---

### P-LOOP-07: composite product type methods in a loop (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [`<B>/app/code/Magento/ConfigurableProduct/Model/Product/Type/Configurable.php#L1411-L1420`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/ConfigurableProduct/Model/Product/Type/Configurable.php#L1411-L1420) (cited in SPEC) | B | `getUsedProducts()` builds `getConfiguredUsedProductCollection()` and calls `getItems()` per parent product. The result is cached on that product instance only. Line range is correct. | verified, supports |
| [`<B>/.../Configurable.php#L490-L508`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/ConfigurableProduct/Model/Product/Type/Configurable.php#L490-L508) | B | `getConfigurableAttributes()` loads an attribute collection per product (cached per product). | verified, supports |
| [`<B>/app/code/Magento/ConfigurableProduct/Model/ResourceModel/Product/Type/Configurable.php#L159-L187`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/ConfigurableProduct/Model/ResourceModel/Product/Type/Configurable.php#L159-L187), [`#L195-L208`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/ConfigurableProduct/Model/ResourceModel/Product/Type/Configurable.php#L195-L208) | B | `getChildrenIds`/`getParentIdsByChild` run one SELECT per call and **accept arrays** (`IN (?)`), so a batch alternative exists. | verified, supports |
| [`<B>/app/code/Magento/GroupedProduct/Model/Product/Type/Grouped.php#L203-L237`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GroupedProduct/Model/Product/Type/Grouped.php#L203-L237) | B | `getAssociatedProducts()` builds and iterates a product collection per grouped product. | verified, supports |
| [`<B>/app/code/Magento/Catalog/Model/ResourceModel/Product.php#L289-L298`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product.php#L289-L298) | B | `getIdBySku()` runs one `fetchOne` per SKU. **It lives on `Catalog\Model\Product` (L709-L712) and the product resource, not on `Product\Type\AbstractType`.** | verified; detection mismatch |
| ECG LoopSniff | C | not opened (supplementary only) | not checked |

Verdict: keep ✅. Wording or detection change: `getIdBySku` is not a type-instance method. Either detect it on `Catalog\Model\Product` / `ResourceModel\Product` or move it to P-LOOP-01. `getUsedProductIds` is `@deprecated 100.1.1` (L578-L588) and calls `getUsedProducts`.

---

### P-LOOP-08: `in_array()` with a non-literal haystack in a loop (⏳, info)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [magento2#36386](https://github.com/magento/magento2/issues/36386) | B | Labels: "Issue: Confirmed", "Area: Performance", "Progress: done". Measured: foreach+`in_array` "around **5.527919 seconds**" vs `array_intersect_key` "around **0.012552**" with 100,000 values. The SPEC's "5,5 с → 0,013 с" matches. The real reindex time fell from about 5 min to about 40 s. | verified, supports |
| [`<B>/app/code/Magento/Elasticsearch/Model/Adapter/BatchDataMapper/ProductDataMapper.php#L372-L391`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Elasticsearch/Model/Adapter/BatchDataMapper/ProductDataMapper.php#L372-L391) | B | The fix is in core. The L381 comment reads: "array_flip() + foreach { isset() } is much faster than foreach { in_array() }". (PR #36306 itself shows state CLOSED; the change is present at the pinned commit.) | verified, supports |

Verdict: keep ⏳ at `info`. The measurement is a synthetic micro-benchmark with 100k×100k elements. Keep FP `med` and say in the docs that the cost matters only for large haystacks.

---

### P-COL-01: `count($collection)` instead of `getSize()` (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [`<B>/lib/internal/Magento/Framework/Data/Collection.php#L858-L862`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L858-L862) (cited) | B | `count()` calls `$this->load()` and then counts `_items`. The line range is exact. | verified, supports |
| [`<B>/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441) (cited) | B | `getSize()` runs `fetchOne(getSelectCountSql())` and caches the result in `_totalRecords`. The line range is exact. | verified, supports |
| ECG CollectionCountSniff | C | not opened | not checked |

Verdict: keep ✅. FP note: if the collection is going to be loaded and iterated anyway, `count()` after loading is free. The docs should say so.

---

### P-COL-02: `getFirstItem()` without `setPageSize(1)` (✅, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [`<B>/lib/internal/Magento/Framework/Data/Collection.php#L305-L315`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L305-L315) (SPEC cites L305-L313) | B | `getFirstItem()` calls `$this->load()` and returns `current($this->_items)`. It adds no LIMIT of its own. The method ends at L315, so update the range. | verified, supports |
| [`<B>/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763) | B | `_renderLimit()` applies `limitPage()` **only `if ($this->_pageSize)`**. | verified, supports |
| [`<B>/lib/internal/Magento/Framework/Data/Collection.php#L84`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L84), [`#L578-L582`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L578-L582), [`#L590-L594`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L590-L594), [`#L250-L257`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L250-L257) | B | `protected $_pageSize = false;` (L84). `setCurPage()` only sets `_curPage` (L580). `setPageSize()` sets `_pageSize` (L592). `getCurPage()` only reads `_curPage` (L250-L257). | verified |
| [`<B>/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1213-L1215`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1213-L1215) | B | The EAV `_loadEntities()` uses the same guard: `if ($this->_pageSize) { ... limitPage(...) }`. | verified |
| ECG GetFirstItemSniff, best-practices skill | C | not opened | not checked |

**`setCurPage()` without `setPageSize()`:** this produces **no LIMIT**. `_pageSize` stays `false` (L84). `_renderLimit()` (AbstractDb L758) and `_loadEntities()` (EAV L1213) both skip `limitPage()`. `setCurPage(1)` alone therefore still loads the whole result set.

Verdict: keep ✅ at `error`. **Detection change needed:** the SPEC's "Обнаружение" (detection) column lists `setCurPage` as a suppressor. Remove it: only `setPageSize(...)`, `getSelect()->limit(...)`/`limitPage(...)`, or a known single-row filter should suppress the rule. Update the cited range to L305-L315.

---

### P-COL-03: `addAttributeToSelect('*')` (✅, marked 🔎)

| Source | Level | Confirms | Status |
|---|---|---|---|
| Adobe docs (developer.adobe.com / experienceleague) | A | **None found.** Searched the `AdobeDocs` org on GitHub (the only hit is a tutorial that lists explicit attributes) and searched the web for both Adobe domains. No page discusses `'*'` cost. | not found |
| magento/magento2 issues | B | **None found** about `'*'` specifically. #40216 (Confirmed) measured an O(attributes×products) cost in `_loadAttributes`, but it is "Progress: done". The O(n·m) loop is gone at the pinned commit (AbstractCollection L1303-L1324), so it is not current evidence. | does not support (fixed) |
| [`<B>/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L583-L588`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L583-L588) | B (mechanism) | For `'*'`, it calls `loadAllAttributes()` and adds **every** entity attribute to `_selectAttributes`. | verified, supports mechanism |
| [`<B>/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1266-L1297`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1266-L1297) | B (mechanism) | `_loadAttributes()` builds a select per backend table for all selected non-static attributes and `fetchAll`s the values. Using `'*'` means reading every value table for every attribute. | verified, supports mechanism |
| [`<B>/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L682-L709`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L682-L709) | B (mechanism) | With the flat catalog, `'*'` adds all flat table columns. | verified |
| skill, ECG | C | not opened | not checked |

Observation (Magento 2.4.9 vendor on the host, read-only grep): 12 non-test files in `vendor/magento/*` call `addAttributeToSelect('*')`, for example in Customer DataProvider, Sales Reorder and Bundle Product Type. Core itself treats `'*'` as acceptable in admin/data-provider contexts, so a flat `warning` with FP `low` is optimistic.

Verdict: **demote ✅ → ⏳** (not 🧪). A B-level mechanism source exists in core code, which satisfies the policy minimum. But no A source and no measured B source shows real cost, and core uses `'*'` itself. Change severity `warning` → `info` and FP `low` → `med` until calibration. If the owner reads policy B strictly as "core code that demonstrates the cost" with measurements, then **demote to 🧪**.

---

### P-COL-04: iterating a collection without pagination in cron, CLI or export (⏳)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe Technical guidelines 6.4.3.3](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines/) (cited) | A | Exact text: "Batch retrieval operations MUST accept `SearchCriteriaInterface` and return `SearchResultInterface` to support pagination." This is about **service contract method signatures**, not about iterating collections in cron or CLI code. | verified, **does not support** the rule's claim |
| TG §13 (CLI), §16 (Cron) | A | Contain nothing about pagination or memory (16.1 is only about idempotency). | verified, does not support |
| Other Adobe docs (backend-performance page, web search) | A | Nothing found about paginating collections in cron or CLI. | not found |
| [`<B>/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836) with [`#L756-L763`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763) | B (mechanism) | Without `_pageSize`, `load()` fetches all rows and creates one item object per row in `_items`. The memory cost grows with table size. | verified, supports mechanism |
| [`<B>/lib/internal/Magento/Framework/DB/Query/Generator.php`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/DB/Query/Generator.php) (docblock L42-L65) | B (supporting) | Core provides a batch query iterator ("Generate select query list with predefined items count in each select item"). | verified, indirect |

Verdict: keep ⏳ only if the cited source is replaced. Swap TG 6.4.3.3 for the core mechanism links above. Without that change the cited A source does not support the rule, and the rule would have to be **demoted to 🧪**. No A source was found.

---

### P-COL-05: `fetchAll()`/`fetchCol()` without `limit()` (🧪)

Skipped as instructed. Its only source is ECG FetchAllSniff (C), which matches its 🧪 status.

---

### Notes for SPEC edits

1. **P-LOOP-04, P-LOOP-05**: the links point to the `2.4-develop` branch, which violates section 2. Replace them with the pinned links:
   - P-LOOP-04: `Url.php#L154-L183`, `DbStorage.php#L107-L131`, `Product/Collection.php#L1455-L1474` / `#L1481-L1504`
   - P-LOOP-05: `Product.php#L753-L769`, `CategoryLink.php#L54-L69`, `Product/Collection.php#L2163-L2200`
2. **P-LOOP-02, P-LOOP-03**: replace "там же" (same as above) with explicit sources. For P-LOOP-03 add TG 6.4.3.2.
3. **P-LOOP-01**: the first link's text "Adobe: extension performance" is fine. Quote the section name "Use data collections" in `docs/rules/P-LOOP-01.md`. Add the pinned `AbstractModel.php#L564-L568` and `ProductRepository.php#L308-L328`. Note that the code review page also recommends "factories or repositories" in loops, for data isolation.
4. **P-LOOP-06**: add the pinned `Source/Table.php#L106-L119` next to #38934. The issue has a "Confirmed" label but no measurements.
5. **P-LOOP-07**: `getIdBySku` belongs to `Catalog\Model\Product` or its resource model, not to `AbstractType`. Fix the detection or move the method to P-LOOP-01.
6. **P-LOOP-08**: optionally add the pinned `ProductDataMapper.php#L372-L391`, which holds the in-core fix and its comment.
7. **P-COL-02**: change the range to `Collection.php#L305-L315`, add `AbstractDb.php#L756-L763` (`_renderLimit`), and **remove `setCurPage` from the suppressors in the detection column**.
8. **P-COL-03**: drop 🔎. Set MVP ⏳, Sev `info`, FP `med`, with B mechanism sources `Eav/.../AbstractCollection.php#L583-L588` and `#L1266-L1297`. Or set 🧪 if a mechanism-only B source is not considered enough.
9. **P-COL-04**: replace TG 6.4.3.3 (it is about service contract signatures) with `AbstractDb.php#L813-L836` plus `#L756-L763`. Otherwise demote to 🧪.
10. **Section 12**: the developer.adobe.com pages render client-side. For reproducible verification, record the AdobeDocs source commit, e.g. `AdobeDocs/commerce-php@80d81d19` `src/pages/best-practices/extensions/security.md` L70-L72 and `src/pages/coding-standards/technical-guidelines.md` L569-L571.
11. The C sources (ECG sniffs, skill) were not opened in this audit. They are supplementary and do not affect the verdicts.

---

## Source audit: SPEC sections 4.3 (P-DI-*) and 4.4 (P-FPC-*)

Audit date: 2026-10-21. Core base: `magento/magento2@a385d02f60ee6dfa052f12cdc7d82e956581fba5`.
Core files were fetched from `raw.githubusercontent.com` at that commit; line numbers were checked against the fetched files.

How the Adobe docs were checked:
- **Every developer.adobe.com URL in SPEC 4.3/4.4 returns HTTP 404 when written with a trailing slash** (`.../technical-guidelines/`, `.../private-content/`, `.../public-content/`, `.../proxies/`). This happened with both curl and WebFetch. The same URLs **without** the trailing slash return 200, and the quotes below were checked in that live HTML. (Directory index pages are the other way round: `.../object-manager/` and `.../indexing/` return 200 with the slash and 404 without it.)
- I also cross-checked the text against the docs source repository, AdobeDocs/commerce-php (`main`, latest commit 80d81d19, 2026-09-23).

Abbreviation used below: `GH` = `https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5`.

---

### P-DI-01: Session or CookieManager in the constructor without a Proxy (✅, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) §9.10 | A | "The Request, Session, and Cookie objects MUST NOT be injected in an object constructor." §9.10 still exists with this meaning. It bans injection outright; it does not say "without Proxy". | verified |
| Same page §2.5 | A | "Proxies and interceptors MUST NEVER be explicitly requested in constructors." So the fix must be a Proxy set in di.xml, not a `\Proxy` type hint. MCS already flags the type hint (`Magento2.Classes.DiscouragedDependencies`). | verified |
| Same page §2.17.1 | A | "Proxies SHOULD be used for lazy-loading optional dependencies." | verified |
| (code) SessionManager.php [GH/lib/internal/Magento/Framework/Session/SessionManager.php#L100-L125](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Session/SessionManager.php#L100-L125) | B | The constructor ends with `$this->start();` (L124). | verified |
| (code) SessionManager::start() [#L162-L208](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Session/SessionManager.php#L162-L208) | B | Calls `session_start()` (L187) and `renewCookie(null)` (L195) when no session is active. This only happens when `SessionStartChecker::check()` is true, which means not CLI ([SessionStartChecker.php#L34-L37](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Session/SessionStartChecker.php#L34-L37)). | verified |
| (code) SessionManager::renewCookie() [#L226-L251](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Session/SessionManager.php#L226-L251) | B | Calls `setPublicCookie` only when a session cookie already exists and a cookie lifetime is set. | verified |
| (code) Customer/Checkout `Session extends SessionManager` ([Customer/Model/Session.php#L28](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/Model/Session.php#L28)) | B | Supports matching `*\Session` subclasses. | verified |
| (code) PhpCookieManager::__construct [GH/lib/internal/Magento/Framework/Stdlib/Cookie/PhpCookieManager.php#L76-L86](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Stdlib/Cookie/PhpCookieManager.php#L76-L86) | B | **Contradicts the performance rationale for CookieManager.** Its constructor only assigns dependencies: no session, no I/O. | verified (does not support perf claim for CookieManager) |
| (code) varnish6.vcl [#L174-L188](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/etc/varnish6.vcl#L174-L188), PageCache Kernel [#L154-L183](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/PageCache/Kernel.php#L154-L183) | B | **Contradicts the wording "sends Set-Cookie even on cacheable pages".** Varnish runs `unset beresp.http.set-cookie` on cacheable GET responses (L188), and the built-in FPC runs `clearHeader('Set-Cookie')` (Kernel L169). | verified (contradicts wording) |
| [Atwix: session depersonalization](https://www.atwix.com/magento-2/what-is-session-depersonalization/) | C | Describes session depersonalization on cacheable GET pages. It does **not** discuss proxies or Set-Cookie. It names `afterGenerateXml`, but core uses `afterGenerateElements`, so the article is partly outdated. | verified (supplementary, does not support the Set-Cookie claim) |

Verdict: **keep ✅**, with a wording change and a severity split.
- New rationale: §9.10 forbids the injection, and the session constructor runs `session_start()` (session storage read and lock) on every instantiation outside CLI.
- Drop "sends Set-Cookie even on cacheable pages".
- CookieManagerInterface: keep it under §9.10 (A) but lower it to **warning**. No source shows a performance cost when it is constructed.
- Fix text: Proxy via di.xml only (§2.5).

### P-DI-02: Constructor does more than assign dependencies and validate arguments (✅, warning)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) §2.3 | A | "Class constructor can have only dependency assignment operations and/or argument validation operations." §2.3 still exists with this meaning. | verified |
| Same page §2.3.1 (example) | A | The official example validates arguments with a `foreach` + `instanceof` + `throw` inside the constructor. | verified |

Verdict: **keep ✅**. Wording change: the allow-list must also accept validation loops and conditionals (`foreach`/`if` that only `throw`), as in the §2.3.1 example. It must also allow the BC pattern `$x ?: ObjectManager::getInstance()->get(...)` (see P-DI-06), otherwise most core-style code produces false positives.

### P-DI-03: Event dispatch in a constructor (✅, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) §2.3.2 | A | "Events MUST NOT be triggered in constructors." §2.3.2 still exists with this meaning. | verified |

Verdict: **keep ✅**.

### P-DI-04: Heavy dependencies in a CLI command constructor without a Proxy (⏳, info)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Proxies](https://developer.adobe.com/commerce/php/development/components/proxies) | A | Constructor injection causes a chain of instantiation, and a Proxy defers an expensive constructor. The page does **not** mention CLI commands. | verified (supports the technique, not the CLI-specific claim) |
| [Adobe: Custom CLI commands](https://developer.adobe.com/commerce/php/development/cli-commands/custom) | A | Only says commands may use constructor DI. No guidance on proxies or performance. | verified (does not support) |
| (code) Console Cli::getDefaultCommands/getApplicationCommands [GH/lib/internal/Magento/Framework/Console/Cli.php#L196-L232](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Console/Cli.php#L196-L232) | B (new) | `objectManager->create(CommandListInterface::class)` and then `getCommands()`. All commands in the `CommandList` di.xml array are instantiated, together with their constructor dependencies, on every `bin/magento` run. | verified |
| (code) Console CommandLoader [GH/lib/internal/Magento/Framework/Console/CommandLoader.php#L49-L55](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Console/CommandLoader.php#L49-L55) | B (new) | Commands registered through `Magento\Framework\Console\CommandLoader` are created lazily, only when invoked. The rule must not fire for these. | verified |
| run_as_root/magento-cli-auto-proxy (packagist) | C | Not opened; it is an alternative tool, not a basis. | unverified (not needed) |

Verdict: **keep ⏳**. Add the B source Cli.php L196-L232 (the A source covers only the generic technique). Detection change: only commands registered in the `Magento\Framework\Console\CommandList` `commands` argument count; exclude commands registered through `CommandLoader`. No magento/magento2 issue with measurements was found (searches for CLI slowness or command-constructor proxies returned nothing relevant).

### P-DI-05: 🧪, skipped (Adobe Proxies page, heuristic). Not audited.

### P-DI-06: Direct `ObjectManager::getInstance()` in PHP classes (✅, warning)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) (no section given) | A | **Does not support.** The only ObjectManager mentions are §11.2.2/§11.2.3, about unit tests. | does not support |
| [Adobe: Object Manager, Usage rules](https://developer.adobe.com/commerce/php/development/components/object-manager/) | A (replacement) | "You do not call the object manager directly because the framework handles this automatically." The Exceptions list allows using it "to maintain backward compatibility for a constructor", in factories/proxies, in `__wakeup()`-style magic methods and in integration-test fixtures. | verified |
| ECG `EcgM2.Performance.ObjectManagerUsage` | C | ECG sniff that flags any "ObjectManager" token in PHP/PHTML. | verified (supplementary) |
| MCS overlap check | n/a | MCS has only `Magento2.Templates.ObjectManager` (templates), so the PHP-class rule does not duplicate MCS. | verified |

Verdict: **keep ✅**, but **replace the source**: use the Object Manager page instead of the Technical guidelines. Wording change: exempt the documented exceptions, above all the constructor BC fallback `$dep ?: ObjectManager::getInstance()->get(...)`, plus factories, proxies and `__wakeup`/`__sleep`. This is a design and quality rule, not a performance rule; consider saying so in the rule page.

---

### P-FPC-01: `$_isScopePrivate` in a block (✅, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content) | A | "Do not use the `$_isScopePrivate` property in your blocks. This property is obsolete and will not work properly." | verified |
| (code) AbstractBlock `$_isScopePrivate` [GH/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L158-L166](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L158-L166) | B (new) | The property is marked `@deprecated` and its docblock links to the private-content page. | verified |

Verdict: **keep ✅**.

### P-FPC-02: Block reads customer data from the session (✅, warning)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [FPC strategy](https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html) | C | Anti-pattern `$this->customerSession->getCustomer()` in a block, with the warning "This makes the ENTIRE PAGE non-cacheable!" Does not mention `getQuote`. | verified (C only) |
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | **Weak.** It covers context variables and invalidation, and its own example plugin reads the customer session. It does not say blocks must not read the session. | verified (does not directly support) |
| [Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) §9.3/§9.4 | A (replacement) | "HTML markup generated on server MUST NOT contain user-specific data." §9.3 says customer-specific data is rendered in the browser. | verified |
| [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content) intro | A | Private content is "specific to individual users" and should be handled on the client (customer-data JS). | verified |
| (code) Customer DepersonalizePlugin::afterGenerateElements [GH/app/code/Magento/Customer/Model/Layout/DepersonalizePlugin.php#L101-L112](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/Model/Layout/DepersonalizePlugin.php#L101-L112) + [PageCache DepersonalizeChecker#L51-L58](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/DepersonalizeChecker.php#L51-L58) | B (new) | On cacheable GET/HEAD pages with FPC on, the customer session storage is cleared after layout generation. Blocks that read customer data then get empty or wrong values, or end up being made non-cacheable. | verified |
| [Atwix](https://www.atwix.com/magento-2/what-is-session-depersonalization/) | C | Same depersonalization mechanism. | verified (supplementary) |

Verdict: **keep ✅**. Replace or add sources: TG §9.4 (A) and DepersonalizePlugin (B) should be the basis. Public content is not a direct basis.

### P-FPC-03: User-specific value in HTTP context (⏳, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) "Configure page variations" | A | "Context variables should not be specific to individual users", because a per-user variable yields a separate cached copy per user. | verified |
| (code) Http/Context::getData/getVaryString [GH/lib/internal/Magento/Framework/App/Http/Context.php#L107-L135](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Http/Context.php#L107-L135) | B (optional add) | Every non-default value goes into the sha256 vary string used as the cache key. | verified |

Verdict: **keep ⏳**.

### P-FPC-04: 🧪, skipped.
Note: B support exists if this rule is promoted later.
- varnish6.vcl [#L180-L187](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/etc/varnish6.vcl#L180-L187): a response is made uncacheable when it sets `X-Magento-Vary` but the request had none.
- Context.php [#L70-L78](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Http/Context.php#L70-L78).
- The public-content page (A) explains the `$default` argument for "newcomers".

### P-FPC-05: frontend/graphql code sets no-cache or private headers (✅, warning)

| Source | Level | Confirms | Status |
|---|---|---|---|
| (code) varnish6.vcl [#L167-L172](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/etc/varnish6.vcl#L167-L172) and [#L191-L200](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/etc/varnish6.vcl#L191-L200) | B | `Cache-Control ~ "private"` means uncacheable. `no-cache|no-store` means hit-for-pass for 120s. | verified |
| (code) PageCache Kernel::process [GH/lib/internal/Magento/Framework/App/PageCache/Kernel.php#L154-L183](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/PageCache/Kernel.php#L154-L183) | B (new) | The built-in FPC stores a page only if Cache-Control matches `public.*s-maxage`. | verified |
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) "Define caching behavior" | A | Shows `setHeader('Cache-Control', 'no-store, no-cache, ...')` in a controller as the **official** way to disable caching. This confirms the effect, but also shows the pattern can be intentional. | verified |

Verdict: **keep ✅ as warning**. Wording: "makes the response uncacheable by Varnish and the built-in FPC; verify that this is intended". Do not call it a defect. To reduce false positives, limit it to GET-reachable controllers and GraphQL query resolvers; POST-only actions are never cached.

### P-FPC-06: State change in a GET controller (✅, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | "Use only HTTP POST or PUT methods to change state (e.g., adding to a shopping cart…)". GET may be cached. | verified |
| [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content) | A | Same sentence. Section invalidation fires only on POST/PUT. | verified |

Verdict: **keep ✅**.

### P-FPC-07: Block with `cache_lifetime` and no `getCacheKeyInfo()` override that reads request/session/registry (⏳, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| (code) AbstractBlock::getCacheKeyInfo/getCacheKey [GH/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L1037-L1074](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L1037-L1074) | B | The default key is `[nameInLayout]` plus the crypt key. A `cache_key` data value overrides it entirely. Request and session are not part of the key. | verified |
| (code) AbstractBlock::getCacheLifetime/_loadCache [#L1101-L1155](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L1101-L1155) | B | The block_html cache is used only when `cache_lifetime` is set (not null/false) and the cache type is enabled. | verified |
| (code) Template::getCacheKeyInfo [GH/lib/internal/Magento/Framework/View/Element/Template.php#L327-L336](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/Template.php#L327-L336) | B | Template key = store code, template file, base_url, template. Nothing else. | verified |

Verdict: **keep ⏳**. Wording: also treat a block that sets a `cache_key` data value as "has a key". That only moves the problem, so still check what the key is built from.

### P-FPC-08: 🧪, skipped (C source only, community FPC strategy).

### P-FPC-09: Cache cleaning in code: `cleanType('full_page')`, `App\Cache::clean()` without tags (✅, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| (code) App/Cache::clean [GH/lib/internal/Magento/Framework/App/Cache.php#L96-L111](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Cache.php#L96-L111) | B | With empty `$tags`, the method loops over **every** frontend in `_frontendPool` and calls `clean()` on each. The code comments: "special case of cleaning by empty tags is deprecated". | verified |
| (code) TypeList::cleanType [GH/lib/internal/Magento/Framework/App/Cache/TypeList.php#L193-L199](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Cache/TypeList.php#L193-L199) | B (new) | Cleans the whole cache type instance. | verified |
| (code) PageCache Cache\Type::clean [GH/app/code/Magento/PageCache/Model/Cache/Type.php#L51-L55](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Cache/Type.php#L51-L55) + CacheInvalidate events.xml [#L27-L29](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/CacheInvalidate/etc/events.xml#L27-L29) | B (new) | `full_page` clean dispatches `adminhtml_cache_refresh_type`, which `FlushAllCacheObserver` handles. With Varnish, `cleanType('full_page')` purges all of Varnish. | verified |
| [FPC strategy](https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html) | C | Shows tag-based cleaning (`CLEANING_MODE_MATCHING_TAG`) as the right approach. Does not mention `cleanType`. | verified (supplementary) |

Verdict: **keep ✅**. Add the Type.php and events.xml B links. They are the strongest evidence for the `cleanType('full_page')` half of the rule.

### P-FPC-10: `reindexAll()` / `reindexList()` of the whole index in an observer, plugin or controller (✅, error)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: indexer configuration](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration) | A | **Weak.** It is about Update on Save versus Update by Schedule: "frequent reindexing degrades database performance which slows site performance". It says nothing about calling `reindexAll` from code. | verified (indirect only) |
| [Adobe: Indexing overview](https://developer.adobe.com/commerce/php/development/components/indexing/) | A (new) | "Full reindex, which means rebuilding all the indexing-related database tables". It also says Update on Save indexers "must use custom code (plugins, events …) in order to trigger reindexing". **That sentence contradicts flagging `reindexList()` in plugins and observers.** | verified |
| (code) Indexer::reindexAll [GH/app/code/Magento/Indexer/Model/Indexer.php#L452-L494](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Indexer/Model/Indexer.php#L452-L494) | B (new) | Runs `executeFull()` synchronously in the caller's process, sets the state to working and suspends the mview subscriptions. | verified |
| Core usage check (local 2.4.9 vendor, read-only grep) | n/a | No non-test core observer, plugin or controller calls `reindexAll()`. 33 non-test core plugin/observer/controller files call `reindexList()`/`reindexRow()` (e.g. CatalogSearch Fulltext plugins). | verified locally (2.4.9 vendor, not the pinned commit) |

Verdict: **keep ✅ for `reindexAll()` only**. Add the Indexer.php B link and the Indexing overview A link. **Remove `reindexList()`** from this rule, or move it to a 🧪 variant that fires only when the ID list is provably the whole entity set. Calling `reindexList`/`reindexRow` from plugins and observers is the documented Update on Save pattern and is used widely in core.

---

### Notes for SPEC edits

1. **Remove the trailing slash** from every developer.adobe.com leaf-page link in sections 4.3, 4.4 and 12 (`technical-guidelines`, `private-content`, `public-content`, `proxies`, `plugins`, `events-and-observers`, …). With the slash they return 404; without it, 200. Index pages (`object-manager/`, `indexing/`) are the reverse: they need the slash. The CI link checker should test the exact URL strings.
2. **Pin the core links.** Replace the `blob/2.4-develop/...` links in 4.3 and 4.4 with `blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/...#Lx-Ly` (section 2 requires commit-pinned links). The line ranges are listed above.
3. **P-DI-01:**
   - Rewrite the rationale. §9.10 bans injection; the SessionManager constructor calls `start()` → `session_start()` (L124, L187).
   - Delete "sends Set-Cookie even on cacheable pages": Varnish (vcl L188) and the built-in FPC (Kernel L169) strip Set-Cookie on cacheable responses.
   - Downgrade the CookieManagerInterface variant to warning, since its constructor is cheap.
   - Fix advice: Proxy via di.xml (§2.5; the MCS `DiscouragedDependencies` sniff flags `\Proxy` type hints).
4. **P-DI-02:** allow validation loops and conditionals (the §2.3.1 official example uses `foreach` + `throw`) and the `?: ObjectManager::getInstance()` BC fallback.
5. **P-DI-04:** add B source Cli.php L196-L232. Exclude commands registered through `Magento\Framework\Console\CommandLoader`, which are lazy.
6. **P-DI-06:** the Technical guidelines link does not support the rule. Replace it with the Object Manager page "Usage rules" and implement its documented exceptions (constructor BC fallback, factories/proxies, `__wakeup`/`__sleep`).
7. **P-FPC-01:** optionally add the B link to AbstractBlock L158-L166 (`@deprecated`).
8. **P-FPC-02:** replace the community FPC-strategy page as the primary source with TG §9.4 (A) and Customer DepersonalizePlugin L101-L112 (B). Keep docs.magento-opensource.com as C only.
9. **P-FPC-05:** reword to "response becomes uncacheable; confirm it is intended". Adobe's public-content page shows the no-cache header as a legitimate way to disable caching. Add the Kernel.php B link.
10. **P-FPC-09:** add the B links PageCache `Cache\Type::clean` L51-L55 and CacheInvalidate `events.xml` L27-L29 (full Varnish purge).
11. **P-FPC-10:** drop `reindexList()` from the default rule. Add the Indexer.php L452-L494 (B) and Indexing overview (A) links. The Experience League indexer-configuration page is only indirect support.
12. **P-FPC-04 (🧪):** B evidence exists (varnish6.vcl L180-L187, Context.php L70-L78, public-content `$default` explanation) if promotion is considered after calibration.
13. Technical guidelines section numbers checked during this audit, all present with the expected meaning: 2.3, 2.3.1, 2.3.2, 2.5, 2.17.1, 6.4.3.3 ("Batch retrieval operations MUST accept `SearchCriteriaInterface`…"), 9.3, 9.4, 9.10.

---

## Source audit: SPEC sections 4.5–4.8

Audit date: 2026-10-21. Core pinned at `magento/magento2@a385d02f60ee6dfa052f12cdc7d82e956581fba5`; every core file was fetched from `raw.githubusercontent.com` at that commit and line numbers were checked against the fetched file. Docs were fetched with curl and their text extracted (quotes are verbatim from the extracted text).


**General finding about URLs.** On developer.adobe.com several SPEC URLs **with a trailing slash return HTTP 404**, but work without it: `.../components/plugins/`, `.../best-practices/extensions/security/`, `.../coding-standards/technical-guidelines/`, `.../cache/page/private-content/`, `.../message-queues/configuration/`. Other pages behave the opposite way (`.../events-and-observers/`, `.../graphql/usage/caching/` and `.../message-queues/` work with the slash; `.../message-queues` without it returns 404). Each URL has to be checked on its own. A link-check job in CI is needed.

---

### P-PLG-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Plugins](https://developer.adobe.com/commerce/php/development/components/plugins) | A | "Avoid using around method plugins when they are not required because they increase stack traces and affect performance." | verified (URL without the trailing slash; the SPEC URL with the slash is 404) |

Verdict: keep ✅. Fix the URL (drop the trailing slash).

### X-PLG-02 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Plugins](https://developer.adobe.com/commerce/php/development/components/plugins) | A | Says nothing about render methods or hot paths. It does say plugins are inherited: "inherit from, classes that have plugins will also inherit plugins from the parent class", so a plugin on `AbstractBlock::toHtml` applies to every block. | the hot-path claim is not supported; the inheritance mechanism is verified |
| [AbstractBlock.php L666-L692](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L666-L692) | B | `toHtml()` runs once per rendered block (the same method dispatches the per-block events). | verified (shows how often it runs, not what a plugin costs) |
| [Layout.php L555-L571](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L555-L571) | B | `renderElement()` runs once per layout element. | verified (same caveat) |

No A/B source says that a plugin on these methods hurts performance. The A/B sources only establish that such a plugin is called for every block or element. Searching magento/magento2 issues returned nothing before GitHub's search rate limit stopped further queries, so the search is inconclusive.

Verdict: downgrade to ⏳. The Basis section must cite plugin inheritance (A) plus the per-block call (B), and state that "slow" is an inference. If the policy requires an explicit statement about performance, demote to 🧪.

### P-EXT-01 (⏳)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Security and performance](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | Discourages `curl_*` for **security** reasons and recommends `\Magento\Framework\HTTP\Adapter\Curl`. Says nothing about latency or synchronous calls. | does not support (and the rule also flags the very adapter this page recommends) |
| [Adobe: Backend performance](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/backend-performance) | A | Lists "Third-party API calls" among performance issues to analyze; example: "found an API call that was consuming 3.5% of the performance time" on a product page. | verified (general, but it does concern the storefront request path) |

Verdict: keep ⏳. Change the wording: drop the security page from Basis, or cite it only for "use the HTTP adapter". Basis rests on the backend-performance page.

### P-EXT-02 (⏳, 🔎)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Backend performance](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/backend-performance) | A | Third-party API calls are a general category of performance problems. Does not mention checkout, orders or observers. | verified, but does not support the checkout-specific claim |
| [Adobe: Webhooks overview](https://developer.adobe.com/commerce/extensibility/webhooks/) (found during the search) | A | "Create a webhook only if synchronous communication with the external server is critical." The same page says synchronous calls are **required** for "order totals, taxes, payments". | verified. It concerns the Adobe webhooks product, not PHP observers, and it undermines the `error` severity: synchronous checkout calls are a legitimate case. |
| [Adobe: Split payment POC architecture](https://experienceleague.adobe.com/en/docs/commerce-learn/tutorials/extensibility/adobe-developer-app-builder/create-a-split-payment-poc/architecture-and-decisions) | A | A checkout guard "must be synchronous in the Commerce request cycle". This argues against a blanket rule. | verified, but does not support the rule |
| best-practices skill | C | supplementary | not used as a basis |

No A/B source specifically says "external call in a checkout or order observer is bad". The GitHub issue search was inconclusive because of the rate limit.

Verdict: demote to 🧪, or fold it into P-EXT-01 as a `warning` whose message mentions checkout. The `error` severity is not justified: payment, tax and fraud modules make these calls legitimately, so false positives would be high.

### X-LAY-01 (✅), priority check

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Code review best practices](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review) | A | In the Performance section it says, verbatim: "Can you spot any cachable="false" attributes? Are they applied correctly?" (Adobe's typo "cachable"). It does **not** say that the attribute disables FPC for the page. | verified, but weak: it is a review checklist item only |
| [Adobe: Page caching overview](https://developer.adobe.com/commerce/php/development/cache/page/) | A | "If any block in a layout is designated as uncacheable, the entire page is uncacheable." Also: "Do not configure content pages (i.e., catalog, product, and CMS pages) to be uncacheable." | verified, **strongest source; add it** |
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | "Using cacheable="false" inside the default.xml file disables caching for all pages on the site." | verified, **add it** (directly supports `error` for `default.xml`) |
| [Layout.php L1192-L1210](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L1192-L1210) | B | `isCacheable()` runs the XPath `//block[@cacheable="false"]`; one such block **that is present in the structure** (`hasElement`, L1199-L1200) makes the whole layout non-cacheable. | verified |
| [PageCache LayoutPlugin.php L70-L75](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Layout/LayoutPlugin.php#L70-L75) | B | Public cache headers are set only `if ($subject->isCacheable() ...)`. | verified |
| [elements.xsd L184-L221](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout/etc/elements.xsd#L184-L221) (`blockType`, `cacheable` at L219) and [L308-L334](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout/etc/elements.xsd#L308-L334) (`blockReferenceType`) | B | `cacheable` is an attribute of `<block>` (L219) and of `uiComponentType` (L242). It is **not** an attribute of `<referenceBlock>`, whose attributes are only name, template, class, group, display and remove. | verified |
| [Sam James: non-cacheable blocks](https://www.samdjames.uk/docs/platforms/magento/development/non-cacheable-blocks/) | C | supplementary | reachable (HTTP 200); content not audited |

Implementation facts from core that affect precision:
- Only `<block cacheable="false">` counts. On `<referenceBlock>` the attribute fails XSD validation and is not matched by the XPath. On `<uiComponent>` the XSD allows it, but `isCacheable()` ignores it because the XPath is `//block`.
- A `cacheable="false"` block that is later removed (`remove="true"` or `<remove>`) does **not** disable FPC, because `hasElement` is false. This is a known false-positive source. It is acceptable for a static analyzer but should be documented.

Verdict: keep ✅. Replace or extend Basis with the page-caching overview and public-content pages (A) plus `Layout::isCacheable` (B). The code-review page stays as a secondary A source. The `error` for `default.xml`, `catalog_*` and `cms_*` is directly backed by A sources.

### X-LAY-02 (✅), priority check

| Source | Level | Confirms | Status |
|---|---|---|---|
| [MergePlugin.php L42-L54](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Layout/MergePlugin.php#L42-L54) | B | Throws `\LogicException` when an entity-specific handle's update XML contains `ttl=`: "must not contain blocks with 'ttl' attribute specified". | verified (the SPEC links `2.4-develop`; re-pin to the commit) |
| [Result/Page.php L236-L252](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Result/Page.php#L236-L252) | B | Entity-specific handles are `<handle>_<key>_<value>`, registered when `$entitySpecific` is true. Product view registers `id` and `sku` as entity-specific; `type` and `attribute_set` are not ([Catalog Helper Product/View.php L186-L188](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Helper/Product/View.php#L186-L188)). Category registers `id` only ([Category/View.php L358-L365](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Controller/Category/View.php#L358-L365)). | verified, so the `*_id_*` / `*_sku_*` patterns are correct |
| [Adobe: Varnish ESI](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/varnish/use-varnish-esi) | A | An ESI tag is created when Varnish is used and "A XML layout block element is added with a ttl attribute". Says nothing about entity handles or `default.xml`. | verified (mechanism only) |
| [Magento_Theme default.xml L76](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/view/frontend/layout/default.xml#L76) | B | Core itself declares `catalog.topnav` with `ttl="3600"` in **`default.xml`**. | verified; **contradicts the `default.xml` warning half** |

Verdict: keep ✅ for the `error` part (entity-specific handles). Change the wording for the `default.xml` part: no A/B source supports it, core does it itself, and Varnish caches ESI fragments for the ttl, so "an extra ESI request on every page" is overstated. Drop that part or move it to 🧪 as `info`. Re-pin the MergePlugin link.

### X-EVT-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [AbstractModel.php L604-L609](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L604-L609) | B | `_afterLoad()` dispatches `model_load_after` (for every model) and `{prefix}_load_after` on every load. | verified (re-pin; the SPEC links the branch) |
| [AbstractModel.php L590-L597](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L590-L597) | B | Same for `model_load_before`. | verified |
| [Adobe: Observers best practices](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) (suggested addition) | A | Keep observers efficient, "especially important when your observer is listening to an event that is frequently dispatched." | verified |

Verdict: keep ✅. Wording change: `model_load_after` fires for every model, while `<prefix>_load_after` fires on every load of one entity type, not of "every model". The message should tell the two apart.

### X-EVT-02 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| AbstractModel.php: [L691-L695](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L691-L695) `model_save_commit_after`, [L723-L730](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L723-L730) `model_save_before`, [L861-L867](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L861-L867) `model_save_after`, [L893-L903](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L893-L903) `model_delete_before`, [L912-L917](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L912-L917) `model_delete_after`, [L926-L930](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L926-L930) `model_delete_commit_after` | B | The generic `model_*` events are dispatched with `['object' => $this]` from the base class, so they fire for every AbstractModel subclass. | verified |
| [Adobe: Observers best practices](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) | A | "frequently dispatched" sentence (as above) | verified |

Verdict: keep ✅.

### X-EVT-03 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Layout.php L555-L571](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L555-L571) | B | `core_layout_render_element` is dispatched inside `renderElement()`, once per element. | verified |
| [AbstractBlock.php L666-L692](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L666-L692) | B | `view_block_abstract_to_html_before` and `_after` are dispatched in every `toHtml()`. The SPEC cites only Layout.php; **add AbstractBlock.php**. | verified |
| [Adobe: Observers best practices](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) | A | "especially important when your observer is listening to an event that is frequently dispatched." | verified |

Verdict: keep ✅. Add the AbstractBlock link and re-pin both. Note: core itself observes `core_layout_render_element` (PageCache `ProcessLayoutRenderElement`), so the core module must be excluded from findings.

### X-AREA-01 (⏳)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Security and performance](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "It is NOT RECOMMENDED to register everything in global area, as the bootstrapping process will become much heavier." | verified (URL without the slash) |
| [Adobe: Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/) | A | "Declare the observer in the appropriate area." | verified |
| [Adobe: Observers best practices](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) | A | Repeats the "NOT RECOMMENDED ... global area" sentence for observers. | verified |

Verdict: keep ⏳. The sources fully support it; ⏳ is justified by the medium false-positive rate of the area heuristic, not by the sources.

### X-SEC-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content) | A | An empty `<action>` "tells The application to invalidate all sections"; for all sections "use * as section name". This documents the mechanism **as a supported feature** and gives no performance warning. | verified (mechanism only); URL without the slash |
| [SectionConfigConverter.php L18-L35](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/CustomerData/SectionConfigConverter.php#L18-L35) | B | An empty action becomes the `*` marker (invalidate all). | verified |
| [section-config.js L37-L54](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/view/frontend/web/js/section-config.js#L37-L54) | B | `_.union(..., sections['*'])`: sections declared under `<action name="*">` are added to **every** POST/PUT action. Action names containing `*` work as globs (L42-L48). | verified; strongest support for the `<action name="*">` case |
| [Customer etc/frontend/sections.xml L10-L21](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/etc/frontend/sections.xml#L10-L21) | B | Core uses `<section name="*"/>` on login, logout, createPost and editPost. | verified; **contradicts `error` for every `section="*"`** |
| [dev.to: customer sections](https://dev.to/magevanta/magento-2-customer-data-sections-localstorage-performance-optimization-4ci9) | C | supplementary | reachable; not audited |

Verdict: keep ✅, with a severity change:
- `error` for `<action name="*">` (and glob action names) that list sections, since those sections are invalidated on every POST.
- `warning` for an empty action or `section name="*"` on other actions, with an allowlist for customer authentication actions (login, logout, create, edit), which core itself configures this way.

The cost claim ("reloads all customer sections") is code-backed (B); no A page calls it a performance issue.

### T-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Technical guidelines 6.2.6](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) | A | "6.2.6 Templates MUST NOT instantiate objects. All objects MUST be passed from the Block objects." | verified (URL without the slash) |

Verdict: keep ✅. Wording change: the A source is an architectural MUST, not a performance statement. The claim "is not cached separately" has no source; phrase the finding as a guideline violation with a possible performance impact.

### T-02 (🧪), noted only

The [Hyvä page](https://docs.hyva.io/hyva-themes/performance/view-model-cache-tags.html) is reachable. It is A only for Hyvä themes, where `ViewModelRegistry` collects `getIdentities()` from view models. The issue is about stale cache (correctness) rather than speed. Stays 🧪, limited to Hyvä.

### X-GQL-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [GraphQlCache Plugin/Query/Resolver.php L43-L82](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/Plugin/Query/Resolver.php#L43-L82) | B | The handler runs only for fields with a non-empty `@cache` annotation. | verified |
| [CacheableQueryHandler.php L59-L85](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/CacheableQueryHandler.php#L59-L85) | B | Without `cacheIdentity`, `$cacheable = false`, and validity is AND-ed across resolvers, so the **whole** response becomes uncacheable. | verified (re-pin) |
| [CacheableQuery.php L73-L79](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/CacheableQuery.php#L73-L79) + [Controller/Plugin/GraphQl.php L134-L140](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Controller/Plugin/GraphQl.php#L134-L140) | B | No cache tags means `setNoCacheHeaders()`. A query whose only fields lack `@cache` is therefore not cached. | verified |
| [Adobe: Identity class](https://developer.adobe.com/commerce/webapi/graphql/develop/identity-class) | A | "If you create a cacheable query ... then you must create an Identity class". | verified |

Verdict: keep ✅, with two wording changes:
- `@cache(cacheable: false)` is a deliberate opt-out for private data and must not be flagged as the "forgot cacheIdentity" error.
- For the "no `@cache`" warning: when such a field is mixed with cacheable fields, the response **is** cached under the other fields' tags. That is a staleness risk, not "uncacheable"; the message should say so.

### X-GQL-02 (⏳)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: GraphQL resolvers](https://developer.adobe.com/commerce/webapi/graphql/develop/resolvers) | A | "We recommend using batch resolvers for queries because they improve performance"; loading related products per product "would be expensive performance-wise". | verified |

Verdict: keep ⏳. Change the detection wording: N+1 arises when a field sits on a type that appears **inside a list** and is resolved per parent. Whether the field's own type is `[Object]` is not what matters. The detection should look at fields of types that are reachable through list fields.

### X-GQL-03 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: GraphQL caching](https://developer.adobe.com/commerce/webapi/graphql/usage/caching/) | A | Documents `idFactorProviders` as the supported way "to define additional factors for computing X-Magento-Cache-Id hash values". **No performance warning.** | verified, but does not support the "harmful" claim |
| [CacheIdCalculator.php L89-L104](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/CacheId/CacheIdCalculator.php#L89-L104) | B | Every provider's value goes into the sha256 cache id. That more variance means more cache entries is an inference. | verified (mechanism only) |

Verdict: demote to 🧪, or make it `info`. The only A source presents this as an official extension point; fragmentation depends on the provider's cardinality, which static analysis cannot see.

### X-MQ-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Communication Converter.php L127-L153](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Communication/Config/Reader/XmlReader/Converter.php#L127-L153) + [L270-L278](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Communication/Config/Reader/XmlReader/Converter.php#L270-L278) | B | When the attribute is missing, `is_synchronous` defaults to `true`; it applies to `schema=` topics and to `request`+`response` topics, while `request`-only topics are forced async (L145-L153). | verified (re-pin) |
| [PublisherPool.php L82-L90](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/MessageQueue/PublisherPool.php#L82-L90) + [Amqp etc/di.xml L19-L30](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Amqp/etc/di.xml#L19-L30) | B | A synchronous topic on `amqp` is published through `Rpc\Publisher`. | verified |
| [Rpc/Publisher.php L105-L125](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/MessageQueue/Rpc/Publisher.php#L105-L125) | B | Sets `reply_to` and returns the decoded response of `$exchange->enqueue()`, i.e. waits for the reply. | verified |
| [Adobe: MQ configuration](https://developer.adobe.com/commerce/php/development/components/message-queues/configuration) | A | `response` "is required if you are defining a synchronous topic"; the sample's first synchronous topic "is for RPC calls". | verified (URL without the slash) |

Verdict: keep ✅. Precise condition: `schema=` without `is_synchronous="false"`, or `request`+`response` without it. A `response` without `request` is not a valid declaration.

### X-MQ-02 (⏳)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: MQ configuration](https://developer.adobe.com/commerce/php/development/components/message-queues/configuration) | A | "The maxIdleTime and sleep attributes are handled only by consumers fired with a defined maxMessages parameter." | verified |
| [Consumer.php L129-L145](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/MessageQueue/Consumer.php#L129-L145) | B | No max-messages means `subscribe()` (endless); otherwise `CallbackInvoker` applies maxIdleTime and sleep. | verified |
| [ConsumersRunner.php L120](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/MessageQueue/Model/Cron/ConsumersRunner.php#L120) + [L176-L198](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/MessageQueue/Model/Cron/ConsumersRunner.php#L176-L198) | B | The cron runner passes `--max-messages = $consumer->getMaxMessages() ?? env default (10000)`. A consumer **without** `maxMessages` in XML still gets 10000 under cron. | verified; **contradicts the rule's claim** |

The claim "without maxMessages the process lives forever" is wrong for cron-spawned consumers, which is the default deployment. It holds only for a manual `queue:consumers:start` without `--max-messages`, which is not visible in XML, or when `cron_consumers_runner.max_messages = 0` (that is C-01).

Verdict: demote to 🧪, or reformulate as `info`: "maxIdleTime/sleep set while relying on the env default". The current wording is factually wrong.

### X-CRON-01 (🧪), noted only

The backend-performance page (A) only says "Developers must evaluate the scheduling frequency"; it says nothing about every-minute schedules or the `default` group. Stays 🧪.

### X-IDX-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Custom indexer](https://developer.adobe.com/commerce/php/development/components/indexing/custom-indexer) | A | `view_id`: "The ID of the view element that is defined in the mview.xml configuration file"; the sample `execute($ids)` is "Used by mview ... in the 'Update on schedule' mode". | verified |
| [Mview/View.php L185-L189](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Mview/View.php#L185-L189) (suggested addition) | B | `load()` throws "view does not exist" when `view_id` has no `<view>` in mview.xml. | verified |
| [Adobe: Indexer configuration](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration) | A | Recommends "Update on Schedule for large sites". Operational advice that does not mention mview declarations. | verified (supplementary only) |

Verdict: keep ✅. Add the Mview View.php link.

### X-SETUP-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Extension lifecycle](https://developer.adobe.com/commerce/php/development/prepare/extension-lifecycle) | A | Recurring runs "after every schema installation or upgrade stage"; RecurringData runs "after every data installation or upgrade stage". | verified |
| [setup Installer.php L1159-L1174](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/setup/src/Magento/Setup/Model/Installer.php#L1159-L1174) (suggested addition) | B | Recurring handlers are invoked for every module, with no version check. | verified |

Verdict: keep ✅.

### X-DB-01 (✅)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Security and performance](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "A Primary Key is required for any DB cluster to run effectively. Without a Primary Key, you will see performance issues during table replication." | verified (URL without the slash) |

Verdict: keep ✅.

### C-01 (🧪, 🔎)

| Source | Level | Confirms | Status |
|---|---|---|---|
| [Adobe: Manage message queues](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues) (found) | A | `max_messages` defaults to 10000: "Although we do not recommend it, you can use 0 to prevent the consumer from terminating." | verified |
| [ConsumersRunner.php L120](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/MessageQueue/Model/Cron/ConsumersRunner.php#L120), [L141-L161](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/MessageQueue/Model/Cron/ConsumersRunner.php#L141-L161) | B | With `max_messages = 0`, no `--max-messages` is passed... | verified |
| [Consumer.php L134-L135](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/MessageQueue/Consumer.php#L134-L135) | B | ...so the consumer calls `subscribe()` and never terminates. | verified |
| best-practices skill | C | "memory leak" | not used as a basis |

Two caveats:
- Per-consumer `maxMessages` from XML is only added inside `if ($maxMessages)`, so with 0 it is ignored too.
- The A source only says "not recommended"; "memory leak" is not stated by any A/B source.

Verdict: promote to ⏳ (A + B found), still in the off-by-default env group. Change the wording to "consumer never terminates (not recommended by Adobe)" and drop "memory leak" or mark it as C.

---

### Notes for SPEC edits

1. **Dead links.** Remove the trailing slash on the developer.adobe.com URLs that 404: plugins, `extensions/security`, technical-guidelines, private-content, message-queues/configuration. Add a link check to CI; slash behaviour differs per page.
2. **Re-pin core links.** Repoint every `blob/2.4-develop/...` link (MergePlugin, AbstractModel, Layout, CacheableQueryHandler, Communication Converter) to `a385d02f60ee6dfa052f12cdc7d82e956581fba5` with line ranges (see the tables above).
3. **X-LAY-01.** Add the A sources (page-caching overview, public content: "Using cacheable="false" inside the default.xml file disables caching for all pages") and the B source `Layout::isCacheable` L1192-L1210. Document two facts: `cacheable` is valid only on `<block>` (and `uiComponent`, which core ignores), never on `<referenceBlock>`; and removed blocks do not count.
4. **X-LAY-02.** Drop or experimentalize the `default.xml` warning; core's own `default.xml` declares `catalog.topnav` with `ttl="3600"`.
5. **X-PLG-02.** Downgrade ✅ to ⏳; there is no source for the claim that these plugins hurt performance.
6. **P-EXT-01.** Base it on backend-performance; the security page is about security and recommends the Curl adapter.
7. **P-EXT-02.** Demote to 🧪 or merge into P-EXT-01 as a warning; Adobe docs treat synchronous totals/tax/payment calls as legitimate.
8. **X-SEC-01.** Split severities: `<action name="*">` is `error` (B: section-config.js L53). `section="*"` or an empty action is `warning`, with a default allowlist for customer auth actions, which core configures that way.
9. **X-EVT-01.** Distinguish `model_load_after` (every model) from `<prefix>_load_after` (one entity). **X-EVT-03:** add the AbstractBlock.php source.
10. **X-GQL-01.** Exempt `@cache(cacheable: false)`. Reword the "no @cache" half as a staleness/uncacheable-when-alone risk. **X-GQL-02:** fix the detection description (fields on list-element types). **X-GQL-03:** demote to 🧪 or `info`.
11. **X-MQ-02.** The claim is contradicted by ConsumersRunner (cron applies 10000 by default); demote or reformulate. **C-01:** A + B found, promote to ⏳, drop "memory leak".
12. **T-01.** The guideline is architectural; avoid claiming "not cached separately" without a source.
13. **X-IDX-01 and X-SETUP-01.** Add the B links (Mview View::load, Installer recurring loop).
14. **GitHub issue search.** The magento/magento2 issue search for these topics hit GitHub's secondary rate limit. No issue-level (B) sources were found, but the search is incomplete; rerun later if issue sources are wanted. The issues in SPEC section 12 (#38934 EAV table source models, #36386 indexer with many attribute options) belong to other sections and do not concern 4.5–4.8.
