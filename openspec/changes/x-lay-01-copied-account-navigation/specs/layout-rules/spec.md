## MODIFIED Requirements

### Requirement: Storefront layout handles of the project
X-LAY-01 and X-LAY-03 SHALL know, for each storefront layout handle, which handles include it, and whether it is an account page: one of its layout files includes `customer_account` or declares a `<block>` named `customer_account_navigation`, or it includes, directly or through other handles, a handle that is an account page. A declared navigation SHALL NOT make `default` or `hyva_default` an account page, as those are on every page. An include SHALL count only as an `<update handle="..."/>` element that is a direct child of a layout file's root.

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
