# P-FPC-09: full page cache or all caches cleaned from code

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.cacheClean` |

## What it catches

- `cleanType('full_page')` on `Magento\Framework\App\Cache\TypeListInterface` (also with `Type::TYPE_IDENTIFIER`);
- `clean()` without tags or with an empty array on `Magento\Framework\App\CacheInterface`.

## Why it hurts performance

Cleaning the `full_page` type throws away every cached page. With Varnish configured, it triggers a full purge, so every following request is rendered by PHP until the cache is warm again. `clean()` without tags empties every cache frontend: configuration, layout and blocks too. Called from an observer or a save handler, this happens on routine admin actions and imports. Tag-based cleaning (`clean(['catalog_product_42'])`) removes only the pages that show the changed entity.

## Example

Bad:

```php
public function execute(Observer $observer)
{
    $this->typeList->cleanType('full_page');
}
```

Good: implement `IdentityInterface` on the entity and on the blocks that render it, and clean only that entity's tags. Core does this by dispatching `clean_cache_by_tags`, which the PageCache and CacheInvalidate modules observe:

```php
$this->eventManager->dispatch('clean_cache_by_tags', ['object' => $product]);
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.cacheClean (admin "flush cache" button)
```

## Known false positives

- Admin actions whose purpose is to flush the cache.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [App/Cache.php#L96-L111](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Cache.php#L96-L111) | B | `clean()` without tags cleans every cache frontend. |
| [PageCache Cache/Type.php#L51-L55](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Cache/Type.php#L51-L55) | B | Cleaning the `full_page` type dispatches the full page cache clean event. |
| [CacheInvalidate events.xml#L27-L29](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/CacheInvalidate/etc/events.xml#L27-L29) | B | That event triggers a full Varnish purge. |
| [FPC strategy (community)](https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html) | C | Background on tag-based invalidation. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
