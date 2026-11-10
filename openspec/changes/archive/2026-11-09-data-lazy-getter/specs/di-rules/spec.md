## MODIFIED Requirements

### Requirement: P-DI-06 direct ObjectManager use
P-DI-06 SHALL report, as `info`, `ObjectManager::getInstance()` in PHP classes, except where Adobe documents it as allowed:
- the backward-compatible constructor fallback (`$param ?: ObjectManager::getInstance()->get(...)`, `??`), also written in a constructor as an `if` that only tests that a parameter is missing (`!isset($param)`, `empty($param)`, `$param === null`, `$param == null`, `!$param`, `is_null($param)`, `!is_object($param)`, `!$param instanceof Type`) and whose block only assigns the result of one object manager call, or an array of them, to a variable or a property, or through a local variable holding the object manager that only such fallbacks use as the object of a call. Closures and classes declared in the constructor do not count as constructor code;
- outside the constructor, the lazy getter: an `if` that tests in the same ways that a property of `$this` is missing, or not an object of the type, and whose block only assigns the result of one object manager call, or an array of them, to that same property. Unless the property is reset, the object manager is asked once per object. A comment alone at the end of a fallback or getter block does not count as work; calls in the arguments of the dependency call are still reported. A getter that returns the object without keeping it is still reported. The same getter may keep the object in the object's data under one literal key: it tests `$this->getData('key')`, or a variable read from it alone, and its block only does `$this->setData('key', <dependency>)`, or assigns the dependency to that variable and then stores the variable under the same key;
- classes whose name ends in `Factory` or `\Proxy`;
- `__wakeup()` and `__sleep()`.

Calibration lowered it from `warning`: most correct findings were lazy BC getters with low impact, which are no longer reported.

#### Scenario: Service locator in a method
- **WHEN** a model method calls `ObjectManager::getInstance()->get(Helper::class)`
- **THEN** P-DI-06 reports an info finding

#### Scenario: Constructor fallback
- **WHEN** a constructor uses the `?:` fallback
- **THEN** P-DI-06 reports nothing

#### Scenario: Constructor fallback written as an if
- **WHEN** a constructor runs `if (!isset($factory)) { $factory = ObjectManager::getInstance()->get(Factory::class); }`
- **THEN** P-DI-06 reports nothing
- **WHEN** the `if` tests a property, or a parameter together with something else, or its block does anything but assign objects from the object manager
- **THEN** P-DI-06 reports the call
- **WHEN** such an `if` is inside a closure in the constructor
- **THEN** P-DI-06 reports the call

#### Scenario: Object manager in a local variable
- **WHEN** a constructor runs `$objectManager = ObjectManager::getInstance()` and uses `$objectManager` only as `$objectManager->get(...)` in `?:` and `??` fallbacks or in a fallback `if`
- **THEN** P-DI-06 reports nothing
- **WHEN** the constructor also calls `$objectManager->create()` outside a fallback, or passes `$objectManager` itself on
- **THEN** P-DI-06 reports the call

#### Scenario: Factory class
- **WHEN** `Acme\Promo\Model\ItemFactory::create()` uses the ObjectManager
- **THEN** P-DI-06 reports nothing

#### Scenario: Lazy getter
- **WHEN** a method runs `if ($this->serializer === null) { $this->serializer = ObjectManager::getInstance()->get(Json::class); }`
- **THEN** P-DI-06 reports nothing
- **WHEN** the block assigns another property, calls a method on the result, or does other work, or the method returns the object without keeping it
- **THEN** P-DI-06 reports the call

#### Scenario: Lazy getter kept in data
- **WHEN** a block reads `$viewModel = $this->getData('style_view_model')` and, when it is empty, assigns `ObjectManager::getInstance()->get(Style::class)` to it and calls `$this->setData('style_view_model', $viewModel)`
- **THEN** P-DI-06 reports nothing
- **WHEN** the block stores the object under another key, or does other work
- **THEN** P-DI-06 reports the call
