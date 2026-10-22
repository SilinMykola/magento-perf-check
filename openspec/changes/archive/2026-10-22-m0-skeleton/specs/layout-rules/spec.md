# Spec Delta

## Purpose

Rules on layout XML that catch configurations which disable or fragment Full Page Cache for whole groups of pages.

## ADDED Requirements

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
X-LAY-01 SHALL NOT report blocks in handles that start with `checkout_`, `customer_account_`, `sales_`, `wishlist_`, `multishipping_` or `paypal_`. These pages are personal by nature and are not meant to be served from FPC.

#### Scenario: Checkout handle
- **WHEN** `checkout_cart_index.xml` contains a block with `cacheable="false"`
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
