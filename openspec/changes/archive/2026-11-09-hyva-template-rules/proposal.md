# Proposal

## Why

A review of a Magento 2.4.5 project with a Hyvä child theme found two template problems no rule sees. Its product list item template declared `initWishlist()` and `initCompare()` in a `<script>`, so each of the products on a page sent about 4.8 KB of the same JavaScript again; the Hyvä default theme declares them once per list. Its menu template built the category tree through `$viewModels->require(Navigation::class)->getNavigation(4)` and never used it, while `require()` still added the navigation's cache tags to the menu.

## What Changes

- **T-03 (new, experimental, `warning`):** a named JavaScript function declared in a `<script>` rendered once per item.
- **T-04 (new, experimental, `warning`):** the result of `$viewModels->require()` assigned to a variable the template never reads.
- Both rest on Hyvä code pinned to a commit, level C, so they stay experimental until calibration.

## Impact

Nothing changes unless a project enables them.
