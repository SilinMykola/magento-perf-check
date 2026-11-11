# project-index Specification

## Purpose
Collects the Magento configuration of the whole project (all modules, including core packages) so rules can check facts that live outside the file or module being analyzed, without reporting on code outside the analyzed paths.

## Requirements

### Requirement: Index scope
The index SHALL be built from every module of the project:
- `app/code`;
- every package under `vendor` that contains `etc/module.xml`, including `vendor/magento` and `vendor/mage-os`.

This scope SHALL hold regardless of `--paths` and test exclusion. Findings SHALL still be reported only for files under the analyzed paths.

A module set to `0` in the `modules` list of `app/etc/config.php` SHALL be left out of the index and out of the analysis, under the directory Composer installed it in and the one it resolves to; when it lies in or holds an analyzed path, one `disabled-module` notice SHALL name it. A module missing from that list, or a project without the file, SHALL count as enabled. The file SHALL be read as text, never run. The list of indexed modules SHALL be part of the cache key, so a module enabled, disabled or added SHALL invalidate cached results.

#### Scenario: Proxy configured in another module
- **WHEN** `app/code/Acme/Promo` is analyzed and a `di.xml` in `app/code/Acme/Core` configures a Proxy for a `Acme\Promo` class argument
- **THEN** rules see that Proxy

#### Scenario: Core declares the table
- **WHEN** `app/code/Acme/Gift/etc/db_schema.xml` adds a column to `sales_order` and the primary key of `sales_order` is declared in `vendor/magento/module-sales`
- **THEN** rules see that `sales_order` has a primary key

#### Scenario: Disabled module
- **WHEN** `app/etc/config.php` sets `Acme_Old` to `0`
- **THEN** nothing in `app/code/Acme/Old` is analyzed and one `disabled-module` notice names `Acme_Old`
- **AND** a Proxy configured in its `di.xml` does not count for other modules

#### Scenario: Module not listed yet
- **WHEN** `app/code/Acme/New` is missing from the `modules` list of `app/etc/config.php`
- **THEN** its findings are reported

### Requirement: Indexed facts
The index SHALL provide at least:
- **DI:** per class, the constructor arguments configured with an object type ending in `\Proxy`, in any area, including through virtual types; the plugin classes declared in `di.xml`; and the `preference` elements of the global, `webapi_rest` and `webapi_soap` areas, including `app/etc/di.xml`.
- **Events:** observer classes per event and area.
- **Mview:** view IDs declared in `mview.xml`.
- **Schema:** table names that have a primary key constraint in any `db_schema.xml`.
- **Web API:** the service class and method of every route in `webapi.xml`.

#### Scenario: Plugin class known
- **WHEN** a `di.xml` declares `<plugin name="p" type="Acme\Promo\Plugin\CartPlugin"/>`
- **THEN** the index reports `Acme\Promo\Plugin\CartPlugin` as a plugin class

#### Scenario: Area-specific proxy
- **WHEN** only `etc/frontend/di.xml` configures the Proxy
- **THEN** the index records the Proxy for that class argument

#### Scenario: Web API preference
- **WHEN** `etc/webapi_rest/di.xml` declares a preference and `etc/frontend/di.xml` another one for the same type
- **THEN** the index records the `webapi_rest` preference and not the frontend one

#### Scenario: Web API route
- **WHEN** a `webapi.xml` route has `<service class="Acme\Promo\Api\CouponManagementInterface" method="apply"/>`
- **THEN** the index records `apply` as a web API method of that class

### Requirement: Index is read-only and cached
Building the index SHALL NOT write into the project. The index MAY be cached in the system temp directory, keyed by project path. The cache SHALL be rebuilt when any indexed file changes.

#### Scenario: Changed di.xml
- **WHEN** a `di.xml` changes between two runs
- **THEN** the second run uses an index that reflects the change

### Requirement: Cached results follow the index
When an XML file behind the project index changes, PHP findings that depend on the index SHALL reflect the change on the next run, even if no PHP file changed.

#### Scenario: Proxy added and removed
- **WHEN** a class reports P-DI-01, then `di.xml` gets a Proxy for that argument, then the Proxy is removed again
- **THEN** the second run reports nothing and the third reports P-DI-01

### Requirement: Console commands and cron jobs
The index SHALL know the classes in the `commands` argument of `CommandList` or `CommandListInterface` in a global `di.xml`, and the `instance` classes of jobs in `crontab.xml`. A virtual type SHALL count as the class it is based on.

#### Scenario: Command
- **WHEN** `etc/di.xml` adds `Acme\Promo\Console\Sync` to the `commands` of `CommandListInterface`
- **THEN** the index reports that class as a console command

#### Scenario: Area di.xml
- **WHEN** only `etc/adminhtml/di.xml` adds the command
- **THEN** the index does not report it

#### Scenario: Cron job
- **WHEN** `etc/crontab.xml` has a job with `instance="Acme\Promo\Cron\Sync"`
- **THEN** the index reports that class as a cron job
