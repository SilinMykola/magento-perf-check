## MODIFIED Requirements

### Requirement: P-DI-02 constructor does more than assign
P-DI-02 SHALL report, as `warning`, a `__construct` that contains anything other than the allowed statements:
- assignments, to properties or to local variables, of parameters, literals, constants and casts of these;
- the same assignments with a `??` or `?:` fallback whose default is one of these values, a `new` expression, or `ObjectManager::getInstance()->get(...)`;
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
