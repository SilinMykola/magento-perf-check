# layout-rules Specification

## Purpose
Rules on layout XML that catch configurations which disable or fragment Full Page Cache for whole groups of pages.

## Requirements

### Requirement: X-LAY-01 default allowlist
X-LAY-01 and X-LAY-03 SHALL NOT report blocks in handles whose name, as written or without a `hyva_` prefix, starts with a prefix on the allowlist, or in a layout whose root has an `<update handle="customer_account"/>` child, the only place core merges it from. These are personal pages that are not meant to be served from FPC.

#### Scenario: Checkout handle
- **WHEN** `checkout_cart_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Customer address book
- **WHEN** `customer_address_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Product compare page
- **WHEN** `catalog_product_compare_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing, although the handle starts with `catalog_`

#### Scenario: Prefix added in configuration
- **WHEN** the configuration adds `acme_account_` to the allowlist and `acme_account_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Customer account layout
- **WHEN** `acme_saved_cards.xml` contains `<update handle="customer_account"/>` and a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Hyva checkout
- **WHEN** `hyva_checkout_index_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Account update inside body
- **WHEN** `acme_nested_account.xml` has `<update handle="customer_account"/>` only inside `<body>`, and a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block, because core merges only the updates that are direct children of the root

### Requirement: X-LAY-01 scope
X-LAY-01 SHALL NOT report:
- Layout files of area `adminhtml`.
- Blocks with `cacheable="true"` or without the attribute.
- Elements other than `<block>`.

#### Scenario: Admin layout
- **WHEN** `view/adminhtml/layout/default.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Cacheable block
- **WHEN** a frontend `default.xml` contains `<block cacheable="true" .../>` or a block without `cacheable`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Text mentioning cacheable
- **WHEN** a frontend layout contains `cacheable="false"` only inside an XML comment or an `<argument>` value
- **THEN** X-LAY-01 reports nothing

### Requirement: X-LAY-02 ESI ttl on entity-specific handle
X-LAY-02 SHALL report, as `error`, a `<block>` with a `ttl` attribute in a frontend or base layout whose handle contains `_id_` or `_sku_`. Core throws a `LogicException` for such blocks when Full Page Cache uses ESI. Blocks with `ttl` in other handles SHALL NOT be reported.

#### Scenario: Entity handle
- **WHEN** `catalog_product_view_id_42.xml` contains `<block name="promo" ttl="3600" .../>`
- **THEN** X-LAY-02 reports an error

#### Scenario: SKU handle
- **WHEN** `catalog_product_view_sku_ABC.xml` contains a block with `ttl`
- **THEN** X-LAY-02 reports an error

#### Scenario: Shared handle
- **WHEN** `default.xml` contains a block with `ttl="3600"`
- **THEN** X-LAY-02 reports nothing

### Requirement: X-LAY-01 default allowlist contents
The default allowlist SHALL be these handle prefixes:
- Customer: `customer_`, `loginascustomer_`, `newsletter_manage_`, `review_customer_`, `downloadable_customer_`.
- Checkout and sales: `checkout_`, `hyva_checkout_`, `onestepcheckout_`, `multishipping_`, `sales_`, `shipping_tracking_`.
- Payment: `paypal_`, `vault_`, `paymentservicespaypal_vault_`, `paymentservicespaypal_smartbuttons_`, `braintree_paypal_review`, `braintree_googlepay_review`, `braintree_lpm_fallback`.
- Lists: `wishlist_`, `catalog_product_compare_`.
- Forms bound to one customer or email link: `productalert_unsubscribe_`, `sendfriend_`.
- Personal pages of known modules: `connector_` (Dotdigital), `paynl_order_`, `paynl_checkout_` (Pay.nl), `payone_onepage_`, `payone_amazon_` (Payone), `buckaroo_mrcash_pay` (Buckaroo), `mpblog_post_history`, `mpblog_post_preview`, `mpblog_author_information` (Mageplaza Blog), `clerk_clerk_powerstep_` (Clerk), `sharecart_index_download` (Mageplaza Share Cart), `mailchimp_cart_loadquote` (Mailchimp), `blog_non_cacheable` (Magefan Blog), `hyvareactcheckout_reactcheckout_` (Hyvä React Checkout).

