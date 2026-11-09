## ADDED Requirements

### Requirement: P-LOOP-11 object manager in a loop
P-LOOP-11 SHALL report, as `error`, a call of `get()` or `create()` on `Magento\Framework\ObjectManagerInterface` in loop context, whether the object manager comes from `ObjectManager::getInstance()` or is injected. Classes whose name ends in `Factory` or `\Proxy` SHALL be left out. A call the loop is left right after SHALL NOT count, as for the other loop rules.

#### Scenario: Injected object manager creates per row
- **WHEN** a `foreach` over rows calls `$this->objectManager->create(Item::class, ['data' => $row])`
- **THEN** P-LOOP-11 reports an error

#### Scenario: Factory builds a list
- **WHEN** a class named `ThingFactory` calls `$this->objectManager->create()` in a loop
- **THEN** P-LOOP-11 reports nothing
