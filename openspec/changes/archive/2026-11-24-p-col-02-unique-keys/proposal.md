# Proposal

## Why

P-COL-02 knows only a fixed list of unique columns (`entity_id`, `sku`, `increment_id`, `username`). On three private Magento 2.1 and 2.2 stores, 7 of 10 graded P-COL-02 findings were false, and 4 of them filtered a collection on every column of a unique index of its table, mostly a composite one, so at most one row loads. The follow-ups of round 6 list the same pattern for `db_schema.xml`. Those stores predate declarative schema: their unique indexes are added in `Setup/InstallSchema.php` and `Setup/UpgradeSchema.php`.

## What Changes

- The project index records the columns of every `primary` and `unique` constraint per table from `db_schema.xml`, composite keys included.
- `CollectionStateVisitor` records the columns a collection is filtered on with an equality (`addFieldToFilter()`/`addAttributeToFilter()`, no table alias or `main_table.`).
- P-COL-02 resolves the collection's main table through `_init()` of the collection and of its resource model, and stays silent when the filtered columns cover a unique or primary key of that table: from the index, or from `addIndex()` with `INDEX_TYPE_UNIQUE`/`INDEX_TYPE_PRIMARY` and `primary` columns in the setup scripts of the resource model's module.
- A filter on only part of a key, or a non-equality filter, is still reported.

Not covered: an aggregate query filtered on one id and grouped by it (one graded finding), a key that is one column wider than the filter, and design-level uniqueness without an index.
