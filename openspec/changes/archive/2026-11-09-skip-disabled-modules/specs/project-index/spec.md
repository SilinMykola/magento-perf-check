## MODIFIED Requirements

### Requirement: Index scope
The index SHALL be built from every module of the project:
- `app/code`;
- every package under `vendor` that contains `etc/module.xml`, including `vendor/magento` and `vendor/mage-os`.

This scope SHALL hold regardless of `--paths` and test exclusion. Findings SHALL still be reported only for files under the analyzed paths.

A module set to `0` in the `modules` list of `app/etc/config.php` SHALL be left out of the index, and findings in its files SHALL NOT be reported: each SHALL become a `disabled-module` notice that names the rule, the location and the module. A module missing from that list, or a project without the file, SHALL count as enabled. The file SHALL be read as text, never run. The list of indexed modules SHALL be part of the cache key, so a module enabled, disabled or added SHALL invalidate cached results.

#### Scenario: Proxy configured in another module
- **WHEN** `app/code/Acme/Promo` is analyzed and a `di.xml` in `app/code/Acme/Core` configures a Proxy for a `Acme\Promo` class argument
- **THEN** rules see that Proxy

#### Scenario: Core declares the table
- **WHEN** `app/code/Acme/Gift/etc/db_schema.xml` adds a column to `sales_order` and the primary key of `sales_order` is declared in `vendor/magento/module-sales`
- **THEN** rules see that `sales_order` has a primary key

#### Scenario: Disabled module
- **WHEN** `app/etc/config.php` sets `Acme_Old` to `0`
- **THEN** findings in `app/code/Acme/Old` are listed as `disabled-module` notices, not as findings
- **AND** a Proxy configured in its `di.xml` does not count for other modules

#### Scenario: Module not listed yet
- **WHEN** `app/code/Acme/New` is missing from the `modules` list of `app/etc/config.php`
- **THEN** its findings are reported
