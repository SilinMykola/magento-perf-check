# Spec Delta

## Purpose

N+1 rules: catch database work repeated once per iteration of a loop where one batched query would do.

## ADDED Requirements

### Requirement: P-LOOP-01 model load or repository get in a loop
P-LOOP-01 SHALL report, in loop context, either:
- A call to `load()` on an object whose type is a subtype of `Magento\Framework\Model\AbstractModel`.
- A call to `get()` or `getById()` on an object whose type is an interface, or implements an interface, whose short name ends with `RepositoryInterface`.

Severity: `error`. The message SHALL name the class and the method.

#### Scenario: Model load in foreach
- **WHEN** `$product->load($id)` is called inside a `foreach` body and `$product` is a `Magento\Catalog\Model\Product`
- **THEN** P-LOOP-01 reports an error

#### Scenario: Repository get in for
- **WHEN** `$this->productRepository->getById($id)` is called inside a `for` body and the property type is `Magento\Catalog\Api\ProductRepositoryInterface`
- **THEN** P-LOOP-01 reports an error

#### Scenario: Repository get inside array_map
- **WHEN** `$this->customerRepository->get($email)` is called inside a closure passed to `array_map`
- **THEN** P-LOOP-01 reports an error

### Requirement: P-LOOP-01 scope
P-LOOP-01 SHALL NOT report:
- The same calls outside loop context, including in the iterable expression of `foreach`.
- `load()` on collections or resource models.
- `get()` on types that are not repositories (for example a config or registry object).
- Receivers whose type PHPStan cannot resolve.

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
