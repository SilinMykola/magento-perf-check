# Calibration on modules the rules were not tuned on

Rounds [1](../calibration.md) and [2](round-2.md) graded findings on the same 25 modules the rules were fixed on, so their figures are optimistic. This page checks the default set on 15 other modules. It decides nothing: the thresholds apply to rounds 1 and 2.

## Modules

The next 15 packages of type `magento2-module` by Packagist downloads on 2026-11-07, chosen the way the calibration corpus was (see [`corpus.json`](corpus.json)). They were not kept in the repository, and they ran on a separate copy of the Magento 2.4.9 corpus project. Before the run, each `registration.php` was checked to contain only the standard `ComponentRegistrar::register` call, because the analysis loads the project's autoloader.

| Package | Version | Downloads |
|---|---|---|
| magepal/magento2-googletagmanager | 3.0.2 | 1671447 |
| magmodules/magento2-channable | 1.25.0 | 1669808 |
| ampersand/magento2-disable-stock-reservation | 1.3.4 | 1666815 |
| mollie/magento2-hyva-compatibility | v3.2.0 | 1472320 |
| yireo/magento2-webp2 | 0.14.3 | 1434983 |
| yireo/magento2-next-gen-images | 0.5.16 | 1371473 |
| msp/common | 0.1.7 | 1365235 |
| firegento/fastsimpleimport | 2.1.1 | 1278454 |
| mageplaza/magento-2-social-login | 4.1.24 | 1258009 |
| mageplaza/module-google-recaptcha | 4.0.5 | 1182662 |
| yireo/magento2-googletagmanager2 | 3.10.8 | 1157584 |
| shipperhq/module-shipper | 20.61.11 | 1096634 |
| smile/module-elasticsuite-cms-search | 2.12.0 | 1035651 |
| fooman/emailattachments-implementation-m2 | 107.6.0 | 1014682 |
| trustpilot/module-reviews | 2.6.580 | 997055 |

## Result

No run failed: 15 modules, 90 findings of the default set, `incomplete: false`. The same run in every output format gave the same exit code; SARIF and GitHub carried every finding, and Checkstyle was well-formed XML.

Up to 12 findings per rule were drawn with a fixed seed (20261107) and graded once by one grader who did not write the rules, by reading the code. The table shows the findings the rules still report.

| Rule | Findings | Graded | TP | TP-low | FP |
|---|---|---|---|---|---|
| P-DI-01 | 41 | 12 | 2 | 10 | 0 |
| P-DI-06 | 18 | 12 | 0 | 10 | 2 |
| P-FPC-05 | 4 | 4 | 0 | 4 | 0 |
| P-LOOP-01 | 3 | 3 | 0 | 3 | 0 |
| P-LOOP-03 | 9 | 9 | 0 | 8 | 1 |
| P-LOOP-10 | 1 | 1 | 0 | 1 | 0 |
| P-PLG-01 | 3 | 3 | 0 | 3 | 0 |
| T-01 | 8 | 8 | 0 | 8 | 0 |
| X-DB-01 | 1 | 1 | 1 | 0 | 0 |
| X-DB-02 | 1 | 1 | 0 | 1 | 0 |
| X-EVT-03 | 1 | 1 | 0 | 1 | 0 |
| **All** | **90** | **55** | **3** | **49** | **3** |

52 of the 55 graded findings are correct, and 3 matter in practice. The tool is right far more often than it is important: most correct findings cost little.

## What this run changed

- P-COL-02 (experimental) had 4 false positives of 4: a `getFirstItem()` used as an emptiness check on a collection whose rows the same function reads later. It now stays silent when the function uses the rows anyway, and reports nothing on these modules.
- P-FPC-05 reported a controller's own private `setNoCacheHeaders()` as well as the `setHeader()` calls inside it. The helper is no longer reported.
- Two defects outside the rules came from running the tool module by module: a stub of a core class in one module's `Test` directory hid 24 findings in other modules, and notices of the disabled rule P-COL-02 appeared on every default run.

## False positives left

- P-DI-06: `Hybridauth\Adapter\OAuth2` subclasses, passed to the library by class name, so Magento does not build them. No general way to detect this is known.
- P-LOOP-03: a save in a loop behind a check on an id, so at most one write runs.

