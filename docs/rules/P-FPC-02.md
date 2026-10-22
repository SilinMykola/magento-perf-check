# P-FPC-02: block or view model reads customer data from the session

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.sessionDataInBlock` |

## What it catches

A call to `getCustomer()`, `getCustomerId()`, `getCustomerGroupId()` or `getQuote()` on a session (`SessionManagerInterface` subtype), made in a block (`AbstractBlock` subclass) or a view model (implements `ArgumentInterface`).

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

- Blocks used only on pages that are uncacheable by design (customer account, checkout).

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) | A | Section 9.4: server-rendered HTML must not contain user-specific data. |
| [DepersonalizePlugin.php#L101-L112](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/Model/Layout/DepersonalizePlugin.php#L101-L112) | B | On cacheable requests the customer session is cleared after layout generation. |
| [FPC strategy (community)](https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html) | C | Background on public and private content. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
