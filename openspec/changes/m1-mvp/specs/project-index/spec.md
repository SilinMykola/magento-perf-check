# Spec Delta

## Purpose

Collects the Magento configuration of the whole project (all modules, including core packages) so rules can check facts that live outside the file or module being analyzed, without reporting on code outside the analyzed paths.

## ADDED Requirements

### Requirement: Index scope
The index SHALL be built from every module of the project:
- `app/code`;
- every package under `vendor` that contains `etc/module.xml`, including `vendor/magento` and `vendor/mage-os`.

This scope SHALL hold regardless of `--paths` and test exclusion. Findings SHALL still be reported only for files under the analyzed paths.

#### Scenario: Proxy configured in another module
- **WHEN** `app/code/Acme/Promo` is analyzed and a `di.xml` in `app/code/Acme/Core` configures a Proxy for a `Acme\Promo` class argument
- **THEN** rules see that Proxy

#### Scenario: Core declares the table
- **WHEN** `app/code/Acme/Gift/etc/db_schema.xml` adds a column to `sales_order` and the primary key of `sales_order` is declared in `vendor/magento/module-sales`
- **THEN** rules see that `sales_order` has a primary key

### Requirement: Indexed facts
The index SHALL provide at least:
- **DI:** per class, the constructor arguments configured with an object type ending in `\Proxy`, in any area, including through virtual types; and the plugin classes declared in `di.xml`.
- **Events:** observer classes per event and area.
- **Mview:** view IDs declared in `mview.xml`.
- **Schema:** table names that have a primary key constraint in any `db_schema.xml`.

#### Scenario: Plugin class known
- **WHEN** a `di.xml` declares `<plugin name="p" type="Acme\Promo\Plugin\CartPlugin"/>`
- **THEN** the index reports `Acme\Promo\Plugin\CartPlugin` as a plugin class

#### Scenario: Area-specific proxy
- **WHEN** only `etc/frontend/di.xml` configures the Proxy
- **THEN** the index records the Proxy for that class argument

### Requirement: Index is read-only and cached
Building the index SHALL NOT write into the project. The index MAY be cached in the system temp directory, keyed by project path. The cache SHALL be rebuilt when any indexed file changes.

#### Scenario: Changed di.xml
- **WHEN** a `di.xml` changes between two runs
- **THEN** the second run uses an index that reflects the change
