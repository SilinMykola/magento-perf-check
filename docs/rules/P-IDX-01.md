# P-IDX-01: `reindexRow()` or `reindexList()` in request code without `isScheduled()`

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.directReindex` |

## What it catches

A call to `reindexRow()` or `reindexList()` on `Magento\Framework\Indexer\IndexerInterface` in an observer, a controller or a class the project lists as a plugin, when the same method calls nothing named `*isScheduled*`.

Core indexer processors (`AbstractProcessor`) are not `IndexerInterface` and check the mode themselves, so calls on them are not reported.

## Why it hurts performance

These methods run the reindex at once, whatever the indexer mode. A store set to Update on Schedule still reindexes inside the request.

## Example

Bad:

```php
$this->indexerRegistry->get('catalog_product_price')->reindexRow($productId);
```

Good:

```php
$indexer = $this->indexerRegistry->get('catalog_product_price');
if (!$indexer->isScheduled()) {
    $indexer->reindexRow($productId);
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.directReindex (reason)
```

## Known false positives

- A forced reindex on purpose, such as an admin "reindex now" action. Suppress with a reason.
- A check of the mode in another method of the class is not seen.
- A change that the indexer's mview subscription cannot see, for example a row changed by a foreign key `ON DELETE` action, needs the direct call. Do not add the check there; suppress with a reason.

A call to `isScheduled()` anywhere in the method silences the rule, even when it does not guard the reindex, so that mistake goes unreported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Indexer configuration](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration) | A | "frequent reindexing degrades database performance which slows site performance" |
| [Indexer.php#L562-L566](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Indexer/Model/Indexer.php#L562-L566) | B | `reindexRow()` runs the action without looking at the mode. |
| [AbstractProcessor.php#L50-L56](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Indexer/AbstractProcessor.php#L50-L56) | B | Core processors return early when the indexer is scheduled. |

Verified 2026-11-07.