#### Scenario: Vault handle
- **WHEN** `vault_cards_listaction.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Module email endpoint
- **WHEN** `connector_email_basket.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Same module, other handle
- **WHEN** `payone_promo_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports it, because no prefix of the module covers that handle

### Requirement: X-LAY-01 configurable allowlist
The configuration file SHALL be able to add prefixes to the allowlist, or replace the default list with its own.

#### Scenario: Default list replaced
- **WHEN** the configuration sets `layout.allowlistHandles: [acme_]` and `layout.replaceAllowlist: true`, and `checkout_cart_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports it

### Requirement: X-LAY-01 non-cacheable block on other handles
X-LAY-01 SHALL report, as `warning`, every `<block>` element with `cacheable="false"` in a layout file of area `frontend` or `base`, when the handle is not one of those X-LAY-03 covers. The message SHALL name the handle and the block name.

#### Scenario: Block in another handle
- **WHEN** `contact_index_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports a `warning`

### Requirement: X-LAY-03 non-cacheable block on shop-wide handles
X-LAY-03 SHALL report, as `error`, every `<block>` element with `cacheable="false"` in a layout file of area `frontend` or `base`, when the handle, without a `hyva_` prefix, is `default` or starts with `catalog_` or `cms_`. The message SHALL name the handle and the block name.

#### Scenario: Block in default handle
- **WHEN** `view/frontend/layout/default.xml` contains `<block name="promo.banner" cacheable="false" .../>`
- **THEN** X-LAY-03 reports an `error` naming handle `default` and block `promo.banner`

#### Scenario: Block in catalog handle
- **WHEN** `catalog_product_view.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-03 reports an `error`

#### Scenario: Theme override
- **WHEN** `app/design/frontend/Acme/shop/Magento_Cms/layout/cms_index_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-03 reports an `error`

#### Scenario: Product compare page
- **WHEN** `catalog_product_compare_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-03 reports nothing, because the prefix is on the allowlist

### Requirement: Hyvä layout handles
X-LAY-03 and X-LAY-01 SHALL treat a handle that starts with `hyva_` as the handle that follows the prefix when they choose between X-LAY-03 and X-LAY-01 and when they match the allowlist. The allowlist SHALL also match the handle as written. The message SHALL name the handle as written.

#### Scenario: Hyvä default handle
- **WHEN** `hyva_default.xml` contains `<block name="promo.hyva.banner" cacheable="false" .../>`
- **THEN** X-LAY-03 reports an `error` naming handle `hyva_default`

#### Scenario: Hyvä customer page
- **WHEN** `hyva_customer_address_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing, because `customer_` is on the allowlist

#### Scenario: Other Hyvä handle
- **WHEN** `hyva_contact_index_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports a `warning`

### Requirement: X-LAY-04 synchronous script in the layout head
X-LAY-04 SHALL report, as `info`, every `<script>` or `<link>` element with a `src` attribute that core renders as a script, inside `<head>` of a layout file of area `frontend` or `base`, when the element has neither an `async` nor a `defer` attribute. The message SHALL name the handle and the script, and say that `dev/js/defer_non_critical` makes core defer it. It SHALL start as experimental.

#### Scenario: Synchronous external script
- **WHEN** `catalog_product_view.xml` has `<head><script src="https://example.com/sdk.js" src_type="url"/></head>`
- **THEN** X-LAY-04 reports an `info` naming handle `catalog_product_view`

#### Scenario: Script added with link
- **WHEN** a frontend layout file has `<head><link src="Acme_Shop::js/widget.js"/></head>`
- **THEN** X-LAY-04 reports an `info`

#### Scenario: Base area
- **WHEN** `view/base/layout/default.xml` has `<head><script src="Acme_Shop::js/everywhere.js"/></head>`
- **THEN** X-LAY-04 reports an `info`

#### Scenario: Deferred script
- **WHEN** a frontend layout file has `<head><script defer="defer" src="Acme_Shop::js/widget.js"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Stylesheet added with link
- **WHEN** a frontend layout file has `<head><link src="Acme_Shop::css/widget.css"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Admin layout
- **WHEN** a layout file under `view/adminhtml` has `<head><script src="Acme_Shop::js/admin.js"/></head>`
- **THEN** X-LAY-04 reports nothing

