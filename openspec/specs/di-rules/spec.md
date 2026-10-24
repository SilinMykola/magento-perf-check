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
P-DI-01 SHALL NOT report generated classes (names ending in `\Proxy`, `\Interceptor` or `Factory`) or classes that are themselves session managers.

#### Scenario: Session subclass
- **WHEN** `Acme\Promo\Model\Session extends SessionManager` passes its own session dependencies to `parent::__construct`
- **THEN** P-DI-01 reports nothing for that class

### Requirement: P-DI-02 constructor does more than assign
P-DI-02 SHALL report, as `warning`, a `__construct` that contains anything other than the allowed statements:
- assignments, to properties or to local variables, of parameters, literals, constants and casts of these;
- the same assignments with a `??` or `?:` fallback whose default is one of these values, a `new` expression, or `ObjectManager::getInstance()->get(...)`;
- `parent::__construct(...)`;
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

### Requirement: P-DI-03 event dispatched in constructor
P-DI-03 SHALL report, as `error`, a call to `dispatch()` on `Magento\Framework\Event\ManagerInterface` inside `__construct`.

#### Scenario: Dispatch in constructor
- **WHEN** a constructor calls `$this->eventManager->dispatch('acme_init')`
- **THEN** P-DI-03 reports an error

### Requirement: P-DI-06 direct ObjectManager use
P-DI-06 SHALL report, as `info`, `ObjectManager::getInstance()` in PHP classes, except where Adobe documents it as allowed:
- the backward-compatible constructor fallback (`$param ?: ObjectManager::getInstance()->get(...)`, `??`);
- classes whose name ends in `Factory` or `\Proxy`;
- `__wakeup()` and `__sleep()`.

Calibration lowered it from `warning`: most correct findings are lazy BC getters with low impact.

#### Scenario: Service locator in a method
- **WHEN** a model method calls `ObjectManager::getInstance()->get(Helper::class)`
- **THEN** P-DI-06 reports an info finding

#### Scenario: Constructor fallback
- **WHEN** a constructor uses the `?:` fallback
- **THEN** P-DI-06 reports nothing

#### Scenario: Factory class
- **WHEN** `Acme\Promo\Model\ItemFactory::create()` uses the ObjectManager
- **THEN** P-DI-06 reports nothing

### Requirement: P-DI-02 skips code outside the object manager
P-DI-02 SHALL NOT report classes in Composer packages whose type is `composer-plugin`, nor classes in `magento/magento-coding-standard`. The object manager never builds these, so DI construction rules do not apply.

#### Scenario: Composer plugin
- **WHEN** a constructor in a package of type `composer-plugin` calls a method
- **THEN** P-DI-02 reports nothing
