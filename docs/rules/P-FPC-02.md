# P-FPC-02: block or view model reads customer data from the session

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default: precision 7 % in [calibration](../calibration.md). Enable it with `enabled: true` in `magento-perf-check.neon`; with PHPStan directly, include `extension-experimental.neon` |
| Analyzer | PHPStan, identifier `magentoPerf.sessionDataInBlock` |

## What it catches

A call to `getCustomer()`, `getCustomerId()` or `getQuote()` on a session (`SessionManagerInterface` subtype), made in a frontend block (`AbstractBlock` subclass outside `Block\Adminhtml`) or a view model (implements `ArgumentInterface`; collections, which implement it too, are not view models).

Not reported:
- `getCustomerGroupId()`: page cache depersonalization keeps the customer group, and the group is part of the cache key;
- admin blocks: the admin is never page-cached;
- a block or view model that the project's frontend layouts (`<block class>`, `<referenceBlock class>`, or an `<argument>` or `<item>` of type `object`) place only on pages that are never cached: pages whose layout holds a `cacheable="false"` block that no `ifconfig` switches and no layout file of the page removes, itself or the element around it, as Magento's own cart, checkout, customer account, order and wishlist pages do; account pages that include `customer_account`; and handles that only such pages include. A `cacheable="false"` block on the shop-wide `default`, `catalog_*` or `cms_*` handles that X-LAY-03 reports does not count: it is an error to fix. The compare page, `catalog_product_compare_index`, which Magento makes uncacheable, does count. A class that no layout names, or that is also placed on a page that may be cached, is still reported, and so is one whose subclass is placed on such a page.

## Why it hurts performance

On cacheable GET pages, Magento clears the customer session after layout generation (depersonalization), so the page can be stored once for everyone. A block that reads the customer from the session during rendering gets an empty session, so the personal data is missing. The usual workaround, making the block or the page uncacheable, costs far more (see X-LAY-01).

## Example

Bad:

```php
public function getGreeting(): string
{
    return 'Hello, ' . $this->customerSession->getCustomer()->getFirstname();
}
```

Good: render a placeholder and fill it from the `customer` customer data section in the browser.

## How to suppress

```php
// @phpstan-ignore magentoPerf.sessionDataInBlock (block only used on the uncacheable account pages)
```

## Known false positives

- A block used only on pages that are uncacheable by design but that the layouts do not show as such, for example a page a controller makes uncacheable with headers.

- A block placed only in a handle that only uncacheable pages include but whose name starts with a storefront route, such as `customer_account.xml`: the rule takes such a handle for a page of its own, which may be cached.

Known false negatives: a class placed both by layout on an uncacheable page and by code on a cacheable one (`createBlock()` in a template, a Hyvä `$viewModels->require()`, a widget or a CMS `{{block}}`) is left out, and so is one whose virtual type or a preference for a subclass is placed on a cacheable page; only layouts and real subclasses are read.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) | A | Section 9.4: server-rendered HTML must not contain user-specific data. |
| [Layout.php#L1192-L1210](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L1192-L1210) | B | A page is not cacheable when a block that is in its layout structure has `cacheable="false"`; a removed block, or one an `ifconfig` leaves out, is not in the structure. |
| [DepersonalizePlugin.php#L101-L112](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/Model/Layout/DepersonalizePlugin.php#L101-L112) | B | On cacheable requests the customer session is cleared after layout generation. |
| [FPC strategy (community)](https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html) | C | Background on public and private content. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
