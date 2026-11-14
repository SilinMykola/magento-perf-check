# di-rules Specification

## Purpose
Rules on object construction and dependency injection that catch work done when an object is created, rather than when it is used, which slows every request that builds the object graph.

## Requirements

### Requirement: P-DI-01 session or cookie manager injected without Proxy
P-DI-01 SHALL report a constructor parameter whose type is a subtype of `Magento\Framework\Session\SessionManagerInterface` (as `warning`) or `Magento\Framework\Stdlib\CookieManagerInterface` (as `info`), unless the project index has a `\Proxy` configured for that class and parameter. The message SHALL advise configuring a Proxy in `di.xml`, not type-hinting `\Proxy`. Calibration lowered both by one step: most correct findings have low impact.

#### Scenario: Customer session in a block
- **WHEN** a block constructor has `Magento\Customer\Model\Session $customerSession` and no `di.xml` configures a Proxy for it
- **THEN** P-DI-01 reports a warning

#### Scenario: Proxy configured
- **WHEN** `etc/frontend/di.xml` sets `<argument name="customerSession" xsi:type="object">Magento\Customer\Model\Session\Proxy</argument>` for that class
- **THEN** P-DI-01 reports nothing

#### Scenario: Cookie manager
- **WHEN** a constructor has `CookieManagerInterface $cookieManager` without Proxy
- **THEN** P-DI-01 reports an info finding

### Requirement: P-DI-01 exclusions
P-DI-01 SHALL NOT report generated classes (names ending in `\Proxy`, `\Interceptor` or `Factory`) or classes that are themselves session managers. It SHALL NOT report a session parameter of a class that implements `Magento\Framework\App\ActionInterface`, because every action starts a session anyway; a cookie manager parameter there is still reported.

#### Scenario: Session subclass
- **WHEN** `Acme\Promo\Model\Session extends SessionManager` passes its own session dependencies to `parent::__construct`
- **THEN** P-DI-01 reports nothing for that class

#### Scenario: Session in a controller action
- **WHEN** a class implementing `ActionInterface` takes `Magento\Customer\Model\Session $customerSession` and `CookieManagerInterface $cookieManager` without a Proxy
- **THEN** P-DI-01 reports only the cookie manager

### Requirement: P-DI-02 constructor does more than assign
P-DI-02 SHALL report, as `warning`, a `__construct` that contains anything other than the allowed statements:
- assignments, to properties or to local variables, of parameters, literals, constants and casts of these;
- the same assignments with a `??` or `?:` fallback whose default is one of these values, a `new` expression, or `ObjectManager::getInstance()->get(...)`, and the same fallback written as `$param ??= <default>`;
- `parent::__construct(...)`, also written as `return parent::__construct(...)`;
- `if` or `foreach` blocks whose only effect is throwing an exception (argument validation).

One finding per constructor SHALL point at the first offending statement.

#### Scenario: Query in constructor
- **WHEN** a constructor calls `$this->config = $this->scopeConfig->getValue('x')`
- **THEN** P-DI-02 reports a warning on that line

#### Scenario: Validation loop
- **WHEN** a constructor runs `foreach ($handlers as $h) { if (!$h instanceof HandlerInterface) { throw new \InvalidArgumentException(); } }`
- **THEN** P-DI-02 reports nothing

#### Scenario: Backward-compatible fallback
- **WHEN** a constructor assigns `$this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class)`
- **THEN** P-DI-02 reports nothing

#### Scenario: Cast of a parameter
- **WHEN** a constructor assigns `$this->prefetchCount = (int) $prefetchCount`
- **THEN** P-DI-02 reports nothing

#### Scenario: Fallback to a local variable
- **WHEN** a constructor assigns `$resolver = $resolver ?? ObjectManager::getInstance()->get(Resolver::class)` before using it
- **THEN** P-DI-02 reports nothing for that statement

#### Scenario: Parent call with a return
- **WHEN** a constructor contains only `return parent::__construct($context);`
- **THEN** P-DI-02 reports nothing

### Requirement: P-DI-03 event dispatched in constructor
P-DI-03 SHALL report, as `error`, a call to `dispatch()` on `Magento\Framework\Event\ManagerInterface` inside `__construct`.

#### Scenario: Dispatch in constructor
- **WHEN** a constructor calls `$this->eventManager->dispatch('acme_init')`
- **THEN** P-DI-03 reports an error

### Requirement: P-DI-06 direct ObjectManager use
P-DI-06 SHALL report, as `warning`, `ObjectManager::getInstance()` in PHP classes, except where Adobe documents it as allowed:
- in the constructor, the backward-compatible fallback (`$param ?: ObjectManager::getInstance()->get(...)`, `??`, `$param ??= ObjectManager::getInstance()->get(...)`, or a full ternary that tests the value and returns it when it is there, such as `$param !== null ? $param : ObjectManager::getInstance()->get(...)` or `$param === null ? ObjectManager::getInstance()->get(...) : $param`), also written in a constructor as an `if` that only tests that a parameter is missing (`!isset($param)`, `empty($param)`, `$param === null`, `$param == null`, `!$param`, `is_null($param)`, `!is_object($param)`, `!$param instanceof Type`) and whose block only assigns the result of one object manager call, or an array of them, to a variable or a property, or through a local variable holding the object manager that only such fallbacks use as the object of a call. Closures and classes declared in the constructor do not count as constructor code;
- outside the constructor, the lazy getter: an `if` that tests in the same ways that a property of `$this`, or a static property of `self` or `static`, is missing, or not an object of the type, and whose block only assigns the result of one object manager call, or an array of them, to that same property, directly or through a local variable that it then stores there; or the same in one expression that keeps the object in the property it tests: `$this->x = $this->x ?? <dependency>`, the same with `?:`, or `$this->x ??= <dependency>`. Unless the property is reset, the object manager is asked once per object. A comment alone at the end of a fallback or getter block does not count as work; calls in the arguments of the dependency call are still reported. A getter that returns the object without keeping it is still reported. The same getter may keep the object in the object's data under one literal key: it tests `$this->getData('key')`, or a variable read from it alone, and its block only does `$this->setData('key', <dependency>)`, or assigns the dependency to that variable and then stores the variable under the same key;
- classes whose name ends in `Factory` or `\Proxy`;
- `__wakeup()` and `__sleep()`.

