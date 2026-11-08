# Proposal

## Why

Most P-DI-06 findings are lazy backward-compatible getters: `if ($this->dependency === null) { $this->dependency = ObjectManager::getInstance()->get(...); }`. Calibration graded them correct but of low impact: the object manager is asked once per object and returns a shared instance. They are a design problem, not a performance one, and they bury the calls that ask the object manager on every call.

## What Changes

- **P-DI-06:** outside the constructor, `ObjectManager::getInstance()` inside an `if` that only tests that a property of `$this` is missing, and whose block only assigns the result of one object manager call to that same property, is not reported. The tests accepted are the same as for the constructor fallback, plus `!is_object($x)` and `!$x instanceof Type`; these two also count for the constructor fallback. A getter that returns the object without keeping it is still reported.

## Impact

Fewer P-DI-06 findings in the default set at `info`: in the calibration corpus 13 of 52 go, in the Magento core 247 of 321. No finding is added.
