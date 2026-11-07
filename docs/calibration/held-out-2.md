# Second check on modules the rules were not tuned on

The [first check](held-out.md) ran on 15 modules and led to changes in the rules, so its figures no longer stand for code the rules have not seen. This page runs the rules on 22 modules that no earlier round used. It decides nothing: the thresholds apply to rounds 1 and 2.

## Modules

The next packages of type `magento2-module` by Packagist downloads on 2026-11-07, after those used in the [calibration corpus](corpus.json), the first check and the GraphQL set, chosen the same way. Three packages with no stable version that fits this Magento install were passed over: `semaio/magento2-configimportexport`, `akeneo/module-magento2-connector-community` and `gene/module-encryption-key-manager`.

The modules ran on a separate copy of the Magento 2.4.9 corpus project, and none was kept in this repository. Because the analysis loads the project's autoloader, each `registration.php` was checked first to contain only the standard `ComponentRegistrar::register` call. Three of the 25 packages drawn failed that check and did not run: `m2epro/magento2-extension`, which takes the module name from its own class; `amzn/amazon-pay-magento-2-module`, which guards the call with a check; and `nosto/module-nostotagging`, which also loads a `.env` file.

| Package | Version | Downloads |
|---|---|---|
| experius/module-wysiwygdownloads | 1.3.0 | 953295 |
| yireo/magento2-csp-utilities | 1.0.2 | 929302 |
| owebia/magento2-module-advanced-shipping | 6.1.5 | 902243 |
| baldwin/magento2-module-url-data-integrity-checker | v1.6.1 | 897789 |
| boldcommerce/magento2-ordercomments | 1.9.0 | 840267 |
| yireo/magento2-whoops | 0.2.5 | 823808 |
| mageplaza/magento-2-blog-extension | 4.3.5 | 820308 |
| fooman/printorderpdf-implementation-m2 | 105.5.0 | 782647 |
| multisafepay/magento2-core | 3.21.0 | 777274 |
| magepal/magento2-reindex | 1.2.1 | 775048 |
| multisafepay/magento2-frontend | 3.2.0 | 772666 |
| magestyapps/module-web-images | 1.2.1 | 727572 |
| graycore/magento2-cors | v2.1.3 | 709192 |
| mageplaza/module-delete-orders | 4.0.4 | 674460 |
| msp/devtools | 1.2.18 | 672664 |
| magefan/module-login-as-customer | 2.3.6 | 647131 |
| tig/postnl-magento2 | 1.27 | 615367 |
| bazaarvoice/bazaarvoice-magento2-ext | 9.1.11 | 612005 |
| kiwicommerce/module-cron-scheduler | 1.0.0 | 610084 |
| honl/magento2-templatehints | 0.6.0 | 605840 |
| msp/cmsimportexport | 1.1.2 | 590437 |
| smile/module-map | 2.1.3 | 577680 |

Modules these depend on were not installed, so types from those stay unknown and some findings can be missed.

## Result

No run failed: 22 modules, with the default set and the experimental rules P-LOOP-04, P-LOOP-06, P-COL-03, P-COL-04, X-PLG-02, X-GQL-02, P-FPC-03 and P-DI-04 on, `incomplete: false`, no notices. Two cold runs gave the same findings. Nine modules have no finding: `Experius_WysiwygDownloads`, `Fooman_PrintOrderPdf`, `Graycore_Cors`, `KiwiCommerce_CronScheduler`, `MagePal_Reindex`, `MagestyApps_WebImages`, `MSP_CmsImportExport`, `Smile_Map` and `Yireo_Whoops`.

Up to 12 findings per rule were drawn with a fixed seed (20261107) and graded once by one grader who did not write the rules, by reading the code. The table shows the findings the rules still report. No time or memory was measured, and the grader marked 25 of the 77 verdicts below as unsure, mostly because they depend on the size of a list or on tools that must see every block.

