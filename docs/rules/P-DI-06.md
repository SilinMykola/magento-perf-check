# P-DI-06: direct `ObjectManager::getInstance()`

| | |
|---|---|
| Severity | `warning`: calibration had lowered it to `info` ([calibration](../calibration.md)); the maintainer raised it again in [round 3](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-3.md) |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.objectManagerUsage` |

## What it catches

`Magento\Framework\App\ObjectManager::getInstance()` in PHP classes. Templates are left to magento-coding-standard. The uses that Adobe documents as allowed are not reported:
- the backward-compatible constructor fallback: `$dependency ?: ObjectManager::getInstance()->get(...)`, or the same with `??`. In a constructor it may also be written as an `if` that only tests that a parameter is missing (`!isset($dependency)`, `empty()`, `=== null`, `== null`, `!$dependency`, `is_null()`, `!is_object()`, `!$dependency instanceof Type`) and whose block only assigns the result of one object manager call, or an array of them, to a variable or a property, or through a local `$objectManager = ObjectManager::getInstance()` that only such fallbacks call;
- outside the constructor, the lazy getter: an `if` that tests in the same ways (also `!is_object()` and `!$dependency instanceof Type`) that a property of `$this` is missing or not an object of the type, and whose block only assigns one object manager call to that same property. Unless the property is reset, the object manager is asked once per object, so the cost is negligible. A getter that returns the object without keeping it is still reported. The object may also be kept in the object's data under one literal key: the `if` tests `$this->getData('key')`, or a variable read from it alone, and the block only does `$this->setData('key', ...)` with the object manager call, or assigns that call to the variable and stores the variable under the same key;
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

None. Static helpers and entry points that run before the object manager can inject anything are reported on purpose: the call hides the dependency there too ([calibration round 3](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-3.md)). Where no injection is possible, suppress the finding with a reason, as above.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Object Manager, usage rules](https://developer.adobe.com/commerce/php/development/components/object-manager/) | A | The object manager must not be called directly. The allowed exceptions are factories and proxies, the constructor backward-compatibility fallback and magic methods such as `__wakeup()`/`__sleep()`. |
| ECG `ObjectManagerUsage` | C | The same check in a token-based standard. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
