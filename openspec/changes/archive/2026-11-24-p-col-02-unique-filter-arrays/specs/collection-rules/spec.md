## MODIFIED Requirements

### Requirement: P-COL-02 unique key filter
P-COL-02 SHALL NOT report `getFirstItem()` when the chain, or an earlier statement on the same collection, filters for equality on a unique key: `addIdFilter()`, `setIdFilter()`, or `addFieldToFilter()`/`addAttributeToFilter()` on `entity_id`, `sku`, `increment_id` or `username`. A table alias prefix such as `main_table.` is allowed; the value must be a scalar or an `eq` condition, and its type where `getFirstItem()` is called must not be an array or possibly one, since Magento turns a plain list into `IN (...)`. A value of unknown type (`mixed`) counts as one value, and so does any value inside `['eq' => ...]`, which Magento renders as `= ...`. Assigning a new value to a variable drops the filters whose value reads it. When several such filters are set, one whose value cannot be an array is enough.

#### Scenario: Increment ID earlier
- **WHEN** `$collection->addFieldToFilter('increment_id', $incrementId);` precedes `$collection->getFirstItem();`
- **THEN** P-COL-02 reports nothing

#### Scenario: Fixed unique column with a list
- **WHEN** code calls `$collection->addFieldToFilter('entity_id', $ids)->getFirstItem()`, or `$collection->addFieldToFilter('sku', $skus)` before `$collection->getFirstItem()`, with an `array` value
- **THEN** P-COL-02 reports an error

#### Scenario: Id filter with a list
- **WHEN** code calls `$collection->addIdFilter($ids)->getFirstItem()` with an `array $ids` or an `int|array $ids`
- **THEN** P-COL-02 reports an error

#### Scenario: Fixed unique column with a single value
- **WHEN** code calls `$collection->addFieldToFilter('entity_id', $id)->getFirstItem()` or `$collection->addFieldToFilter('entity_id', ['eq' => $id])->getFirstItem()` with an `int $id`, or with an untyped `$id`, or `$collection = $factory->create()->addFieldToFilter('entity_id', $id);` before `$collection->getFirstItem();`
- **THEN** P-COL-02 reports nothing

#### Scenario: Fixed unique column with a list inside eq
- **WHEN** code calls `$collection->addAttributeToFilter('sku', ['eq' => $skus])->getFirstItem()` with an `array $skus`
- **THEN** P-COL-02 reports nothing

#### Scenario: One of two filters with a single value
- **WHEN** code calls `$collection->addFieldToFilter('entity_id', $id)->addFieldToFilter('sku', $skus)->getFirstItem()` with an `int $id` and an `array $skus`
- **THEN** P-COL-02 reports nothing

#### Scenario: Fixed unique column value reassigned
- **WHEN** code calls `$collection->addIdFilter($ids); $ids = 5; $collection->getFirstItem();` with an `array $ids`
- **THEN** P-COL-02 reports an error
