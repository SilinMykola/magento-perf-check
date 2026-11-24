# Proposal

## Why

P-COL-02 stays silent on `getFirstItem()` after a filter on one of the fixed unique columns (`entity_id`, `sku`, `increment_id`, `username`) or after `addIdFilter()`/`setIdFilter()`. That skip only records that such a filter was made, not its value, so `$collection->addFieldToFilter('entity_id', $ids)->getFirstItem()` with a list of ids, or `addIdFilter($ids)`, is skipped although Magento turns the list into `IN (...)` and the query loads every matching row. The declared-key skip already checks the value's type (a value of unknown type, or any value inside `['eq' => ...]`, counts as one value); the fixed list did not.

## What Changes

- `CollectionStateVisitor` keeps the value of each filter on a fixed unique column or by id, through the same value facts it keeps for equality-filtered columns, and sets them on `getFirstItem()`. A new value assigned to a variable that the value reads drops the filter, as for the declared-key filters.
- P-COL-02 skips the call only when one of these values counts as one value, by the same check as the declared-key filters: a type that cannot be an array, a value of unknown type (`mixed`), or any value inside `['eq' => ...]`, which Magento renders as `= ...`, never as `IN (...)`.
- P-COL-04 still reads the yes/no fact of such a filter on a `foreach`; its behavior does not change.
