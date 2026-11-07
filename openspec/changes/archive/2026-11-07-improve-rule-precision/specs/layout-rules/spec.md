## MODIFIED Requirements

### Requirement: X-LAY-01 default allowlist
X-LAY-01 and X-LAY-03 SHALL NOT report blocks in handles whose name starts with a prefix on the allowlist, or in a layout that contains `<update handle="customer_account"/>`. These are personal pages that are not meant to be served from FPC.

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

## ADDED Requirements

### Requirement: X-LAY-01 non-cacheable block on other handles
X-LAY-01 SHALL report, as `warning`, every `<block>` element with `cacheable="false"` in a layout file of area `frontend` or `base`, when the handle is not one of those X-LAY-03 covers. The message SHALL name the handle and the block name.

#### Scenario: Block in another handle
- **WHEN** `contact_index_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports a `warning`

### Requirement: X-LAY-03 non-cacheable block on shop-wide handles
X-LAY-03 SHALL report, as `error`, every `<block>` element with `cacheable="false"` in a layout file of area `frontend` or `base`, when the handle is `default` or starts with `catalog_` or `cms_`. The message SHALL name the handle and the block name.

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

## REMOVED Requirements

### Requirement: X-LAY-01 non-cacheable block
**Reason**: Split by handle: the `default`, `catalog_*` and `cms_*` handles move to X-LAY-03, which is in the default set; other handles stay in experimental X-LAY-01.
**Migration**: Replace `perf-ignore X-LAY-01` and baseline entries on those handles with `X-LAY-03`.
