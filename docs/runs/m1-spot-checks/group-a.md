> Appendix to [m1-core.md](../m1-core.md). Findings were sampled with seed 20261022 from the run before the spot-check fixes, then graded by reading the code in Magento 2.4.9. TP: the rule's claim holds. TP-low: it holds, but the impact is negligible. FP: the claim does not hold.

# Graded sample A (Magento 2.4.9 core)

## P-COL-01

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-security/Block/Adminhtml/Session/Activity.php:78 | FP | Collection memoized in the block; `session/activity.phtml` iterates the same instance (`foreach ($sessionInfoCollection ...)`), so count() only moves the load earlier. Small admin-user session list anyway. |
| 2 | module-downloadable/Model/Product/Type.php:241 | FP | `getSamples()` caches the collection on the product; the only caller (`samples.phtml` via `Block\Catalog\Product\Samples`) iterates the same samples right after `hasSamples()`. Load happens anyway. |
| 3 | module-paypal/view/frontend/templates/billing/agreements.phtml:15 | FP | `$billingAgreements` is iterated at line 31 of the same template; count() only moves the load earlier (documented known-FP, but the "loads before" check missed that the load comes after). |
| 4 | module-catalog/view/frontend/templates/navigation/left.phtml:18 | FP | `$_categories` is iterated at line 31; also guarded by `is_array()` ternary. Load happens anyway. |
| 5 | module-bundle/Model/Product/Type.php:1337 | FP | `$options[$optionId]` is a plain array (`int[]` param, guarded by `is_array()`), not a collection. |
| 6 | framework-message-queue/ConsumerConfiguration.php:124 | FP | `$topics` is `getData()` result guarded by `is_array($topics) &&`; it is an array, not a collection. |
| 7 | module-admin-adobe-ims/Model/ImsWebapiRepository.php:174 | FP | Branch without page size; `getItems()` on the next line loads the same collection anyway, so count() costs nothing extra (and getSize() would add a query). |
| 8 | module-weee/Block/Renderer/Weee/Tax.php:140 | FP | `$data` is guarded by `is_array($data) &&`; array, not a collection. |
| 9 | module-paypal/view/frontend/templates/billing/agreement/view.phtml:72 | FP | `$relatedOrders` is iterated at line 93 of the same template; load happens anyway. |
| 10 | module-elasticsearch/Model/Adapter/Document/Builder.php:60 | FP | Inside `if (is_array($value))`; plain array. |

Summary: 0 TP, 0 TP-low, 10 FP, 0 unclear
Pattern: (a) values narrowed by `is_array()` (or of mixed/array type) are treated as `AbstractDb`: the type check must require the type to be a collection (`yes`), not accept `maybe`/mixed; 4 of 10. (b) count() before a later `foreach`/`getItems()` on the same variable in the same function/template (the load happens anyway): the rule should also look forward in the function, not only backward; 4 of 10. (c) count() on a memoized getter whose collection the template iterates (cross-method).

## P-COL-02

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-webapi/Model/WebapiRoleLocator.php:50 | TP-low | `setUserFilter(user_id, user_type)` matches one role row per user; no LIMIT but at most ~1 row. |
| 2 | module-sales/Model/Order.php:1810 | FP | `getStatusHistoryCollection()` already iterates (loads) the collection when the order has an id; `getFirstItem()` runs no query. For a new order the filter matches nothing. |
| 3 | module-bundle/Model/LinkManagement.php:422 | FP | `setIdFilter($optionId)` filters by `main_table.option_id` (primary key) with a scalar: at most one row. |
| 4 | module-authorization/Model/Acl/AclRetriever.php:163 | TP-low | Same as #1: `setUserFilter()` matches one role per user. |
| 5 | module-catalog-rule-sample-data/Model/Rule.php:160 | TP-low | `url_key` filter can match a few categories, but this is one-time sample-data install. |
| 6 | module-swatches/Helper/Data.php:269 | TP | Children of a configurable filtered by possibly partial swatch attributes: can match many children with full EAV load, only the first is used. |
| 7 | module-cms-sample-data/Model/Block/Converter.php:102 | TP-low | Same as #5: url_key lookup, sample-data install only. |
| 8 | module-bundle/Model/LinkManagement.php:380 | FP | Same as #3: `setIdFilter()` on option_id primary key. |
| 9 | module-security/Model/SecurityChecker/Frequency.php:101 | FP | `filterLastItem()` does `getSelect()->limit(1)` inside the collection method; LIMIT is present. |
| 10 | module-new-relic-reporting/Model/Cron/ReportCounts.php:94 | FP | `->setPageSize(1)` is at the end of the assignment chain on lines 86-93; rule missed a limit in the chain assigned to the variable (rule bug). |

