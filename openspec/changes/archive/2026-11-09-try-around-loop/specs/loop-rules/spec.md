## MODIFIED Requirements

### Requirement: Calls the loop is left right after are not loop context
A method call SHALL NOT count as loop context for the loop rules when it is in a statement of its own, or in a `return`, and every way on from it in the same iteration reaches `return`, `throw`, or a `break` that leaves the only loop of the function around it, with no `continue`, `break` or `goto` on the way. Such a call runs at most once per run of the loop. For P-LOOP-02 it SHALL NOT take the one finding of its collection either. A `try` outside any loop of the function SHALL NOT change this for the loops in its body, its `catch` blocks or its `finally`: an exception reaches such a `catch` only after leaving the loop.

#### Scenario: Load returned from a loop
- **WHEN** a `foreach` over ids runs `return $this->productRepository->getById($id);`
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Load returned from a loop inside try
- **WHEN** a method runs `try { foreach ($ids as $id) { return $this->productRepository->getById($id); } } catch (\Throwable $e) { return null; }`
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
- **WHEN** the call is inside a `try` in the loop, or in a loop nested in such a `try`, in a closure, or in the condition of an `if`, or the `break` leaves only an inner loop
- **THEN** the call counts as loop context
