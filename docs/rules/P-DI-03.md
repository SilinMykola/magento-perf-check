# P-DI-03: event dispatched in a constructor

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.dispatchInConstructor` |

## What it catches

A call to `dispatch()` on `Magento\Framework\Event\ManagerInterface` inside `__construct`.

## Why it hurts performance

Every observer of the event runs whenever the object is created, including when the object only exists as a dependency of something else and is never used. Observers usually load data or write logs, so this multiplies with every place the class is injected.

## Example

Bad:

```php
public function __construct(private readonly ManagerInterface $eventManager)
{
    $this->eventManager->dispatch('acme_promo_init', ['promo' => $this]);
}
```

Good: dispatch the event from the method where the thing it announces actually happens.

## How to suppress

```php
// @phpstan-ignore magentoPerf.dispatchInConstructor (reason)
```

## Known false positives

None known.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) | A | Section 2.3.2: "Events MUST NOT be triggered in constructors." |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
