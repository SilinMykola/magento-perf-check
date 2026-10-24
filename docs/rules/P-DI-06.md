# P-DI-06: direct `ObjectManager::getInstance()`

| | |
|---|---|
| Severity | `info`; was `warning` until calibration: most findings are correct but low impact ([calibration](../calibration.md)) |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.objectManagerUsage` |

## What it catches

`Magento\Framework\App\ObjectManager::getInstance()` in PHP classes. Templates are left to magento-coding-standard. The uses that Adobe documents as allowed are not reported:
- the backward-compatible constructor fallback: `$dependency ?: ObjectManager::getInstance()->get(...)`, or the same with `??`;
- classes whose name ends in `Factory` or `\Proxy`;
- `__wakeup()` and `__sleep()`.

## Why it hurts performance

This is mainly a design rule. Dependencies fetched through the object manager are hidden from DI configuration, so they cannot be replaced by a Proxy, which keeps expensive objects from being created early. They also cannot be swapped in tests. It is listed here because the SPEC catalog includes it; the cost is indirect.

## Example

Bad:

```php
public function getLogger(): LoggerInterface
{
    return ObjectManager::getInstance()->get(LoggerInterface::class);
}
```

Good:

```php
public function __construct(private readonly LoggerInterface $logger)
{
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.objectManagerUsage (static entry point without DI)
```

## Known false positives

- Static helpers and entry points that run before the object manager can inject anything.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Object Manager, usage rules](https://developer.adobe.com/commerce/php/development/components/object-manager/) | A | The object manager must not be called directly. The allowed exceptions are factories and proxies, the constructor backward-compatibility fallback and magic methods such as `__wakeup()`/`__sleep()`. |
| ECG `ObjectManagerUsage` | C | The same check in a token-based standard. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
