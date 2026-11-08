## ADDED Requirements

### Requirement: Calls the loop is left right after are not loop context
A method call SHALL NOT count as loop context for the loop rules when it is in a statement of its own, or in a `return`, and every way on from it in the same iteration reaches `return`, `throw`, or a `break` that leaves the only loop of the function around it, with no `continue`, `break` or `goto` on the way. Such a call runs at most once per run of the loop. For P-LOOP-02 it SHALL NOT take the one finding of its collection either.

#### Scenario: Load returned from a loop
- **WHEN** a `foreach` over ids runs `return $this->productRepository->getById($id);`
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Resource load before return
- **WHEN** a `foreach` runs `$this->resource->load($product, $id);` and then `return $product;`
- **THEN** P-LOOP-10 reports nothing

#### Scenario: Collection loaded again after a leaving load
- **WHEN** a `foreach` creates a collection, returns `$collection->getItems()` inside an `if`, and otherwise calls `$collection->getItems()` again
- **THEN** P-LOOP-02 reports the second call

#### Scenario: Calls that still count
- **WHEN** a `break` inside a `switch` is followed, after the `switch`, by the end of the loop body
- **THEN** the call before it counts as loop context
- **WHEN** the call is inside `try`, in a closure, or in the condition of an `if`, or the `break` leaves only an inner loop
- **THEN** the call counts as loop context

## MODIFIED Requirements

### Requirement: P-LOOP-03 save or delete in a loop
P-LOOP-03 SHALL report, as `info`, in loop context:
- `save()` or `delete()` on an `AbstractModel` subtype;
- `save()`, `delete()` or `deleteById()` on a type named `*RepositoryInterface`, or one that implements such an interface;
- `save()` or `delete()` on a resource model (subtype of `Magento\Framework\Model\ResourceModel\Db\AbstractDb`).

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
