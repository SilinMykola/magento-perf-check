# Proposal

## Why

A third review of the P-COL-02 changes from the 0.13.0 reviews found:

- The `create()` guard covered only calls such as `load()` and `setPageSize()`. A `foreach` over `$this->collectionFactory->create()`, `count($this->collectionFactory->create())` and `$this->collectionFactory->create()->getSize() === 1` still marked `$this->collectionFactory` as loaded or limited, so a later `$this->collectionFactory->create()->getFirstItem()` was not reported, contrary to the spec.
- Row uses were counted by the root of the chain, so `$this->collectionFactory->create()->getItems()` anywhere in a function counted as a use of the rows of every `$this->collectionFactory->create()->getFirstItem()` in it, which then was not reported. P-COL-01 reads the same count on `count()`.
- After `$this->hits[$sku] ??= 0` or `$count[$sku] += 1`, every variable in the target, the key `$sku` included, lost its filters, so `$collection->addFieldToFilter('sku', $sku)` no longer counted and `getFirstItem()` was reported.

## What Changes

- `CollectionStateVisitor` records no load or limit on the root of a chain that calls `create()` for a `foreach`, `count()` or a `getSize()` comparison either, and counts no row use for such a chain.
- An element write forgets only the filters whose value reads the array (or `$this` property) written; list destructuring recurses into its items.
- P-COL-01 follows: a load or the rows of one `create()` no longer silence `count()` of another.
- The X-DB-01 case-insensitive lookup gets a rule-level fixture in the XML golden test.
