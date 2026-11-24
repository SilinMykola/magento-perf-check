# Proposal

## Why

P-LOOP-12 leaves out a query in the fallback of `?:` or `??`, because the value before it is usually a cache or a value found earlier, as in `$this->cache[$id] ?? $this->cache[$id] = $this->repository->getById($id)` or `$attribute ?: $this->attributeFactory->create()->load($id)`. Review found the same shape where the value before the fallback is never filled: `return $this->names[$id] ?? $this->repository->getById($id)->getName();` in a class that declares `$names = []` and never writes it. The fallback then runs on every call, a real N+1 that the rule stopped reporting.

## What Changes

- The fallback still counts when the value before it reads a property, or an element of one, that the class itself declares `private` and never fills: no default other than `[]` or `null`, no assignment of another value to the property in any method of the class, and no assignment to an element of it. Static properties are treated the same way.
- Not changed: a property that the class does not declare, a `protected` or `public` property, or a property of a trait (a parent, child or using class may fill it), a property filled by constructor promotion or by any assignment, and a value before the fallback that is not a property, such as a parameter, a local variable or a method result. Those fallbacks stay left out. The two real cases the fallback rule was built for keep their result: Magento core `TierPriceFactory::create()` reads `$rawPrice['customer_group_code']`, a parameter its caller fills from a batch lookup, and a private store's `Configurable::getAttributeById()` reads `$attribute`, a local variable from `parent::getAttributeById()`.
