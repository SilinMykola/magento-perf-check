# Proposal

## Why

`Magento\Framework\DataObject::__call()` implements `set*()` and `uns*()` and returns the object itself, but PHPStan types such a magic call as `mixed`. A chain like `$post->setStatus($status)->save()` in a loop therefore lost the model's type, and P-LOOP-03 missed the save. Seen in an admin mass action of a third-party module on a Magento 2.3.3 project, whose `load()` in the same loop was reported.

## What Changes

- **Extension:** a `set*()` or `uns*()` call on a subclass of `DataObject` that the class neither declares nor documents with `@method` is typed as the object it is called on. Every loop rule and every rule that reads a receiver's type sees the chain.
- Getters (`get*()`, `has*()`) stay untyped: their value is whatever was stored.
- A class outside `DataObject` with its own `__call()` is left alone.

## Impact

More findings, never fewer, where code chains a magic setter into a call that a rule checks.
