# P-LOOP-04: product URL built inside a loop without `addUrlRewrite()`

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default |
| Analyzer | PHPStan, identifier `magentoPerf.loopProductUrl` |

## What it catches

`getProductUrl()` or `getUrlInStore()` on a product, or `getProductUrl()`, `getUrl()` or `getUrlInStore()` on `Magento\Catalog\Model\Product\Url`, inside a loop, unless `addUrlRewrite()` was called earlier in the same function.

## Why it hurts performance

A product without a request path looks its rewrite up in the `url_rewrite` table, with one query per product. `addUrlRewrite()` on the collection fills the request paths of all its products at once.

## Example

Bad:

```php
foreach ($collection as $product) {
    $urls[] = $product->getProductUrl();
}
```

Good:

```php
$collection->addUrlRewrite();
foreach ($collection as $product) {
    $urls[] = $product->getProductUrl();
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopProductUrl (reason)
```

## Known false positives

- The collection got `addUrlRewrite()` in another function. A collection from a core listing block usually did. The rule looks only at the current function.
- A product that already has a request path runs no query, for example one that a category page loaded.
- A small, capped set, such as the items of one order.
- One product used in every iteration of a loop over something else: it looks its rewrite up once and keeps it.

A call of `addUrlRewrite()` on any collection in the function silences the rule, so a call on another collection goes unreported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Product/Url.php#L154-L183](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/Product/Url.php#L154-L183) | B | Without a request path, `getUrl()` looks the rewrite up through `findOneByData()`. |
| [DbStorage.php#L107-L131](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/UrlRewrite/Model/Storage/DbStorage.php#L107-L131) | B | The lookup runs a query on the `url_rewrite` table. |
| [Product/Collection.php#L1455-L1504](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L1455-L1504) | B | `addUrlRewrite()` fills the request paths of the whole collection. |

Verified 2026-11-07.
