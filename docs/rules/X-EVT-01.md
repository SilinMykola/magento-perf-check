# X-EVT-01: observer on `model_load_after` or `<entity>_load_after`

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | XML (`events.xml`) |

## What it catches

An `<observer>` (not `disabled="true"`) on `model_load_after`, on `core_collection_abstract_load_after`, or on any event ending in `_load_after`, such as `catalog_product_load_after`.

Not reported:
- `*_collection_load_after` events of one collection type, which fire once per collection, not per entity;
- observers in `Magento\Framework\EntityManager\Observer`, which core registers on `*_api_data_*interface_load_after` events to map entity data, without running a query.

## Why it hurts performance

`AbstractModel::_afterLoad()` dispatches `model_load_after` after loading any model of any type, and the entity-specific `<prefix>_load_after` after every load of that entity type. `AbstractCollection::_afterLoad()` likewise dispatches `core_collection_abstract_load_after` after loading any database collection, whatever it holds. An observer there runs on every model or collection load in every request: product pages, carts, admin grids, imports. Even a cheap observer adds up; one that queries the database turns every load into two.

## Example

Bad:

```xml
<event name="catalog_product_load_after">
    <observer name="acme_badge" instance="Acme\Promo\Observer\AddBadge"/>
</event>
```

Also bad, an observer that only needs one collection type:

```xml
<event name="core_collection_abstract_load_after">
    <observer name="acme_wishlist_items" instance="Acme\Wishlist\Observer\AfterItemCollectionLoad"/>
</event>
```

Better: add the data where it is used. Use a plugin on the specific repository or a view model, or join it into the collection that lists the products. For one collection type, observe that collection's own `<prefix>_collection_load_after` event if it defines one, or extend the code that loads it.

## How to suppress

`<!-- perf-ignore X-EVT-01: reason -->` directly before the `<observer>`.

## Known false positives

- Observers that only set a flag in memory cost little; they are still reported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [AbstractModel.php#L590-L609](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L590-L609) | B | `_afterLoad()` dispatches `model_load_after` and the entity-specific `*_load_after` event on every load. |
| [AbstractCollection.php#L591-L605](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/ResourceModel/Db/Collection/AbstractCollection.php#L591-L605) | B | `_afterLoad()` of every database collection dispatches `core_collection_abstract_load_after`, then its own `<prefix>_load_after`. |
| [Adobe: observers best practices](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) | A | Observers run every time their event fires and must be kept lightweight. |

Verified 2026-10-21 (see [source audit](../source-audit.md)); the collection source was added and verified on 2026-10-25.
