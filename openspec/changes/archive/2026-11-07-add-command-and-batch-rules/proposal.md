# Proposal

## Why

Two planned rules look at code that is common in the 56 modules checked so far. In `di.xml`, 42 console commands are registered in `CommandList`. Magento creates all of them on every `bin/magento` run, and 25 take class dependencies that core does not create anyway and that have no Proxy. Five `foreach` statements in cron jobs and console commands walk a collection the function created, without a page size.

Checking the other planned rules ruled them out for now. No controller, block, view model, observer or plugin in those modules calls an HTTP client directly, so P-EXT-01 as planned finds nothing, and the Adobe page it cites only lists third-party API calls as something to analyze. P-FPC-07 has one candidate, which already has `getCacheKeyInfo()`. P-LOOP-08 has about 102 candidates, and the rule cannot tell how large the array is.

A finding also went missing: `getProductUrl()` on a product from `ProductRepositoryInterface::getById()` was not recognized, because that returns the interface and only the model class counted as a product.

## What Changes

- **P-DI-04:** a console command registered in `CommandList` with class dependencies that are not factories, not configured as a Proxy, and not among the types that commands of core take without one, because instances are shared and those exist anyway. One `info` finding per command. Off by default.
- **P-COL-04:** a `foreach` in a cron job or console command over a collection created in the same function, with no page size and no filter on a unique key. `warning`, off by default.
- **Project index:** remembers console commands of `CommandList` and cron job classes.
- **Products:** `ProductInterface` counts as a product in P-LOOP-04 and P-LOOP-06. Not in P-LOOP-05, because `ProductRepository::getList()` returns that type and has already called `addCategoryIds()`.

## Impact

The default set does not change. P-LOOP-04, off by default, reports 2 more findings on the 56 modules. The new rules start experimental.
