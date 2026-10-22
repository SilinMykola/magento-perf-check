> Appendix to [m1-core.md](../m1-core.md). Findings were sampled with seed 20261022 from the run before the spot-check fixes, then graded by reading the code in Magento 2.4.9. TP: the rule's claim holds. TP-low: it holds, but the impact is negligible. FP: the claim does not hold.

# Graded sample B (Magento 2.4.9 core)

## P-DI-03

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-customer/Model/Session.php:195 | TP-low | `customer_session_init` really is dispatched at the end of `__construct` (violates Adobe 2.3.2), but no core observer listens to it and the session is a shared singleton, so it fires once per request. |

Summary: 0 TP, 1 TP-low, 0 FP, 0 unclear
Pattern: none.

## P-DI-06

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-rule/Model/AbstractModel.php:494 | FP | `getExtensionFactory()` is only called as `$extensionFactory ?: $this->getExtensionFactory()` in the constructor: the allowed BC constructor fallback, moved into a private helper. |
| 2 | module-eav/Model/AttributeRepository.php:217 | TP-low | Deprecated lazy getter `if (!$this->collectionProcessor) ... OM->get()`; shared instance fetched once, design-only cost. |
| 3 | module-downloadable/Model/Product/TypeHandler/AbstractTypeHandler.php:205 | TP-low | Memoized lazy getter for MetadataPool (shared); one-time lookup. |
| 4 | module-swatches/Helper/Data.php:592 | TP-low | Deprecated memoized getter for MetadataPool; one-time lookup. |
| 5 | framework/File/Uploader.php:421 | TP-low | Memoized getter for DirectoryList; one-time lookup. |
| 6 | module-payment-services-paypal/Model/ConfigProvider.php:63 | FP | Deliberate soft dependency: `get("\Magento\Csp\Helper\CspNonceProvider")` in try/catch because the class does not exist on 2.4.6; it cannot be a typed constructor argument. |
| 7 | framework/EntityManager/Operation/Create.php:150 | TP-low | Deprecated memoized getter for SequenceApplier; one-time lookup. |
| 8 | module-cms/Model/BlockRepository.php:222 | TP-low | Deprecated memoized getter for the collection processor; one-time lookup. |
| 9 | framework/Backup/Filesystem/Rollback/Fs.php:105 | TP-low | Deprecated memoized getter; backup/rollback only. |
| 10 | module-sales/Model/Order/AddressRepository.php:249 | TP-low | Deprecated memoized getter for the collection processor; one-time lookup. |

Summary: 0 TP, 8 TP-low, 2 FP, 0 unclear
Pattern: the BC fallback through a helper (`$arg ?: $this->getX()` in `__construct`) should count as the allowed constructor fallback. The memoized lazy getter (`if (!$this->x) { $this->x = OM->get(...) }`, mostly `@deprecated`) is the other BC form core uses. It is technically a match but always low impact; consider a lower severity or a separate identifier for it.

## P-FPC-01

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-paypal/Block/Express/Review/Shipping.php:78 | TP-low | Sets `_isScopePrivate = true`, but the block is not referenced in any core layout (legacy PayPal Express review, an uncacheable page), so the flag has no effect. |
| 2 | framework/View/Element/AbstractBlock.php:166 | FP | Base class only declares the deprecated property with the default `false`. |
| 3 | module-sales/Block/Order/Recent.php:67 | TP-low | Only used in `customer_account_index` (cacheable="false"); the flag is a no-op there. |
| 4 | module-captcha/Block/Captcha.php:39 | TP-low | Every core layout adds this block with `cacheable="false"`, so the page is never FPC-cached and the flag does nothing. |
| 5 | module-gift-message/Block/Cart/GiftOptions.php:25 | FP | Redeclares `$_isScopePrivate = false` (the default); does not mark anything private. |
| 6 | module-bundle/Block/Checkout/Cart/Item/Renderer.php:67 | TP-low | Cart item renderer; `checkout_cart_index` is uncacheable, so the flag is a no-op. |
| 7 | module-customer/Block/Address/Renderer/DefaultRenderer.php:69 | TP-low | Address renderer used in account, checkout, emails and admin, none of them FPC-cached; no-op. |
| 8 | module-multishipping/Block/Checkout/Billing/Items.php:43 | TP-low | Multishipping layouts are all cacheable="false"; no-op. |
| 9 | module-reports/Block/Product/AbstractProduct.php:57 | TP | Base of the legacy Reports Viewed/Compared blocks (per-visitor data). Placed on a cacheable page (widget or custom layout), they hit exactly the broken private-block path. |
| 10 | module-gift-message/Block/Cart/Item/Renderer/Actions/GiftOptions.php:52 | TP-low | Cart item action; cart page uncacheable; no-op. |

Summary: 1 TP, 7 TP-low, 2 FP, 0 unclear
Pattern: report only an assignment of `true` (or a declaration with `true`); skip a declaration with the default `false` and the AbstractBlock declaration itself. Most of the remaining hits are dead flags on uncacheable pages: still worth cleaning up, but low impact.

