# Spec Delta

## MODIFIED Requirements

### Requirement: P-COL-02 scope
P-COL-02 SHALL NOT report `getFirstItem()` in these cases:
- The collection is not a database collection (in-memory `Magento\Framework\Data\Collection`).
- PHPStan cannot resolve the receiver's type.
- The class is unrelated and merely has a method named `getFirstItem`.
- The same collection was already loaded earlier in the function: `load()`, `getItems()`, `count()`, `count($collection)`, or iteration with `foreach`.
- The chain or an earlier statement on the same collection filters for equality on a unique key: `addIdFilter()`, or `addFieldToFilter()`/`addAttributeToFilter()` on `entity_id`, `sku`, `increment_id` or `username`. A table alias prefix such as `main_table.` is allowed; the value must be a scalar or an `eq` condition.

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

## ADDED Requirements

### Requirement: P-COL-01 count() on a collection
P-COL-01 SHALL report, as `warning`, `count($x)` where `$x` is a database collection that was not already loaded earlier in the same function. "Already loaded" has the same meaning as in P-COL-02. `count()` loads every row, while `getSize()` runs a `COUNT` query.

#### Scenario: Count before loading
- **WHEN** `if (count($collection) > 0)` is the first use of a product collection in a method
- **THEN** P-COL-01 reports a warning

#### Scenario: Count after iteration
- **WHEN** `count($collection)` follows a `foreach ($collection as $item)` in the same method
- **THEN** P-COL-01 reports nothing

#### Scenario: Count of an array
- **WHEN** `count($items)` is called on an array
- **THEN** P-COL-01 reports nothing
