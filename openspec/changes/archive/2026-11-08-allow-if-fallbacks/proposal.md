# Proposal

## Why

P-DI-06 allows the backward-compatible constructor fallback only when it is written as `$param ?: ObjectManager::getInstance()->get(...)` or with `??`. Magento core and third-party modules write the same fallback in two more ways, and P-DI-06 reports both:
- an `if` on the missing parameter: `if (!isset($param)) { $param = ObjectManager::getInstance()->get(...); }`, or `empty()`, `=== null`, `!$param`, `is_null()`;
- the object manager kept in a local variable that only the fallbacks use: `$objectManager = ObjectManager::getInstance(); $this->a = $a ?: $objectManager->get(A::class);`.

Calibration graded both as false positives (P-DI-06 rows 4, 5 and 23).

## What Changes

- **P-DI-06:** in a constructor, `ObjectManager::getInstance()` inside an `if` whose condition only tests that a constructor parameter is missing, and whose block only assigns the result of one object manager call to a variable or a property, is the fallback. So is `$variable = ObjectManager::getInstance()` when every later use of `$variable` in the constructor is a call on it inside such a fallback. Closures and classes declared in the constructor are not constructor code.

## Impact

Fewer P-DI-06 findings in the default set at `info`: on 78 third-party modules 7 of 81 go, in the Magento core 22 of 343. No finding is added.
