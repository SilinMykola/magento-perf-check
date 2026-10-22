# P-FPC-01: `$_isScopePrivate` in a block

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.isScopePrivate` |

## What it catches

A declaration of, or an assignment to, the `$_isScopePrivate` property in a subclass of `Magento\Framework\View\Element\AbstractBlock`.

## Why it hurts performance

`$_isScopePrivate` was the old way to mark a block as private content. Adobe documents it as obsolete and not working properly: the block's personal data is either missing or rendered into the cached page. The supported mechanism is customer data sections, which load personal data in the browser and keep the page cacheable.

## Example

Bad:

```php
class Greeting extends \Magento\Framework\View\Element\Template
{
    protected $_isScopePrivate = true;
}
```

Good: render a placeholder in the template and fill it from a customer data section (`sections.xml` plus a `customerData` JS component).

## How to suppress

```php
// @phpstan-ignore magentoPerf.isScopePrivate (legacy block scheduled for removal)
```

## Known false positives

None known.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content) | A | Do not use the `$_isScopePrivate` property: it is obsolete and will not work properly. |
| [AbstractBlock.php#L158-L166](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L158-L166) | B | The property is marked `@deprecated`. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