Summary: 1 TP, 4 TP-low, 5 FP, 0 unclear
Pattern: (a) limit/load/unique filter applied inside a collection helper method (`filterLastItem()`, `setIdFilter()`, `getStatusHistoryCollection()` which loads) is not seen; consider recognizing `setIdFilter` and treating methods returning an already-iterated collection. (b) Definite bug: `$c = $f->create()->addFieldToFilter()->addOrder()->setPageSize(1); $c->getFirstItem();` is reported; the chain in the assignment is not scanned (possibly broken by `addOrder()`). (c) `user_id` style filters on 1:1 tables (role per user) are low impact.

## P-LOOP-02

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:237 | TP-low | Real one query per attribute group, but admin attribute-set page with ~5-15 groups. |
| 2 | module-review/Model/ResourceModel/Rating/Option/Vote/Collection.php:181 | TP | One rating-option collection per vote; options repeat per rating_id, so one `in` query would do. Classic N+1 over votes. |
| 3 | module-catalog-sample-data/Model/Product/Converter.php:237 | TP-low | One option query per select attribute of the set; sample-data install only. |
| 4 | module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:241 | TP-low | Same loop as #1 (duplicate finding); `getSize()` on the loaded collection does run an extra COUNT query, but no new collection is created here. |
| 5 | module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:243 | FP | `getItems()` on `$nodeChildren` already loaded at line 239: no query. Duplicate of #1. |
| 6 | module-review-sample-data/Model/Review.php:147 | TP-low | One COUNT per CSV row; sample-data install only. |
| 7 | module-catalog-import-export/Model/Import/Product/Type/AbstractType.php:402 | FP | Loop over `array_chunk($ids, 1000)`: this is the recommended batched load (`in` filter per 1000-id chunk). |
| 8 | module-cms-sample-data/Model/Block/Converter.php:228 | FP | Outer collection filtered by a single `attribute_code`: at most one iteration. (Also sample data.) |
| 9 | module-sales-rule-sample-data/Model/Rule.php:96 | TP-low | One load per CSV row (by rule name); sample-data install only. |
| 10 | module-catalog/Model/ProductLink/CollectionProvider/LinkedMapProvider.php:150 | FP | Loop over link types (related/upsell/crosssell, fixed ~3-4), each query already batched for all product IDs. |

Summary: 1 TP, 5 TP-low, 4 FP, 0 unclear
Pattern: (a) multiple findings per loop for the same collection: after a collection is loaded, later `getSize()`/`getItems()` on the same variable in the same iteration should not be reported again (one finding per created collection). (b) loops over `array_chunk(...)` are batching and should be exempt. (c) Sample-data modules (`*-sample-data`) produce many TP-low findings; consider excluding them by default or lowering severity.

