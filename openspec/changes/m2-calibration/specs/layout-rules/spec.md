# Spec Delta

## MODIFIED Requirements

### Requirement: X-LAY-01 default allowlist
X-LAY-01 SHALL NOT report blocks in handles whose name starts with a prefix on the allowlist. These are personal pages that are not meant to be served from FPC.

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

### Requirement: X-LAY-01 default allowlist contents
The default allowlist SHALL be these handle prefixes:
- Customer: `customer_`, `loginascustomer_`, `newsletter_manage_`, `review_customer_`, `downloadable_customer_`.
- Checkout and sales: `checkout_`, `multishipping_`, `sales_`.
- Payment: `paypal_`, `vault_`, `paymentservicespaypal_vault_`.
- Lists: `wishlist_`, `catalog_product_compare_`.

#### Scenario: Vault handle
- **WHEN** `vault_cards_listaction.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

### Requirement: X-LAY-01 configurable allowlist
The configuration file SHALL be able to add prefixes to the allowlist, or replace the default list with its own.

#### Scenario: Default list replaced
- **WHEN** the configuration sets `layout.allowlistHandles: [acme_]` and `layout.replaceAllowlist: true`, and `checkout_cart_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports it