## P-FPC-02

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-paypal/Block/Express/InContext/Minicart/SmartButton.php:216 | FP | Shortcut block; `shouldRender()` excludes the product page, so it renders only on the uncacheable cart or inside the minicart `extra_actions`, which `Checkout\CustomerData\Cart` builds in the private customer-data section. |
| 2 | module-sales/Block/Adminhtml/Order/Create/Giftmessage/Form.php:127 | FP | Adminhtml block reading the backend quote session; admin is never page-cached. |
| 3 | module-paypal/Block/Express/Review/Billing.php:271 | FP | Not in any core layout; legacy PayPal Express review page (checkout flow, uncacheable). |
| 4 | module-sales/Block/Adminhtml/Order/Create.php:50 | FP | Adminhtml block, `Backend\Model\Session\Quote`; admin is never page-cached. |
| 5 | module-payment-services-paypal/Block/SmartButtonsCart.php:68 | FP | Created only for the cart page (`AddCheckoutComponents`, pageType cart), which is uncacheable. |
| 6 | module-catalog/Model/ResourceModel/Product/Collection.php:2020 | FP | Not a block or view model: it matched because `Data\Collection` implements `CollectionDataSourceInterface extends ArgumentInterface`. Also, `getCustomerGroupId()` is restored by DepersonalizePlugin and is part of the page-cache vary context. |
| 7 | module-payment-services-paypal/Block/SmartButtons.php:165 | TP-low | The minicart instance renders inside the private customer-data section. Through the `SmartButtonsProduct` subclass, `parent::getComponentParams()` loads the session quote on the cacheable product page, but `isVirtual` is then overwritten from the product: a wasted quote load on a cache miss, no leak. |
| 8 | module-paypal/Block/Express/InContext/Minicart/Button.php:110 | FP | `shouldRender()` requires `isMiniCart`, so it is rendered only in the minicart customer-data section (private). |
| 9 | module-catalog/Block/Product/View.php:347 | TP-low | Cacheable product page, but the protected `getCustomerId()` has no caller in core; a risk only for subclasses or templates that call it. |
| 10 | module-checkout/Block/Onepage/Link.php:58 | FP | Only in `checkout_cart_index` (uncacheable). |

Summary: 0 TP, 2 TP-low, 8 FP, 0 unclear
Pattern: (a) exclude collections: `ArgumentInterface` is inherited through `CollectionDataSourceInterface`, so require a direct or non-collection ArgumentInterface. (b) Exclude `getCustomerGroupId()`: it survives depersonalization by design. (c) Exclude Adminhtml blocks and the backend session (`Magento\Backend\Model\Session\Quote`). (d) Shortcut and minicart blocks rendered through `CustomerData\Cart::extra_actions` and cart/checkout-only blocks are the remaining noise, which needs layout awareness.

## P-FPC-05

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-catalog-search/Controller/Result/Index.php:192 | FP | Deliberate: only the `getNotCacheableResult()` branch (non-popular terms, extra params) sets no-cache so the term's popularity is counted; popular terms take the cacheable branch. |
| 2 | module-customer/Controller/Section/Load.php:69 | FP | `customer/section/load` is the private customer-data endpoint; it must never be cached. |
| 3 | module-downloadable/Controller/Download.php:42 | FP | The header is `Pragma: public`, not no-cache. The name-only match ignores the value; this is also a personal purchased-file download. |
| 4 | module-page-cache/Controller/Block/Render.php:54 | FP | `page_cache/block/render` serves private or ESI blocks; private headers are the intended design. |
| 5 | module-customer/Controller/Section/Load.php:70 | FP | Same statement group as #2 (`Pragma: no-cache`); private by design, duplicate report. |
| 6 | module-downloadable/Controller/Download.php:42 | FP | Duplicate of #3 on the same chained statement (`Cache-Control: must-revalidate, post-check=0...`, no no-cache/private); personal download. |
| 7 | module-cms/Controller/Noroute/Index.php:54 | TP-low | The 404 page is intentionally `no-store`, so bot and stale-URL 404 traffic always reaches PHP (Varnish could cache 404s). Core's choice, but a real cost worth deciding consciously. |

Summary: 0 TP, 1 TP-low, 6 FP, 0 unclear
Pattern: check the header value, not only the name (`Pragma: public` and `must-revalidate` are not no-cache/private). Allowlist core private endpoints (`customer/section/load`, `page_cache/block/render`, downloadable links). Report one finding per call chain or statement, not one per header.

## P-FPC-06

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-product-alert/Controller/Add/Stock.php:98 | FP | Always returns a redirect (Varnish caches only 200/404; the built-in FPC caches only public layout pages) and needs a logged-in customer; never served from cache. |
| 2 | module-payment-services-paypal/Controller/SmartButtons/Cancel.php:79 | FP | PayPal cancel return URL (GET by necessity, the browser is redirected by PayPal); always responds with a redirect to the cart, so it is never cached. |
| 3 | module-product-alert/Controller/Add/Price.php:100 | FP | Same as #1: redirect-only, customer-only. |
| 4 | module-integration/Controller/Adminhtml/Integration/TokensDialog.php:45 | FP | Adminhtml controller; admin responses are never page-cached. |
| 5 | module-integration/Controller/Adminhtml/Integration/TokensExchange.php:55 | FP | Adminhtml controller; never page-cached. |
| 6 | module-product-alert/Controller/Unsubscribe/Price.php:78 | FP | Email unsubscribe link (GET by nature, documented known FP); redirect-only response. |
| 7 | module-sales/Controller/Adminhtml/Order/ReviewPayment.php:63 | FP | Adminhtml controller; never page-cached. |

