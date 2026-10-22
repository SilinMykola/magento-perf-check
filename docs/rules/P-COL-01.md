# P-COL-01: `count()` on a collection instead of `getSize()`

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.collectionCount` |

## What it catches

`count($collection)` or `sizeof($collection)` on a database collection (a subtype of `Magento\Framework\Data\Collection\AbstractDb`) that has not been loaded earlier in the same function.

## Why it hurts performance

`Collection::count()` calls `load()`: it runs the full query, fetches every row and builds a model for each one, only to return how many there are. `getSize()` runs a `SELECT COUNT(*)` and creates no objects. On a catalog or order collection the difference is thousands of hydrated models per call.

## Example

Bad:

```php
$collection = $this->orderCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);
if (count($collection) > 0) {
    return true;
}
```

Good:

```php
$collection = $this->orderCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);
if ($collection->getSize() > 0) {
    return true;
}
```

When the code iterates the collection anyway, counting after the loop is free; the rule does not report that case.

## How to suppress

```php
// @phpstan-ignore magentoPerf.collectionCount (the items are rendered right after this check)
$count = count($collection);
```

## Known false positives

- The collection is loaded later in the same function anyway (for example rendered after the check). `count()` then only moves the load earlier. The rule only recognizes loads that come before the `count()`.
- A collection loaded through another variable or in another method is not recognized as loaded.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Collection.php#L858-L862](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L858-L862) | B | `Collection::count()` calls `load()` before counting the items. |
| [AbstractDb.php#L433-L441](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441) | B | `AbstractDb::getSize()` runs only a COUNT query. |
| ECG `CollectionCountSniff` | C | The same check in a token-based standard. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
