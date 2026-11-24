# Proposal

## Why

A fourth review of P-COL-02 found two gaps in how a write drops a filter whose value it changes:

- A plain `=` on an element, as in `$c->addIdFilter($m['id']); $m['id'] = 5;`, never reached the code that forgets filter values, because only `=&`, compound assignments and destructuring did. The spec already says such a write gives the array a new value.
- Since the third review, a write whose base is neither a variable nor a `$this` property, as in `$o->ids = ...`, `[$o->ids] = [5]` or `foreach ([5] as $o->ids)`, forgot nothing; before it forgot the variables in the target.

## What Changes

- Every plain `=` whose target is not a variable or a `$this` property (an element, a property of another object, destructuring) goes through the same forgetting as the other writes.
- A write walks from an element or a property to the variable it goes through and forgets the filters whose value reads it: `$map` for `$map[$k]`, `$this->hits` for `$this->hits[$k]`, `$o` for `$o->items[$k]`; never a key, and never `$this` itself.
- The yes/no `UNIQUE_FILTER` fact is no longer recorded on the root of a chain that makes a new object (`create()` or a getter), as for the filter values. P-COL-04 reads that fact on a `foreach`, and a `foreach` over a `create()` chain never took it from the factory, so its results do not change for that case.

PHPStan types a property fetch stored from the filter call as it was there, so the rule test cannot show a write through another object; a unit test of the visitor checks the filter values it hands to `getFirstItem()`.
