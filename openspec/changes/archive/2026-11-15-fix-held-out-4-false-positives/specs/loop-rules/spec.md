## MODIFIED Requirements

### Requirement: P-LOOP-01 scope
P-LOOP-01 SHALL NOT report:
- The same calls outside loop context, including in the iterable expression of `foreach`.
- `load()` on collections or resource models.
- `get()` on types that are not repositories (for example a config or registry object).
- Receivers whose type PHPStan cannot resolve.
- `load()` on `Magento\Directory\Model\Currency`, which only sets the currency code.
- `get()` or `getById()` on `Magento\Store\Api\StoreRepositoryInterface`, `WebsiteRepositoryInterface` or `GroupRepositoryInterface`, which read cached configuration.
- `get()` or `getById()` on a repository that keeps what it loaded for the rest of the request (`Magento\Catalog\Api\ProductRepositoryInterface`, `CategoryRepositoryInterface`, `ProductAttributeRepositoryInterface`, `Magento\Customer\Api\CustomerRepositoryInterface`, `Magento\Sales\Api\OrderRepositoryInterface`, `Magento\Quote\Api\CartRepositoryInterface`, `Magento\Eav\Api\AttributeRepositoryInterface`), when every argument is a literal, a constant, or a variable that no enclosing loop statement changes: assigns, increments, unsets, iterates, catches, declares `static` or `global`, passes to a function, method or constructor other than `get()`, `getById()` or `load()`, or takes as a closure parameter or by-reference use. Every variable SHALL count as changed in a loop with a variable variable, `extract()` or `parse_str()`, a call of a callable held in a variable, `include`, `eval`, or a `save()`, `delete()` or `deleteById()` call, and in a function that makes a reference. A fourth argument, `ProductRepository`'s `$forceReload`, SHALL be absent or the literal `false`. Such a call queries in the first iteration only.

#### Scenario: Outside a loop
- **WHEN** `$this->productRepository->getById($id)` is called once in a method body without a loop
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Batch fetch before loop
- **WHEN** `foreach ($this->productRepository->getList($criteria)->getItems() as $product)` iterates the result
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Collection load in loop
- **WHEN** `$collection->load()` is called inside a loop
- **THEN** P-LOOP-01 reports nothing (collection rules cover this)

#### Scenario: Non-repository get
- **WHEN** `$this->scopeConfig->getValue(...)` or `$this->request->get('id')` is called inside a loop
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Unknown type
- **WHEN** the receiver's type is `mixed`
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Load that runs no query
- **WHEN** `$currency->load($code)` or `$this->storeRepository->getById($id)` is called inside a loop
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Same id in every iteration
- **WHEN** `$this->productRepository->getById($id)` is called in a loop over stores and `$id` is a parameter of the method that the loop does not change
- **THEN** P-LOOP-01 reports nothing
- **WHEN** the argument is the loop variable, a variable assigned in the loop, a variable that an enclosing loop changes, or the receiver is a model's `load($id)`
- **THEN** P-LOOP-01 reports the call
