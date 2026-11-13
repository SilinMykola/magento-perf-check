## MODIFIED Requirements

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

## ADDED Requirements

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
X-LAY-01 SHALL NOT report blocks in a handle that other handles include when every one of them is personal: allowlisted, an account page, or itself included only by personal handles. A handle that includes itself, or that a cycle leads back to, SHALL NOT count as one that includes it. A handle whose name starts with the id of a storefront route and `_` is a page and SHALL NOT be judged by what includes it. A `hyva_` handle SHALL be personal only when the handle without the prefix is personal and every handle that includes the `hyva_` handle itself is personal. X-LAY-03 SHALL NOT follow includes, because a controller may add a shop-wide handle itself.

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

#### Scenario: Hyvä copy included by a public page
- **WHEN** only `sales_order_view.xml` includes `acme_hyva_pub`, and `contact_index_index.xml` includes `hyva_acme_hyva_pub`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Shop-wide handle included by a personal page
- **WHEN** only `checkout_cart_configure_type_simple.xml` includes `catalog_product_view_type_simple`, which has a block with `cacheable="false"`
- **THEN** X-LAY-03 reports the block

#### Scenario: Handle that nothing includes
- **WHEN** `acme_page_index.xml` has a block with `cacheable="false"` and no layout file includes `acme_page_index`
- **THEN** X-LAY-01 reports the block
