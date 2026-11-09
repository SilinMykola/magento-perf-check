# Proposal

## Why

P-LOOP-03 no longer reports a save after which the loop is left, because it runs at most once. The other loop rules report the same shape: `foreach ($ids as $id) { return $this->repository->getById($id); }` is one load, not one per item. In the Magento core this gives 2 P-LOOP-01 findings, at `error`, and 6 P-LOOP-10 findings.

## What Changes

- **All loop rules:** a method call after which the loop is left in the same iteration does not count as loop context, with the conditions P-LOOP-03 already uses. The condition moves from the P-LOOP-03 requirement to a requirement shared by the loop rules, next to the one for batch loops.
- **P-LOOP-02:** a load that the loop is left right after no longer takes the one finding of its collection, so a later load of the same collection in the iteration is reported.
- **X-GQL-02** stays as it is: a resolver runs once per element of a list, so leaving a PHP loop inside it does not make a query run once.

## Impact

Fewer findings of P-LOOP-01 (default set, `error`) and P-LOOP-10 (default set, `warning`) in the Magento core; none in the calibration corpus. No finding is added. The rule for experimental P-LOOP-04 and P-LOOP-06 changes the same way.
