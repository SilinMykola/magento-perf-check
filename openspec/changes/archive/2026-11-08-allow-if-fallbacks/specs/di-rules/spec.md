## MODIFIED Requirements

### Requirement: P-DI-06 direct ObjectManager use
P-DI-06 SHALL report, as `info`, `ObjectManager::getInstance()` in PHP classes, except where Adobe documents it as allowed:
- the backward-compatible constructor fallback (`$param ?: ObjectManager::getInstance()->get(...)`, `??`), also written in a constructor as an `if` that only tests that a parameter is missing (`!isset($param)`, `empty($param)`, `$param === null`, `!$param`, `is_null($param)`) and whose block only assigns the result of one object manager call, or an array of them, to a variable or a property, or through a local variable holding the object manager that only such fallbacks use as the object of a call. Closures and classes declared in the constructor do not count as constructor code;
- classes whose name ends in `Factory` or `\Proxy`;
- `__wakeup()` and `__sleep()`.

Calibration lowered it from `warning`: most correct findings are lazy BC getters with low impact.

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
