# xml-analysis Specification

## Purpose
Finds Magento XML configuration and layout files in the analyzed paths, works out their context (area, module, layout handle) and runs XML rules on them, so that FPC and configuration anti-patterns are caught where no other tool looks.

## Requirements

### Requirement: XML file discovery
The XML analyzer SHALL discover the XML files that its rules need under the analyzed paths. These are module files under `etc/` and `view/<area>/layout/`, and theme files under `app/design/<area>/<Vendor>/<theme>/<Module_Name>/layout/`. Packages under `vendor/magento` and `vendor/mage-os` SHALL be analyzed only when given explicitly in `--paths`.

#### Scenario: Module layout file
- **WHEN** `app/code/Acme/Promo/view/frontend/layout/default.xml` exists
- **THEN** it is analyzed as a layout file

#### Scenario: Theme layout override
- **WHEN** `app/design/frontend/Acme/shop/Magento_Catalog/layout/catalog_product_view.xml` exists
- **THEN** it is analyzed as a layout file

#### Scenario: Unrelated XML ignored
- **WHEN** an XML file lies outside `etc/` and `layout/` directories (for example a UI component under `view/adminhtml/ui_component/`)
- **THEN** no layout rule runs on it

### Requirement: Area resolution
Every analyzed XML file SHALL be assigned an area derived from its path:
- Under `etc/`: `etc/<file>` is `global`, and `etc/<area>/<file>` is `<area>`.
- Layout files: `view/<area>/layout/` and `app/design/<area>/` give `<area>`.

#### Scenario: Global config
- **WHEN** the file is `etc/events.xml`
- **THEN** its area is `global`

#### Scenario: Area-specific config
- **WHEN** the file is `etc/frontend/di.xml`
- **THEN** its area is `frontend`

#### Scenario: Admin layout
- **WHEN** the file is `view/adminhtml/layout/sales_order_view.xml`
- **THEN** its area is `adminhtml`

### Requirement: Module and handle resolution
Every analyzed XML file SHALL be assigned the module it belongs to. For module files, this is the `name` in the nearest `etc/module.xml`. For theme files, it is the `<Module_Name>` directory. A layout file's handle SHALL be its file name without `.xml`.

#### Scenario: Module from module.xml
- **WHEN** a layout file lies in a package whose `etc/module.xml` declares `Acme_Promo`
- **THEN** the finding's module is `Acme_Promo`

#### Scenario: Handle from file name
- **WHEN** the layout file is `catalog_category_view.xml`
- **THEN** its handle is `catalog_category_view`

### Requirement: Robust parsing
The XML analyzer MUST NOT stop on a file it cannot parse. It SHALL skip that file, record an analysis notice with the file path and parser message, and continue with the remaining files.

#### Scenario: Malformed XML
- **WHEN** one layout file is not well-formed
- **THEN** the other files are still analyzed
- **AND** the report summary counts one analysis notice naming that file

### Requirement: Line numbers in XML findings
Every XML finding SHALL point at the line of the element that triggered it.

#### Scenario: Finding line
- **WHEN** the violating `<block>` element starts on line 12
- **THEN** the finding's line is 12

### Requirement: Suppression comments in XML
An XML finding SHALL NOT be reported when the element that triggered it is directly preceded, ignoring whitespace, by a comment `<!-- perf-ignore <RULE-ID>: <reason> -->` naming that rule. The reason SHALL be required: a comment without a reason suppresses nothing and SHALL be reported as an analysis notice naming the file and line.

#### Scenario: Suppressed with reason
- **WHEN** `<!-- perf-ignore X-LAY-01: payment return page must not be cached -->` directly precedes a `<block cacheable="false">`
- **THEN** X-LAY-01 reports nothing for that block

#### Scenario: Comment for another rule
- **WHEN** the preceding comment is `<!-- perf-ignore X-LAY-02: ... -->`
- **THEN** the X-LAY-01 finding is still reported

#### Scenario: Missing reason
- **WHEN** the preceding comment is `<!-- perf-ignore X-LAY-01 -->`
- **THEN** the finding is reported
- **AND** the summary counts one analysis notice for the comment without a reason

#### Scenario: Comment not adjacent
- **WHEN** another element stands between the comment and the violating element
- **THEN** the finding is reported

### Requirement: Additional configuration files
The XML analyzer SHALL also discover these module files under `etc/` and assign them area and module like other config files:
- `sections.xml`, `communication.xml`, `indexer.xml`, `mview.xml`, `db_schema.xml`.
- `.graphqls` files under `etc/` (area `graphql`).

#### Scenario: GraphQL schema
- **WHEN** `app/code/Acme/Promo/etc/schema.graphqls` exists
- **THEN** it is analyzed by the GraphQL rules with module `Acme_Promo`

#### Scenario: Frontend sections
- **WHEN** `app/code/Acme/Promo/etc/frontend/sections.xml` exists
- **THEN** it is analyzed with area `frontend`
