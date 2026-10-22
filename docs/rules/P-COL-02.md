# P-COL-02: `getFirstItem()` without `setPageSize()`

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.getFirstItemWithoutLimit` |

## What it catches

A `getFirstItem()` call on a database collection (a subtype of `Magento\Framework\Data\Collection\AbstractDb`) when no limit is set on that collection. The limit can be set in two places:
- earlier in the same call chain;
- in an earlier statement of the same function, on the same variable or `$this` property.

These calls count as a limit: `setPageSize()`, `getSelect()->limit()` and `getSelect()->limitPage()`. `setCurPage()` alone does not count: without a page size it adds no `LIMIT`.

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

- A filter that matches at most one row (primary key, unique column) loads one row anyway. The rule cannot tell, so add `setPageSize(1)` or suppress with a reason.
- A limit set through a different variable that refers to the same collection is not seen.
- A collection that is already loaded (for example iterated earlier) does not query again.
- A limit set inside a branch counts as set, even when that branch is not taken; this side of the trade-off avoids false positives.

The rule needs the receiver's type. A collection created through a generated factory is typed when the analyzed project has `generated/code`, or when bitexpert/phpstan-magento is installed (see [ADR 2](../adr/0002-bitexpert-phpstan-magento-as-suggestion.md)). Otherwise the call is skipped, not reported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Collection.php#L305-L315](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L305-L315) | B | `getFirstItem()` calls `load()` and returns the first loaded item; it adds no limit. |
| [AbstractDb.php#L756-L763](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763) | B | `_renderLimit()` applies `limitPage()` only when a page size is set. |
| [Collection.php#L84](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L84), [#L578-L582](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L578-L582) | B | The page size defaults to `false`; `setCurPage()` sets only the page number. |
| [EAV AbstractCollection.php#L1213-L1215](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1213-L1215) | B | EAV collections apply the limit under the same condition. |
| ECG `GetFirstItemSniff` | C | The same check in a token-based standard. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
