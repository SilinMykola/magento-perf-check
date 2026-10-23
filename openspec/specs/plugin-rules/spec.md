# plugin-rules Specification

## Purpose
Rules on interceptor plugins that catch plugin types more expensive than the job requires.

## Requirements

### Requirement: P-PLG-01 around plugin that could be before/after
P-PLG-01 SHALL report, as `warning`, a public `around*` method of a plugin class in which:
- `$proceed` is called exactly once;
- the call is in a top-level statement, not inside `if`, a loop, `try` or a closure;
- the arguments are the method's own parameters after `$subject` and `$proceed`, unchanged and in order (or spread with `...`).

#### Scenario: Pass-through around
- **WHEN** `aroundGetPrice($subject, callable $proceed, $qty)` does `$result = $proceed($qty); return $result * 2;`
- **THEN** P-PLG-01 reports a warning

#### Scenario: Conditional proceed
- **WHEN** the method calls `$proceed` only inside `if ($this->isEnabled())`
- **THEN** P-PLG-01 reports nothing

#### Scenario: Changed arguments
- **WHEN** the method calls `$proceed($qty + 1)`
- **THEN** P-PLG-01 reports nothing

#### Scenario: Exception handling
- **WHEN** the call is wrapped in `try`/`catch`
- **THEN** P-PLG-01 reports nothing

### Requirement: Plugin class detection
A class SHALL count as a plugin class when the project index lists it as one in any `di.xml`, or when its short name ends with `Plugin`.

#### Scenario: Plugin declared in di.xml
- **WHEN** `Acme\Promo\Model\PriceModifier` is declared as a plugin in `di.xml`
- **THEN** its `around*` methods are checked
