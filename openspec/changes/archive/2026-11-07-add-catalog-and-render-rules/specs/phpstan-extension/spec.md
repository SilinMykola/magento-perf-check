## ADDED Requirements

### Requirement: Types of factories that are not generated
The extension SHALL type `create()` of a class named `*Factory` that does not exist as the class named without that suffix, when that class exists. A factory class that exists SHALL keep its own signature. In runs of the `magento-perf-check` command, private properties without a declared type SHALL take their type from the constructor; a project's own PHPStan configuration decides this for itself. Findings SHALL not depend on whether `setup:di:compile` has run, as far as factories go.

#### Scenario: Factory not generated
- **WHEN** `generated/code` has no `Acme\ThingFactory`, `Acme\Thing` is a model and a loop calls `$this->thingFactory->create()->load($id)`
- **THEN** P-LOOP-01 reports the call

#### Scenario: Factory that exists
- **WHEN** `Acme\ThingFactory` exists and its `create()` returns another class
- **THEN** the return type of that method is used

#### Scenario: Nothing to create
- **WHEN** neither `Acme\NothingFactory` nor `Acme\Nothing` exists
- **THEN** the call stays untyped and no rule reports it

### Requirement: Runs from a checkout match an installed package
Classes that stand in for Magento in tests SHALL not be reachable through this package's autoloader, because PHPStan loads it during a run and they would replace the project's real classes.

#### Scenario: Run from a checkout
- **WHEN** the tool runs from a checkout with dev dependencies installed, and from a copy without them, on the same project
- **THEN** both report the same findings