### Requirement: X-LAY-04 what counts as a script
X-LAY-04 SHALL count an element as a script as core decides: with `src_type` `url` or `controller`, a `<script>`, or a `<link>` with `content_type="js"`; otherwise, an element whose `src`, as written, has the extension `js`.

#### Scenario: Remote link without a content type
- **WHEN** a frontend layout file has `<head><link src="https://example.com/widget.js" src_type="url"/></head>`
- **THEN** X-LAY-04 reports nothing, because core renders it as `<link href>`

#### Scenario: Local link with a query string
- **WHEN** a frontend layout file has `<head><link src="Acme_Shop::js/widget.js?v=2"/></head>`
- **THEN** X-LAY-04 reports nothing, because core takes `js?v=2` as the content type

### Requirement: X-LAY-04 scripts left out
X-LAY-04 SHALL NOT report a script that core never defers: the RequireJS loader, `requirejs-config.js`, `requirejs/mixins.js`, `requirejs/baseUrlResolver.js`, `requirejs-map.js`, `requirejs-min-resolver.js`, `static.js`, files in `js/bundle/`, `_cache/` or `merged/`, and `sri.js`. It SHALL NOT report an element with a non-empty `ie_condition` attribute, which core wraps in an Internet Explorer conditional comment.

#### Scenario: Script core never defers
- **WHEN** a frontend layout file has `<head><script src="requirejs/require.js"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Internet Explorer only
- **WHEN** a frontend layout file has `<head><script src="Acme_Shop::js/polyfill.js" ie_condition="lt IE 11"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Empty Internet Explorer condition
- **WHEN** a frontend layout file has `<head><script src="Acme_Shop::js/empty-ie.js" ie_condition=""/></head>`
- **THEN** X-LAY-04 reports an `info`, because core does not wrap it

### Requirement: X-LAY-05 module stylesheet on every page
X-LAY-05 SHALL report, as `warning`, every `<css>` or `<link>` element with a `src` attribute that core renders as a stylesheet, inside `<head>` of a module layout file of area `frontend` or `base` whose handle is `default`, `default_head_blocks`, `hyva_default` or `hyva_default_head_blocks`. The message SHALL name the stylesheet and the handle, and say that `dev/css/use_css_critical_path` makes core load it without blocking. It SHALL be in the default set.

#### Scenario: Module stylesheet in default.xml
- **WHEN** a module's `view/frontend/layout/default.xml` has `<head><css src="Acme_Rules::css/slider.css"/></head>`
- **THEN** X-LAY-05 reports a `warning` naming handle `default`

#### Scenario: Head blocks, Hyvä copy and base area
- **WHEN** a module's `default_head_blocks.xml`, `hyva_default.xml`, `hyva_default_head_blocks.xml` or `view/base/layout/default.xml` has a `<css src>` in `<head>`
- **THEN** X-LAY-05 reports a `warning`

#### Scenario: Other handle
- **WHEN** a module's `catalog_product_view.xml` has `<head><css src="Acme_Rules::css/styles.css"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Theme layout
- **WHEN** a theme's `Magento_Theme/layout/default_head_blocks.xml` has `<head><css src="css/theme.css"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Admin layout
- **WHEN** a module's `view/adminhtml/layout/default.xml` has `<head><css src="Acme_Rules::css/admin.css"/></head>`
- **THEN** X-LAY-05 reports nothing

### Requirement: X-LAY-05 what counts as a stylesheet
X-LAY-05 SHALL count an element as a stylesheet as core decides. Its content type is, with `src_type` `url` or `controller`, `css` for a `<css>` and otherwise the written `content_type`, or `link` without one; for a local element, the extension of its `src` as written. An element is a stylesheet when its content type is `css`, or when its content type is neither `js` nor a font type (`eot`, `svg`, `ttf`, `woff`, `woff2`) and it has `rel="stylesheet"` in any case.

#### Scenario: Local link
- **WHEN** a module's `default.xml` has `<head><link src="Acme_Rules::css/linked.css"/></head>`
- **THEN** X-LAY-05 reports a `warning`