## P-LOOP-03

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-inventory-admin-ui/Controller/Adminhtml/Source/MassEnable.php:117 | TP-low | Admin mass action over a handful of sources; no batch save API for sources. |
| 2 | module-customer/Model/Config/Backend/Show/Customer.php:131 | TP-low | Loop over the fixed small set of attributes for one config field, on config save. |
| 3 | module-downloadable/Observer/SaveDownloadableOrderItemObserver.php:163 | TP-low | One save per purchased link of an order item; few links per product, no batch API. |
| 4 | module-authorization/Setup/Patch/Data/InitializeAuthRoles.php:94 | TP-low | Setup patch, rules filtered by role and `resource_id = all`: ~1 row, runs once. |
| 5 | module-sales/Model/ResourceModel/Order/Relation.php:88 | TP-low | Order save writes each status-history entry; few per order, unchanged models are skipped by the resource save. |
| 6 | module-ui/Controller/Adminhtml/Bookmark/Save.php:254 | FP | `break` right after the first current bookmark: save runs at most once. |
| 7 | module-catalog/Model/Config/Backend/Category.php:66 | TP | Saving root-category config for a store view loads and saves every category of the tree (full model save with events/url rewrites/reindex): thousands of saves on large catalogs. |
| 8 | module-downloadable/Model/Sample/UpdateHandler.php:72 | TP-low | Product save, one repository save per sample; samples per product are few. |
| 9 | module-bundle/Model/Product/SaveHandler.php:208 | TP-low | Deletes only removed bundle options on product save; few. |
| 10 | module-tax/Model/ResourceModel/Sales/Order/Relation.php:89 | TP-low | Order save, one tax row per applied tax rate (typically 1-3). |

Summary: 1 TP, 8 TP-low, 1 FP, 0 unclear
Pattern: (a) loops that end with an unconditional `break` after the save (execute at most once) should be exempt. (b) Most hits are entity relation saves bounded by the size of one aggregate (order items/taxes/links of one product); the rule is noisy as a default `warning` - consider reporting only when the iterated value is a collection/repository list, or demoting to experimental.

## P-LOOP-07

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-catalog-inventory/Model/StockIndex.php:238 | FP | Loop is over product type instances (fixed ~6 types, simple/virtual return `[]` without a query) for one product id; not a per-product loop, and arrays across types cannot be merged into one call. |
| 2 | module-product-links-sample-data/Model/ProductLink.php:103 | TP-low | `getIdBySku()` per linked SKU; sample-data install only. |
| 3 | module-catalog-rule-configurable/Plugin/CatalogRule/Model/Rule/ConfigurableProductHandler.php:82 | TP | Catalog rule indexing: one `getChildrenIds()` query per configurable product id (memoized per id only); resource `getChildrenIds()` accepts an array. |
| 4 | module-configurable-product/Model/ResourceModel/Product/Type/Configurable/Attribute/Collection.php:254 | TP-low | Deprecated `_addAssociatedProductFilters()`, no callers in core; `$this->products` is usually one product and `getUsedProducts()` caches on it. |
| 5 | module-wishlist-sample-data/Model/Helper.php:98 | TP-low | `getConfigurableAttributes()` per SKU (cached on product); sample-data install only. |
| 6 | module-product-links-sample-data/Model/ProductLink.php:90 | TP-low | `getIdBySku()` per CSV row; sample-data install only. |
| 7 | module-wishlist-sample-data/Model/Helper.php:78 | TP-low | `getIdBySku()` per SKU; sample-data install only. |

Summary: 1 TP, 5 TP-low, 1 FP, 0 unclear
Pattern: (a) loops over product type instances (`getProductTypeInstances()` / `$this->productType->getTypes()`) are fixed-bound; (b) 4 of 7 are sample-data modules.

## P-DI-01

