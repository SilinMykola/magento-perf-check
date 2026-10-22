# P-FPC-10: `reindexAll()` during a request

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.fullReindex`, with the project index of `di.xml` for plugins |

## What it catches

`reindexAll()` on `Magento\Framework\Indexer\IndexerInterface`, called from:
- an observer (implements `ObserverInterface`);
- a controller (implements `ActionInterface`);
- a class that some `di.xml` in the project declares as a plugin.

`reindexList()` and `reindexRow()` are not reported: reindexing the changed IDs from a plugin or observer is how "Update on Save" indexers are meant to work.

## Why it hurts performance

`reindexAll()` rebuilds the whole index synchronously: every product, every price, every store. Called from request-time code, it holds the request (and often a database lock) for seconds to hours, depending on catalog size, and runs again on the next save.

## Example

Bad:

```php
public function execute(Observer $observer)
{
    $this->indexerRegistry->get('catalog_product_price')->reindexAll();
}
```

Good:

```php
public function execute(Observer $observer)
{
    $productId = (int) $observer->getEvent()->getProduct()->getId();
    $this->indexerRegistry->get('catalog_product_price')->reindexRow($productId);
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.fullReindex (admin "rebuild" action, runs rarely and on purpose)
```

## Known false positives

- Admin actions whose purpose is a full rebuild.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Indexing](https://developer.adobe.com/commerce/php/development/components/indexing/) | A | "Update on Save" indexers reindex the changed entities through plugins or events. |
| [Indexer.php#L452-L494](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Indexer/Model/Indexer.php#L452-L494) | B | `reindexAll()` rebuilds the whole index synchronously. |
| [Adobe: indexer configuration](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration) | A | General guidance on indexer modes (indirect support). |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