| Rule | Findings | Graded | TP | TP-low | FP |
|---|---|---|---|---|---|
| P-COL-03 (experimental) | 1 | 1 | 0 | 1 | 0 |
| P-DI-01 | 49 | 12 | 0 | 12 | 0 |
| P-DI-04 (experimental) | 8 | 8 | 0 | 8 | 0 |
| P-DI-06 | 5 | 5 | 0 | 5 | 0 |
| P-FPC-01 | 1 | 1 | 0 | 1 | 0 |
| P-FPC-03 (experimental) | 1 | 1 | 1 | 0 | 0 |
| P-FPC-05 | 1 | 1 | 0 | 1 | 0 |
| P-LOOP-01 | 19 | 12 | 1 | 11 | 0 |
| P-LOOP-02 | 2 | 2 | 0 | 2 | 0 |
| P-LOOP-03 | 22 | 12 | 1 | 11 | 0 |
| P-PLG-01 | 5 | 5 | 0 | 5 | 0 |
| T-01 | 7 | 7 | 0 | 7 | 0 |
| X-DB-02 | 4 | 4 | 0 | 4 | 0 |
| X-EVT-01 | 2 | 2 | 0 | 2 | 0 |
| X-PLG-02 (experimental) | 4 | 4 | 0 | 4 | 0 |
| **All** | **131** | **77** | **3** | **74** | **0** |

All 77 are correct and 3 matter in practice, the same shape as the first check: the rules are right far more often than what they report is important. P-COL-04, P-LOOP-04, P-LOOP-06 and X-GQL-02 report nothing here, so this run did not test them, nor any other rule without a row.

## What this run changed

- P-FPC-05 reported `setHeader('Pragma', 'no-cache')` as making a response uncacheable. The grader marked this as false, and the code agrees: `Kernel::process()` and `varnish6.vcl` read `Cache-Control` from a response and never `Pragma`. The rule no longer reports it, which removes one finding here and two in the first check, each on the line after a `Cache-Control` finding in the same controller.

## What to read into it

- **P-DI-04:** all 8 findings are TP-low. With the 25 graded on the earlier modules, that makes 33: 10 TP, 23 TP-low, 0 FP. Its zero false positives say little, because a command is created on every run whether or not that costs anything.
- **X-PLG-02:** all 4 findings are in tools that must see every block or that do almost nothing by default, which is the rule's documented false positive. Counted as false, the rule has no correct finding here. It stays experimental.
- **P-LOOP-02:** both findings are per-store loops, where the fix the rule suggests does not fit.

## Limitations

- 22 modules and one grader. A verdict can miss a caller outside the files read, and some core constructors under P-DI-04 were graded as cheap without being read.
- Impact was judged from the code, not measured.

## Grading tables

