## MODIFIED Requirements

### Requirement: P-COL-01 count() on a collection
P-COL-01 SHALL report, as `warning`, `count($x)` where `$x` is a database collection that is not loaded anyway in the same function: not loaded before the call (as in P-COL-02), and not loaded after it either (`load()`, `getItems()`, `->count()` or `foreach` on the same variable later in the function). `count()` loads every row, while `getSize()` runs a `COUNT` query.

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
