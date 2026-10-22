# P-LOOP-02: collection created and loaded inside a loop

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.loopCollectionLoad` |

## What it catches

Inside a loop, a database collection is created by `create()` and loaded by `load()`, `getItems()`, `getSize()`, `getFirstItem()` or `count()` within the same iteration. The collection can be created in the same call chain, or assigned to a variable inside the loop body. Loop context is the same as for [P-LOOP-01](P-LOOP-01.md): loop bodies, and closures passed to `array_map()`, `array_walk()` or `->walk()`.

## Why it hurts performance

Each iteration builds a new select and sends it to the database. A loop over 200 parent products that loads each product's children runs 200 queries, plus the model hydration for every row. One collection filtered with `in` on all IDs does the same work in one query.

## Example

Bad:

```php
foreach ($parentIds as $parentId) {
    $children = $this->collectionFactory->create()
        ->addFieldToFilter('parent_id', $parentId)
        ->getItems();
    $result[$parentId] = $children;
}
```

Good:

```php
$collection = $this->collectionFactory->create()
    ->addFieldToFilter('parent_id', ['in' => $parentIds]);
foreach ($collection as $child) {
    $result[$child->getParentId()][] = $child;
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopCollectionLoad (at most two store views)
$items = $this->collectionFactory->create()->setStoreId($storeId)->getItems();
```

## Known false positives

- Loops with a small fixed bound, such as iterating over two or three store views, cost little. Suppress with a reason.
- A collection created in a loop by a helper method and loaded in the loop is not recognized as created in the loop. That is a missed finding, not a false one; cross-method analysis is planned for M4.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "Running SQL queries in a loop often results in a performance bottleneck." It recommends data collections instead of per-item loading. |
| [AbstractDb.php#L813-L836](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836) | B | `load()` runs the select and builds every item. |
| [AbstractDb.php#L433-L441](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441) | B | `getSize()` runs its own COUNT query. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