Summary: 0 TP, 0 TP-low, 7 FP, 0 unclear
Pattern: (a) skip `Controller\Adminhtml` (admin is never FPC-cached; the GET-mutation is a CSRF/REST concern, not a cache one). (b) Skip or downgrade controllers whose `execute()` only returns `TYPE_REDIRECT` results: 3xx responses are never cached by Varnish (`beresp.status != 200 && != 404` gives uncacheable) or by the built-in FPC. The cache argument does not apply to any of the 7.

## P-FPC-09

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-translation/Console/Command/UninstallLanguageCommand.php:144 | FP | CLI deploy command removing a language package; a full flush is the correct action. |
| 2 | module-encryption-key/Console/Command/UpdateEncryptionKeyCommand.php:96 | FP | CLI key rotation; all cached encrypted config becomes stale; a full flush is required. |
| 3 | module-store/Model/Config/Importer.php:132 | FP | `app:config:import` of the store structure at deploy time; changes every scope; a full flush is justified. |
| 4 | module-config/Model/Config/Backend/Store.php:52 | FP | Backend model of `web/url/use_store` only (runs on an actual value change); toggling store codes in URLs changes every URL, so a full flush is correct. |
| 5 | module-theme/Console/Command/ThemeUninstallCommand.php:373 | FP | CLI theme uninstall; a full flush is expected. |
| 6 | magento2-base/setup/src/Magento/Setup/Console/Command/AbstractModuleCommand.php:80 | FP | `module:enable`/`module:disable` cleanup in setup; a full flush is required. |

Summary: 0 TP, 0 TP-low, 6 FP, 0 unclear
Pattern: exclude `Console\Command` classes and `setup/` code (deployment-time operations where a full flush is the intent). Global config or structure changes (config importers, store-structure backend models) are also legitimate. The rule's real target, observers, save handlers and imports on routine entity changes, did not appear in this sample.

## P-PLG-01

| # | Location | Verdict | Reason |
|---|---|---|---|
| 1 | module-re-captcha-frontend-ui/Plugin/ExcludeFromMinification.php:27 | TP | Only appends to the result using `$contentType`; `afterGetExcludes($subject, $result, $contentType)` is equivalent. `Minification::isExcluded()` calls `getExcludes` per asset through the interceptor. |
| 2 | module-inventory-in-store-pickup-sales-admin-ui/Plugin/Sales/Model/AdminOrder/Create/AdaptSetShippingAddressPlugin.php:26 | FP | `if (pickup) { ...; return $subject; }` before `return $proceed($address)`: the original is conditionally skipped, so around is required. |
| 3 | module-security/Model/Plugin/AdminUserForm.php:59 | TP-low | Modifies the form, then `return $proceed()`; a before plugin would do. Admin user edit form, called once. |
| 4 | module-security/Model/Plugin/AuthSession.php:72 | FP | Two early `return null` branches destroy the session without calling `$proceed`; around is required. |
| 5 | module-inventory-catalog/Plugin/CatalogInventory/Model/Source/StockPlugin.php:74 | FP | Early `return $subject` for non-default stock skips the original; around is required. |
| 6 | module-swatches/Model/Plugin/FilterRenderer.php:50 | FP | Swatch attributes return a different block's HTML without calling `$proceed`; around is required. |
| 7 | module-configurable-product/Model/Order/Admin/Item/Plugin/Configurable.php:79 | FP | Configurable items return `getIdBySku()` without `$proceed`; around is required. |
| 8 | module-configurable-product/Model/Entity/Product/Attribute/Group/AttributeMapper/Plugin.php:48 | TP-low | Unconditional `$proceed($attribute)`, then modifies the result; `afterMap($subject, $result, $attribute)` would do (the registry read can move after). Admin attribute-set page only. |
| 9 | module-persistent/Plugin/AddPersistentRememberMeInitPlugin.php:39 | TP-low | `$proceed()`, then after-logic only; an after plugin would do. `generateElements` runs once per request, so the saved overhead is one closure. |
| 10 | module-theme/Model/Indexer/Design/Config/Plugin/Website.php:36 | FP | Captures `$isObjectNew` before the call and uses it after: needs pre-call state and the result together (documented known FP). Admin website save, rare. |

Summary: 1 TP, 3 TP-low, 6 FP, 0 unclear
Pattern: the main FP is an early `return` before `$proceed` (inside `if`/`elseif`). The check "the `$proceed` statement is directly in the method body" does not catch that a preceding `if` branch returns without calling the original. Treat any `return` that is not the `$proceed` statement and comes before it as making the call conditional. Second, smaller pattern: state captured before `$proceed` and used after it.
