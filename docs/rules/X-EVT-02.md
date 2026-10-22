# X-EVT-02: observer on generic model save/delete events

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | XML (`events.xml`) |

## What it catches

An `<observer>` (not `disabled="true"`) on `model_save_before`, `model_save_after`, `model_save_commit_after`, `model_delete_before`, `model_delete_after` or `model_delete_commit_after`.

## Why it hurts performance

These events fire for every model saved or deleted, of every type: quotes, quote items, sessions stored as models, log entries, configuration. An observer meant for one entity runs, and usually checks the type and returns, on all of them. Each entity has its own events (`catalog_product_save_after`, `sales_order_save_after`, ...) that fire only for that type.

## Example

Bad:

```xml
<event name="model_save_after">
    <observer name="acme_sync" instance="Acme\Sync\Observer\Push"/>
</event>
```

Good:

```xml
<event name="catalog_product_save_after">
    <observer name="acme_sync" instance="Acme\Sync\Observer\Push"/>
</event>
```

## How to suppress

`<!-- perf-ignore X-EVT-02: reason -->` directly before the `<observer>`.

## Known false positives

- Generic auditing that really needs every model.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [AbstractModel.php#L691-L730](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L691-L730) | B | `model_save_before`/`after` are dispatched for every model. |
| [AbstractModel.php#L893-L930](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L893-L930) | B | `model_delete_*` events are dispatched for every model. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
