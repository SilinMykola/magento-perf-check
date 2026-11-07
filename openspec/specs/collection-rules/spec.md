# collection-rules Specification

## Purpose
Rules on Magento collections and database fetches that catch code loading far more rows than it uses.

## Requirements

### Requirement: P-COL-02 getFirstItem without limit
P-COL-02 SHALL report a call to `getFirstItem()` on an object whose type is a database collection (a subtype of `Magento\Framework\Data\Collection\AbstractDb`) when no limit is set. A limit is set when `setPageSize()`, `setPage()` or `getSelect()->limit()` is called on the same collection, either earlier in the same method call chain or in an earlier statement of the same function or method. A comparison of `getSize()` or `getTotalCount()` with `1`, using `===`, `!==`, `==` or `!=`, earlier in the function counts as a limit too. Severity: `error`.

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

### Requirement: P-COL-02 scope
P-COL-02 SHALL NOT report `getFirstItem()` on a receiver that is not a database collection: an in-memory `Magento\Framework\Data\Collection`, a receiver whose type PHPStan cannot resolve, or an unrelated class that merely has a method named `getFirstItem`.

#### Scenario: In-memory collection
- **WHEN** `getFirstItem()` is called on a `Magento\Framework\Data\Collection` that is not an `AbstractDb`
- **THEN** P-COL-02 reports nothing

#### Scenario: Unknown type
- **WHEN** the receiver's type is `mixed` or an unknown class
- **THEN** P-COL-02 reports nothing

#### Scenario: Unrelated class
- **WHEN** a class that is not a collection declares and calls its own `getFirstItem()`
- **THEN** P-COL-02 reports nothing

#### Scenario: Already loaded by count
- **WHEN** `$count = $collection->count();` precedes `$collection->getFirstItem();` in the same method
- **THEN** P-COL-02 reports nothing

#### Scenario: Already iterated
- **WHEN** `foreach ($collection as $item)` precedes `$collection->getFirstItem();` in the same method
- **THEN** P-COL-02 reports nothing

#### Scenario: Unique key filter
- **WHEN** code calls `$collection->addFieldToFilter('sku', $sku)->getFirstItem()`
- **THEN** P-COL-02 reports nothing

#### Scenario: Non-equality filter on a unique key
- **WHEN** code calls `$collection->addFieldToFilter('sku', ['like' => 'A%'])->getFirstItem()`
- **THEN** P-COL-02 reports an error

#### Scenario: Rows used anyway
- **WHEN** `$collection->getFirstItem()` is followed in the same function by `$collection->getColumnValues('entity_id')` or `foreach ($collection as $item)`
- **THEN** P-COL-02 reports nothing

### Requirement: P-COL-01 count() on a collection
P-COL-01 SHALL report, as `warning`, `count($x)` where `$x` is a database collection that is not loaded anyway in the same function: not loaded before the call (as in P-COL-02), and not loaded after it either (`load()`, `getItems()`, `getColumnValues()`, `->count()` or `foreach` on the same variable later in the function). `count()` loads every row, while `getSize()` runs a `COUNT` query. It SHALL NOT report a collection that lives on the object: a `$this` property, or the result of a method called directly on `$this`. These are usually memoized and their rows are read elsewhere.

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

### Requirement: P-COL-02 already loaded collection
P-COL-02 SHALL NOT report `getFirstItem()` when the same collection was already loaded earlier in the function: `load()`, `getItems()`, `count()`, `count($collection)`, or iteration with `foreach`.

#### Scenario: Loaded by getItems
- **WHEN** `$items = $collection->getItems();` precedes `$collection->getFirstItem();`
- **THEN** P-COL-02 reports nothing

### Requirement: P-COL-02 unique key filter
P-COL-02 SHALL NOT report `getFirstItem()` when the chain, or an earlier statement on the same collection, filters for equality on a unique key: `addIdFilter()`, `setIdFilter()`, or `addFieldToFilter()`/`addAttributeToFilter()` on `entity_id`, `sku`, `increment_id` or `username`. A table alias prefix such as `main_table.` is allowed; the value must be a scalar or an `eq` condition.

#### Scenario: Increment ID earlier
- **WHEN** `$collection->addFieldToFilter('increment_id', $incrementId);` precedes `$collection->getFirstItem();`
- **THEN** P-COL-02 reports nothing