## Limitations

- 15 modules and one grader. 55 findings are a small sample, and a correct verdict can miss a caller outside the files read.
- Rules with no finding here (X-LAY-03, P-LOOP-05, P-LOOP-09 and others) were not tested by this run.
- Impact was judged from the code, not measured.

## Grading tables

### P-DI-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magmodules/Channable/Plugin/AroundIsSalableWithReservationsCondition.php:34 | TP-low | Plugins are built on first intercepted call and the only method reads the session first, so a Proxy defers nothing; the path is requested-qty salability only. |
| app/code/ShipperHQ/Shipper/Observer/RecordMultiOrder.php:67 | TP-low | The observer runs only on the frontend multishipping success action, where the session is already active. |
| app/code/Magmodules/Channable/Plugin/AfterStockItemConfiguration.php:29 | TP | `isManageStock()` is in the core `IsProductSalable` chain, so GraphQL and token REST salability checks start a session, but a Proxy alone would not help because the method reads the session at once. |
| app/code/ShipperHQ/Shipper/Block/Backend/Config/Carrier/About.php:36 | TP-low | This is an admin config block that only forwards the auth session to the core parent constructor, and the admin session is already active. |
| app/code/ShipperHQ/Shipper/Service/Backend/GetAdminShipData.php:31 | TP-low | `shipperadmin` defaults to active in global config, so rate collection in all areas reads this backend session unconditionally, which a Proxy would not defer. |
| app/code/MagePal/GoogleTagManager/Model/Customer.php:27 | TP-low | The model is used only by a customer section source and by cart and checkout blocks, and `getCustomer()` reads the session unconditionally. |
| app/code/Yireo/GoogleTagManager2/DataLayer/Tag/EnhancedConversions.php:21 | TP-low | It is wired only in the `checkout_onepage_success` layout, and `merge()` reads the session on that private page. |
| app/code/Yireo/GoogleTagManager2/Plugin/AddDataToCustomerSection.php:39 | TP-low | The frontend plugin runs on customer section load, where the session is already needed and is read unconditionally. |
| app/code/Trustpilot/Reviews/Block/Success.php:28 | TP-low | The block is used on the checkout success page, which already needs the checkout session. |
| app/code/Magmodules/Channable/Model/Carrier/Channable.php:71 | TP-low | The carrier defaults to active and `collectRates()` reads the session whenever active, so a Proxy helps only paths that build it without collecting rates. |
| app/code/Mageplaza/SocialLogin/Controller/Social/DataDeletion.php:76 | TP-low | This frontend controller forwards the session to its parent constructor, and the customer `ContextPlugin` reads the session on the same action anyway. |
| app/code/ShipperHQ/Shipper/Helper/Package.php:56 | TP | The helper is an eager dependency of the `Shipper` carrier and `RecordOrder`, so it defeats the Proxy that `etc/di.xml` sets on their own session and starts a session on GraphQL and token REST rate paths. |

