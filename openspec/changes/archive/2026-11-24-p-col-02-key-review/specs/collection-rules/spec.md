## MODIFIED Requirements

### Requirement: P-COL-02 scope
P-COL-02 SHALL NOT report `getFirstItem()` on a receiver that is not a database collection: an in-memory `Magento\Framework\Data\Collection`, a receiver whose type PHPStan cannot resolve, or an unrelated class that merely has a method named `getFirstItem`. It SHALL NOT report a collection filtered, with an equality on the main table whose value cannot be an array (Magento turns a plain list into `IN (...)`; a value of type `mixed`, and any value in `['eq' => ...]`, counts as one value), on every column of a unique or primary key of its main table: a key declared in any `db_schema.xml` of the project, or, on a table that no `db_schema.xml` declares, added with `addIndex()` and `INDEX_TYPE_UNIQUE` or `INDEX_TYPE_PRIMARY` (or a `primary` column of `newTable()`) in `Setup/InstallSchema.php` or `Setup/UpgradeSchema.php` of the module that holds the collection's resource model, unless a later `dropIndex()` there removes it (when the dropped index cannot be tied to its columns, no setup-script key of that table counts). The main table is the first argument of `_init()` in the resource model's `_construct()`, as a string literal or a string constant of that class; the resource model is the second argument of `_init()` in the collection's `_construct()`, or a parent's when the collection declares no `_construct()` or its `_construct()` calls `parent::_construct()` as a top-level statement. When either cannot be read this way, the filter does not count. A key filter set in a conditional branch (a body of `if`, `elseif`, `else`, `case`, `catch` or a loop, an operand of `?:`, `??`, `&&` or `||` that may not run, a `match` arm, or the body of an arrow function) counts only for a `getFirstItem()` in that branch or one nested in it. A collection on which the function calls a method whose name starts with `join`, directly or on its `getSelect()`, or `from()` or `union()` on its `getSelect()`, does not count as filtered on a key of its main table. Assigning a new value to the variable or `$this` property that holds the collection drops its earlier filters, and assigning a new value to a variable drops the filters whose value reads it.

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

#### Scenario: Key filter only in a branch
- **WHEN** code calls `if ($id !== null) { $collection->addFieldToFilter('id', $id); }` and then `$collection->getFirstItem()` after the `if`, or in its `else`
- **THEN** P-COL-02 reports an error

#### Scenario: Key filter and getFirstItem in the same branch
- **WHEN** both `$collection->addFieldToFilter('id', $id)` and `$collection->getFirstItem()` are inside the same `if` body, or the filter comes before the `if` that holds `getFirstItem()`
- **THEN** P-COL-02 reports nothing

#### Scenario: Key filter with a joined table
- **WHEN** code filters on every column of a unique key and calls `$collection->getSelect()->joinLeft(...)` or a collection method such as `joinStates()` before `getFirstItem()`
- **THEN** P-COL-02 reports an error

#### Scenario: Filter on part of a unique key
- **WHEN** the unique key is `label_id`, `order_id` and code filters only on `order_id` with an equality
- **THEN** P-COL-02 reports an error

#### Scenario: Non-equality filter on a unique key
- **WHEN** code calls `$collection->addFieldToFilter('sku', ['like' => 'A%'])->getFirstItem()`
- **THEN** P-COL-02 reports an error

#### Scenario: Rows used anyway
- **WHEN** `$collection->getFirstItem()` is followed in the same function by `$collection->getColumnValues('entity_id')` or `foreach ($collection as $item)`
- **THEN** P-COL-02 reports nothing

#### Scenario: Key value that may be a list
- **WHEN** code calls `$collection->addFieldToFilter('id', $ids)->getFirstItem()` and `$ids` is an `array`, or a declared type that may be one such as `int|array`
- **THEN** P-COL-02 reports an error

#### Scenario: Key value that is a single value
- **WHEN** code calls `$collection->addFieldToFilter('id', ['eq' => $id])->getFirstItem()` or `$collection->addAttributeToFilter('id', $id)` before `$collection->getFirstItem()`, and `$id` is an `int`
- **THEN** P-COL-02 reports nothing

#### Scenario: Key value of unknown type or inside eq
- **WHEN** code calls `$collection->addFieldToFilter('id', $id)->getFirstItem()` with an untyped `$id`, or `$collection->addFieldToFilter('id', ['eq' => $ids])->getFirstItem()` with an `array $ids`
- **THEN** P-COL-02 reports nothing

#### Scenario: Key filter in other conditional code
- **WHEN** the key filter is in an operand of `?:`, `??` or `&&`, a `match` arm, a `case`, a `catch`, a loop body or the body of an arrow function, and `getFirstItem()` follows outside it
- **THEN** P-COL-02 reports an error

#### Scenario: Property replaced after the filter
- **WHEN** code calls `$this->collection->addFieldToFilter('id', $id); $this->collection = $this->factory->create(); $this->collection->getFirstItem();`
- **THEN** P-COL-02 reports an error

#### Scenario: Collection with its own _construct()
- **WHEN** the collection extends one whose `_construct()` calls `_init()`, and overrides `_construct()` with one that calls `_setMainTable('acme_tag_archive')` and not `parent::_construct()`
- **THEN** P-COL-02 reports an error for a filter on a key of the parent's main table

#### Scenario: Filter value reassigned
- **WHEN** code calls `$collection->addFieldToFilter('id', $ids); $ids = 5; $collection->getFirstItem();` with an `array $ids`
- **THEN** P-COL-02 reports an error

#### Scenario: Parent _construct() called only in a branch
- **WHEN** the collection's `_construct()` calls `parent::_construct()` only inside an `if` or `else`
- **THEN** P-COL-02 reports an error for a filter on a key of the parent's main table

#### Scenario: Collection that calls the parent _construct()
- **WHEN** the collection's `_construct()` calls `parent::_construct()`, whose `_init()` names the resource model
- **THEN** P-COL-02 reports nothing for a filter on every column of a key of that resource model's main table

#### Scenario: Setup-script key on a declared table
- **WHEN** `Setup/UpgradeSchema.php` adds a unique index on `label_id` of `acme_tag_order`, a `db_schema.xml` declares `acme_tag_order` without that key, and code calls `$collection->addFieldToFilter('label_id', $labelId)->getFirstItem()`
- **THEN** P-COL-02 reports an error

#### Scenario: Another table through from() or union()
- **WHEN** code filters on every column of a unique key and calls `$collection->getSelect()->from(...)` or `$collection->getSelect()->union(...)` before `getFirstItem()`
- **THEN** P-COL-02 reports an error

#### Scenario: Keys dropped in the same setup script
- **WHEN** a setup script builds a table with `newTable()`, a `primary` column `queue_id` and a unique index on `code`, and later in the same script calls `dropIndex($setup->getTable('acme_tag_queue'), 'PRIMARY')`
- **THEN** P-COL-02 reports an error for a filter on `queue_id`, and for a filter on `code`
