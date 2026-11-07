# P-FPC-03: value specific to a visitor or a moment in the HTTP context

| | |
|---|---|
| Severity | `error` |
| Status | experimental, off by default |
| Analyzer | PHPStan, identifier `magentoPerf.httpContextUserValue` |

## What it catches

A call of `Magento\Framework\App\Http\Context::setValue()` whose value is a customer id, an email, a session id, a quote id, the id of a customer, quote or session object, or the current time. The value can pass through a cast, a concatenation, a hash, an array in `implode()`, a ternary branch or `??`. A variable or property named like `$customerId`, `$email` or `$quoteId` counts too, because the rule does not follow assignments.

A flag is fine: `(bool) $session->getCustomerId()`, or a comparison, gives two values. So is the customer group, which many visitors share.

## Why it hurts performance

Every value of the HTTP context goes into the vary string that is part of the page cache key. A value per visitor gives one cached copy per visitor, so nobody shares a page and the cache fills with copies.

## Example

Bad:

```php
$this->httpContext->setValue('customer', $this->customerSession->getCustomerId(), 0);
```

Good:

```php
$this->httpContext->setValue('customer_group', $this->customerSession->getCustomerGroupId(), 0);
```

Load personal data in the browser through customer sections.

## How to suppress

```php
// @phpstan-ignore magentoPerf.httpContextUserValue (reason)
```

## Known false positives

- A variable with such a name that holds something else, for example a shared support email.
- A date with a coarse format such as `date('Y')`: it splits the cache once a year, which is harmless.

No false positives are known from real code, because no real finding exists yet: none of the 56 modules checked so far calls `setValue()` with such a value. The rule's precision is unmeasured.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) | A | "Context variables should not be specific to individual users", because each value yields a separate cached copy. |
| [Http/Context.php#L107-L135](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Http/Context.php#L107-L135) | B | Every non-default value goes into the vary string of the page cache key. |

Verified 2026-11-07.
