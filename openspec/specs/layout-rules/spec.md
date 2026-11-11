# layout-rules Specification

## Purpose
Rules on layout XML that catch configurations which disable or fragment Full Page Cache for whole groups of pages.

## Requirements

### Requirement: X-LAY-01 default allowlist
X-LAY-01 and X-LAY-03 SHALL NOT report blocks in handles whose name, as written or without a `hyva_` prefix, starts with a prefix on the allowlist, or in a layout that contains `<update handle="customer_account"/>`. These are personal pages that are not meant to be served from FPC.

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
- Payment: `paypal_`, `vault_`, `paymentservicespaypal_vault_`.
- Lists: `wishlist_`, `catalog_product_compare_`.
- Forms bound to one customer or email link: `productalert_unsubscribe_`, `sendfriend_`.
- Personal pages of known modules: `connector_` (Dotdigital), `paynl_order_`, `paynl_checkout_` (Pay.nl), `payone_onepage_`, `payone_amazon_` (Payone), `buckaroo_mrcash_pay` (Buckaroo), `mpblog_post_history`, `mpblog_post_preview`, `mpblog_author_information` (Mageplaza Blog), `clerk_clerk_powerstep_` (Clerk), `sharecart_index_download` (Mageplaza Share Cart), `mailchimp_cart_loadquote` (Mailchimp), `blog_non_cacheable` (Magefan Blog).

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
