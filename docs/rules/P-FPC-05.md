# P-FPC-05: no-cache or private headers on cacheable requests

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.noCacheHeaders` |

## What it catches

A statement that disables caching, with one finding per statement however many headers it sets:
- `setNoCacheHeaders()` or `setPrivateHeaders()`, on anything but `$this`: a controller's own helper with such a name is not the response;
- `setHeader()` with a literal `Cache-Control` name and a value that contains `no-cache`, `no-store` or `private`, or is computed at run time. A computed value counts by the literals it is built from, by concatenation, interpolation or as the format of `sprintf()`: `sprintf('private, max-age=%d', $ttl)` is reported, while `sprintf('public, max-age=%d', $ttl)`, `'max-age=' . $ttl` and `"public, max-age={$ttl}"` with an `int` `$ttl` are not, as they ask for caching. The other parts must be numbers: `'max-age=60, ' . $scope` with a string `$scope`, or a `%s` in the format, is reported, as it may say `private`. `setHeader()` on a block, such as what `getLayout()->getBlock()` returns, is not reported: a block's `setHeader()` only sets its `header` data.

`Pragma` is not reported, whatever its value: neither [`Kernel::process()`](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/PageCache/Kernel.php) nor the Varnish configuration of core reads it from a response, so it does not change what gets cached.

The rule applies in two kinds of classes:
- a frontend controller that can answer GET (it implements `HttpGetActionInterface`, or no HTTP method interface at all, and is not under `Controller\Adminhtml`);
- a GraphQL resolver.

Core endpoints that are private by design are not reported: `Magento\Customer\Controller\Section\Load`, `Magento\PageCache\Controller\Block\Render` and the controllers under `Magento\Downloadable\Controller\Download`.

## Why it hurts performance

Varnish and the built-in page cache store only responses with public cache headers. A no-cache or private header makes every request for that URL go to PHP. Sometimes this is intended (Adobe shows it as the way to disable caching for a page); often it is copied from somewhere and turns a cacheable page into a slow one. The finding asks to confirm the intent.

## Example

```php
public function execute()
{
    $this->getResponse()->setNoCacheHeaders();
    return $this->resultPageFactory->create();
}
```

If the page is meant to be cached: remove the call and move personal data to customer data sections. If it is meant to be private: keep it and suppress with the reason.

## How to suppress

```php
// @phpstan-ignore magentoPerf.noCacheHeaders (payment return page must never be cached)
```

## Known false positives

- Pages that are personal by design. The rule is a `warning` and asks for a decision.
- A computed `public` or `max-age` value whose number is 0 at run time can stop caching; the rule does not see the number.
- `setHeader()` on a block that the analysis cannot type, such as one read through a property without a type, is still reported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [varnish6.vcl#L191-L200](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/etc/varnish6.vcl#L191-L200) | B | Varnish passes responses marked no-cache or private without caching them. |
| [PageCache Kernel.php#L154-L183](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/PageCache/Kernel.php#L154-L183) | B | The built-in page cache stores only responses with public cache headers. |
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | Shows no-cache headers as the deliberate way to disable caching for a page. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
