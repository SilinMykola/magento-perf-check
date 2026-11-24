# P-COL-02: `getFirstItem()` without `setPageSize()`

| | |
|---|---|
| Severity | `error` |
| Status | experimental, off by default: precision 24 % in [calibration](../calibration.md). Enable it with `enabled: true` in `magento-perf-check.neon`; with PHPStan directly, include `extension-experimental.neon` |
| Analyzer | PHPStan, identifier `magentoPerf.getFirstItemWithoutLimit` |

## What it catches

A `getFirstItem()` call on a database collection (a subtype of `Magento\Framework\Data\Collection\AbstractDb`) that may load many rows. The rule stays silent when any of these is found on the same collection, earlier in the call chain or in an earlier statement of the same function (same variable or `$this` property):

- **A limit:** `setPageSize()`, `setPage()`, `getSelect()->limit()` or `getSelect()->limitPage()`, or a comparison of `getSize()` or `getTotalCount()` with `1`. `setCurPage()` alone does not count: without a page size it adds no `LIMIT`.
- **An earlier load:** `load()`, `getItems()`, `count()`, `getFirstItem()` or a `foreach` over the collection. The items are already in memory, so `getFirstItem()` runs no query.
- **Rows used anyway:** the same function, before or after the call, iterates the collection or calls `load()`, `getItems()`, `getLastItem()` or `getColumnValues()` on it. Adding a limit would then drop rows the code needs.
- **A unique-key filter:** `addIdFilter()`, `setIdFilter()`, or `addFieldToFilter()`/`addAttributeToFilter()` on `entity_id`, `sku`, `increment_id` or `username` (optionally with a table alias such as `main_table.`), with a plain value or an `['eq' => ...]` condition. At most one row matches.
- **A filter on a declared unique key:** equality filters on every column of a primary or unique key of the collection's main table, composite keys included, with no table alias or `main_table.`. A filter value whose type may be an array does not count, since Magento turns a list into `IN (...)`. The key is read from any `db_schema.xml` of the project (a constraint or table declared `disabled="true"` or `disabled="1"` does not count), or, for a table that no `db_schema.xml` declares, from `addIndex()` with `INDEX_TYPE_UNIQUE` or `INDEX_TYPE_PRIMARY` and `primary` columns of `newTable()` in `Setup/InstallSchema.php` and `Setup/UpgradeSchema.php` of the module that holds the resource model. The main table is the first argument of `_init()` in the resource model's `_construct()` (a string or a string constant of that class); the resource model is the second argument of `_init()` in the collection's `_construct()`, or a parent's when the collection has no `_construct()` of its own or its `_construct()` calls `parent::_construct()` as a statement of its own, outside any branch. A filter whose value variable is assigned again before `getFirstItem()` does not count. A filter set in a branch (including an arrow function) counts only for a `getFirstItem()` in the same branch or one nested in it, since the branch may not run. A new value assigned to the variable or `$this` property drops its earlier filters. A collection that joins another table (`join*()` on it or on its `getSelect()`, such as `joinStates()`, or `from()` or `union()` on its `getSelect()`) is still reported, since a key of its main table no longer identifies one row. A filter on part of a key is still reported.

## Why it hurts performance

`getFirstItem()` does not add a `LIMIT 1`. It calls `load()`, which loads every matching row and hydrates a model for each one, then returns the first. On a filter that matches thousands of rows, such as all products of an attribute set or all orders of a store, a lookup meant for one row reads and builds thousands of objects.

## Example

Bad:

```php
$product = $this->productCollectionFactory->create()
    ->addAttributeToFilter('sku', ['like' => $prefix . '%'])
    ->getFirstItem();
```

Good:

```php
$product = $this->productCollectionFactory->create()
    ->addAttributeToFilter('sku', ['like' => $prefix . '%'])
    ->setPageSize(1)
    ->getFirstItem();
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.getFirstItemWithoutLimit (filtered by primary key, at most one row)
$item = $collection->getFirstItem();
```

## Known false positives

- A unique column that the rule cannot tie to the collection is not recognized: a main table set by a variable or a constant of another class, a key added to the table by another module's setup script, or a key enforced only by the module's own logic. Add `setPageSize(1)`, or suppress with a reason.
- A filter on all but one column of a unique key loads only the few rows that differ in that column, and is still reported.
- A unique index that a setup script drops with `dropIndex()` no longer counts; when the dropped index's name is not a `getIdxName()` call with literal columns, such as `'PRIMARY'`, no unique key of that table from the setup scripts counts.
- A key that a setup script adds to a table that a `db_schema.xml` declares does not count: the rule takes `db_schema.xml` as the description of the table.
- A filter value typed as `mixed` or as a union with `array` does not count, even when it holds one value at runtime.
- A change to the `_init()` call of a collection or its resource model does not make cached results of the file that calls `getFirstItem()` out of date; a change to a module's `db_schema.xml` or setup scripts does.
- An aggregate query filtered on one id and grouped by it returns one row, and is still reported.
- A limit or load done through a different variable that refers to the same collection is not seen.
- A limit set inside a branch counts as set even when the branch is not taken. This trade-off favours fewer false positives.

## Known false negatives

- A collection class that joins another table inside itself, in `_initSelect()`, `_renderFiltersBefore()` or through `addFilterToMap()`, is not seen as joined: a filter on a key of its main table still counts, although the joined rows may repeat it.
- A filter value that is a variable counts as one value for the fixed unique columns (`entity_id`, `sku`, `increment_id`, `username`) and for `addIdFilter()`, even when it holds a list at runtime.

Since M1 the rule recognizes earlier loads and unique-key filters. These were the false positives found in the M0 core run (`docs/runs/m0-core.md`).

The rule needs the receiver's type. A collection created through a generated factory is typed when the analyzed project has `generated/code`, or when bitexpert/phpstan-magento is installed (see [ADR 2](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0002-bitexpert-phpstan-magento-as-suggestion.md)). Otherwise the call is skipped, not reported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Collection.php#L305-L315](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L305-L315) | B | `getFirstItem()` calls `load()` and returns the first loaded item; it adds no limit. |
| [AbstractDb.php#L756-L763](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763) | B | `_renderLimit()` applies `limitPage()` only when a page size is set. |
| [Collection.php#L84](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L84), [#L578-L582](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L578-L582) | B | The page size defaults to `false`; `setCurPage()` sets only the page number. |
| [EAV AbstractCollection.php#L1213-L1215](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1213-L1215) | B | EAV collections apply the limit under the same condition. |
| ECG `GetFirstItemSniff` | C | The same check in a token-based standard. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
