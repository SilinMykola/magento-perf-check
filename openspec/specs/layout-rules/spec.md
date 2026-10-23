# layout-rules Specification

## Purpose
Rules on layout XML that catch configurations which disable or fragment Full Page Cache for whole groups of pages.

## Requirements

### Requirement: X-LAY-01 non-cacheable block
X-LAY-01 SHALL report every `<block>` element with `cacheable="false"` in a layout file of area `frontend` or `base`. Severity is `error` when the handle is `default` or starts with `catalog_` or `cms_`, and `warning` for other handles. The message SHALL name the handle and the block name.

#### Scenario: Block in default handle
- **WHEN** `view/frontend/layout/default.xml` contains `<block name="promo.banner" cacheable="false" .../>`
- **THEN** X-LAY-01 reports an `error` naming handle `default` and block `promo.banner`

#### Scenario: Block in catalog handle
- **WHEN** `catalog_product_view.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports an `error`

#### Scenario: Block in another handle
- **WHEN** `contact_index_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports a `warning`

#### Scenario: Theme override
- **WHEN** `app/design/frontend/Acme/shop/Magento_Cms/layout/cms_index_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports an `error`

### Requirement: X-LAY-01 default allowlist
X-LAY-01 SHALL NOT report blocks in personal handles that are not meant to be served from FPC. The default allowlist is these handle prefixes:
- Customer: `customer_`, `loginascustomer_`, `newsletter_manage_`, `review_customer_`, `downloadable_customer_`.
- Checkout and sales: `checkout_`, `multishipping_`, `sales_`.
- Payment: `paypal_`, `vault_`, `paymentservicespaypal_vault_`.
- Lists: `wishlist_`, `catalog_product_compare_`.

The configuration file SHALL be able to add prefixes or replace the list.

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
