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

### Requirement: P-COL-03 all attributes selected
P-COL-03 SHALL report, as `info`, `addAttributeToSelect('*')` on an EAV collection. It is off by default.

#### Scenario: Literal star
- **WHEN** code calls `$productCollection->addAttributeToSelect('*')`
- **THEN** P-COL-03 reports the call

#### Scenario: Named attributes
- **WHEN** code calls `addAttributeToSelect('name')` or `addAttributeToSelect(['name', 'price'])`
- **THEN** P-COL-03 reports nothing

#### Scenario: Another class
- **WHEN** a class that is not an EAV collection has `addAttributeToSelect('*')`
- **THEN** P-COL-03 reports nothing

### Requirement: P-COL-04 unpaged collection in a batch job
P-COL-04 SHALL report, as `warning`, a `foreach` over a database collection, or over its `getItems()`, in a class listed in `crontab.xml` or extending the console `Command`, when the same function created the collection with `create()` on another object, no page size or limit was set earlier in the function, and it has no equality filter on a unique key. It is off by default.

#### Scenario: Cron job over a whole collection
- **WHEN** a class in `crontab.xml` iterates `$this->factory->create()->addFieldToFilter('status', 'new')`
- **THEN** P-COL-04 reports the `foreach`

#### Scenario: Paged
- **WHEN** `setPageSize()` is called on the collection before the `foreach`
- **THEN** P-COL-04 reports nothing

#### Scenario: Limit after other select calls
- **WHEN** `$collection->getSelect()->order('entity_id')->limit(10)` comes before the `foreach`
- **THEN** P-COL-04 reports nothing

#### Scenario: Variable assigned by the loop
- **WHEN** an outer `foreach` over `[$collection, $label]` assigns a `$collection` that the function earlier created with `create()`, and an inner `foreach` iterates it
- **THEN** P-COL-04 reports nothing

#### Scenario: Collection from elsewhere
- **WHEN** the collection is a parameter, the result of a method of this class such as `$this->getCollection()`, or of a getter of what the function created, such as `$order->getItemsCollection()` or `$factory->create()->getItemsCollection()`
- **THEN** P-COL-04 reports nothing

#### Scenario: Request code
- **WHEN** a controller iterates a collection it created
- **THEN** P-COL-04 reports nothing
