# phpstan-extension Specification

## Purpose
Delivers the PHP rules as a PHPStan extension, so that they are type-aware, run inside existing PHPStan setups and IDE integrations, and can be suppressed with standard PHPStan mechanisms.

## Requirements

### Requirement: Installation into an existing PHPStan setup
The package SHALL be installable next to PHPStan 1.12 and PHPStan 2.x. With `phpstan/extension-installer` present, its rules SHALL be active without manual configuration. Without the installer, including the package's `extension.neon` in the project's PHPStan config SHALL activate them.

#### Scenario: PHPStan 1.12 project
- **WHEN** the package is required in a project locked to `phpstan/phpstan` 1.12
- **THEN** Composer resolves without changing the PHPStan version
- **AND** PHPStan reports violations of the package's rules

#### Scenario: PHPStan 2.x project
- **WHEN** the package is required in a project on `phpstan/phpstan` 2.x
- **THEN** PHPStan reports violations of the package's rules

#### Scenario: Manual include
- **WHEN** the project's `phpstan.neon` includes the package's `extension.neon` and the extension installer is absent
- **THEN** the rules are active

### Requirement: Rule identifiers
Every PHP rule SHALL report errors with a stable PHPStan identifier in the `magentoPerf.` namespace. Every message SHALL start with the rule ID in brackets (for example `[P-LOOP-01]`).

#### Scenario: Inline suppression
- **WHEN** a violating line is preceded by `// @phpstan-ignore magentoPerf.loopModelLoad (reason)`
- **THEN** PHPStan does not report that violation

#### Scenario: Message carries rule ID
- **WHEN** P-LOOP-01 reports a violation
- **THEN** the message starts with `[P-LOOP-01]`

### Requirement: Independence from PHPStan level
The rules SHALL report violations at every PHPStan level, including level 0.

#### Scenario: Level 0
- **WHEN** PHPStan runs at level 0 on code with a P-COL-02 violation
- **THEN** the violation is reported

### Requirement: Loop context
Rules that depend on loop context SHALL treat code as inside a loop when either holds:
- It is in the body of `foreach`, `for`, `while` or `do`-`while`.
- It is in the body of a closure or arrow function passed directly to `array_map`, `array_walk` or a collection's `walk()`.

The iterable expression of `foreach` and the init expressions of `for` SHALL NOT count as inside the loop.

#### Scenario: foreach body
- **WHEN** a call is inside a `foreach` body
- **THEN** it is in loop context

#### Scenario: Iterated expression
- **WHEN** a call produces the iterable of `foreach` (`foreach ($repo->getList($c)->getItems() as $item)`)
- **THEN** that call is not in loop context

#### Scenario: Closure passed to array_map
- **WHEN** a call is inside a closure passed as the callback of `array_map`
- **THEN** it is in loop context

#### Scenario: Closure stored, not iterated
- **WHEN** a closure containing the call is assigned to a variable outside any loop and never passed to an iterating function in the same expression
- **THEN** the call is not in loop context

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