Proxy check done: grepped every `di.xml` in vendor/magento and app (all areas, type and virtualType) for each class and its parents (AbstractForm, AbstractCreate, AbstractSidebar, AbstractCart, Rule\Model\AbstractModel, AbstractProduct, Action). No Proxy is configured for any of the 10 arguments; only plugins/other arguments exist for these types. `SessionManagerInterface` is preferred to `Session\Generic` (frontend) / `Backend\Model\Session` (adminhtml).

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-sales/Block/Adminhtml/Order/Create/Form/Account.php:70 | TP-low | No Proxy; but admin order-create block, admin session is already started on every adminhtml request and the block uses the quote session. |
| 2 | module-checkout/Block/Cart.php:46 | TP-low | No Proxy; cart page is not cacheable and starts customer/checkout sessions anyway. |
| 3 | module-sales/Block/Adminhtml/Order/Create/Sidebar/Pviewed.php:40 | TP-low | Same as #1 (admin, session already active). |
| 4 | module-store/App/Response/Redirect.php:104 | TP | No Proxy (backend di.xml only sets `canUseSessionIdInParam`); `$_session` is assigned and never used, and Redirect is built for every action controller via the action Context, so a `Session\Generic` is started on every routed request. |
| 5 | module-catalog-rule/Model/Rule.php:253 | TP | No Proxy; session used only in `calcProductPriceRule()`, but every Rule model instance (admin, indexer, cron) triggers session creation. |
| 6 | module-checkout/Controller/Cart/UpdateItemQty.php:68 | TP-low | No Proxy; POST cart action that always uses the checkout session. |
| 7 | module-quote/Observer/Frontend/Quote/Address/CollectTotalsObserver.php:81 | TP-low | No Proxy; fires during quote totals collection (non-cacheable flows) where the session is active anyway. |
| 8 | module-two-factor-auth/Model/UserConfig/HtmlAreaTokenVerifier.php:68 | TP-low | No Proxy; guideline violation, but `PhpCookieManager` construction does no I/O, admin-only. |
| 9 | module-gift-message/Block/Message/Inline.php:73 | TP-low | No Proxy; rendered in cart/checkout (non-cacheable) where the customer session is active. |
| 10 | module-catalog/Block/Product/ProductList/Upsell.php:87 | TP | No Proxy; `$_checkoutSession` is assigned and never used, and the block renders on cacheable product pages, starting a checkout session on every FPC miss. |

Summary: 3 TP, 7 TP-low, 0 FP, 0 unclear
Pattern: no FP. Impact is real mainly where the class is on cacheable frontend pages or built on every request; in adminhtml and in non-cacheable cart/checkout code the session is already started. Consider lowering severity for adminhtml-only classes (admin blocks/controllers) and for the cookie manager.

## P-DI-02

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | framework/Data/Form/Element/Column.php:27 | TP-low | `$this->setType('column')`: a trivial DataObject setter. |
| 2 | module-config/Model/Config/Structure.php:127 | TP | `$structureData->get()` pulls the whole merged system.xml structure (large cached array) in the constructor, whenever Structure is injected. |
| 3 | framework/Filesystem/DriverPool.php:46 | TP-low | Loop registers/validates the few `extraTypes` from DI; cheap. |
| 4 | module-catalog-url-rewrite/Model/ObjectRegistry.php:26 | FP | Value object created by its factory with `$entities`; building the id map from the argument is its whole purpose (documented known-FP category). |
| 5 | module-paypal/Block/PayLater/Banner.php:65 | FP | `$this->placement = $data['placement'] ?? ''`: assigning an element of a parameter; no work. |
| 6 | magento-coding-standard/Magento2/Sniffs/Commenting/ConstantsPHPDocFormattingSniff.php:27 | FP | PHP_CodeSniffer sniff, not a Magento runtime class, never built by the object manager. |
| 7 | framework-message-queue/Config/Reader/Xml/SchemaLocator.php:37 | TP-low | Two `UrnResolver::getRealPath()` calls (path resolution); class only used when reading queue config on cache miss. |
| 8 | framework/Filter/Money.php:28 | FP | `trigger_error('Class is deprecated', E_USER_DEPRECATED)`: intended deprecation signal in a dead class, not performance work. |
| 9 | framework/Data/Form/Element/Gallery.php:48 | FP | `$x = $x ?? ObjectManager::getInstance()->get(...)` BC fallback assigned to a local variable before `parent::__construct()`; allowed pattern, rule only accepts it when assigned to a property. |
| 10 | module-customer/Controller/Account/Confirm.php:116 | TP-low | `$urlFactory->create()` creates one Url object; controller is only built when this route is dispatched. |

Summary: 1 TP, 4 TP-low, 5 FP, 0 unclear
Pattern: allowlist gaps: (a) BC fallback `??`/`?:` ObjectManager assigned to a local variable (or param) then passed to parent; (b) `$data['key'] ?? default` / array offsets of parameters; (c) `trigger_error(E_USER_DEPRECATED)`; (d) `$this->setType('...')`/simple `setData` on form elements (cheap, very common in framework Data\Form elements). Also exclude `vendor/magento/magento-coding-standard` (and other dev tools) from analysis. With 1391 findings, the rule is far too noisy for the default set.
