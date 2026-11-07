# P-PLG-01: around plugin where before/after would do

| | |
|---|---|
| Severity | `warning` |
| Status | default set: 11 of 11 correct in [round 2 of calibration](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-2.md), after a rule change |
| Analyzer | PHPStan, identifier `magentoPerf.unnecessaryAroundPlugin`, with the project index of `di.xml` |

## What it catches

A public `around*` method of a plugin class in which all of these hold:
- `$proceed` is called exactly once;
- the call is in a statement directly in the method body (an expression or a `return`), not inside `if`, a loop, `try` or a closure;
- the call is not a branch of a ternary (`?:`) and not the right operand of `&&`, `||`, `and`, `or` or `??`, where it may not run;
- the arguments are the method's own parameters after `$subject` and `$proceed`, in order or spread with `...`, and none of them (or an element of an array parameter) is assigned before the call. Setting a property on an object parameter does not count as a change: a before plugin can do that;
- no local variable is assigned before the call and read after it. Such a variable holds what the call changes or deletes, and a before and after pair cannot share it.

A class counts as a plugin class when some `di.xml` in the project declares it as a plugin, or when its name ends in `Plugin`.

## Why it hurts performance

An around plugin wraps the original method in a closure chain: every call builds and invokes `$proceed`, and the stack grows by several frames. When the code only changes the result, or only does something before the call, an after or before plugin achieves the same without the closure. Adobe advises against around plugins that are not required.

## Example

Bad:

```php
public function aroundGetPrice(Product $subject, callable $proceed)
{
    $price = $proceed();
    return $price * $this->getMultiplier();
}
```

Good:

```php
public function afterGetPrice(Product $subject, $price)
{
    return $price * $this->getMultiplier();
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.unnecessaryAroundPlugin (needs the arguments and the result together)
```

## Known false positives

- Shared state kept outside a local variable, such as a property set before `$proceed` and read after it, is not recognized.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Plugins](https://developer.adobe.com/commerce/php/development/components/plugins) | A | "Avoid using around method plugins when they are not required because they increase stack traces and affect performance." |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
