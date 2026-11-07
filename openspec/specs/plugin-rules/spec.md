# plugin-rules Specification

## Purpose
Rules on interceptor plugins that catch plugin types more expensive than the job requires.

## Requirements

### Requirement: P-PLG-01 around plugin that could be before/after
P-PLG-01 SHALL report, as `warning` and in the default set, a public `around*` method of a plugin class in which:
- `$proceed` is called exactly once;
- the call is in a top-level statement, not inside `if`, a loop, `try` or a closure;
- the call is not a branch of a ternary and not the right operand of `&&`, `||`, `and`, `or` or `??`;
- the arguments are the method's own parameters after `$subject` and `$proceed`, in order (or spread with `...`), and none of them, or an element of one, is assigned before the call;
- no local variable is assigned before the call and read after it, because a before and after pair cannot share it.

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

#### Scenario: State read before the call
- **WHEN** the method does `$isNew = $subject->isObjectNew(); $result = $proceed(); return $isNew ? $result : null;`
- **THEN** P-PLG-01 reports nothing

#### Scenario: Variable not read after the call
- **WHEN** the method does `$id = $subject->getId(); $subject->log($id); return $proceed();`
- **THEN** P-PLG-01 reports a warning

### Requirement: Plugin class detection
A class SHALL count as a plugin class when the project index lists it as one in any `di.xml`, or when its short name ends with `Plugin`.

#### Scenario: Plugin declared in di.xml
- **WHEN** `Acme\Promo\Model\PriceModifier` is declared as a plugin in `di.xml`
- **THEN** its `around*` methods are checked

### Requirement: X-PLG-02 plugin on a method that runs per rendered block
X-PLG-02 SHALL report, as `info`, a public `before`, `after` or `around` method for `toHtml()` or `getChildHtml()` of a class configured as a plugin on `AbstractBlock` or `Template`, for `fetchView()` on `Template`, and for `renderElement()` on `Layout` or `LayoutInterface`. A plugin that any module disables, by target and name, or that is configured only in the `adminhtml` area SHALL not count. It is off by default.

#### Scenario: After plugin on the base block
- **WHEN** a class configured in `etc/frontend/di.xml` as a plugin on `AbstractBlock` has `afterToHtml()`
- **THEN** X-PLG-02 reports that method

#### Scenario: Another method of the plugin
- **WHEN** a plugin on `Template` has `aroundGetTemplate()`
- **THEN** X-PLG-02 reports nothing

#### Scenario: A plugin on a specific block
- **WHEN** a plugin on `Magento\Catalog\Block\Product\View` has `afterToHtml()`
- **THEN** X-PLG-02 reports nothing

#### Scenario: Admin area
- **WHEN** the plugin is configured only in `etc/adminhtml/di.xml`
- **THEN** X-PLG-02 reports nothing

#### Scenario: Disabled plugin
- **WHEN** the plugin has `disabled="true"`
- **THEN** X-PLG-02 reports nothing

#### Scenario: Disabled by another module
- **WHEN** another module has `<plugin name="same_name" disabled="true"/>` on the same class
- **THEN** X-PLG-02 reports nothing
