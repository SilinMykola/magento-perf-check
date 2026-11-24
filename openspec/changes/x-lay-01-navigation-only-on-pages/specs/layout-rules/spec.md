## MODIFIED Requirements

### Requirement: Storefront layout handles of the project
X-LAY-01 and X-LAY-03 SHALL know, for each storefront layout handle, which handles include it, and whether it is an account page: one of its layout files includes `customer_account`; it is a page and one of its layout files declares a `<block>` named `customer_account_navigation`; it is no page, one of its layout files declares that block, and every handle that includes it, directly or through other handles, is a page that is not shop-wide, so that neither a shop-wide handle nor a handle that no layout includes takes it in; or it includes, directly or through other handles, a handle that is an account page. A shop-wide handle that X-LAY-03 covers (`default`, `catalog_*`, `cms_*`, also with a `hyva_` prefix) SHALL be an account page only when one of its layout files includes `customer_account`: a navigation it declares or a handle it includes SHALL NOT make it one, as it is on many pages. An include SHALL count only as an `<update handle="..."/>` element that is a direct child of a layout file's root.

#### Scenario: Include in a nested element
- **WHEN** only an `<update handle="acme_nested"/>` inside `<body>` of `sales_order_view.xml` names `acme_nested`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block, because core merges only the updates that are direct children of the root

#### Scenario: Copied account layout
- **WHEN** the page `acmeroute_reward_index.xml` includes `acme_reward_account`, which declares `<block name="customer_account_navigation">`, and has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Account navigation declared on the page
- **WHEN** the page `acmeroute_navcopy_index.xml` declares `<block name="customer_account_navigation">` and has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Account navigation only referenced
- **WHEN** the page `acmeroute_navref_index.xml` has only `<referenceBlock name="customer_account_navigation">` and a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Account navigation in default
- **WHEN** `default.xml` declares `<block name="customer_account_navigation">` and includes `acme_header_links`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Account navigation included by default
- **WHEN** `default.xml` includes `acme_account_dropdown`, which is no page, declares `<block name="customer_account_navigation">` and has a block with `cacheable="false"`, and `default.xml` has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block in `acme_account_dropdown.xml` and X-LAY-03 the block in `default.xml`

#### Scenario: Public page that includes the navigation through another handle
- **WHEN** `contact_index_index.xml` includes `acme_header`, which includes `acme_account_dropdown`, and has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block, because `default.xml` includes `acme_account_dropdown` too, so it is no account page

#### Scenario: Header dropdown for P-FPC-02
- **WHEN** `default.xml` includes `acme_account_menu`, which declares `<block name="customer_account_navigation">` and places a block without `cacheable="false"`, and the pages `acmeroute_navpage_index`, which declares the navigation, and `acmeroute_rewardpage_index`, which includes `acme_reward_account`, place blocks without `cacheable="false"`
- **THEN** `acmeroute_navpage_index`, `acmeroute_rewardpage_index` and `acme_reward_account` are handles no cacheable page uses, and `acme_account_menu` is not

#### Scenario: Account navigation included by a shop-wide page
- **WHEN** the page `catalog_product_view` includes `acme_product_menu`, which declares `<block name="customer_account_navigation">`
- **THEN** `acme_product_menu` is no account page

#### Scenario: Account navigation included by a handle added by code
- **WHEN** only `acme_code_area`, which no layout includes and which is no page, includes `acme_code_menu`, which declares `<block name="customer_account_navigation">`
- **THEN** `acme_code_menu` is no account page
