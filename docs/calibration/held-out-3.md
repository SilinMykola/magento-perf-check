# Third check on modules the rules were not tuned on

The first two held-out checks ([first](held-out.md), [second](held-out-2.md)) ran on Packagist modules chosen by downloads. This one runs on modules from GitHub: free modules of extension vendors, agency tools and developer utilities, many of them not on Packagist at all. It decides nothing: the thresholds apply to the calibration rounds. The false positives it found were fixed in the rules, or are explained below where they were not.

## Modules

The `github` part of the [calibration corpus](corpus.json), built on 2026-11-13: the 50 most-starred GitHub repositories with the topic `magento2-module`, not archived and not forks, that hold one module of type `magento2-module` not yet in the corpus and whose requirements the corpus copy meets. Each was cloned at its default branch; the table links the commit that was analyzed. 74 repositories were passed over on the way: 34 already in the corpus, 26 a requirement the copy does not meet, 12 no single module at the root or in `src/`, 1 registration.php that holds more than the standard call, 1 no `composer.json` of type `magento2-module`.

The part is a copy of the Magento 2.4.9 corpus project with the 125 Packagist modules and Hyvä Theme of the other parts, so the modules these depend on are present. Only the 50 new modules were analyzed.

| Repository at the analyzed commit | Module | Stars |
|---|---|---|
| [friends-of-hyva/magento2-react-checkout](https://github.com/friends-of-hyva/magento2-react-checkout/tree/fff0ec0ae454ceaeba4322b1d2a776b64b8be07d) | `FriendsOfHyva_ReactCheckout` | 193 |
| [markshust/magento2-module-pagebuildersourcecode](https://github.com/markshust/magento2-module-pagebuildersourcecode/tree/fc32fd948ef4a222105c8ff3a17f40419cfe4835) | `MarkShust_PageBuilderSourceCode` | 119 |
| [swissup/module-search-mysql-legacy](https://github.com/swissup/module-search-mysql-legacy/tree/db007202993e6fcbf53a10c9924afd22eb36e66d) | `Swissup_SearchMysqlLegacy` | 105 |
| [sbodak/magento2-checkout-custom-form](https://github.com/sbodak/magento2-checkout-custom-form/tree/110890eea67e5932da395a579efd28368a0b07d9) | `Bodak_CheckoutCustomForm` | 100 |
| [magepal/magento2-custom-shipping-rate](https://github.com/magepal/magento2-custom-shipping-rate/tree/f2ca911656b7ae5ec88782f8d7fa71ab4fb6f23d) | `MagePal_CustomShippingRate` | 96 |
| [markshust/magento2-module-simpledata](https://github.com/markshust/magento2-module-simpledata/tree/75cd52787c59a526e1952d6183350e91470e865c) | `MarkShust_SimpleData` | 94 |
| [markshust/magento2-module-ordergrid](https://github.com/markshust/magento2-module-ordergrid/tree/158a79f0300021e7aedd3365c0ef8d613c92c69f) | `MarkShust_OrderGrid` | 90 |
| [zepgram/module-disable-search-engine](https://github.com/zepgram/module-disable-search-engine/tree/4478f6020b284c7eedfd65499e1a356c4def859c) | `Zepgram_DisableSearchEngine` | 86 |
| [baldwin-agency/magento2-module-image-cleanup](https://github.com/baldwin-agency/magento2-module-image-cleanup/tree/a9188294ed92ac315ec172513cfe88b37aef4abb) | `Baldwin_ImageCleanup` | 85 |
| [karliuka/m2.SmartCategory](https://github.com/karliuka/m2.SmartCategory/tree/481e7d4e0c390f40cd75031ae110eb5a1ee2dc1e) | `Faonni_SmartCategory` | 84 |
| [mageprince/magento2-buynow](https://github.com/mageprince/magento2-buynow/tree/b8ab94a21df26c6c09f1721ec62bf2eeb7377abb) | `Mageprince_BuyNow` | 82 |
| [magepal/magento2-preview-checkout-success-page](https://github.com/magepal/magento2-preview-checkout-success-page/tree/54f86d84fd740f46f96e39c851981c0ac2b3699b) | `MagePal_PreviewCheckoutSuccessPage` | 78 |
| [magepal/magento2-guest-to-customer](https://github.com/magepal/magento2-guest-to-customer/tree/0a6ac3c3f933e8762fd9335d35c83d310766c35b) | `MagePal_GuestToCustomer` | 69 |
| [MagePsycho/magento2-custom-shipping](https://github.com/MagePsycho/magento2-custom-shipping/tree/61d9260e9029f681e7a4d97e7cff38bcd5d42951) | `MagePsycho_CustomShipping` | 69 |
| [run-as-root/magento2-message-queue-retry](https://github.com/run-as-root/magento2-message-queue-retry/tree/88e113ef107c1cc6eb41084b790799827fcbac7c) | `RunAsRoot_MessageQueueRetry` | 64 |
| [karliuka/m2.Price](https://github.com/karliuka/m2.Price/tree/136d7f7b99a8bcc00e43d978ec7603a74086e7e5) | `Faonni_Price` | 63 |
| [tuyennn/magento2-outofstockatlast](https://github.com/tuyennn/magento2-outofstockatlast/tree/acbf5c434d50f34b6194666ba4c4412bb62da946) | `GhoSter_OutOfStockAtLast` | 62 |
| [lizardmedia/varnish-warmer-magento2](https://github.com/lizardmedia/varnish-warmer-magento2/tree/b9217acc1a4d464b41232af4ccd036a67dab2bf3) | `LizardMedia_VarnishWarmer` | 61 |
| [mageprince/magento2-FAQ](https://github.com/mageprince/magento2-FAQ/tree/e84668ae2546d1134ac74bc717e81ea9c513146d) | `Mageprince_Faq` | 60 |
| [shkoliar/magento-grid-colors](https://github.com/shkoliar/magento-grid-colors/tree/c62284f9b9ff152c9d623d9cacf9cabf93c4ee2e) | `Shkoliar_GridColors` | 59 |
| [magepal/magento2-catalog-lazy-load](https://github.com/magepal/magento2-catalog-lazy-load/tree/f69e7a5f8c08ab20158377005ccfcb8ee599a920) | `MagePal_CatalogLazyLoad` | 57 |
| [magepal/magento2-link-product](https://github.com/magepal/magento2-link-product/tree/7709ec101c56857cb6ae726e0e230d81602eb24d) | `MagePal_LinkProduct` | 55 |
| [williankeller/magento2-sign-in-with-phone-number](https://github.com/williankeller/magento2-sign-in-with-phone-number/tree/f16c168552af0e7e951e7d427a553e05fac3a974) | `Magestat_SigninPhoneNumber` | 54 |
| [magefan/module-lazyload](https://github.com/magefan/module-lazyload/tree/dcaf3cd07d9a2799b76b312a055e4404f77310a7) | `Magefan_LazyLoad` | 52 |
| [basecom/magento2-csp-split-header](https://github.com/basecom/magento2-csp-split-header/tree/2e2328a3be76dbe78b792986dea66dd2f9fda359) | `Basecom_CspSplitHeader` | 51 |
| [deved-it/magento2-disable-customer-registration](https://github.com/deved-it/magento2-disable-customer-registration/tree/eb61d73d7cb2baed814aedbedcc5ac1da0c10990) | `Deved_DisableRegistration` | 50 |
| [magepow/magento-2-mega-menu](https://github.com/magepow/magento-2-mega-menu/tree/22809b08f63cf64bc35ecca04dc05a46e79a55dc) | `Magiccart_Magicmenu` | 49 |
| [shkoliar/magento-ngrok](https://github.com/shkoliar/magento-ngrok/tree/d53065a8a81b0e7e04b04b815c64b44ceed2e0a6) | `Shkoliar_Ngrok` | 49 |
| [christophermallory/magento2-freeshipping-progress-bar](https://github.com/christophermallory/magento2-freeshipping-progress-bar/tree/223b7d6a9f8c3e1e00534c1589a210b6909095a1) | `ChrisMallory_FreeShippingProgressBar` | 48 |
| [magefan/module-conflict-detector](https://github.com/magefan/module-conflict-detector/tree/26273ee3aa1eb942c9feafdbd9aac66cd5367e9d) | `Magefan_ConflictDetector` | 47 |
| [mageprince/magento2-logviewer](https://github.com/mageprince/magento2-logviewer/tree/6b21289d1248eb1457b13d31ee45b1054aa129cf) | `Mageprince_LogViewer` | 45 |
| [MagePsycho/magento2-easy-template-path-hints](https://github.com/MagePsycho/magento2-easy-template-path-hints/tree/e90ab3c1ca4c095d9650b293339939bec5669f96) | `MagePsycho_Easypathhints` | 44 |
| [magefan/module-rocketjavascript](https://github.com/magefan/module-rocketjavascript/tree/fb7294047ed06dfb9955def688c6184f97bd244a) | `Magefan_RocketJavaScript` | 43 |
| [magepal/magento2-customer-account-links-manager](https://github.com/magepal/magento2-customer-account-links-manager/tree/c1be40dbfa04a06d6ee7add1123b96ff1ecae9ab) | `MagePal_CustomerAccountLinksManager` | 40 |
| [magepow/magento-2-categories](https://github.com/magepow/magento-2-categories/tree/e0b14222e5b36adf84cd29bfb90f113a2ca8a694) | `Magepow_Categories` | 40 |
| [karliuka/m2.IndexerUrlRewrite](https://github.com/karliuka/m2.IndexerUrlRewrite/tree/794e5bd9496cda9443557231870f08e1a27c7ddd) | `Faonni_IndexerUrlRewrite` | 40 |
| [integer-net/magento2-global-custom-layout](https://github.com/integer-net/magento2-global-custom-layout/tree/fc8d562748b5590a32a3c83af36196da78bd0cbc) | `IntegerNet_GlobalCustomLayout` | 40 |
| [trive-digital/magento2-varnish-fixes](https://github.com/trive-digital/magento2-varnish-fixes/tree/0fa9d2aff532128877425d881af765b277ecfb4e) | `Trive_Varnish` | 39 |
| [magefan/module-cli](https://github.com/magefan/module-cli/tree/5156dbdad1cb7d609548d6794c53acca3ab2882f) | `Magefan_Cli` | 37 |
| [graschik/magento-2-admin-ui](https://github.com/graschik/magento-2-admin-ui/tree/e3aaaedc8894eda50de11a58ecee8d8d2235ba2b) | `Grasch_AdminUi` | 37 |
| [williankeller/magento2-facebook-pixel](https://github.com/williankeller/magento2-facebook-pixel/tree/8119d5f33e6e56749c1779f983c9a51fd6e192e4) | `Magestat_FacebookPixel` | 36 |
| [magefan/module-google-tag-manager](https://github.com/magefan/module-google-tag-manager/tree/69563d146162e4af08cc7671ff6d586cc721bb2e) | `Magefan_GoogleTagManager` | 36 |
| [shockwavemk/magento2-module-cron-schedule](https://github.com/shockwavemk/magento2-module-cron-schedule/tree/a0e50b95f8830329ec8988e0a54684377c5fc30c) | `Shockwavemk_CronSchedule` | 34 |
| [DivanteLtd/magento2-pimcore-bridge](https://github.com/DivanteLtd/magento2-pimcore-bridge/tree/884a7af04f8f71e9fcf6cdc03b37003f4feedf12) | `Divante_PimcoreIntegration` | 34 |
| [rangerz/magento2-module-quicklink](https://github.com/rangerz/magento2-module-quicklink/tree/6947943257eec38cd64eb9dd70e068ab9cafcb2f) | `Rangerz_Quicklink` | 34 |
| [macopedia/CategoryImporter](https://github.com/macopedia/CategoryImporter/tree/bbd0a32e5470a677d86d7fd322ffc9330a3cf7d5) | `Macopedia_CategoryImporter` | 34 |
| [Itonomy/magento2-product-visibillitygrid](https://github.com/Itonomy/magento2-product-visibillitygrid/tree/319012f21b080764bd4cf63f547e43e4712d0873) | `Itonomy_ProductVisibilityGrid` | 34 |
| [robaimes/module-checkout-designs](https://github.com/robaimes/module-checkout-designs/tree/cbee3dcb6a501df39b4882b9495b8ef7a169b22d) | `Aimes_CheckoutDesigns` | 33 |
| [Siteation/magento2-debugbar](https://github.com/Siteation/magento2-debugbar/tree/35da8d078ddd10d4d4bcb322f4ad68b7036096b4) | `Siteation_DebugBar` | 32 |
| [samgranger/StoreCodeBodyClass](https://github.com/samgranger/StoreCodeBodyClass/tree/3ee96eaebecd749459bfad8e8239b610da23da92) | `SamGranger_StoreCodeBodyClass` | 32 |

## Result

The run ([`runs/github/`](runs/github/github.meta.json), tool commit 70ea6e2) enabled every rule, experimental ones included: 211 findings, `incomplete: false`, two notices for receivers of unknown type. 16 modules have no finding. 70 findings come from 11 rules of the default set.

Up to 12 findings per rule of the default set were drawn with a fixed seed (20261113) and graded once by reading the code, by graders who did not write the rules, with the verdicts of the [method](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration.md#method). The four false positives were checked again against the code before the rules were changed. No time or memory was measured.

| Rule | Findings | Graded | TP | TP-low | FP |
|---|---|---|---|---|---|
| P-COL-01 | 1 | 1 | 0 | 1 | 0 |
| P-DI-01 | 20 | 12 | 0 | 12 | 0 |
| P-DI-06 | 7 | 7 | 0 | 5 | 2 |
| P-FPC-01 | 1 | 1 | 0 | 1 | 0 |
| P-LOOP-01 | 9 | 9 | 1 | 7 | 1 |
| P-LOOP-02 | 1 | 1 | 0 | 1 | 0 |
| P-LOOP-03 | 12 | 12 | 1 | 10 | 1 |
| P-LOOP-11 | 1 | 1 | 0 | 1 | 0 |
| P-PLG-01 | 5 | 5 | 2 | 3 | 0 |
| T-01 | 12 | 12 | 0 | 12 | 0 |
| X-EVT-01 | 1 | 1 | 0 | 1 | 0 |
| **All** | **70** | **62** | **4** | **54** | **4** |

58 of 62 verdicts are correct (94 %) and 4 of those 58 matter in practice, the same shape as the first two checks: most correct findings sit in admin actions, on pages that start a session anyway, or on short lists. The 141 findings of experimental rules were not graded.

## What this run changed

- **P-LOOP-03** reported `$this->attributeSetRepository->delete($attrSet)` in a `catch` that always ends with `throw $ex`, inside two loops. It runs at most once: the exception leaves both loops. The loop rules now follow a `catch` in a loop like any block, so a call in it that the loop is left after no longer counts. In a `try` in a loop, a call in the `try` itself counts unless every `catch` leaves the loop too, and a `finally` counts after a `continue`.
- **P-DI-06** reported two forms of what it allows: `$this->config = $config !== null ? $config : ObjectManager::getInstance()->get(DeploymentConfig::class)` in a constructor, the fallback written as a full ternary, and a lazy getter that assigns the object to a local variable before storing it in the tested property. Both are accepted now, the ternary in a constructor only, and so is `!empty($x) ? $x : ...` there.
- **P-LOOP-01** reported `$this->productRepository->getById()` in `try { ...; break; } catch (NoSuchEntityException $e) {}`, in a loop over the options of a quote item, where only one option has the code the `if` around it tests. It was graded false and stays reported: when the product is missing, the repository throws after its query and the empty `catch` goes on with the next option, so the rule cannot tell this loop from one that looks up many ids. Counted as false, it is 1 of 9 for this rule here.

Effect of these changes on every part of the corpus, with all rules on and the result cache cleared before each run:

| Part | Before | After | Removed |
|---|---|---|---|
| Magento 2.4.9 core | 2911 | 2904 | 6 P-DI-06, all constructor fallbacks written as `null !== $x ? $x : ...` (`TemporaryStorage` of the MySQL search adapter and five `LinkedProductSelectBuilderBy*` classes); 1 P-LOOP-11, an object manager call in a `catch` of `Checkout\Controller\Cart\Addgroup` that returns right after |
| packagist (25 modules) | 566 | 566 | none |
| extended (100 modules, Hyvä) | 1609 | 1609 | none |
| github (50 modules) | 211 | 208 | the three fixed false positives |

No finding was added, and none of the removed ones is in an earlier graded sample.

## Limitations

- 50 modules, picked by stars, and one grading of each finding. A verdict can miss a caller outside the files read.
- Impact was judged from the code, not measured.
- The default branch of a repository changes; the commits in the table are what was graded.

## Grading tables

### P-COL-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magefan/GoogleTagManager/Model/Transaction/Log.php:112 | TP-low | `isTransactionUnique()` runs from a plugin on the purchase data layer (order success page) and `count()` does load the collection, but the filter on requester, transaction id and store id matches at most a row or two, so a full select instead of COUNT costs next to nothing. |

### P-DI-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/ChrisMallory/FreeShippingProgressBar/ViewModel/Cart/ProgressBar.php:54 | TP-low | The view model is used only in `checkout_cart_index.xml`, so it is built only on the uncacheable cart page, where the checkout session is started and the quote read anyway. |
| app/code/FriendsOfHyva/ReactCheckout/Observer/AddCartInfoInCheckoutConfig.php:41 | TP-low | The observer listens to `hyva_react_checkout_config`, which only the checkout config view model dispatches on the checkout page, and it reads the session at once, so a Proxy would save nothing. |
| app/code/FriendsOfHyva/ReactCheckout/ViewModel/ReactCheckoutLocalStorage.php:38 | TP-low | The view model appears only in `hyvareactcheckout_reactcheckout_index.xml` (the checkout page), where the checkout session is needed and started anyway. |
| app/code/MagePal/GuestToCustomer/Block/Customer/LookupForm.php:61 | TP-low | The block appears only on the customer account page `customer_guesttocustomer_lookupform`, which needs the customer session anyway. |
| app/code/MagePal/PreviewCheckoutSuccessPage/Plugin/Model/Session/SuccessValidatorPlugin.php:51 | TP-low | It is a frontend plugin on `SuccessValidator`, which itself depends on the checkout session and runs only on the checkout success action, so the session is already started there. |
| app/code/MagePsycho/Easypathhints/Model/Cookie.php:42 | TP-low | The cookie manager is built through the helper on every frontend and admin action (the `controller_action_predispatch` observer and the `TemplateEngineFactory` plugin), but building `PhpCookieManager` is cheap, so only the guideline (design) issue remains. |
| app/code/MagePsycho/Easypathhints/Model/Cookie.php:44 | TP-low | Through `TemplateHintCookie`, the helper, the `controller_action_predispatch` observer and the `TemplateEngineFactory` plugin, a generic session starts on every action dispatch, but on page cache misses and admin requests the session is started anyway (unsure: a template rendered in REST, for example an order email, would start a session that nothing else needs). |
| app/code/Magefan/GoogleTagManager/Block/DataLayer/BeginCheckout.php:43 | TP-low | The block appears only in the checkout layouts (`checkout_index_index`, `hyva_checkout_index_index`, `onestepcheckout_index_index`), where the checkout session is started anyway. |
| app/code/Mageprince/Faq/Helper/Data.php:52 | TP-low | The helper is built on every storefront layout through `xsi:type="helper"` arguments in `default.xml`/`hyva_default.xml`, but only on page cache misses, where the customer `ContextPlugin` starts the session anyway. |
| app/code/Magestat/FacebookPixel/Block/Success.php:49 | TP-low | The block appears only in `checkout_onepage_success.xml`, an uncacheable page that needs the checkout session anyway. |
| app/code/Magestat/SigninPhoneNumber/Block/Form/Login.php:43 | TP-low | It is a preference for the core login form block, which passes the session on to the parent (the core block also has no Proxy) and renders on the customer login page, where the session is needed anyway. |
| app/code/Swissup/SearchMysqlLegacy/Model/Adapter/Mysql/Aggregation/DataProvider/SelectBuilderForAttribute.php:65 | TP-low | It runs only for layered navigation aggregation when the legacy `lmysql` search engine is selected, on category or search pages that start the session anyway, and `build()` reads the customer group from the session for the price bucket anyway (unsure: a GraphQL products query with aggregations would start the session there). |

### P-DI-06

| Location | Verdict | Reason |
|---|---|---|
| app/code/Baldwin/ImageCleanup/Service/HyvaThemeFallbackStoreThemeResolver.php:84 | FP | This is a lazy getter: `if ($this->hyvaThemeFallbackConfig === null)` assigns the object manager result to a local variable, then stores it in the same property, so the object manager is asked once per object, which is the pattern the rule exempts; the dependency is also optional (Hyvä may be absent) in an image cleanup tool. |
| app/code/Faonni/SmartCategory/Model/Indexer/AbstractIndexer.php:182 | TP-low | `getCacheContext()` returns `ObjectManager::get(CacheContext::class)` without keeping it, and the indexer `doExecuteList` calls it once per batch, so the cost is a shared-instance lookup and the issue is design only. |
| app/code/Magefan/GoogleTagManager/Block/DataLayer/PurchaseGetOrderFromRequest.php:36 | TP-low | The block appears only on the 2c2p payment success page (`p2c2p_payment_success.xml`), and it fetches `OrderFactory` from the object manager once, so the issue is design only. |
| app/code/Magefan/LazyLoad/Model/Config.php:158 | TP-low | `getEnabled()` caches its result in `$this->enabled`, so the object manager is asked once per request on storefront pages, for a soft Plumrocket dependency or the request; the issue is design only. |
| app/code/Magefan/RocketJavaScript/Model/Controller/ResultPlugin.php:184 | TP-low | It runs in `aroundRenderResult` on frontend page renders only when Plumrocket AMP is configured, to reach an optional class that cannot be injected; the issue is design only. |
| app/code/Magestat/SigninPhoneNumber/Model/AccountManagement.php:236 | TP-low | The `getAuthentication()` getter, copied from core, does not keep the object and runs only on customer login (the preference for `AccountManagementInterface`), a one-off action. |
| app/code/Swissup/SearchMysqlLegacy/SearchAdapter/Mysql/TemporaryStorage.php:49 | FP | `$this->config = $config !== null ? $config : ObjectManager::getInstance()->get(DeploymentConfig::class);` in the constructor is the backward-compatible constructor fallback that Adobe allows, written as a full ternary instead of `??`. |

### P-FPC-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Bodak/CheckoutCustomForm/Block/Order/CustomFields.php:60 | TP-low | The constructor sets the obsolete `$_isScopePrivate = true`, but the block is only placed on the customer-account `sales_order_view` page, which core already makes uncacheable through `cacheable="false"` on `sales.order.view`, so the flag changes nothing. |

### P-LOOP-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magefan/GoogleTagManager/Model/AbstractDataLayer.php:186 | TP-low | `getCategoryNames()` runs on storefront product, cart and checkout data layers and loads each category of the chosen category's path through `CategoryRepository::get()`, but the path is bounded by tree depth (3 to 5 ids) and the repository caches by id and store, many of them already loaded by the loop at line 227 (unsure: on a large cart the per-item paths add up). |
| app/code/Magefan/GoogleTagManager/Model/AbstractDataLayer.php:227 | TP | `getCategoryByProduct()` loads every category assigned to the product with `CategoryRepository::get()` (a full EAV category load each) on every storefront product view and for every cart/checkout item, and a product can sit in many categories, where one category collection filtered by the ids would do. |
| app/code/Magefan/GoogleTagManager/Model/DataLayer/Cart/Item.php:79 | FP | `getItemProduct()` calls `productRepository->getById()` only for the quote item option with code `simple_product` and `break`s right after it, so it runs once per call; the rule counts it only because the call sits inside a `try` in the loop. |
| app/code/Mageprince/Faq/Controller/Adminhtml/Faq/InlineEdit.php:86 | TP-low | Admin grid inline edit loads each edited FAQ by id; the list is the rows the admin edited on one grid page, an admin-only and bounded action. |
| app/code/Mageprince/Faq/Controller/Adminhtml/FaqGroup/InlineEdit.php:86 | TP-low | Admin grid inline edit loads each edited FAQ group by id; admin-only and bounded by the rows edited on one grid page. |
| app/code/Mageprince/Faq/Observer/ConfigChange.php:105 | TP-low | The observer on `admin_system_config_changed_section_faqtab` loads one URL rewrite per store view on an admin config save, a short bounded list on a rare admin action. |
| app/code/Magiccart/Magicmenu/Block/Menu.php:898 | TP-low | `$this->_categoryInstance->load()` is in the loop of `getTreeCategories()` but only when the third argument `$count` is passed, and no caller in the corpus passes it (all calls, including the recursion, pass two arguments), so the load never runs as shipped; the menu block is also block-cached for 86400 s. |
| app/code/Magiccart/Magicmenu/Controller/Adminhtml/Extra/MassStatus.php:40 | TP-low | Admin mass status action loads each selected extra menu by id; admin-only, bounded by the grid selection of a small menu table. |
| app/code/Magiccart/Magicmenu/Controller/Adminhtml/Menu/MassStatus.php:40 | TP-low | Admin mass status action loads each selected menu by id; admin-only, bounded by the grid selection of a small menu table. |

### P-LOOP-02

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageprince/Faq/Observer/ConfigChange.php:101 | TP-low | A URL rewrite collection is created and loaded with `getFirstItem()` once per store view in an observer on the FAQ admin config section save: a short bounded list on a rare admin action. |

### P-LOOP-03

| Location | Verdict | Reason |
|---|---|---|
| app/code/Divante/PimcoreIntegration/Cron/ProductPublisher.php:138 | TP | The `enable_products` cron runs every 15 minutes and saves each matching disabled product through `ProductRepository::save()` (full product save with reindex and URL rewrites), an unbounded list where a mass attribute update would set the status in one call. |
| app/code/Divante/PimcoreIntegration/Cron/UnusedAttributeSetRemover.php:79 | TP-low | Cron (schedule from config) deletes attribute sets no product uses; the list is a handful of sets and each is deleted once, so the per-item delete hardly matters. |
| app/code/Divante/PimcoreIntegration/Listeners/NewAttributeListener.php:197 | FP | `attributeSetRepository->delete($attrSet)` sits in a `catch` block that ends with `throw $ex`, so it runs at most once before the exception leaves both loops of the observer. |
| app/code/Divante/PimcoreIntegration/Queue/Action/Product/MediaGalleryDataModifier.php:109 | TP-low | During Pimcore product import (queue processing) one asset-queue row is saved per new or changed image of one product, a short list bounded by that product's gallery (unsure: across a large import the saves add up, but the outer loop is elsewhere). |
| app/code/Mageprince/Faq/Controller/Adminhtml/Faq/InlineEdit.php:88 | TP-low | Admin grid inline edit saves each edited FAQ; admin-only and bounded by the rows edited on one grid page. |
| app/code/Mageprince/Faq/Controller/Adminhtml/FaqGroup/InlineEdit.php:88 | TP-low | Admin grid inline edit saves each edited FAQ group; admin-only and bounded by the rows edited on one grid page. |
| app/code/Mageprince/Faq/Observer/ConfigChange.php:108 | TP-low | Deletes the FAQ URL rewrite once per store view on an admin config save, a short bounded list on a rare action. |
| app/code/Mageprince/Faq/Observer/ConfigChange.php:115 | TP-low | Saves the FAQ URL rewrite once per store view on an admin config save, a short bounded list on a rare action. |
| app/code/Magiccart/Magicmenu/Controller/Adminhtml/Extra/MassDelete.php:38 | TP-low | Admin mass delete of selected extra menus; admin-only and bounded by the selection. |
| app/code/Magiccart/Magicmenu/Controller/Adminhtml/Extra/MassStatus.php:43 | TP-low | Admin mass status change saves each selected extra menu; admin-only and bounded by the selection. |
| app/code/Magiccart/Magicmenu/Controller/Adminhtml/Menu/MassDelete.php:38 | TP-low | Admin mass delete of selected menus; admin-only and bounded by the selection. |
| app/code/Magiccart/Magicmenu/Controller/Adminhtml/Menu/MassStatus.php:43 | TP-low | Admin mass status change saves each selected menu; admin-only and bounded by the selection. |

### P-LOOP-11

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mageprince/BuyNow/Controller/Cart/Add.php:94 | TP-low | The storefront Buy Now controller calls `_objectManager->get(Escaper::class)` per error message, but only in the `LocalizedException` catch path with a few messages, and `get()` returns the shared instance, so the cost is a lookup on a rare error path. |

### P-PLG-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Faonni/Price/Plugin/Directory/Model/Currency.php:66 | TP | `aroundConvert` only post-processes the result of `$proceed($price, $toCurrency)`, so `afterConvert` would do the same, and `Currency::convert` runs for many prices on every storefront listing and every quote total (the plugin is disabled in adminhtml only). |
| app/code/GhoSter/OutOfStockAtLast/Plugin/Model/ResourceModel/Fulltext/Collection/SearchResultApplierPlugin.php:36 | TP-low | `flag->apply()` before and `flag->stop()` after map cleanly onto a before and after pair, but `SearchResultApplier::apply()` runs about once per search or category collection load, so the saved closure is tiny. |
| app/code/MagePal/CustomerAccountLinksManager/Plugin/View/Element/Html/LinksPlugin.php:32 | TP | `aroundRenderLink` only filters the output of `$proceed($link)`, so an after plugin would do the same, and as a frontend plugin on every `Html\Links` block it runs for each header, footer and account link on every storefront page render (unsure: the per-call closure cost is small even times tens of links). |
| app/code/MagePal/GuestToCustomer/Plugin/Api/OrderCustomerManagementInterfacePlugin.php:36 | TP-low | `aroundCreate` only acts on the returned customer, so an after plugin would do, but converting a guest order to a customer is a rare one-off frontend action. |
| app/code/Magefan/RocketJavaScript/Model/Controller/ResultPlugin.php:68 | TP-low | `aroundRenderResult` only rewrites `$response` after `$proceed($response)`, which `afterRenderResult` (it receives `$response` too) could do, but it runs once per page cache miss, so the overhead is one closure per request. |

### T-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magefan/GoogleTagManager/view/frontend/templates/js_code.phtml:77 | TP-low | The template creates a `Magefan\Community\Block\JsScript` block in `head.additional` on every storefront page through `default.xml`, but only when cookie restriction mode is on and only on a page cache miss, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/edit.phtml:25 | TP-low | The template creates the core Name widget on the customer account edit page, which the module's layout marks `cacheable="false"` like core, and core `form/edit.phtml` uses the same pattern, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/edit.phtml:27 | TP-low | The template creates the core Dob widget on the uncacheable customer account edit page, the same pattern core `form/edit.phtml` uses, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/edit.phtml:28 | TP-low | The template creates the core Taxvat widget on the uncacheable customer account edit page, the same pattern core `form/edit.phtml` uses, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/edit.phtml:29 | TP-low | The template creates the core Gender widget on the uncacheable customer account edit page, the same pattern core `form/edit.phtml` uses, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/register.phtml:18 | TP-low | The template creates the core Name widget on the `customer_account_create` page, built once per page cache miss, and core `form/register.phtml` uses the same pattern, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/register.phtml:28 | TP-low | The template creates the core Dob widget on the registration page once per page cache miss, the same pattern core `form/register.phtml` uses, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/register.phtml:33 | TP-low | The template creates the core Taxvat widget on the registration page once per page cache miss, the same pattern core `form/register.phtml` uses, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/register.phtml:38 | TP-low | The template creates the core Gender widget on the registration page once per page cache miss, the same pattern core `form/register.phtml` uses, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/register.phtml:48 | TP-low | The template creates the core Company widget on the registration page, only when address fields are shown and once per page cache miss, the same pattern core uses, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/register.phtml:53 | TP-low | The template creates the core Telephone widget on the registration page, only when address fields are shown and once per page cache miss, the same pattern core uses, so the effect is design-only. |
| app/code/Magestat/SigninPhoneNumber/view/frontend/templates/form/register.phtml:58 | TP-low | The template creates the core Fax widget on the registration page, only when address fields are shown and once per page cache miss, the same pattern core uses, so the effect is design-only. |

### X-EVT-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Bodak/CheckoutCustomForm/etc/events.xml:7 | TP-low | The observer does run after every order model load in all areas, but orders load only on checkout success, customer account, admin and API paths, not catalog pages, and the observer only copies five fields already on the order into extension attributes without a query. |
