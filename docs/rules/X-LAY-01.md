# X-LAY-01: block with `cacheable="false"`

| | |
|---|---|
| Severity | `error` for the `default` handle and handles starting with `catalog_` or `cms_`; `warning` for other handles |
| Status | experimental, off by default: precision 20 % in [calibration](../calibration.md). Enable it with `enabled: true` in `magento-perf-rules.neon` |
| Analyzer | XML (layout files of area `frontend` or `base`) |

## What it catches

A `<block>` with `cacheable="false"` in a frontend layout file. This includes module layouts (`view/frontend/layout`, `view/base/layout`) and theme overrides (`app/design/frontend/<Vendor>/<theme>/<Module_Name>/layout`).

## Why it hurts performance

When a layout contains a non-cacheable block, Magento treats the whole page as non-cacheable. No cached copy is stored in Varnish or the built-in Full Page Cache, so every request for every page that uses the handle renders the full page in PHP. In `default.xml` that means every page of the store. In `catalog_*` and `cms_*` handles it hits the highest-traffic pages, which Adobe says must stay cacheable.

The rule does not report handles that are personal by nature and not meant to be cached. Default allowlist (handle prefixes):
- Customer: `customer_`, `loginascustomer_`, `newsletter_manage_`, `review_customer_`, `downloadable_customer_`.
- Checkout and sales: `checkout_`, `multishipping_`, `sales_`.
- Payment: `paypal_`, `vault_`, `paymentservicespaypal_vault_`.
- Lists: `wishlist_`, `catalog_product_compare_`.

The SPEC started with six of these. The rest are the handles that Magento 2.4.9 core itself makes uncacheable, found in the M0 core run ([docs/runs/m0-core.md](../runs/m0-core.md)). `catalog_product_compare_` is allowed even though `catalog_` handles otherwise give an `error`: the compare page is per customer. Add your own prefixes, or replace the list, in `magento-perf-rules.neon` (`layout.allowlistHandles`, `layout.replaceAllowlist`).

## Example

Bad: a mini-cart counter in `default.xml` turns off FPC for the whole store.

```xml
<!-- view/frontend/layout/default.xml -->
<referenceContainer name="header-wrapper">
    <block class="Acme\Promo\Block\CartCounter" name="acme.cart.counter"
           template="Acme_Promo::cart-counter.phtml" cacheable="false"/>
</referenceContainer>
```

Good: keep the block cacheable and load the personal part on the client through a customer data section (`sections.xml` + `customerData`), or through an AJAX endpoint.

```xml
<referenceContainer name="header-wrapper">
    <block class="Magento\Framework\View\Element\Template" name="acme.cart.counter"
           template="Acme_Promo::cart-counter.phtml"/>
</referenceContainer>
```

The template renders a placeholder, and a JS component fills it from the `cart` customer data section, the way the core mini-cart does. See [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content).

## How to suppress

If a page is meant to be uncacheable (for example a payment return page under a handle the allowlist does not cover), keep the attribute and record the reason.

- **Command line:** put `<!-- perf-ignore X-LAY-01: reason -->` directly before the block. A comment without a reason suppresses nothing and is reported as a notice. To allow a whole handle, add its prefix to `layout.allowlistHandles` in `magento-perf-rules.neon`.
- **PHPStan:** this is an XML rule, so `@phpstan-ignore` does not apply.

## Known false positives

- A non-cacheable block that a later layout update removes (`remove="true"` or `<referenceBlock name="..." remove="true"/>`) does not disable FPC. `Layout::isCacheable()` only counts blocks that are still in the layout structure. The rule looks at one file at a time and still reports it.
- Handles that only some pages use (`warning` level) may be intentionally uncacheable. Review them rather than fixing them blindly.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Page caching](https://developer.adobe.com/commerce/php/development/cache/page/) | A | "If any block in a layout is designated as uncacheable, the entire page is uncacheable." Also: do not configure catalog, product and CMS pages to be uncacheable. |
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | "Using `cacheable="false"` inside the `default.xml` file disables caching for all pages on the site." |
| [Adobe: Code review best practices](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review) | A | The performance checklist asks reviewers to spot `cacheable="false"` attributes and check they are applied correctly. |
| [Layout.php#L1192-L1210](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L1192-L1210) | B | `isCacheable()` returns false as soon as one `<block cacheable="false">` present in the layout structure is found. |
| [PageCache LayoutPlugin.php#L70-L75](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Layout/LayoutPlugin.php#L70-L75) | B | Public cache headers are set only when the layout is cacheable. |
| [elements.xsd#L184-L221](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout/etc/elements.xsd#L184-L221) | B | `cacheable` is an attribute of `<block>`, not of `<referenceBlock>`; the rule therefore checks only `<block>`. |
| [Sam James: non-cacheable blocks](https://www.samdjames.uk/docs/platforms/magento/development/non-cacheable-blocks/) | C | Supplementary explanation. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
