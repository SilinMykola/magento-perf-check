# X-EVT-03: observer on per-block render events

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | XML (`events.xml`) |

## What it catches

An `<observer>` (not `disabled="true"`) on `core_layout_render_element`, `view_block_abstract_to_html_before` or `view_block_abstract_to_html_after`. Observers declared by `Magento_PageCache` are not reported, since core needs them for the page cache.

## Why it hurts performance

These events fire once for every layout element or block rendered, which is hundreds of times on a typical page. Any work in the observer is multiplied by that count, on every uncached request.

## Example

Bad: wrapping every block's HTML to add a debugging attribute in production:

```xml
<event name="view_block_abstract_to_html_after">
    <observer name="acme_wrap" instance="Acme\Debug\Observer\Wrap"/>
</event>
```

Good: a plugin on the one block class that needs the change, or a layout or template change.

## How to suppress

`<!-- perf-ignore X-EVT-03: reason -->` directly before the `<observer>`.

## Known false positives

- Developer tools that are only enabled in development mode.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Layout.php#L555-L571](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L555-L571) | B | `core_layout_render_element` is dispatched for every rendered element. |
| [AbstractBlock.php#L666-L692](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L666-L692) | B | `view_block_abstract_to_html_before`/`after` are dispatched for every block. |
| [Adobe: observers best practices](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) | A | Observers run every time their event fires and must be kept lightweight. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
