# Proposal

## Why

P-LOOP-12 counts every query of the called method, also one that runs only as a fallback: `return $attribute ?: $this->attributeFactory->create()->load($id);` loads only when the parent method found nothing, and `$known[$id] ?? $this->repository->getById($id)` only when the map has no entry. On private Magento 2.1 and 2.2 stores two of the ten graded P-LOOP-12 findings had this shape, both graded false.

## What Changes

- A query in the fallback of `$a ?: $b` or `$a ?? $b` is no longer counted as a query on every call. A query on the left of `?:` or `??`, and a query in either branch of a full ternary, still count.
- Not changed: a query inside an `if` branch. The graded findings had both kinds there: a load under `if ($id)` that the caller never reaches (false) and the same method called with an id (true), so the branch alone does not tell them apart. A query under `if (!$arg instanceof Shipment)` whose caller passes a Shipment is also left: the callers' argument types were a plain `DataObject` or unknown, so the guard cannot be proven. An inline `@var` that names the wrong model is trusted as PHPStan trusts it.