### P-COL-03

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageplaza/Blog/Block/Post/RelatedProduct.php:151 | TP-low | The collection does select every product attribute while its template uses only a few, but it holds at most 12 rows by default and runs only on a post page cache miss (unsure: the cost depends on the store's attribute count). |

### P-DI-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Bold/OrderComment/Model/OrderCommentConfigProvider.php:40 | TP-low | No Proxy is configured, but this config provider is registered only for the frontend checkout config and reads the checkout session as soon as it runs on that uncached page. |
| app/code/Magefan/LoginAsCustomer/Model/Login.php:99 | TP-low | No Proxy is configured, but the model is built only in the login-as-customer admin and frontend controllers, where a session is already running. |
| app/code/Mageplaza/Blog/Block/Adminhtml/Category/Tree.php:69 | TP-low | No Proxy is configured, but this admin block needs the argument for its core parent class and the admin auth session is already started on every admin page. |
| app/code/Mageplaza/Blog/Controller/Post/EditPost.php:89 | TP-low | The session is injected without a Proxy and is never read in this class, but this frontend action already gets a session from the core customer context plugin on any dispatch. |
| app/code/Mageplaza/Blog/Controller/Post/Manage.php:136 | TP-low | The session is injected without a Proxy and is never read in this class, but this is a POST frontend action that already gets a session from the core customer context plugin. |
| app/code/Mageplaza/Blog/Helper/Data.php:160 | TP-low | The helper starts a session without a Proxy on frontend actions, where core starts one anyway, and on the ACL-protected REST routes through BlogRepository (unsure: the REST start is real cost, but I judged that traffic to be rare). |
| app/code/MultiSafepay/ConnectCore/Util/TaxUtil.php:64 | TP-low | The session is read only when customer group auto-assign is on, so a Proxy would help, but the class is reached only while building payment requests and refunds, which are already slow. |
| app/code/TIG/PostNL/Controller/AbstractDeliveryOptions.php:57 | TP-low | No Proxy is configured, but this base class only serves checkout AJAX frontend actions that use the checkout session and already have a session from core. |
| app/code/TIG/PostNL/Controller/DeliveryOptions/Timeframes.php:29 | TP-low | No Proxy is configured, but this checkout AJAX frontend action already has a session from core and works on the current quote. |
| app/code/TIG/PostNL/Plugin/Quote/ResetDeliveryDate.php:21 | TP-low | The global plugin starts a session for add-to-cart, also in REST and GraphQL, but it reads the session at once so a Proxy would not avoid it (unsure: the start is a real cost in headless add-to-cart, which is an already expensive write). |
| app/code/TIG/PostNL/Service/Carrier/Price/TaxedFee.php:16 | TP-low | No Proxy is configured, but its only users are checkout config classes registered in the frontend area, where the session is needed anyway. |
| app/code/TIG/PostNL/Service/Quote/CheckIfQuoteHasOption.php:26 | TP-low | No Proxy is configured, which also undoes the Proxy given to its caller IsShippingOptionsActive, but its users are frontend checkout config classes that need the session anyway. |

### P-DI-04

| Location | Verdict | Reason |
|---|---|---|
| app/code/Baldwin/UrlDataIntegrityChecker/Console/Command/CheckCategoryUrlKeys.php:26 | TP-low | The command is registered in CommandList and its dependencies are created on every run, but the constructors followed one or two levels down only store arguments and factories. |
| app/code/Baldwin/UrlDataIntegrityChecker/Console/Command/CheckCategoryUrlPaths.php:26 | TP-low | The command is registered in CommandList and its dependencies are created on every run, but the constructors followed one or two levels down only store arguments and factories. |
| app/code/Baldwin/UrlDataIntegrityChecker/Console/Command/CheckProductUrlKeys.php:28 | TP-low | The command is registered in CommandList and its dependencies are created on every run, but Progress has a trivial constructor and the others followed down only store arguments and factories. |
| app/code/Baldwin/UrlDataIntegrityChecker/Console/Command/CheckProductUrlPaths.php:26 | TP-low | The command is registered in CommandList and its dependencies are created on every run, but the constructors followed one or two levels down only store arguments and factories. |
| app/code/Mageplaza/DeleteOrders/Console/Delete.php:73 | TP-low | The command is registered in CommandList and its dependencies are created on every run, but the helper, order repository and registry do no database or session work when constructed as far as I followed them. |
| app/code/TIG/PostNL/Console/Config/Dump.php:24 | TP-low | The command is registered in CommandList and ConfigExporter is created on every run, but it only stores a config reader, a repository and a search criteria builder. |
| app/code/TIG/PostNL/Console/Config/Import.php:41 | TP-low | The command is registered in CommandList and its dependencies are created on every run, but the exporter and importer only store their arguments and the directory list and cache type list are core services with no visible work on creation. |
| app/code/TIG/PostNL/Console/Sync/Grids.php:21 | TP-low | The command is registered in CommandList and its dependencies are created on every run, but ShipmentRepository only stores factories and the search criteria builder is cheap. |

### P-DI-06

| Location | Verdict | Reason |
|---|---|---|
| app/code/Bazaarvoice/Connector/Model/ConfigProvider.php:599 | TP-low | The object manager is really called directly to read the module version, but only on the order pixel and in feeds, and the cost is the design issue the rule describes. |
| app/code/Magefan/LoginAsCustomer/Model/Login.php:116 | TP-low | The code looks like the allowed constructor fallback, but the two parameters are untyped with no di.xml argument, so the object manager always supplies them (unsure: the rule's exemption might be meant to cover this form). |
| app/code/Mageplaza/DeleteOrders/Controller/Adminhtml/Order/Delete.php:52 | TP-low | The object manager is called directly inside execute() to create the order management service, which could be injected, but this is a rare single-order admin action. |
| app/code/MultiSafepay/ConnectCore/Util/CustomReturnUrlUtil.php:82 | TP-low | The constructor fetches a service through the object manager behind an outdated Magento 2.3 version check, so it could be injected, but the object is cheap and the effect is on design only. |
| app/code/MultiSafepay/ConnectCore/Util/ThirdPartyPluginsUtil.php:111 | TP-low | The object manager is called directly to get classes of optional third-party modules, which cannot be constructor arguments, so an injected ObjectManagerInterface is the most that could change and the impact is nil. |

### P-FPC-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Bold/OrderComment/Block/Order/Comment.php:25 | TP-low | The block does set the obsolete property, but it sits on the customer order view page, which core already makes uncacheable with cacheable="false" blocks. |

### P-FPC-03

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageplaza/Blog/Helper/Data.php:281 | TP | The real customer id of a logged-in visitor goes into the HTTP context from the cacheable post view action and block, so each logged-in customer gets a separate cached copy of those pages. |

### P-FPC-05

| Location | Verdict | Reason |
|---|---|---|
| app/code/MultiSafepay/ConnectCore/Controller/Connect/ApiToken.php:85 | TP-low | The no-store header does stop caching, but the docblock states this is deliberate and a token tied to the current quote could not be cached anyway (unsure: the finding only asks to confirm intent, which the code already answers). |

### P-LOOP-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Bazaarvoice/Connector/Model/Feed/PurchaseFeed.php:204 | TP | The weekly purchase feed cron does a full EAV product load for every item of every order, with no row limit visible in the code, where one collection per store would do (unsure: it depends on weekly order volume). |
| app/code/Bazaarvoice/Connector/ViewModel/Pixel.php:192 | TP-low | The call is in a loop over one order's items, but it runs only with families enabled, a parent item and a placeholder image, and the repository caches by id. |
| app/code/Mageplaza/Blog/Model/Import/MageFanM2.php:385 | TP-low | One load per already imported tag does run, but only in a manual one-off admin import from another blog (unsure: it depends on the number of tags). |
| app/code/Mageplaza/Blog/Model/Import/MageFanM2.php:817 | TP-low | One load per imported reply comment does run, but only in a manual one-off admin import (unsure: it depends on the number of comments). |
| app/code/Mageplaza/Blog/Model/Import/MageFanM2.php:856 | TP-low | One load runs per source admin user, but that list is short and this is a manual one-off import. |
| app/code/Mageplaza/Blog/Model/Import/MageFanM2.php:859 | TP-low | One load runs per source admin user, but that list is short and this is a manual one-off import. |
| app/code/Mageplaza/Blog/Model/Import/WordPress.php:200 | TP-low | One load per already imported post does run, but only in a manual one-off admin import (unsure: it depends on the number of posts). |
| app/code/Mageplaza/Blog/Model/Import/WordPress.php:500 | TP-low | One load per already imported category does run, but only in a manual one-off admin import of a usually short list (unsure: it depends on the number of categories). |
| app/code/Mageplaza/Blog/Model/Import/WordPress.php:570 | TP-low | One load per child category does run, but only in a manual one-off admin import of a usually short list (unsure: it depends on the number of categories). |
| app/code/Mageplaza/Blog/Model/Import/WordPress.php:623 | TP-low | One load per WordPress user does run, but only in a manual one-off admin import (unsure: a WordPress users table can be large). |
| app/code/MultiSafepay/ConnectCore/Service/Invoice/CreateInvoiceAfterShipment.php:113 | TP-low | One lookup runs per shipment item, but a shipment has few items and this happens once per shipment during a manual capture flow. |
| app/code/TIG/PostNL/Model/Shipment.php:855 | TP-low | The array_walk closure does one lookup per simple order item, but the list is one order's items and the only caller found builds the label request for one shipment. |

### P-LOOP-02

| Location | Verdict | Reason |
|---|---|---|
| app/code/Baldwin/UrlDataIntegrityChecker/Checker/Catalog/Product/UrlKey/DuplicateUrlKey.php:100 | TP-low | One collection and one count query run per store view in a batch checker, and the fix of one collection does not fit because each store needs its own scoped values (unsure: the iteration bound is the store count, not the item count). |
| app/code/Baldwin/UrlDataIntegrityChecker/Checker/Catalog/Product/UrlKey/EmptyUrlKey.php:100 | TP-low | One collection and one count query run per store view in a batch checker, and the fix of one collection does not fit because each store needs its own scoped values (unsure: the iteration bound is the store count, not the item count). |

### P-LOOP-03

| Location | Verdict | Reason |
|---|---|---|
| app/code/Bazaarvoice/Connector/Model/Feed/PurchaseFeed.php:261 | TP-low | A full order save with its events runs per order just to set one flag where one UPDATE could do it, but this is a weekly off-hours cron (unsure: it depends on weekly order volume). |
| app/code/Bazaarvoice/Connector/Model/Indexer/Eav.php:1107 | TP | The product indexer writes each index row with its own resource model save on every reindex, where a bulk insert on duplicate would do (unsure: the cost scales with catalog size). |
| app/code/Bazaarvoice/Connector/Model/Indexer/Eav.php:1117 | TP-low | This save is in a loop but runs only in the catch branch after a failed save, so it is rare. |
| app/code/Bazaarvoice/Connector/Model/Indexer/Flat.php:1145 | TP-low | This save is in a loop but runs only in the catch branch after a failed save, so it is rare. |
| app/code/Mageplaza/Blog/Model/Import/MageFanM2.php:273 | TP-low | One save runs per new topic, but only in a manual one-off admin import (unsure: it depends on the number of related-post groups). |
| app/code/Mageplaza/Blog/Model/Import/MageFanM2.php:770 | TP-low | One save runs per new comment, but only in a manual one-off admin import (unsure: it depends on the number of comments). |
| app/code/Mageplaza/Blog/Model/Import/MageFanM2.php:864 | TP-low | One save runs per new author, but the source admin user list is short and this is a manual one-off import. |
| app/code/Mageplaza/DeleteOrders/Controller/Adminhtml/Order/MassDelete.php:125 | TP-low | One delete runs per selected order, but this is an admin mass action on a selection and the repository offers no batch delete. |
| app/code/MultiSafepay/ConnectCore/Model/SecondChance.php:79 | TP-low | One save runs per cancelled item of one order, a short list, and only when a cancelled order is reopened. |
| app/code/TIG/PostNL/Controller/Adminhtml/Matrix/Save.php:47 | TP-low | One save runs per country selected in a rarely used admin form, so the list is bounded and the action is rare. |
| app/code/TIG/PostNL/Service/Shipment/ErsShipmentManager.php:76 | TP-low | One delete runs per return label of one shipment, a very short list. |
| app/code/TIG/PostNL/Service/Shipment/Labelling/GenerateAbstract.php:216 | TP-low | One save runs per label of one API response, a very short list next to the remote API call that precedes it. |

### P-PLG-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Ho/Templatehints/Plugin/View/LayoutPlugin.php:75 | TP-low | An after plugin could do this since it receives the result and name, but the closure overhead is small even per element and this is a developer tool (unsure: it runs hundreds of times per uncached page). |
| app/code/MSP/DevTools/Plugin/PhpEnvironment/ResponsePlugin.php:81 | TP-low | An after plugin could do this, and the around wrapper costs one closure per response. |
| app/code/Mageplaza/Blog/Plugin/Frontend/CheckModuleEnabled.php:63 | TP-low | A before plugin could do this because the check only throws before the call, and the around wrapper costs one closure per dispatch. |
| app/code/Owebia/AdvancedShipping/Plugin/Magento/Quote/Model/Cart/ShippingMethodConverter.php:37 | TP-low | An after plugin could do this since it also receives the rate model, and the wrapper runs only once per shipping rate. |
| app/code/TIG/PostNL/Plugin/Quote/Address/Total/Shipping.php:40 | TP-low | An after plugin could do this since it also receives the subject and the assignment, and the wrapper runs only once per totals collection. |

### T-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageplaza/Blog/view/frontend/templates/account/register.phtml:36 | TP-low | The template does create a block, but no layout file or block in the module references this template, so it appears never to render. |
| app/code/Mageplaza/Blog/view/frontend/templates/account/register.phtml:48 | TP-low | The template does create a block, but no layout file or block in the module references this template, so it appears never to render. |
| app/code/Mageplaza/Blog/view/frontend/templates/account/register.phtml:53 | TP-low | The template does create a block, but no layout file or block in the module references this template, so it appears never to render. |
| app/code/Mageplaza/Blog/view/frontend/templates/account/register.phtml:58 | TP-low | The template does create a block, but no layout file or block in the module references this template, so it appears never to render. |
| app/code/Mageplaza/Blog/view/frontend/templates/account/register.phtml:68 | TP-low | The template does create a block, but no layout file or block in the module references this template, so it appears never to render. |
| app/code/Mageplaza/Blog/view/frontend/templates/account/register.phtml:73 | TP-low | The template does create a block, but no layout file or block in the module references this template, so it appears never to render. |
| app/code/Mageplaza/Blog/view/frontend/templates/account/register.phtml:78 | TP-low | The template does create a block, but no layout file or block in the module references this template, so it appears never to render. |

### X-DB-02

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageplaza/Blog/etc/db_schema.xml:168 | TP-low | The index repeats the primary key column traffic_id, and the extra write cost on a table with roughly one row per post is small. |
| app/code/Mageplaza/Blog/etc/db_schema.xml:216 | TP-low | The index repeats the primary key column comment_id, and it changes only on insert or delete of a blog comment, so the extra write cost is small. |
| app/code/Mageplaza/Blog/etc/db_schema.xml:232 | TP-low | The index repeats the primary key column like_id, and it changes only on insert or delete of a comment like, so the extra write cost is small. |
| app/code/Mageplaza/Blog/etc/db_schema.xml:266 | TP-low | The index repeats the primary key column like_id, and it changes only on insert or delete of a post like, so the extra write cost is small. |

### X-EVT-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/MSP/DevTools/etc/events.xml:26 | TP-low | The observer does run after every model load in all areas, but off an allowed developer IP it only reads a memoised flag, and a profiler needs every model (unsure: a developer tool that must see everything may count as a false positive). |
| app/code/MSP/DevTools/etc/events.xml:29 | TP-low | The observer does run after every flat collection load in all areas, but off an allowed developer IP it only reads a memoised flag, and a profiler needs every collection (unsure: a developer tool that must see everything may count as a false positive). |

### X-PLG-02

| Location | Verdict | Reason |
|---|---|---|
| app/code/Ho/Templatehints/Plugin/View/LayoutPlugin.php:75 | TP-low | The plugin runs for every layout element, but without the ath=1 request parameter it only does one parameter check, and a hints tool must see every element (unsure: this fits the rule's documented false positive for tools that must see every block). |
| app/code/MSP/DevTools/Plugin/View/Element/AbstractBlockPlugin.php:60 | TP-low | The plugin runs for every block, but it returns after one memoised flag when inactive, and a profiler must see every block (unsure: this fits the rule's documented false positive for profiling). |
| app/code/MSP/DevTools/Plugin/View/LayoutPlugin.php:99 | TP-low | The plugin runs for every layout element, but it returns after one memoised flag when inactive, and a profiler must see every element (unsure: this fits the rule's documented false positive for profiling). |
| app/code/Yireo/CspUtilities/Plugin/AddCspInlineScripts.php:30 | TP-low | The frontend plugin runs for every template block, but it only does an in_array check on a name list that is empty by default (unsure: this fits the rule's documented false positive for post-processing that returns quickly). |
