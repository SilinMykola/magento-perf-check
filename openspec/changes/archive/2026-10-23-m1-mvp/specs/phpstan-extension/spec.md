# Spec Delta

## ADDED Requirements

### Requirement: Template analysis
`magento-perf-check` SHALL analyze `.phtml` files under the analyzed paths with all PHP rules. When the extension is used in a project's own PHPStan setup, `.phtml` files SHALL be analyzed only if the project adds `phtml` to PHPStan's `fileExtensions`.

#### Scenario: Template rule in runner
- **WHEN** `magento-perf-check` analyzes a module with `view/frontend/templates/promo.phtml` that calls `$block->getLayout()->createBlock(...)`
- **THEN** T-01 reports it

#### Scenario: Untyped template variables
- **WHEN** a `.phtml` file calls `$item->load($id)` inside a `foreach` and `$item` has no known type
- **THEN** P-LOOP-01 reports nothing, as for any untyped receiver

### Requirement: Project index available to PHP rules
PHP rules SHALL be able to look up facts from the project index (see the `project-index` capability). The index root SHALL be the PHPStan parameter `magentoPerf.projectRoot`, defaulting to PHPStan's current working directory. Rules that need the index SHALL report nothing when the index has no Magento configuration, rather than guessing.

#### Scenario: Runner passes the project root
- **WHEN** `magento-perf-check --project=/srv/shop` runs
- **THEN** PHP rules read the index built from `/srv/shop`

#### Scenario: No Magento configuration found
- **WHEN** PHPStan runs on a directory without any `etc/di.xml`
- **THEN** index-dependent rules such as P-DI-01 report nothing
