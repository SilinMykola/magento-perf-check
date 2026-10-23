# Spec Delta

## MODIFIED Requirements

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

## ADDED Requirements

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
