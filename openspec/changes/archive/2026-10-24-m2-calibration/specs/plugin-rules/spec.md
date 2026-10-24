## MODIFIED Requirements

### Requirement: P-PLG-01 around plugin that could be before/after
P-PLG-01 SHALL report, as `warning`, a public `around*` method of a plugin class in which:
- `$proceed` is called exactly once;
- the call is in a top-level statement, not inside `if`, a loop, `try` or a closure;
- the call is not a branch of a ternary and not the right operand of `&&`, `||`, `and`, `or` or `??`;
- the arguments are the method's own parameters after `$subject` and `$proceed`, in order (or spread with `...`), and none of them, or an element of one, is assigned before the call.

#### Scenario: Pass-through around
- **WHEN** `aroundGetPrice($subject, callable $proceed, $qty)` does `$result = $proceed($qty); return $result * 2;`
- **THEN** P-PLG-01 reports a warning

#### Scenario: Conditional proceed
- **WHEN** the method calls `$proceed` only inside `if ($this->isEnabled())`
- **THEN** P-PLG-01 reports nothing

#### Scenario: Proceed in a ternary branch
- **WHEN** the method does `return $this->isEnabled() ? $proceed($qty) : 0;`
- **THEN** P-PLG-01 reports nothing

#### Scenario: Proceed in a ternary condition
- **WHEN** the method does `return $proceed($qty) > 0 ? 1 : 0;`
- **THEN** P-PLG-01 reports a warning

#### Scenario: Changed arguments
- **WHEN** the method calls `$proceed($qty + 1)`
- **THEN** P-PLG-01 reports nothing

#### Scenario: Reassigned argument
- **WHEN** the method does `$qty = max($qty, 1); return $proceed($qty);`
- **THEN** P-PLG-01 reports nothing

#### Scenario: Exception handling
- **WHEN** the call is wrapped in `try`/`catch`
- **THEN** P-PLG-01 reports nothing
