# Proposal

## Why

An independent review of P-COL-04 found three wrong results that follow from how the collection facts are tracked. A getter after `create()` still counted as a collection the function created, so `$factory->create()->getItemsCollection()` was reported although the spec says a collection from a method of what the function created is not. `limit()` counted only directly on `getSelect()`, so `getSelect()->order('id')->limit(10)` was treated as no limit. A `foreach` that destructures its value kept the old facts of the variables it assigns.

## What Changes

- **Created here:** a getter other than `getItems()` after `create()` ends the chain, in one statement or through an assignment.
- **Limit:** `limit()` and `limitPage()` count anywhere after `getSelect()` in a chain. This also applies to P-COL-02, which reads the same fact.
- **Foreach:** every variable the value assigns loses its old facts, including in `[$a, $b]`.

## Impact

The default set does not change: P-COL-02 and P-COL-04 are off by default.
