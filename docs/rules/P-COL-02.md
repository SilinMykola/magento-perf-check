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
- **A unique-key filter:** `addIdFilter()`, `setIdFilter()`, or `addFieldToFilter()`/`addAttributeToFilter()` on `entity_id`, `sku`, `increment_id` or `username` (optionally with a table alias such as `main_table.`), with a plain value or an `['eq' => ...]` condition. At most one row matches.

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

- A filter on another column that is unique in your schema (for example a custom `code` column) is not recognized. Add `setPageSize(1)`, or suppress with a reason.
- A limit or load done through a different variable that refers to the same collection is not seen.
- A limit set inside a branch counts as set even when the branch is not taken. This trade-off favours fewer false positives.

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
