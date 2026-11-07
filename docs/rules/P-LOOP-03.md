# P-LOOP-03: `save()` or `delete()` inside a loop

| | |
|---|---|
| Severity | `info`; was `warning` until calibration: most findings are correct but low impact ([calibration](../calibration.md)) |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.loopSave` |

## What it catches

Inside a loop, any of these calls:
- `save()` or `delete()` on a model (`AbstractModel` subtype);
- `save()` or `delete()` on a resource model (`Model\ResourceModel\Db\AbstractDb` subtype);
- `save()`, `delete()` or `deleteById()` on a repository (a type named `*RepositoryInterface`, or one that implements such an interface).

## Why it hurts performance

Every save is at least one write in its own transaction. It also dispatches the model's `*_save_before`/`*_save_after` events, runs plugins on the repository, and often reindexes or cleans cache tags for that entity. Done once per item in a loop, this multiplies all of that work. Where Magento offers a batch API (for example `Magento\InventoryApi\Api\SourceItemsSaveInterface`, or `insertMultiple`/`insertOnDuplicate` on the connection for plain tables), one call replaces the loop.

## Example

Bad:

```php
foreach ($prices as $sku => $price) {
    $product = $this->productRepository->get($sku);
    $product->setPrice($price);
    $this->productRepository->save($product);
}
```

Better: use a batch API built for the job, here `Magento\Catalog\Api\BasePriceStorageInterface::update()`, which takes a list of prices.

## How to suppress

Batch APIs do not exist for everything. Import and cron code often has to save item by item; record the reason:

```php
// @phpstan-ignore magentoPerf.loopSave (no batch API for this entity; runs in cron)
$this->ruleRepository->save($rule);
```

## Known false positives

- Loops in import, cron or CLI code where no batch API exists. The rule is `info` for this reason.
- Loops with a small fixed bound.
- A save inside an `if` in the loop that matches at most one key, so it runs once.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "Running SQL queries in a loop often results in a performance bottleneck." |
| [Adobe: Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) | A | Section 6.4.3.2: service contracts SHOULD support batch data processing. |
| [AbstractModel.php#L680-L684](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L680-L684) | B | `save()` delegates to the resource model: one write per call. |
| [AbstractModel.php#L881-L885](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L881-L885) | B | `delete()` delegates to the resource model: one write per call. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
