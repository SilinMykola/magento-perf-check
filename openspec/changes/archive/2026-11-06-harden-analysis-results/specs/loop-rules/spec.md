## MODIFIED Requirements

### Requirement: P-LOOP-01 scope
P-LOOP-01 SHALL NOT report:
- The same calls outside loop context, including in the iterable expression of `foreach`.
- `load()` on collections or resource models.
- `get()` on types that are not repositories (for example a config or registry object).
- Receivers whose type PHPStan cannot resolve.
- `load()` on `Magento\Directory\Model\Currency`, which only sets the currency code.
- `get()` or `getById()` on `Magento\Store\Api\StoreRepositoryInterface`, `WebsiteRepositoryInterface` or `GroupRepositoryInterface`, which read cached configuration.

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
