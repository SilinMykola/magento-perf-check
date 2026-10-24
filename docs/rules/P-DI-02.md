# P-DI-02: constructor does more than assign dependencies

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default: precision 73 % in [calibration](../calibration.md). Enable it with `enabled: true` in `magento-perf-rules.neon`; with PHPStan directly, include `extension-experimental.neon` |
| Analyzer | PHPStan, identifier `magentoPerf.constructorWork` |

## What it catches

A `__construct` with a statement other than:
- assigning, to a property or a local variable, a parameter, a literal, a constant, or a cast of one of these;
- the same with a `??` or `?:` fallback whose default is such a value, a `new` expression, or `ObjectManager::getInstance()->get(...)` (the documented backward-compatible fallback);
- calling `parent::__construct(...)`;
- argument validation: `if` or `foreach` blocks that contain nothing but throwing an exception.

One finding per constructor points at the first such statement.

## Why it hurts performance

The object manager builds whole dependency graphs when a class is requested, often many objects per request that are never used on that code path. Work in a constructor (reading configuration, querying, building caches) runs for every object built, used or not. In the same method where the result is needed, it runs only when needed, and a Proxy can then defer even the object creation.

## Example

Bad:

```php
public function __construct(private readonly ScopeConfigInterface $scopeConfig)
{
    $this->enabled = $this->scopeConfig->isSetFlag('acme/promo/enabled');
}
```

Good:

```php
public function __construct(private readonly ScopeConfigInterface $scopeConfig)
{
}

private function isEnabled(): bool
{
    return $this->scopeConfig->isSetFlag('acme/promo/enabled');
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.constructorWork (value object built from trusted input)
```

Classes in Composer packages of type `composer-plugin` and in `magento/magento-coding-standard` are skipped: the object manager never builds them.

## Known false positives

- Cheap statements that are not in the allowed list, such as `$this->items = new \ArrayObject()`, `$this->setType(...)` or string concatenation of parameters. The guideline still forbids them; the cost is small.
- Data objects and value objects that are not created by the object manager.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) | A | Section 2.3: "Class constructor can have only dependency assignment operations and/or argument validation operations. No other operations are allowed." Section 2.3.1 shows validation with `foreach` and `throw`. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