#### Scenario: Remote stylesheet
- **WHEN** a module's `default.xml` has `<head><css src="https://fonts.example.com/css?family=Sans" src_type="url"/></head>` or `<head><link rel="stylesheet" src="https://cdn.example.com/library.css" src_type="url"/></head>`
- **THEN** X-LAY-05 reports a `warning`

#### Scenario: Remote stylesheet by content type, controller or mixed case
- **WHEN** a module's `default.xml` has `<head><link src="https://cdn.example.com/typed.css" src_type="url" content_type="css"/></head>`, `<head><css src="acme/styles/dynamic" src_type="controller"/></head>` or `<head><link rel="Stylesheet" src="https://cdn.example.com/upper-rel.css" src_type="url"/></head>`
- **THEN** X-LAY-05 reports a `warning`

#### Scenario: Print in mixed case
- **WHEN** a module's `default.xml` has `<head><css src="Acme_Rules::css/print-upper.css" media=" Print"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Remote link without rel
- **WHEN** a module's `default.xml` has `<head><link src="https://cdn.example.com/no-rel.css" src_type="url"/></head>`
- **THEN** X-LAY-05 reports nothing, because core renders it as `<link href>` without `rel`

#### Scenario: Script or font with rel stylesheet
- **WHEN** a module's `default.xml` has `<head><link rel="stylesheet" src="Acme_Rules::fonts/icons.woff2"/></head>` or `<head><link rel="stylesheet" defer="defer" src="Acme_Rules::js/rel.js"/></head>`
- **THEN** X-LAY-05 reports nothing

### Requirement: X-LAY-05 stylesheets left out
X-LAY-05 SHALL NOT report an element whose `media` is `print` in any case, or that has a non-empty `ie_condition` attribute.

#### Scenario: Print stylesheet
- **WHEN** a module's `default.xml` has `<head><css src="Acme_Rules::css/print.css" media="print"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Internet Explorer only
- **WHEN** a module's `default.xml` has `<head><css src="Acme_Rules::css/old-ie.css" ie_condition="lt IE 9"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Empty Internet Explorer condition
- **WHEN** a module's `default.xml` has `<head><css src="Acme_Rules::css/empty-ie.css" ie_condition=""/></head>` or `ie_condition="0"`
- **THEN** X-LAY-05 reports a `warning`, because core does not wrap it

### Requirement: Storefront layout handles of the project
X-LAY-01 and X-LAY-03 SHALL know, for each storefront layout handle, which handles include it, and whether one of its layout files includes `customer_account`. An include SHALL count only as an `<update handle="..."/>` element that is a direct child of a layout file's root.

#### Scenario: Include in a nested element
- **WHEN** only an `<update handle="acme_nested"/>` inside `<body>` of `sales_order_view.xml` names `acme_nested`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block, because core merges only the updates that are direct children of the root

### Requirement: Layout files read for the handles
The handles SHALL come from the `frontend` and `base` layouts and the storefront routes of every module that `app/etc/config.php` does not disable, under `app/code` and installed by Composer, and from the layouts of every frontend theme, under `app/design/frontend` and installed by Composer, whatever `--paths` says. A theme's folder of a module that is not enabled SHALL be left out, as Magento leaves it out.

#### Scenario: Include from a theme installed by Composer
- **WHEN** only `sales_order_view.xml` and a Composer theme's `Acme_Core/layout/cms_index_index.xml` include `acme_vendor_theme_only`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Include from a disabled module
- **WHEN** `acme_off_only`, which has a block with `cacheable="false"`, is included by `sales_order_view.xml` and by a layout of a module that `app/etc/config.php` disables
- **THEN** X-LAY-01 reports nothing in `acme_off_only.xml`

#### Scenario: Account mark in a disabled module's theme folder
- **WHEN** only a theme's folder of a disabled module has an `acme_theme_account.xml` that includes `customer_account`, and a module's `acme_theme_account.xml` has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

### Requirement: Account pages across layout files
X-LAY-01 and X-LAY-03 SHALL NOT report blocks in a handle, as written or without a `hyva_` prefix, that one of its layout files anywhere in the project makes a customer account page.

#### Scenario: Account page in another file
- **WHEN** a theme's `acme_credit_index.xml` has a block with `cacheable="false"`, and a module's `acme_credit_index.xml` includes `customer_account`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Shop-wide account page in another file
- **WHEN** a theme's `cms_acme_account.xml` has a block with `cacheable="false"`, and a module's `cms_acme_account.xml` includes `customer_account`
- **THEN** X-LAY-03 reports nothing

