# X-LAY-01: block with `cacheable="false"` on other handles

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default: precision 20 % in [calibration](../calibration.md); after the module allowlist of [round 8](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-8.md), 5 findings, all correct; with 10 more from private stores in [round 15](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-15.md), 15 findings and a precision of 47 %; after the rule began to follow handle includes, [round 16](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-16.md): 10 findings, 70 %. Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | XML (layout files of area `frontend` or `base`) |

## What it catches

A `<block>` with `cacheable="false"` in a frontend layout file, on any handle except `default`, `catalog_*` and `cms_*`. Those belong to [X-LAY-03](X-LAY-03.md), which is on by default. This includes module layouts (`view/frontend/layout`, `view/base/layout`) and theme overrides (`app/design/frontend/<Vendor>/<theme>/<Module_Name>/layout`). A handle with Hyvä's `hyva_` prefix is treated as the handle it extends, for this split and for the allowlist: `hyva_default` belongs to X-LAY-03, `hyva_customer_address_index` is on the allowlist.

## Why it hurts performance

When a layout contains a non-cacheable block, Magento treats the whole page as non-cacheable. No cached copy is stored in Varnish or the built-in Full Page Cache, so every request for every page that uses the handle renders the full page in PHP. 
The rule does not report handles that are personal by nature and not meant to be cached. Default allowlist (handle prefixes):
- Customer: `customer_`, `loginascustomer_`, `newsletter_manage_`, `review_customer_`, `downloadable_customer_`.
- Checkout and sales: `checkout_`, `hyva_checkout_`, `onestepcheckout_`, `multishipping_`, `sales_`, `shipping_tracking_`.
- Payment: `paypal_`, `vault_`, `paymentservicespaypal_vault_`, `paymentservicespaypal_smartbuttons_` (the review and success pages of PayPal Payment Services, which include core's `checkout_onepage_review_item_renderers` and `checkout_onepage_success`), and of PayPal Braintree, which Magento ships: `braintree_paypal_review`, `braintree_googlepay_review` and the return page `braintree_lpm_fallback`.
- Lists: `wishlist_`, `catalog_product_compare_`.
- Forms bound to one customer or email link: `productalert_unsubscribe_`, `sendfriend_`.
- Personal pages of known modules: `connector_` (Dotdigital: endpoints its servers fetch with a passcode, mostly one customer's email content, also a few shared reports), `paynl_order_` and `paynl_checkout_` (Pay.nl), `payone_onepage_` and `payone_amazon_` (Payone), `buckaroo_mrcash_pay` (Buckaroo), `mpblog_post_history`, `mpblog_post_preview` and `mpblog_author_information` (Mageplaza Blog), `clerk_clerk_powerstep_` (Clerk), `sharecart_index_download` (Mageplaza Share Cart), `mailchimp_cart_loadquote` (Mailchimp), `blog_non_cacheable` (Magefan Blog) and, since [round 20](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-20.md), `hyvareactcheckout_reactcheckout_` (Hyvä React Checkout: its checkout page).

A layout that includes `<update handle="customer_account"/>` as a direct child of its root, where core merges it from, is personal too and is not reported, whatever its handle is called. The allowlist applies to [X-LAY-03](X-LAY-03.md) as well.

The rule also follows how the project's handles include each other, as Magento does when it builds a page: it reads the storefront layouts and routes of every enabled module, Composer packages included, and the layouts of every frontend theme, whatever `--paths` says; like Magento, it leaves out a theme's folder of a module that is not enabled. A handle is personal, and not reported:
- when any of its layout files includes `customer_account`, so a theme file of an account page whose module file includes it is not reported, and this holds for [X-LAY-03](X-LAY-03.md) too;
- when other handles include it with `<update handle="..."/>` and every one of them is personal, so a block in a handle that only the customer's order pages include is not reported. A page is the handle of a storefront controller action: the route id, the controller path and the action in lower case, read from the `Controller` classes of the route's modules, as `checkout_cart_index` for `Magento\Checkout\Controller\Cart\Index`; like Magento, it skips a controller path with `adminhtml` in it and abstract classes, and reads `PrintAction` as the action `print`. A handle that extends a page's name, such as `catalog_product_view_type_simple`, is added by that page's controller: it is personal when it or its page is, and every handle that includes it is personal too. A route whose id starts with `hyva_` has pages of its own, not `hyva_` copies. A page is personal when the allowlist names it or it is an account page; its `hyva_` copy also needs every handle that includes the copy to be personal. The allowlist names pages only: a handle that is no page, such as `checkout_cart_sidebar_item_renderers`, follows the handles that include it, so a `cacheable="false"` block there is reported when `default.xml` includes it. A handle that nothing includes is added by code and is judged by the allowlist like a page. A shop-wide handle of X-LAY-03 is not followed, as a controller may add it itself.

Limitations of following includes: a handle that a controller adds with `addHandle()` under a name that starts with no route id, and that only personal pages include, is taken as personal. A handle that an allowlisted page and other pages include is reported, also when it is a core or Hyvä file: a shared renderer handle such as `checkout_onepage_review_item_renderers` is reported when a page that the allowlist does not name includes it; add that page's prefix to `layout.allowlistHandles` when it is personal. A handle is taken for an addition to the page whose name it extends: `acme_order_view_totals`, which only an allowlisted page includes, is reported when `acme_order_view` is a public page, as that page's controller could add it. Includes in page layout files (`page_layout/*.xml`) and in layout updates stored in the database are not read, and neither is the old `advanced/modules_disable_output` setting, with which Magento also drops a module's layouts. Building the index of handles took about 0.3 s on the Magento 2.4.9 core (2026-11-13).

The SPEC started with six of these. Most of the rest are the handles that Magento 2.4.9 core itself makes uncacheable, found in the M0 core run ([docs/runs/m0-core.md](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/runs/m0-core.md)). The module group comes from grading the calibration corpus ([round 8](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-8.md)): each prefix is as narrow as that module's personal pages allow, so another handle of the module, such as Mageplaza Blog's public author page, would still be reported. `connector_` is Dotdigital's route id; a different module that uses the same route id for public pages would be hidden too. `catalog_product_compare_` is allowed even though X-LAY-03 otherwise reports `catalog_` handles: the compare page is per customer. Add your own prefixes, or replace the list, in `magento-perf-check.neon` (`layout.allowlistHandles`, `layout.replaceAllowlist`).

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

- **Command line:** put `<!-- perf-ignore X-LAY-01: reason -->` directly before the block. A comment without a reason suppresses nothing and is reported as a notice. To allow a whole handle, add its prefix to `layout.allowlistHandles` in `magento-perf-check.neon`.
- **PHPStan:** this is an XML rule, so `@phpstan-ignore` does not apply.

## Known false positives

- A non-cacheable block that a later layout update removes (`remove="true"` or `<referenceBlock name="..." remove="true"/>`) does not disable FPC. `Layout::isCacheable()` only counts blocks that are still in the layout structure. The rule looks at one file at a time and still reports it.
- Handles that only some pages use (`warning` level) may be intentionally uncacheable. Review them rather than fixing them blindly.
- A `hyva_` layout in a store whose themes are not Hyvä themes: Hyvä adds the `hyva_` handles only on a Hyvä theme, so the file is not applied there. The rule does not know which theme a store uses.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Page caching](https://developer.adobe.com/commerce/php/development/cache/page/) | A | "If any block in a layout is designated as uncacheable, the entire page is uncacheable." Also: do not configure catalog, product and CMS pages to be uncacheable. |
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | "Using `cacheable="false"` inside the `default.xml` file disables caching for all pages on the site." |
| [Adobe: Code review best practices](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review) | A | The performance checklist asks reviewers to spot `cacheable="false"` attributes and check they are applied correctly. |
| [Layout.php#L1192-L1210](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L1192-L1210) | B | `isCacheable()` returns false as soon as one `<block cacheable="false">` present in the layout structure is found. |
| [PageCache LayoutPlugin.php#L70-L75](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Layout/LayoutPlugin.php#L70-L75) | B | Public cache headers are set only when the layout is cacheable. |
| [Layout/Merge.php#L619-L630](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Model/Layout/Merge.php#L619-L630), [#L722-L727](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Model/Layout/Merge.php#L722-L727) | B | A page merges every layout file of each of its handles, and a handle named in `<update handle="..."/>`, recursively; only the `<update>` elements that are direct children of a file's root are followed. |
| [Router/ActionList.php#L42-L50](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Router/ActionList.php#L42-L50), [#L133-L139](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Router/ActionList.php#L133-L139) | B | Which controller class serves a request: a controller path with `adminhtml` in it serves nothing on the storefront, and an action named after a PHP reserved word is the class `<Word>Action`. |
| [elements.xsd#L184-L221](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout/etc/elements.xsd#L184-L221) | B | `cacheable` is an attribute of `<block>`, not of `<referenceBlock>`; the rule therefore checks only `<block>`. |
| [Sam James: non-cacheable blocks](https://www.samdjames.uk/docs/platforms/magento/development/non-cacheable-blocks/) | C | Supplementary explanation. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)); the Merge.php source on 2026-11-13, the ActionList.php source on 2026-11-14.
