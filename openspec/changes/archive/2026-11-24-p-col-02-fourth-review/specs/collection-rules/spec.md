## MODIFIED Requirements

### Requirement: P-COL-02 unique key filter
P-COL-02 SHALL NOT report `getFirstItem()` when the chain, or an earlier statement on the same collection, filters for equality on a unique key: `addIdFilter()`, `setIdFilter()`, or `addFieldToFilter()`/`addAttributeToFilter()` on `entity_id`, `sku`, `increment_id` or `username`. A table alias prefix such as `main_table.` is allowed; the value must be a scalar or an `eq` condition, and its type where `getFirstItem()` is called must not be an array or possibly one, since Magento turns a plain list into `IN (...)`. A value of unknown type (`mixed`) counts as one value, and so does any value inside `['eq' => ...]`, which Magento renders as `= ...`. A value PHPStan cannot type, such as a call on a class it cannot find, counts as one value too. Assigning a new value to a variable drops the filters whose value reads it: with `=`, `=&`, a compound operator such as `+=`, list destructuring, or as the key or value of a `foreach`. A write to an element, as in `$map[$sku] = ...` or `$this->hits[$sku] ??= 0`, gives a new value to the array only, not to the key; a write to a property of another object or an element of one, as in `$o->ids = ...` or `$o->items[$k] = ...`, gives a new value to the variable it goes through, `$o`. A filter set in a conditional branch, as listed under P-COL-02 scope for a key filter, counts only for a `getFirstItem()` in that branch or one nested in it. A filter on a new collection from `create()`, as in `$this->collectionFactory->create()->addIdFilter(5)`, does not count for the collection of a later `create()`. When several such filters are set, one whose value cannot be an array is enough.

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

#### Scenario: Fixed unique column filter only in a branch
- **WHEN** code calls `if ($sku) { $collection->addFieldToFilter('sku', $sku); }` or `if ($id !== null) { $collection->addIdFilter($id); }` and then `$collection->getFirstItem()` after the `if`, or `array_map(fn ($id) => $collection->addFieldToFilter('entity_id', $id), $ids); $collection->getFirstItem();`
- **THEN** P-COL-02 reports an error

#### Scenario: Fixed unique column filter in the same branch
- **WHEN** both `$collection->addIdFilter($id)` and `$collection->getFirstItem()` are inside the same `if` body
- **THEN** P-COL-02 reports nothing

#### Scenario: Id filter on another new collection
- **WHEN** `$this->collectionFactory->create()->addIdFilter(5)->getSize();` precedes `$this->collectionFactory->create()->getFirstItem();`
- **THEN** P-COL-02 reports an error on the second call

#### Scenario: Fixed unique column with a value PHPStan cannot type
- **WHEN** code calls `$collection->addIdFilter($transfer->getWarehouseId())->getFirstItem()` and the class of `$transfer` cannot be found
- **THEN** P-COL-02 reports nothing

#### Scenario: Fixed unique column value reassigned in other ways
- **WHEN** code calls `$collection->addIdFilter($ids)` with an `array $ids` and then, before `$collection->getFirstItem()`, `$ids = &$id;`, `[$ids] = [5];`, `foreach ([5] as $ids) {}` or `foreach ([5 => true] as $ids => $_) {}`; or filters on `$values[$n]` and then `$n += 1;`
- **THEN** P-COL-02 reports an error

#### Scenario: Fixed unique column value used as a key
- **WHEN** code calls `$collection->addFieldToFilter('sku', $sku); $this->hits[$sku] ??= 0; $collection->getFirstItem();`, or the same with `$count[$sku] += 1;`, and `$sku` is a `string`
- **THEN** P-COL-02 reports nothing

#### Scenario: Fixed unique column value written through an element
- **WHEN** code calls `$collection->addIdFilter($m['id']); $m['id'] = 5; $collection->getFirstItem();` with an `array{id: list<int>} $m`
- **THEN** P-COL-02 reports an error

#### Scenario: Fixed unique column value written through another object
- **WHEN** code calls `$collection->addIdFilter($o->ids)` and then `$o->ids = 5;`, `$o->ids[0] = 5;`, `[$o->ids] = [5];` or `foreach ([5] as $o->ids) {}` before `$collection->getFirstItem()`
- **THEN** the filter does not count for that `getFirstItem()`

#### Scenario: Fixed unique column value after skipped or nested destructuring
- **WHEN** code calls `$collection->addIdFilter($ids)` with a `list<int> $ids`, then `[, $ids] = [0, 5];` or `[[$ids]] = [[5]];`, then `$collection->getFirstItem()`
- **THEN** P-COL-02 reports an error
