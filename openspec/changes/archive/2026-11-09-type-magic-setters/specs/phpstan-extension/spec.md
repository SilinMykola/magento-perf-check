## ADDED Requirements

### Requirement: Types of DataObject magic setters
The extension SHALL type a call of a method whose name starts with `set` or `uns` on a subclass of `Magento\Framework\DataObject` as the object it is called on, when the class neither declares that method nor documents it with `@method`: `DataObject::__call()` stores or removes the value and returns the object. Other magic calls, such as getters, SHALL keep PHPStan's type, and a class outside `DataObject` SHALL be left alone.

#### Scenario: Save after a magic setter
- **WHEN** a loop over models calls `$model->setStatus($status)->save()`
- **THEN** P-LOOP-03 reports the save

#### Scenario: Declared setter
- **WHEN** the model declares `setTarget()` with another return type
- **THEN** that return type is used
