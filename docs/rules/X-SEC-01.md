# X-SEC-01: broad customer data invalidation in `sections.xml`

| | |
|---|---|
| Severity | `error` for `<action name="*">`; `warning` for an action that invalidates all sections |
| Status | default set |
| Analyzer | XML (`sections.xml`) |

## What it catches

- `<action name="*">`: its sections are invalidated after every POST request. Reported as `error`, unless its only section is `messages`, which is how core shows flash messages after a POST.
- An action that invalidates all sections, either with `<section name="*"/>` or with no `<section>` at all. Reported as `warning`, except for actions that change the customer, store or currency, which core itself configures this way: `customer/account/logout`, `loginPost`, `createPost`, `editPost`, `stores/store/switch`, `stores/store/switchrequest` and `directory/currency/switch`.

## Why it hurts performance

After an invalidated action, the browser reloads the affected customer data sections from `customer/section/load`, which is an uncached request that builds each section (cart, wishlist, compare, messages, ...) in PHP. Invalidating everything, or invalidating on every POST, turns routine form submissions into a full private-content reload.

## Example

Bad:

```xml
<action name="acme/wishlist/share">
    <section name="*"/>
</action>
```

Good:

```xml
<action name="acme/wishlist/share">
    <section name="wishlist"/>
</action>
```

## How to suppress

`<!-- perf-ignore X-SEC-01: reason -->` directly before the `<action>`.

## Known false positives

- Actions that change the customer's identity or group (like the core login actions) do need all sections. Add a suppression comment with that reason.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content) | A | Explains how actions map to the sections they invalidate in `sections.xml`. |
| [section-config.js#L37-L54](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/view/frontend/web/js/section-config.js#L37-L54) | B | Sections configured for `*` are invalidated on every POST; `*` as section means all sections. |
| [SectionConfigConverter.php#L18-L35](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/CustomerData/SectionConfigConverter.php#L18-L35) | B | An action without sections is converted to "invalidate all". |
| [dev.to: customer sections](https://dev.to/magevanta/magento-2-customer-data-sections-localstorage-performance-optimization-4ci9) | C | Background on section reloads. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
