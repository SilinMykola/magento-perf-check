# Proposal

## Why

The experimental rules had not been graded on the `github` part of the corpus: 141 findings on 50 modules no round used. Round 20 grades up to 12 per rule. Two false positives point at gaps that are cheap to close.

## What Changes

- `docs/calibration/round-20.md` and `graded/round-20/`: the round, with its run stored as `runs/github/round-20.json`. No rule changes status.
- P-DI-02 takes the constructor fallback written as a full ternary, `$x !== null ? $x : ObjectManager::getInstance()->get(...)`, as allowed, like `?:` and `??`: P-DI-06 already marks that call as the fallback. The fallback P-DI-02 allows is narrowed to one `get()` or `create()` call with plain arguments: before, any chain of calls on the marked object manager passed, such as `->get(X::class)->load(1)`.
- X-LAY-01 leaves out `hyvareactcheckout_reactcheckout_` handles, the checkout page of Hyvä React Checkout, with the personal pages of known modules.
