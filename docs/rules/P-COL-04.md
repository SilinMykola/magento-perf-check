# P-COL-04: collection walked without a page size in a cron job or console command

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default |
| Analyzer | PHPStan and the project's `crontab.xml` files, identifier `magentoPerf.unpagedBatchCollection` |

## What it catches

A `foreach` over a database collection, or over its `getItems()`, in a class listed as a job in `crontab.xml` or extending the console `Command`, when all of this holds:

- the same function created the collection with `create()` on another object;
- no `setPageSize()`, `setPage()`, or `limit()` or `limitPage()` on `getSelect()`, comes earlier in the function;
- it has no equality filter on `entity_id`, `sku`, `increment_id` or `username`.

## Why it hurts performance

Without a page size, the query has no `LIMIT`, and the collection holds every matching row as a model in memory until it is released. A batch job runs on whatever the table has grown to, so a job that works on a test store can run out of memory on a real one.

## Example

Bad:

```php
$orders = $this->orderCollectionFactory->create()->addFieldToFilter('status', 'pending');
foreach ($orders as $order) {
    $this->exporter->export($order);
}
```

Good:

```php
$orders = $this->orderCollectionFactory->create()->addFieldToFilter('status', 'pending');
$orders->setPageSize(500);
for ($page = 1, $last = $orders->getLastPageNumber(); $page <= $last; $page++) {
    $orders->setCurPage($page)->load();
    foreach ($orders as $order) {
        $this->exporter->export($order);
    }
    $orders->clear();
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.unpagedBatchCollection (reason)
```

## Known false positives

- A filter that leaves few rows by nature, such as a narrow time window or a small table. The rule does not know how many rows match.
- A collection that a method of its own class limits, such as `$collection->onlyRecent()`.
- A small collection per item of another loop, such as the lines of one order.
- A collection limited through a variable, `$select = $c->getSelect(); $select->limit(10);`, or by a helper it is passed to, such as `CollectionProcessor`. Neither appeared in the 78 modules checked: the first by a syntax search of their roughly 5400 PHP files outside test directories, the second by a search of their `Cron` and `Console` directories only.

A collection that a parameter or another method hands over is not reported, because its limit may be set there. These are missed too:

- a job class that calls a service to do the walking;
- a job whose method is inherited, because the class that declares it is not the one in `crontab.xml`. None of the 47 jobs in those modules is like that;
- a collection from a getter after `create()`: `$factory->create()->getCollection()`, and also a getter that returns the collection itself, such as `getFiltered()` on a credit memo collection. Only `getItems()` is known to stay on the same collection;
- rows or a collection kept elsewhere first: `$items = $factory->create()->getItems(); foreach ($items ...)`, or `$this->collection = $factory->create()`;
- a collection filtered in the `foreach` itself after it was created: `$c = $factory->create(); foreach ($c->addFieldToFilter(...) as ...)`;
- a collection compared with `getSize() === 1` earlier in the function, or with a filter on one of those four columns that holds an array or matches many rows, such as `sku` on order items.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [AbstractDb.php#L813-L836](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836) | B | `loadWithFilter()`, which `load()` runs, adds every fetched row to the collection. |
| [AbstractDb.php#L756-L763](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763) | B | `_renderLimit()` adds a `LIMIT` only when a page size is set. |

Verified 2026-11-07. There is no level A source, so the rule stays experimental until calibration decides.
