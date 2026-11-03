# X-LAY-02: ESI `ttl` on a block in an entity-specific handle

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | XML (frontend and base layout files) |

## What it catches

A `<block>` with a `ttl` attribute in a layout handle that contains `_id_` or `_sku_`, such as `catalog_product_view_id_42.xml` or `catalog_product_view_sku_ABC.xml`.

## Why it hurts performance

`ttl` makes Varnish load the block as a separate ESI request. For handles bound to one entity, core refuses this: the page cache layout plugin throws a `LogicException` when such a block is found, so the page fails instead of rendering. ESI blocks belong in shared handles.

## Example

Bad (`catalog_product_view_id_42.xml`):

```xml
<block class="Acme\Promo\Block\Countdown" name="promo.countdown" ttl="60"/>
```

Good: put the block in `catalog_product_view.xml` (or `default.xml`) and pass the product to it, or drop `ttl`.

## How to suppress

`<!-- perf-ignore X-LAY-02: reason -->` directly before the block.

## Known false positives

None known.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [MergePlugin.php#L42-L54](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Layout/MergePlugin.php#L42-L54) | B | Throws a `LogicException` for `ttl` blocks in handles that contain an entity ID. |
| [Result/Page.php#L236-L252](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Result/Page.php#L236-L252) | B | Entity-specific handles are the `*_id_*` and `*_sku_*` ones. |
| [Adobe: Varnish ESI](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/varnish/use-varnish-esi) | A | How blocks with `ttl` are rendered through ESI. |

The SPEC's former warning for `ttl` in `default.xml` was dropped: core's own `default.xml` declares `catalog.topnav` with `ttl="3600"` (see the [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
