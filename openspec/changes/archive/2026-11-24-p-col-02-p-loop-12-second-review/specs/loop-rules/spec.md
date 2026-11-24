## MODIFIED Requirements

### Requirement: P-LOOP-12 queries that do not repeat per call
P-LOOP-12 SHALL NOT count a query of the called method when:
- the method keeps that query's result for later calls (the value stored in an element of a property or with `??=` on a property, a query inside an `if` that reads and assigns a property, or the value assigned to a property after an `if` on it that returns early), unless the receiver may be a new object in each iteration: the foreach value, something reached from it, `new` or a `create()` result;
- it is a `getList()` and the loop is a `while`, `do` or `for` loop;
- it is in a closure that the method returns or stores;
- it is in the fallback of `$a ?: $b` or `$a ?? $b`, which runs only when `$a` is empty. A query on the left of `?:` or `??`, or in a branch of a full ternary, SHALL still count. A query in the fallback SHALL still count when `$a` reads a property, or an element of one, that the class declares `private` and never fills: no default other than `[]` or `null`, no assignment of another value to the property in any method of the class, no `++` or `--` on it, no assignment to an element of it, and no trait used by the class, whose methods may fill it. The property is read as `$this->name`, `self::$name`, `static::$name` or through the class's own name; a static property of another class with the same name is not it.

#### Scenario: Cached per key
- **WHEN** the called method returns `$this->products[$id]` and loads the product only when that element is not set
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Paging loop
- **WHEN** a `while` loop calls a method whose only query is a repository `getList()`
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Lazy getter
- **WHEN** a loop calls `$this->currentProduct($id)`, and `currentProduct()` returns `$this->product ??= $this->productRepository->getById($id)`
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Lazy getter on each item
- **WHEN** a `foreach` over order lines calls `$line->product()`, and `product()` returns `$this->product ??= $this->productRepository->getById($this->productId)`
- **THEN** P-LOOP-12 reports the call

#### Scenario: Fallback query
- **WHEN** a loop calls a method that returns `$product ?: $this->product->load($id)` or `$known[$id] ?? $this->productRepository->getById($id)`
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Query before the fallback
- **WHEN** a loop calls a method that returns `$this->product->load($id) ?: null`
- **THEN** P-LOOP-12 reports the call

#### Scenario: Fallback after a property the class never fills
- **WHEN** a loop calls a method that returns `$this->names[$id] ?? $this->productRepository->getById($id)->getName()`, and the class declares `private array $names = []` and assigns it nowhere, or only `[]` or `null`; or the same with a static property
- **THEN** P-LOOP-12 reports the call

#### Scenario: Fallback after a property the class fills
- **WHEN** a loop calls a method that returns `$this->cache[$id] ?? $this->productRepository->getById($id)`, and another method of the class, or the fallback itself, assigns `$this->cache[...]`, even `[]`; or the same with a static property
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Fallback after a property with a value from its declaration
- **WHEN** the property before the fallback has a non-empty default or is promoted from a constructor parameter
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Fallback after a property another class may fill
- **WHEN** the property before the fallback is declared in a parent class or in a trait, or is `protected` or `public`, or the class uses a trait whose method fills it
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Fallback after a static property of another class
- **WHEN** a loop calls a method that returns `FallbackRegistry::$items[$id] ?? $this->productRepository->getById($id)`, and the class declares its own `private static array $items = []` and never fills it
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Fallback after a property changed with ++ or --
- **WHEN** a loop calls a method that returns `$this->hits ?: $this->productRepository->getById($id)`, the class declares `private ?int $hits = null` and another method runs `$this->hits++` or `--$this->hits`
- **THEN** P-LOOP-12 reports nothing

#### Scenario: Fallback after a property named through the class
- **WHEN** a loop calls a method that returns `CountedFallbackReader::$named[$id] ?? $this->productRepository->getById($id)` inside `CountedFallbackReader`, which never fills `$named`
- **THEN** P-LOOP-12 reports the call

#### Scenario: Fallback after a property filled through the class name
- **WHEN** a loop calls a method that returns `self::$own[$id] ?? $this->productRepository->getById($id)`, and another method assigns `CountedFallbackReader::$own[...]` inside `CountedFallbackReader`
- **THEN** P-LOOP-12 reports nothing