### P-DI-06

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageplaza/SocialLogin/Model/Providers/Vkontakte.php:92 | FP | The class is a Hybridauth adapter passed by class name to the library rather than built by Magento DI, so constructor injection is not available, and the method has no callers in the module. |
| app/code/Mageplaza/SocialLogin/Model/Social.php:246 | TP-low | Direct use holds and is injectable, but it runs only in one branch of social customer creation. |
| app/code/Mageplaza/SocialLogin/Controller/Social/AbstractSocial.php:272 | TP-low | Direct use holds and is injectable, but it only creates a `DataObject` during a login redirect. |
| app/code/Mageplaza/SocialLogin/Controller/Popup/Create.php:176 | TP-low | Direct use holds and is injectable, but it is a lazy singleton lookup on a rare POST path. |
| app/code/Magmodules/Channable/Plugin/AroundIsAnySourceItemInStockCondition.php:65 | TP-low | Direct use holds and a factory would fit, but it runs only when the Channable skip-reservation session flag is set. |
| app/code/Mageplaza/SocialLogin/Model/Social.php:288 | TP-low | Direct use holds and is injectable, but it is a singleton lookup during social account creation. |
| app/code/Mageplaza/SocialLogin/Controller/Popup/Create.php:377 | TP-low | Direct use holds and is injectable, but it only creates a `DataObject` during a registration redirect. |
| app/code/Mageplaza/SocialLogin/Controller/Popup/Create.php:188 | TP-low | Direct use holds and is injectable, but it is a lazy singleton lookup on a rare POST path. |
| app/code/Mageplaza/SocialLogin/Controller/Popup/Create.php:330 | TP-low | Direct use holds and is injectable, but it is a lazy lookup in a deprecated accessor. |
| app/code/Trustpilot/Reviews/Helper/Data.php:204 | TP-low | Direct use holds and `ProductMetadataInterface` is injectable, but the lookup returns a shared instance at trivial cost. |
| app/code/Mageplaza/SocialLogin/Controller/Social/AbstractSocial.php:352 | TP-low | Direct use holds and is injectable, but it is a lazy lookup in a deprecated accessor. |
| app/code/Mageplaza/SocialLogin/Model/Providers/Zalo.php:128 | FP | The class is a Hybridauth adapter passed by class name to the library rather than built by Magento DI, so constructor injection is not available, and the method has no callers in the module. |

### P-FPC-05

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magmodules/Channable/Controller/Feed/Json.php:102 | TP-low | The header is intended, as the method comment says, on a token-protected feed that also records the last fetch on each request. |
| app/code/Magmodules/Channable/Controller/Feed/Preview.php:95 | TP-low | The header is deliberate on a token-protected preview response that serves no shoppers. |
| app/code/Magmodules/Channable/Controller/Feed/Preview.php:96 | TP-low | This `Pragma` header belongs to the same decision as line 95 on a token-protected preview. |
| app/code/Magmodules/Channable/Controller/Feed/Json.php:103 | TP-low | This `Pragma` header belongs to the same decision as line 102 on a token-protected feed. |

### P-LOOP-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/FireGento/FastSimpleImport/Model/Import/Category.php:631 | TP-low | One load per store per category holds in a batch import, but the store count caps it, URL rewrite generation dominates, and the same-ID store-scoped loads do not fit a single `getList()`. |
| app/code/ShipperHQ/Shipper/Model/Api/FetchUpdatedCarrierRate.php:184 | TP-low | One product load per item holds, but the loop is bound by the items in one request to an ACL-protected listing API. |
| app/code/Yireo/GoogleTagManager2/Util/GetCategoryPath.php:48 | TP-low | The loop is capped by category depth, and the class has no callers in the module. |

### P-LOOP-03

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magmodules/Channable/Controller/Adminhtml/Returns/MassDelete.php:73 | TP-low | One delete per selected row holds, but this is an admin-only mass action. |
| app/code/ShipperHQ/Shipper/Helper/Package.php:198 | TP-low | The write is conditional and bound by the carrier groups of one order. |
| app/code/Magmodules/Channable/Service/Order/Items/Add.php:144 | TP-low | One quote item save per line holds, but it is bound by the lines of one imported order. |
| app/code/ShipperHQ/Shipper/Plugin/Checkout/ShippingInformationPlugin.php:182 | FP | The save is guarded by `$oneAddress->getId() == $quoteAddress->getCustomerAddressId()`, so at most one address matches and at most one write runs per call. |
| app/code/ShipperHQ/Shipper/Setup/Patch/Data/AddDestinationTypeAttributeToForm.php:78 | TP-low | The loop covers a fixed list of two attributes in a data patch that runs once. |
| app/code/ShipperHQ/Shipper/Helper/Package.php:205 | TP-low | The write is conditional and bound by the carrier groups of one order. |
| app/code/Magmodules/Channable/Plugin/CreditmemoSaveAfter.php:107 | TP-low | The write is bound by the items of one credit memo that have a matching return. |
| app/code/ShipperHQ/Shipper/Helper/Package.php:195 | TP-low | One status history save per carrier group holds, but the group count of one order is small. |
| app/code/Magmodules/Channable/Controller/Adminhtml/Item/InlineEdit.php:63 | TP-low | The write is bound by the rows of one admin inline-edit request. |

