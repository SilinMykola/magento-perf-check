# P-FPC-06: state change in a GET-only controller

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.stateChangeOnGet` |

## What it catches

In `execute()` of a frontend controller (not under `Controller\Adminhtml`) that implements `HttpGetActionInterface` and not `HttpPostActionInterface`, a call to either:
- `save()`, `delete()` or `deleteById()` on a model, resource model or repository;
- `addProduct()` on `Magento\Checkout\Model\Cart` or `Magento\Quote\Model\Quote`.

Not reported:
- admin controllers, which are never page-cached;
- actions whose every `return` gives a redirect or forward, recognized by `setPath`/`setUrl`/`setRefererUrl`/`setRefererOrBaseUrl`, `_redirect()`/`_forward()`, `create()` on a redirect or forward result factory, or `create(ResultFactory::TYPE_REDIRECT)`/`create(ResultFactory::TYPE_FORWARD)` on the generic result factory. Varnish and the built-in page cache store only 200 and 404 responses, so these are never served from cache.

## Why it hurts performance

GET responses may be cached by Varnish or the built-in page cache and served without running the controller. A state change on GET then happens once for the first visitor and never for the rest, or is done by crawlers and prefetching browsers. Keeping such actions uncacheable to avoid that removes the page from the cache entirely. Adobe's rule is to change state only on POST or PUT.

## Example

Bad:

```php
class Add implements HttpGetActionInterface
{
    public function execute()
    {
        $this->cart->addProduct($product, $params)->save();
        // ...
    }
}
```

Good: implement `HttpPostActionInterface` and send the request as POST (with a form key).

## How to suppress

```php
// @phpstan-ignore magentoPerf.stateChangeOnGet (signed one-time link from an email)
```

## Known false positives

- One-time links (unsubscribe, email confirmation) are GET by nature; the page is usually uncacheable already.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | "Use only HTTP POST or PUT methods to change state." |
| [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content) | A | The same rule, for private content. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
