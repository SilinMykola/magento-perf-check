# P-LOOP-05: `getCategoryIds()` inside a loop

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.loopCategoryIds` |

## What it catches

A call to `getCategoryIds()` on `Magento\Catalog\Model\Product` or a subclass inside a loop, unless `addCategoryIds()` was called earlier in the same function.
"Inside a loop" has the meaning given on the [P-LOOP-01 page](P-LOOP-01.md). The body of a `foreach` over `array_chunk()` does not count: one query per batch is the fix.

## Why it hurts performance

When `category_ids` is not set, each call runs one query for that product. `addCategoryIds()` fills it for all loaded products with one query.

## Example

Bad:

```php
foreach ($collection as $product) {
    $ids = $product->getCategoryIds();
}
```

Good:

```php
$collection->load();
$collection->addCategoryIds();
foreach ($collection as $product) {
    $ids = $product->getCategoryIds();
}
```

`addCategoryIds()` works only on a loaded collection. Called before `load()` it does nothing.

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopCategoryIds (reason)
```

## Known false positives

- Products that already carry `category_ids`, because another method called `addCategoryIds()`, are reported. Suppress with a reason.
- Loops over one or two products cost little.

The rule also accepts `addCategoryIds()` called before `load()`, which does nothing, so that mistake goes unreported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Product.php#L753-L769](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/Product.php#L753-L769) | B | `getCategoryIds()` asks the resource model when `category_ids` is not set. |
| [CategoryLink.php#L54-L69](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/CategoryLink.php#L54-L69) | B | Each call runs one `fetchAll` for one product id. |
| [Collection.php#L2163-L2200](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L2163-L2200) | B | `addCategoryIds()` runs one query for all loaded items, and returns early when none are loaded. |

Verified 2026-11-07.