### P-LOOP-10

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magmodules/Channable/Controller/Adminhtml/Item/InlineEdit.php:55 | TP-low | One load per row holds, but it is bound by the rows of one admin inline-edit request. |

### P-PLG-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magmodules/Channable/Plugin/AddDiscountToInvoice.php:21 | TP-low | The body only post-processes the `$proceed()` result, so an after plugin is equivalent, but it runs only when invoice totals are collected. |
| app/code/Mageplaza/SocialLogin/Plugin/Captcha/FixNullFormId.php:48 | TP-low | The body only edits the request before `$proceed()`, so a before plugin at the same sort position is equivalent, but the path is AJAX login only. |
| app/code/ShipperHQ/Shipper/Plugin/Quote/QuoteManagementPlugin.php:97 | TP-low | The body runs a helper and then proceeds with unchanged arguments, so a before plugin is equivalent, but it runs once per placed order. |

### T-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageplaza/SocialLogin/view/frontend/templates/popup/form/create.phtml:58 | TP-low | `createBlock()` is present, but it builds one widget during a page cache miss and core `form/register.phtml` uses the same pattern. |
| app/code/Mageplaza/SocialLogin/view/frontend/templates/popup/form/create.phtml:36 | TP-low | `createBlock()` is present, but it builds one widget during a page cache miss and core `form/register.phtml` uses the same pattern. |
| app/code/Mageplaza/SocialLogin/view/frontend/templates/hyva/popup/form/create.phtml:38 | TP-low | `createBlock()` is present, but it builds one widget when the template renders and core `form/register.phtml` uses the same pattern. |
| app/code/Mageplaza/SocialLogin/view/frontend/templates/popup/form/create.phtml:182 | TP-low | `createBlock()` is present, but it builds one widget during a page cache miss and core `form/register.phtml` uses the same pattern. |
| app/code/Mageplaza/SocialLogin/view/frontend/templates/hyva/popup/form/create.phtml:61 | TP-low | `createBlock()` is present, but it builds one widget when the template renders and core `form/register.phtml` uses the same pattern. |
| app/code/Mageplaza/SocialLogin/view/frontend/templates/popup/form/create.phtml:178 | TP-low | `createBlock()` is present, but it builds one widget during a page cache miss and core `form/register.phtml` uses the same pattern. |
| app/code/Mageplaza/SocialLogin/view/frontend/templates/hyva/popup/form/create.phtml:74 | TP-low | `createBlock()` is present, but it builds one widget when the template renders and core `form/register.phtml` uses the same pattern. |
| app/code/Mageplaza/SocialLogin/view/frontend/templates/hyva/popup/form/create.phtml:70 | TP-low | `createBlock()` is present, but it builds one widget when the template renders and core `form/register.phtml` uses the same pattern. |

### X-DB-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageplaza/SocialLogin/etc/db_schema.xml:26 | TP | The table declares only a foreign key and non-unique indexes, including a plain index on its identity column, and the module has no `Setup` directory that adds a primary key. |

### X-DB-02

| Location | Verdict | Reason |
|---|---|---|
| app/code/ShipperHQ/Shipper/etc/db_schema.xml:25 | TP-low | The btree index duplicates `PRIMARY` on `synch_id`, but this is a synchronization work table, so the write overhead is negligible. |

### X-EVT-03

| Location | Verdict | Reason |
|---|---|---|
| app/code/Yireo/NextGenImages/etc/frontend/events.xml:4 | TP-low | The observer fires per block, but it returns after a `ttl` data check and a header read unless the block has a `ttl` or the request is an Alpine AJAX call. |

## GraphQL modules

The modules above have one GraphQL schema between them, so 16 more were checked. They were picked by hand from the top of the Packagist search for "graphql" by downloads on 2026-11-07, preferring modules that add fields to core types, so this sample is not random: `scandipwa/{catalog,quote,wishlist,cms,store,compare,sales,review}-graphql`, `mageplaza/module-{blog,product-labels,free-gifts,store-locator,shop-by-brand}-graphql`, `mageworx/module-optiongraphql`, `dotdigital/dotdigital-magento2-extension-graph-ql` and `multisafepay/magento2-graphql`. Each `registration.php` was checked first, as above. Modules they depend on were not installed, so types from those stay unknown and some queries go unreported, for example in `Mageplaza\FreeGiftsGraphQl\Model\Resolver\FreeGiftsItem`.

