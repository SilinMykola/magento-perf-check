# Proposal

## Why

A second review of the P-COL-02 unique-key change and the P-LOOP-12 unfilled-property change found inputs where a rule stays silent although the query can return many rows or run on every call, and inputs where P-LOOP-12 reports a fallback that a trait may fill:

- P-COL-02 keeps the value of a filter on a fixed unique column (`entity_id`, `sku`, `increment_id`, `username`) or of `addIdFilter()` without the branch it was set in, so `if ($sku) { $collection->addFieldToFilter('sku', $sku); } return $collection->getFirstItem();` and a filter inside `array_map(fn ($id) => ...)` silence a `getFirstItem()` that may run without them. The filters on columns of a declared key already count only within their branch.
- An undefined variable has PHPStan's error type, which extends `mixed`, so it counted as one value.
- `$this->factory->create()->addIdFilter(5)` kept its value on `$this->factory`, so a later `$this->factory->create()->getFirstItem()` looked filtered. The same chain ending in `getFirstItem()` or with `setPageSize(1)` marked `$this->factory` as loaded or limited.
- A filter value was dropped only after `$a = ...`; `$a = &$b`, `$a += 1`, `[$a] = ...` and a `foreach` key or value give the variable a new value too.
- `return parent::_construct();` in a collection's `_construct()` was not seen as a call of the parent's.
- A `db_schema.xml` that only adds a column to a table created by `Setup/InstallSchema.php` made the rule drop the script's unique keys, although Magento keeps them.
- P-LOOP-12 took a private property as never filled when only a method of a trait used by the class fills it.
- `Registry::$items` was taken for the class's own static `$items`, while `++`/`--` on a property and `OwnClass::$x = ...` did not count as filling it.

## What Changes

- P-COL-02: the value facts of fixed unique columns and `addIdFilter()` carry their branch like the column facts, and `getFirstItem()` reads both through one helper. The yes/no `UNIQUE_FILTER` fact that P-COL-04 reads on a `foreach` is unchanged.
- P-COL-02: a value of PHPStan's error type is not one value.
- `CollectionStateVisitor` records no filter value on the root of a chain that makes a new object (as for column filters), and no limit or load on the root of a chain that calls `create()`.
- `CollectionStateVisitor` drops the filter values that read a variable written by `=&`, a compound assignment, list destructuring or a `foreach` key or value.
- `CollectionUniqueKeys` accepts `return parent::_construct();`, and counts setup-script keys unless a `db_schema.xml` declares the table with a `primary` constraint. `ProjectIndex::declaresTable()`, now unused, is removed; `tableHasPrimaryKey()` answers the question.
- P-LOOP-12: a class that uses any trait counts as filling all its properties; `++` and `--` fill a property; `MemoizingMethodCollector::property()` takes the class, and a static property counts as the class's own only through `self::`, `static::` or the class's name. The collector itself still passes no class: a value kept in a static property of any class outlives the call.

Not covered: a mixed-case table name in a `db_schema.xml` that declares a primary key is looked up in lower case by P-COL-02 and is then not seen as owned, so setup-script keys of that table also count.
