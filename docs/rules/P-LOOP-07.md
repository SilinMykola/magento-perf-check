# P-LOOP-07: composite product type methods inside a loop

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.loopCompositeType` |

## What it catches

Inside a loop, calls to:
- `getUsedProducts`, `getConfigurableAttributes`, `getChildrenIds`, `getParentIdsByChild`, `getUsedProductIds` or `getAssociatedProducts`, on a product type (`Magento\Catalog\Model\Product\Type\AbstractType` subtype) or on the configurable type resource model;
- `getIdBySku`, on `Magento\Catalog\Model\Product` or its resource model.

## Why it hurts performance

These methods query the database for one product per call. `Configurable::getUsedProducts()` loads a whole child product collection for its parent. A category page or a feed that calls it for every listed product runs one collection load per product. The resource model's `getChildrenIds()` and `getParentIdsByChild()` accept arrays, so one call can serve all products.

## Example

Bad:

```php
foreach ($childIds as $childId) {
    $parents[$childId] = $this->configurableResource->getParentIdsByChild($childId);
}
```

Good:

```php
$allParentIds = $this->configurableResource->getParentIdsByChild($childIds);
```

`getParentIdsByChild()` with an array returns the parent IDs of all the given children together. When you need the mapping per child, query the `catalog_product_super_link` table once for all children.

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopCompositeType (product detail page, one configurable)
$children = $type->getUsedProducts($product);
```

## Known false positives

- `getUsedProducts()` caches its result on the product object, so calling it repeatedly for the same product is cheap. The rule cannot see whether the products repeat.
- Loops with a small fixed bound.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Configurable.php#L1411-L1420](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/ConfigurableProduct/Model/Product/Type/Configurable.php#L1411-L1420) | B | `getUsedProducts()` loads a child product collection for each product. |
| [ResourceModel Configurable.php#L159-L208](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/ConfigurableProduct/Model/ResourceModel/Product/Type/Configurable.php#L159-L208) | B | `getChildrenIds()` and `getParentIdsByChild()` run a query per call and accept arrays. |
| [Grouped.php#L203-L237](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GroupedProduct/Model/Product/Type/Grouped.php#L203-L237) | B | `Grouped::getAssociatedProducts()` loads a collection per product. |
| ECG `LoopSniff` | C | Flags the same methods in loops by name, without types. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