No run failed: 12 findings with X-GQL-02 and P-FPC-03 enabled, `incomplete: false`, no notices. One grader who did not write the rules read all 12 once:

| Rule | Findings | TP | TP-low | FP |
|---|---|---|---|---|
| X-GQL-02 | 4 | 3 | 1 | 0 |
| P-DI-01 | 5 | 1 | 4 | 0 |
| P-LOOP-01 | 3 | 2 | 1 | 0 |

Twelve is too few to decide anything, and the grader was unsure of 3 grades: the size of two lists and how often one code path runs. X-GQL-02 stays experimental until it has at least 10 graded findings.

This run also showed that 3 of the 14 schemas were skipped with a notice, although Magento reads them: `//` and `/** */` comments and `extends type` did not parse. They parse now.

## What the first runs missed

Everything above, and rounds 1 and 2, ran from a checkout of this repository. That turned out to undercount, for two reasons.

- **Test stubs.** They were in Composer's `autoload-dev`, and PHPStan loads this package's autoloader, so the stand-ins for `Product`, `AbstractModel` and the collections replaced the real classes. A method missing from a stub left the rest of a call chain untyped. An installed package never had this. A run from a clean install and one from the fixed checkout now give the same findings.
- **Factories that are not generated.** Of the 1205 factory parameters in the 56 modules, 680 named a factory that the checked project had not generated, so calls on them went unchecked. `create()` is now typed from the factory's name. On all 56 modules with the experimental rules on, the run with generated factories and the run without `generated/code` both give 383 findings; without this fix, the second gives 327.

Findings of the default set now:

| Modules | First runs | Now |
|---|---|---|
| 25 of the calibration corpus | 218 | 228 |
| 15 above | 90 | 96 |
| 16 GraphQL modules | 8 | 9 |

These projects have some generated factories and the first runs had the stubs, so this table does not show the size of each fix. The table below does for factories, against a run on all 56 modules with no `generated/code` and no factory typing, on a clean autoloader.

The grades above were not redone. These tables grade what the fixes added, by one grader who did not write the rules, reading the code once. Most of the sample is correct and cheap, as before. Most P-LOOP-03 findings not graded are in one-off blog imports of `Magefan\Blog\Model\Import`.

| Rule | Added on 56 modules | Graded | TP | TP-low | FP |
|---|---|---|---|---|---|
| P-LOOP-01 | 7 | 5 | 3 | 2 | 0 |
| P-LOOP-02 | 3 | 2 | 0 | 2 | 0 |
| P-LOOP-03 | 39 | 18 | 4 | 13 | 1 |
| P-LOOP-07 | 1 | 0 | | | |
| P-LOOP-10 | 4 | 4 | 2 | 2 | 0 |
| **All** | **54** | **29** | **9** | **19** | **1** |

Two more P-LOOP-02 findings were false, paged queries in a `while` loop. The rule no longer reports those. The P-LOOP-03 false positive is a save inside an `if` in a loop that matches at most one key.

The six experimental rules added in this work, on all 56 modules:

| Rule | Findings | Graded | TP | TP-low | FP |
|---|---|---|---|---|---|
| X-GQL-02 | 6 | 6 | 3 | 1 | 2 |
| P-LOOP-04 | 3 | 3 | 3 | 0 | 0 |
| P-LOOP-06 | 1 | 1 | 1 | 0 | 0 |
| P-COL-03 | 2 | 1 | 0 | 1 | 0 |
| X-PLG-02 | 5 | 5 | 1 | 4 | 0 |
| P-FPC-03 | 0 | | | | |

These samples are too small to move any rule to the default set. Both X-GQL-02 false positives come from putting ScandiPWA's quote module and Mollie in one project, which this scratch project does and few real ones do. The grader doubted the TP grades of two P-LOOP-04 findings, on cart and checkout pages, because carts are small, and that of P-LOOP-06, because the attribute comes from configuration.

