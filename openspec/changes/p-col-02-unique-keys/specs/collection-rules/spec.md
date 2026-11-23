## MODIFIED Requirements

### Requirement: P-COL-02 scope
P-COL-02 SHALL NOT report `getFirstItem()` on a receiver that is not a database collection: an in-memory `Magento\Framework\Data\Collection`, a receiver whose type PHPStan cannot resolve, or an unrelated class that merely has a method named `getFirstItem`. It SHALL NOT report a collection filtered, with an equality on the main table, on every column of a unique or primary key of its main table: a key declared in any `db_schema.xml` of the project, or added with `addIndex()` and `INDEX_TYPE_UNIQUE` or `INDEX_TYPE_PRIMARY` (or a `primary` column of `newTable()`) in `Setup/InstallSchema.php` or `Setup/UpgradeSchema.php` of the module that holds the collection's resource model, unless a later `dropIndex()` there removes it (when the dropped index cannot be tied to its columns, no setup-script key of that table counts). The main table is the first argument of `_init()` in the resource model's `_construct()`, as a string literal or a string constant of that class; the resource model is the second argument of `_init()` in the collection's `_construct()` or a parent's. When either cannot be read this way, the filter does not count.

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

#### Scenario: Filter on every column of a declared unique key
- **WHEN** the collection's `_construct()` calls `_init()` with a resource model whose `_construct()` calls `_init('acme_tag_order', ...)`, a `db_schema.xml` of the project declares a `unique` or `primary` constraint on `acme_tag_order` with the columns `label_id` and `order_id`, and code calls `$collection->addFieldToFilter('label_id', $labelId)->addFieldToFilter('order_id', $orderId)->getFirstItem()`
- **THEN** P-COL-02 reports nothing

#### Scenario: Unique key declared in a setup script
- **WHEN** the unique index on those columns is added in `Setup/InstallSchema.php` or `Setup/UpgradeSchema.php` of the module that holds the resource model, through `addIndex()` with `AdapterInterface::INDEX_TYPE_UNIQUE`, on a table built with `newTable()` or on the connection
- **THEN** P-COL-02 reports nothing

#### Scenario: Filter on part of a unique key
- **WHEN** the unique key is `label_id`, `order_id` and code filters only on `order_id` with an equality
- **THEN** P-COL-02 reports an error

#### Scenario: Non-equality filter on a unique key
- **WHEN** code calls `$collection->addFieldToFilter('sku', ['like' => 'A%'])->getFirstItem()`
- **THEN** P-COL-02 reports an error

#### Scenario: Rows used anyway
- **WHEN** `$collection->getFirstItem()` is followed in the same function by `$collection->getColumnValues('entity_id')` or `foreach ($collection as $item)`
- **THEN** P-COL-02 reports nothing