### Requirement: Handles that only personal pages include
X-LAY-01 SHALL NOT report blocks in a handle that other handles include when every one of them is personal: allowlisted, an account page, or itself included only by personal handles. A handle that includes itself, or that a cycle leads back to, SHALL NOT count as one that includes it. A page is the handle of a storefront controller action of the route's modules: route id, controller path and action in lower case, as `Router\ActionList` finds the class (an action class named after a PHP reserved word, such as `PrintAction`, serves that word; a controller path with `adminhtml` in it, an abstract class, an interface and a trait serve nothing). A handle of a route whose modules have no controllers counts as a page. A handle of a route whose id starts with `hyva_` SHALL NOT be read as a `hyva_` copy. A page SHALL be personal when it is allowlisted or an account page; its `hyva_` copy only when, besides, every handle that includes the copy is personal. A handle that extends a page's name with `_` and more is added by that page's controller: it SHALL be personal when it or its page is allowlisted or an account page and every handle that includes it is personal. Any other handle SHALL follow the handles that include it, whatever the allowlist says of its name; one that no layout includes SHALL be decided by the allowlist, as code adds it. A `hyva_` handle SHALL be personal only when the handle without the prefix is personal and every handle that includes the `hyva_` handle itself is personal. X-LAY-03 SHALL NOT follow includes, because a controller may add a shop-wide handle itself.

#### Scenario: Handle included only by order pages
- **WHEN** `acme_fee_total.xml` has a block with `cacheable="false"`, and only `sales_order_view.xml` and `sales_order_print.xml` include `acme_fee_total`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Handle included by a public page
- **WHEN** `acme_widget.xml` has a block with `cacheable="false"`, and `sales_order_view.xml` and `contact_index_index.xml` include `acme_widget`
- **THEN** X-LAY-01 reports the block

#### Scenario: Handle included through a chain
- **WHEN** only `acme_totals.xml` includes `acme_fee_total`, and only `sales_order_view.xml` includes `acme_totals`
- **THEN** X-LAY-01 reports nothing in `acme_fee_total.xml`

#### Scenario: Handles that only include each other
- **WHEN** `acme_cycle_a` and `acme_cycle_b` include each other, both have a block with `cacheable="false"`, and nothing else includes them
- **THEN** X-LAY-01 reports both blocks

#### Scenario: Cycle with an order page
- **WHEN** `acme_cyc_x` and `acme_cyc_y` include each other, both have a block with `cacheable="false"`, and only `sales_order_view.xml` includes `acme_cyc_x` besides them
- **THEN** X-LAY-01 reports neither block

#### Scenario: Route handle
- **WHEN** a module has the storefront route `acmeroute`, and only `sales_order_view.xml` includes `acmeroute_index_index`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Allowlisted handle that is no page
- **WHEN** the route `acmeshop` has the controller `Controller/Cart/Index.php`, `acmeshop_` is allowlisted, and `default.xml` includes `acmeshop_cart_sidebar`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block
- **WHEN** the block is in `acmeshop_cart_index.xml`, in `acmeshop_order_print.xml` with `Controller/Order/PrintAction.php`, in `acmeshop_cart_index_type_bundle.xml`, or in a handle no layout includes
- **THEN** X-LAY-01 reports nothing

#### Scenario: Hyvä copy included by a public page
- **WHEN** only `sales_order_view.xml` includes `acme_hyva_pub`, and `contact_index_index.xml` includes `hyva_acme_hyva_pub`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Addition to a personal page that a public page includes
- **WHEN** `acme_cart_index` is an allowlisted page, and the public page `acme_order_print.xml` includes `acme_cart_index_type_virtual`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Shop-wide handle included by a personal page
- **WHEN** only `checkout_cart_configure_type_simple.xml` includes `catalog_product_view_type_simple`, which has a block with `cacheable="false"`
- **THEN** X-LAY-03 reports the block

#### Scenario: Handle that nothing includes
- **WHEN** `acme_page_index.xml` has a block with `cacheable="false"` and no layout file includes `acme_page_index`
- **THEN** X-LAY-01 reports the block