Calibration had lowered it to `info` because most correct findings have low impact; the maintainer raised it back in round 3: a direct object manager call hides the dependency from DI configuration, whatever its cost.

#### Scenario: Service locator in a method
- **WHEN** a model method calls `ObjectManager::getInstance()->get(Helper::class)`
- **THEN** P-DI-06 reports a warning finding

#### Scenario: Constructor fallback
- **WHEN** a constructor uses the `?:` fallback, or `$param ??= ObjectManager::getInstance()->get(...)`
- **THEN** P-DI-06 reports nothing
- **WHEN** a method other than the constructor returns `$this->resource ?: ObjectManager::getInstance()->get(...)`, or a closure declared in the constructor uses `?:`
- **THEN** P-DI-06 reports the call

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

#### Scenario: Ternary fallback
- **WHEN** a constructor assigns `$this->config = $config !== null ? $config : ObjectManager::getInstance()->get(DeploymentConfig::class)`
- **THEN** P-DI-06 reports nothing
- **WHEN** the ternary tests one value and returns another, calls the object manager when the value is there, or is outside the constructor
- **THEN** P-DI-06 reports the call

#### Scenario: Lazy getter through a variable
- **WHEN** a method runs `if ($this->config === null) { $config = ObjectManager::getInstance()->get(Config::class); $this->config = $config; }`
- **THEN** P-DI-06 reports nothing
- **WHEN** the block stores the variable in another property, or does more with it
- **THEN** P-DI-06 reports the call

#### Scenario: Lazy getter in one expression
- **WHEN** a method runs `$this->notifier = $this->notifier ?? ObjectManager::getInstance()->get(Notifier::class)` or `$this->notifier ??= ObjectManager::getInstance()->get(Notifier::class)`
- **THEN** P-DI-06 reports nothing
- **WHEN** the expression tests another property or a local variable, keeps more than the object manager call, keeps it under a key of an array, or is in a closure
- **THEN** P-DI-06 reports the call

#### Scenario: Lazy getter kept in data
- **WHEN** a block reads `$viewModel = $this->getData('style_view_model')` and, when it is empty, assigns `ObjectManager::getInstance()->get(Style::class)` to it and calls `$this->setData('style_view_model', $viewModel)`
- **THEN** P-DI-06 reports nothing
- **WHEN** the block stores the object under another key, or does other work
- **THEN** P-DI-06 reports the call

### Requirement: P-DI-02 skips code outside the object manager
P-DI-02 SHALL NOT report classes in Composer packages whose type is `composer-plugin`, nor classes in `magento/magento-coding-standard`. The object manager never builds these, so DI construction rules do not apply.

#### Scenario: Composer plugin
- **WHEN** a constructor in a package of type `composer-plugin` calls a method
- **THEN** P-DI-02 reports nothing

### Requirement: P-DI-04 console command with eager dependencies
P-DI-04 SHALL report, as `info`, a class registered in the `commands` argument of `CommandList` or `CommandListInterface` in a global `di.xml`, when its constructor, its own or inherited, has required parameters typed with a class that is not a factory, not a `\Proxy`, not one that commands of core already create on every run or that is cheap to create (`Filesystem`), and not configured with a `\Proxy` for that argument. One command SHALL give one finding, which names those parameters. It is off by default.

#### Scenario: Command with a repository
- **WHEN** a command registered in `CommandListInterface` takes `ProductRepositoryInterface $products`
- **THEN** P-DI-04 reports its constructor and names `$products`

#### Scenario: Inherited constructor
- **WHEN** a registered command has no constructor of its own and its parent takes `ProductRepositoryInterface $products`
- **THEN** P-DI-04 reports the class and names `$products`

#### Scenario: Inherited constructor with a Proxy on the command
- **WHEN** `di.xml` configures a `\Proxy` for that argument on the command
- **THEN** P-DI-04 reports nothing

#### Scenario: No constructor
- **WHEN** neither a registered command nor its parents have a constructor
- **THEN** P-DI-04 reports nothing

#### Scenario: Proxy in di.xml
- **WHEN** `di.xml` configures a `\Proxy` for each class parameter of the command
- **THEN** P-DI-04 reports nothing

#### Scenario: Created anyway
- **WHEN** the command takes only `Magento\Framework\App\State` and `StoreManagerInterface`
- **THEN** P-DI-04 reports nothing

#### Scenario: Optional parameter
- **WHEN** the command takes `?Api $api = null`
- **THEN** P-DI-04 reports nothing, because the object manager passes the default

#### Scenario: Only factories
- **WHEN** the command takes only `*Factory` parameters and scalars
- **THEN** P-DI-04 reports nothing

#### Scenario: Not a registered command
- **WHEN** a class extends the console `Command` but is not in `CommandList`
- **THEN** P-DI-04 reports nothing
