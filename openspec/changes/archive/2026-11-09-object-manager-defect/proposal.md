# Proposal

## Why

The maintainer treats a direct object manager call as a defect, not a matter of impact: it hides the dependency from DI configuration and Adobe's usage rules forbid it outside factories, proxies, the constructor fallback and `__wakeup`/`__sleep`. Calibration had lowered P-DI-06 to `info` by impact. A review of a Magento 2.3.3 project also showed object manager calls inside loops that create an object per item, and twice a `create(Model::class)->load($id)` per id that P-LOOP-01 cannot see, because the created model is untyped.

## What Changes

- **P-DI-06:** default severity `warning` instead of `info`.
- **P-LOOP-11 (new, default set, `error`):** `get()` or `create()` on `ObjectManagerInterface` in loop context, through `getInstance()` or an injected object manager. Factories and proxies are left out, and so is a call the loop is left right after.
- The decision and the grading are recorded as calibration round 3.

## Impact

Both change CI results: P-DI-06 findings move from `info` to `warning`, and P-LOOP-11 adds `error` findings. In the calibration corpus P-LOOP-11 gives 10 findings, on the Magento 2.3.3 project 6 and in the Magento core 79.
