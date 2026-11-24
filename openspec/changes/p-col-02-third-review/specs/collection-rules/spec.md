## MODIFIED Requirements

### Requirement: P-COL-02 getFirstItem without limit
P-COL-02 SHALL report a call to `getFirstItem()` on an object whose type is a database collection (a subtype of `Magento\Framework\Data\Collection\AbstractDb`) when no limit is set. A limit is set when `setPageSize()`, `setPage()` or `getSelect()->limit()` is called on the same collection, either earlier in the same method call chain or in an earlier statement of the same function or method. A limit set on a new collection from `create()`, as in `$this->collectionFactory->create()->setPageSize(1)`, or a comparison of its `getSize()` with `1`, does not count for the collection of a later `$this->collectionFactory->create()`. A comparison of `getSize()` or `getTotalCount()` with `1`, using `===`, `!==`, `==` or `!=`, earlier in the function counts as a limit too. Severity: `error`.

#### Scenario: No limit
- **WHEN** code calls `$collection->addFieldToFilter('sku', $sku)->getFirstItem()` and `$collection` is a product collection
- **THEN** P-COL-02 reports an error on that line

#### Scenario: Limit in chain
- **WHEN** code calls `$collection->setPageSize(1)->getFirstItem()`
- **THEN** P-COL-02 reports nothing

#### Scenario: Limit earlier in method
- **WHEN** `$collection->setPageSize(1);` precedes `$collection->getFirstItem();` in the same method
- **THEN** P-COL-02 reports nothing

#### Scenario: Limit through select
- **WHEN** `$collection->getSelect()->limit(1);` precedes `$collection->getFirstItem();` in the same method
- **THEN** P-COL-02 reports nothing

#### Scenario: Collection from factory
- **WHEN** the receiver is `$this->collectionFactory->create()` and PHPStan resolves its return type to a database collection
- **THEN** P-COL-02 reports the call when no limit is set in the chain

#### Scenario: Guard on the size
- **WHEN** `if ($collection->getSize() === 1)` guards `$collection->getFirstItem()`
- **THEN** P-COL-02 reports nothing

#### Scenario: Guard on any size
- **WHEN** `if ($collection->getSize() > 0)` guards `$collection->getFirstItem()`
- **THEN** P-COL-02 reports an error

#### Scenario: Limit on another new collection
- **WHEN** `$this->collectionFactory->create()->setPageSize(1)->getFirstItem();` precedes `$this->collectionFactory->create()->getFirstItem();`
- **THEN** P-COL-02 reports an error on the second call

#### Scenario: Size of another new collection
- **WHEN** `if ($this->collectionFactory->create()->getSize() === 1) {}` precedes `$this->collectionFactory->create()->getFirstItem();`
- **THEN** P-COL-02 reports an error on `getFirstItem()`

### Requirement: P-COL-02 already loaded collection
P-COL-02 SHALL NOT report `getFirstItem()` when the same collection was already loaded earlier in the function: `load()`, `getItems()`, `count()`, `count($collection)`, or iteration with `foreach`. A load of a new collection from `create()`, including a `foreach` over it or `count()` of it, does not count for the collection of a later `create()`, and neither does a use of its rows anywhere in the function.

#### Scenario: Loaded by getItems
- **WHEN** `$items = $collection->getItems();` precedes `$collection->getFirstItem();`
- **THEN** P-COL-02 reports nothing

#### Scenario: Load of another new collection
- **WHEN** `$this->collectionFactory->create()->addIdFilter(5)->getFirstItem();` precedes `$this->collectionFactory->create()->getFirstItem();`
- **THEN** P-COL-02 reports an error on the second call

#### Scenario: Another new collection iterated or counted
- **WHEN** `foreach ($this->collectionFactory->create() as $row) {}` or `count($this->collectionFactory->create());` precedes `$this->collectionFactory->create()->getFirstItem();`
- **THEN** P-COL-02 reports an error on `getFirstItem()`

