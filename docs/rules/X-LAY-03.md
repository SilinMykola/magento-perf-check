# X-LAY-03: block with `cacheable="false"` on the default, catalog or CMS handle

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | XML (layout files of area `frontend` or `base`) |

## What it catches

A `<block cacheable="false">` in a layout whose handle is `default` or starts with `catalog_` or `cms_`. Other handles belong to [X-LAY-01](X-LAY-01.md), which is experimental because many of them are personal pages.

`catalog_product_compare_*` and the other prefixes on the X-LAY-01 allowlist are not reported, and neither are layouts that include `<update handle="customer_account"/>`. The allowlist settings of `layout.allowlistHandles` apply here too.

## Why it hurts performance

One such block makes the whole page uncacheable. In `default.xml` that is every page of the store: each request runs PHP instead of being served from Full Page Cache.

## Example

Bad:

```xml
<!-- view/frontend/layout/default.xml -->
<block class="Acme\Promo\Block\CartCounter" name="acme.cart.counter" cacheable="false"/>
```

Good:

Keep the block cacheable and load the personal part on the client through a customer data section (`sections.xml` + `customerData`), or through an AJAX endpoint.

## How to suppress

`<!-- perf-ignore X-LAY-03: reason -->` directly before the element.

## Known false positives

- A handle used only on a page that must stay private, such as a preview. Add its prefix to `layout.allowlistHandles` or suppress with a reason.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Page caching](https://developer.adobe.com/commerce/php/development/cache/page/) | A | "If any block in a layout is designated as uncacheable, the entire page is uncacheable." Also: do not configure catalog, product and CMS pages to be uncacheable. |
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | "Using `cacheable="false"` inside the `default.xml` file disables caching for all pages on the site." |
| [Layout.php#L1192-L1210](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L1192-L1210) | B | `isCacheable()` returns false as soon as one such block is found. |

Verified 2026-11-07.
