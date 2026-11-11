## ADDED Requirements

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

## MODIFIED Requirements

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
