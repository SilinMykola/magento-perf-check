## MODIFIED Requirements

### Requirement: P-LOOP-03 save or delete in a loop
P-LOOP-03 SHALL report, as `info`, in loop context:
- `save()` or `delete()` on an `AbstractModel` subtype;
- `save()`, `delete()` or `deleteById()` on a type named `*RepositoryInterface`, or one that implements such an interface;
- `save()` or `delete()` on a resource model (subtype of `Magento\Framework\Model\ResourceModel\Db\AbstractDb`).

It SHALL NOT report a call that runs at most once per run of the loop: a call in a statement of its own, or in a `return`, after which every way on in the same iteration reaches `return`, `throw`, or a `break` that leaves the only loop of the function around it, with no `continue`, `break` or `goto` on the way. A `break` inside a `switch` leaves the `switch`, so what follows the `switch` decides. Calls inside `try`, in a closure, or in the condition of an `if` are still reported.

Calibration lowered it from `warning`: most correct findings loop over small, capped sets.

#### Scenario: Repository save in foreach
- **WHEN** `$this->productRepository->save($product)` is called inside a `foreach`
- **THEN** P-LOOP-03 reports an info finding

#### Scenario: Save outside loop
- **WHEN** `save()` is called once after the loop
- **THEN** P-LOOP-03 reports nothing

#### Scenario: Save followed by leaving the loop
- **WHEN** a `foreach` runs `if ($bookmark->getCurrent()) { $this->bookmarkRepository->save($bookmark); break; }`
- **THEN** P-LOOP-03 reports nothing
- **WHEN** the save is followed by `return` or `throw`, or sits in a nested `if` that the `break` follows
- **THEN** P-LOOP-03 reports nothing
- **WHEN** a `continue` comes between the save and the `break`, or the `break` only leaves an inner loop or a `switch` that the loop goes on after
- **THEN** P-LOOP-03 reports the call
