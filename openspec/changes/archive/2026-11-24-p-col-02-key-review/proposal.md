# Proposal

## Why

A review of the P-COL-02 unique-key change found inputs where the rule stays silent although the query can return many rows, and the key it relies on may not exist:

- A filter value that is an array becomes `IN (...)` in Magento, so `addFieldToFilter('id', $ids)` with a list of ids matches many rows.
- `$this->collection = $factory->create()` after a filter on `$this->collection` keeps the old filter in the visitor; only `$var = ...` dropped it.
- A filter inside an arrow function counts as if it always ran.
- A collection that overrides `_construct()` without calling `parent::_construct()`, for example to set another main table, still gets the parent's `_init()` and its key.
- A setup-script index on a table that a `db_schema.xml` declares counts, although that file describes the table and its keys.
- A constraint disabled with `disabled="1"`, valid for `xs:boolean`, and a table declared with `disabled="true"` keep their keys in the index.
- `getSelect()->from()` and `getSelect()->union()` add rows of another table or query, but did not count as a join.
- `dropIndex($table, 'PRIMARY')` in the same setup script that built the table did not remove its `primary` columns, which were added after all calls.

## What Changes

- `CollectionStateVisitor` keeps, for each equality-filtered column, the value expression; P-COL-02 drops a column whose value type may be an array. The older fixed list of unique columns (`entity_id`, `sku`, ...) is unchanged.
- An assignment to `$this->prop` resets the facts of that collection like an assignment to a variable.
- The body of an arrow function is a conditional branch.
- `from()` and `union()` on `getSelect()` count as a join.
- The main-table lookup stops at the first class that declares `_construct()` unless it calls `parent::_construct()`.
- Setup-script keys do not count on a table that any `db_schema.xml` declares; the project index records declared tables.
- The project index reads `disabled` as `xs:boolean` (`true` or `1`), and drops all keys of a disabled table.
- `dropIndex()` in a setup script also removes the `primary` columns built earlier in that script.
- A cached `null` main table is no longer looked up again on every call.

- The value's type is read where `getFirstItem()` is called, so a filter whose value variable is assigned again before it does not count.

Not covered: a join made inside the collection class (`_initSelect()`, `_renderFiltersBefore()`, `addFilterToMap()`), and an array value for the fixed unique columns and `addIdFilter()`, which set a separate fact without the value; both are listed as known false negatives on the rule page.
