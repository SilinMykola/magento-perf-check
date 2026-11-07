# P-LOOP-10: resource model `load()` inside a loop

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.loopResourceLoad` |

## What it catches

A call to `load()` on a subtype of `Magento\Framework\Model\ResourceModel\Db\AbstractDb` inside a loop. This is the form that replaces the deprecated `AbstractModel::load()`, so P-LOOP-01 does not see it.
"Inside a loop" has the meaning given on the [P-LOOP-01 page](P-LOOP-01.md). The body of a `foreach` over `array_chunk()` does not count: one query per batch is the fix.

## Why it hurts performance

Each call runs one `fetchRow()`. A loop over 100 ids runs 100 queries where one collection query would do.

## Example

Bad:

```php
foreach ($ids as $id) {
    $this->resource->load($model = $this->factory->create(), $id);
}
```

Good:

```php
$items = $this->collectionFactory->create()->addFieldToFilter('entity_id', ['in' => $ids])->getItems();
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopResourceLoad (reason)
```

## Known false positives

- Loops with a small, fixed bound cost little, such as one per website when importing configuration. Suppress with a reason.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "Running SQL queries in a loop often results in a performance bottleneck." |
| [AbstractDb.php#L323-L333](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/ResourceModel/Db/AbstractDb.php#L323-L333) | B | `load()` runs `fetchRow()` on every call. |

Verified 2026-11-07.
