## ADDED Requirements

### Requirement: P-LOOP-12 method that queries, called in a loop
P-LOOP-12 SHALL report a method call (`->`, `?->` or `::`) in loop context when the called method is declared in the analyzed code and contains a call that P-LOOP-01, P-LOOP-09 or P-LOOP-10 would report in that loop. The message SHALL name the called method and that query, with the query's line and file name. The rule SHALL be experimental, with severity `warning`.

#### Scenario: Helper that loads a product
- **WHEN** a `foreach` body calls `$this->getProductName($id)` and `getProductName()` calls `$this->productRepository->getById($id)`
- **THEN** P-LOOP-12 reports the call in the loop and names `getById()` with its line

#### Scenario: Injected service
- **WHEN** a loop calls `$this->stockReader->qty($sku)` on a concrete class of the analyzed code whose `qty()` loads a model
- **THEN** P-LOOP-12 reports the call

### Requirement: P-LOOP-12 scope
P-LOOP-12 SHALL NOT report:
- a call outside loop context;
- a call whose method has no query of its own, including one that queries only through another method;
- a call resolved to an interface or abstract method, or to a method outside the analyzed paths;
- a call that P-LOOP-01, P-LOOP-09 or P-LOOP-10 reports itself.

#### Scenario: Through an interface
- **WHEN** the loop calls a method on a property typed with an interface of the analyzed code
- **THEN** P-LOOP-12 reports nothing

### Requirement: P-LOOP-12 queries that do not repeat per call
P-LOOP-12 SHALL NOT count a query of the called method when:
- the method keeps that query's result for later calls (the value stored in an element of a property or with `??=` on a property, a query inside an `if` that reads and assigns a property, or the value assigned to a property after an `if` on it that returns early), unless the receiver may be a new object in each iteration: the foreach value, something reached from it, `new` or a `create()` result;
- it is a `getList()` and the loop is a `while`, `do` or `for` loop;
- it is in a closure that the method returns or stores.

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