#### Scenario: Rows of another new collection
- **WHEN** a function calls `$this->collectionFactory->create()->getItems()` and `$this->collectionFactory->create()->addFieldToFilter('name', $name)->getFirstItem()`
- **THEN** P-COL-02 reports an error on `getFirstItem()`

### Requirement: P-COL-02 unique key filter
P-COL-02 SHALL NOT report `getFirstItem()` when the chain, or an earlier statement on the same collection, filters for equality on a unique key: `addIdFilter()`, `setIdFilter()`, or `addFieldToFilter()`/`addAttributeToFilter()` on `entity_id`, `sku`, `increment_id` or `username`. A table alias prefix such as `main_table.` is allowed; the value must be a scalar or an `eq` condition, and its type where `getFirstItem()` is called must not be an array or possibly one, since Magento turns a plain list into `IN (...)`. A value of unknown type (`mixed`) counts as one value, and so does any value inside `['eq' => ...]`, which Magento renders as `= ...`. A value PHPStan cannot type, such as a call on a class it cannot find, counts as one value too. Assigning a new value to a variable drops the filters whose value reads it: with `=`, `=&`, a compound operator such as `+=`, list destructuring, or as the key or value of a `foreach`. A write to an element, as in `$map[$sku] = ...` or `$this->hits[$sku] ??= 0`, gives a new value to the array only, not to the key. A filter set in a conditional branch, as listed under P-COL-02 scope for a key filter, counts only for a `getFirstItem()` in that branch or one nested in it. A filter on a new collection from `create()`, as in `$this->collectionFactory->create()->addIdFilter(5)`, does not count for the collection of a later `create()`. When several such filters are set, one whose value cannot be an array is enough.

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

### Requirement: P-COL-01 count() on a collection
P-COL-01 SHALL report, as `warning`, `count($x)` where `$x` is a database collection that is not loaded anyway in the same function: not loaded before the call (as in P-COL-02), and not loaded after it either (`load()`, `getItems()`, `getColumnValues()`, `->count()` or `foreach` on the same variable later in the function). A load or a use of the rows of a new collection from `create()` says nothing about the collection of another `create()`. `count()` loads every row, while `getSize()` runs a `COUNT` query. It SHALL NOT report a collection that lives on the object: a `$this` property, or the result of a method called directly on `$this`. These are usually memoized and their rows are read elsewhere.

#### Scenario: Count before loading
- **WHEN** `if (count($collection) > 0)` is the first and only use of a product collection in a method
- **THEN** P-COL-01 reports a warning

#### Scenario: Count after iteration
- **WHEN** `count($collection)` follows a `foreach ($collection as $item)` in the same method
- **THEN** P-COL-01 reports nothing

#### Scenario: Count of an array
- **WHEN** `count($items)` is called on an array
- **THEN** P-COL-01 reports nothing

#### Scenario: Collection iterated after the count
- **WHEN** `if (count($collection))` is followed by `foreach ($collection as $item)` in the same method
- **THEN** P-COL-01 reports nothing, because the rows are loaded anyway

#### Scenario: Count in a load hook
- **WHEN** `count($this)` is called in a collection's `_afterLoad()`
- **THEN** P-COL-01 reports nothing, because the rows are loaded there

#### Scenario: Memoized getter
- **WHEN** `count($this->getRows())` or `count($this->rows)` is called on a collection
- **THEN** P-COL-01 reports nothing

#### Scenario: Getter of another object
- **WHEN** `count($other->getRows())` is called on a collection and its rows are not used in the function
- **THEN** P-COL-01 reports a warning

#### Scenario: Count of a new collection after another one
- **WHEN** a function calls `$this->collectionFactory->create()->getItems()` or `count($this->collectionFactory->create())`, and then `count($this->collectionFactory->create())`
- **THEN** P-COL-01 reports the later `count()`
