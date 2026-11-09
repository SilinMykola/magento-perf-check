# Proposal

## Why

P-FPC-06 reported a GET action that saves the quote in an `execute()` returning nothing. `FrontController` sets no-cache headers before every action; `Action::dispatch()` returns the action's own response when `execute()` returns nothing, and an action outside `Action` that returns nothing fails, so neither is cached. Only a rendered layout replaces the headers with public ones, through the page cache's `LayoutPlugin`. Seen on a Magento 2.4.3 project and graded as a false positive.

## What Changes

- **P-FPC-06:** an `execute()` with no return value, by `return;` or none at all, counts like a JSON or raw result, unless it may render a layout: a call whose name contains `layout` or `render`, a call on a page, layout or view object, or a call of its own class other than `getRequest()` and `getResponse()`.
- An explicit `return null;` is unchanged.

## Impact

Fewer P-FPC-06 findings, at `error`; none added.
