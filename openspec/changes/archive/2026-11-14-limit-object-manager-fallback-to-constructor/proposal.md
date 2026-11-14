# Proposal

## Why

P-DI-06 allows the backward-compatible constructor fallback, `$param ?: ObjectManager::getInstance()->get(...)` or `??`, which Adobe documents. The code accepts these two forms in every method, not only in the constructor, so a getter like `return $this->resource ?: ObjectManager::getInstance()->get($this->resourceName);` is not reported, although the rule page says a getter that returns the object without keeping it is. On the other side, the fallback written with `??=` on a constructor parameter, `$failedRegistry ??= ObjectManager::getInstance()->get(...)`, is reported: the rule does not know `??=` at all. Both came up in the review of the third held-out check.

## What Changes

- `?:`, `??` and the new `??=` count as the fallback in the constructor only, like the full ternary and the `if` form; code in closures declared there does not count, as for the other forms.
- Outside the constructor, the lazy getter may also be written in one expression that keeps the object in the property it tests: `$this->x = $this->x ?? ObjectManager::getInstance()->get(...)`, the same with `?:`, or `$this->x ??= ObjectManager::getInstance()->get(...)`. The value must be one object manager call or an array of them, as in the `if` form.
- Everything else outside the constructor is reported. A static property of `self` or `static` counts as a place to keep the object, like a property of `$this`.
- P-DI-02 (experimental) takes `$param ??= <fallback>` in a constructor as the same allowed fallback as `$param = $param ?? <fallback>`.

Measured on the whole corpus before and after; this can add findings of a default-set rule, so it goes into the changelog.
