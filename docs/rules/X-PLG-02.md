# X-PLG-02: plugin on a method that runs for every rendered block or layout element

| | |
|---|---|
| Severity | `info` |
| Status | experimental, off by default |
| Analyzer | PHPStan and the project's `di.xml` files, identifier `magentoPerf.hotRenderPlugin` |

## What it catches

A public `before`, `after` or `around` method for one of these, in a class configured as a plugin on that class in `di.xml`:

| Class | Methods |
|---|---|
| `Magento\Framework\View\Element\AbstractBlock` | `toHtml`, `getChildHtml` |
| `Magento\Framework\View\Element\Template` | `toHtml`, `getChildHtml`, `fetchView` |
| `Magento\Framework\View\Layout`, `LayoutInterface` | `renderElement` |

A plugin that is disabled, also by another module through its name, or configured only in `etc/adminhtml/di.xml`, does not count.

## Why it hurts performance

A plugin on a class also applies to the classes that inherit from it, so this one runs for every block or element of every page, however few it is meant for. A page can render hundreds. This is an inference from how often the method runs: no source measures what such a plugin costs, so the severity is `info`. A cheap check at the start of the plugin costs little.

## Example

Bad:

```xml
<type name="Magento\Framework\View\Element\AbstractBlock">
    <plugin name="acme_banner" type="Acme\Banner\Plugin\AddBanner"/>
</type>
```

Good:

```xml
<type name="Magento\Catalog\Block\Product\View">
    <plugin name="acme_banner" type="Acme\Banner\Plugin\AddBanner"/>
</type>
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.hotRenderPlugin (reason)
```

## Known false positives

- A plugin that really must see every block, such as profiling or HTML post-processing of all output, and that returns quickly otherwise.

A plugin on another base class, or a method called `after...` for a target the plugin is not configured on, is not reported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Plugins](https://developer.adobe.com/commerce/php/development/components/plugins) | A | A plugin on a class also applies to the classes that inherit from it. |
| [AbstractBlock.php#L666-L692](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L666-L692) | B | `toHtml()` runs once per rendered block. |
| [Layout.php#L555-L571](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L555-L571) | B | `renderElement()` runs once per layout element. |

Verified 2026-11-07.
