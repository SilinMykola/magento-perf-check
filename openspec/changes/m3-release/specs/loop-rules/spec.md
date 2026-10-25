# Spec Delta

## MODIFIED Requirements

### Requirement: P-LOOP-02 collection created and loaded in a loop
P-LOOP-02 SHALL report, as `error`, a collection that is created and then loaded within one loop iteration. A collection is created by `create()` returning a database collection, either directly in a chain or through a variable assigned inside the same loop body. It is loaded by `load()`, `getItems()`, `getSize()`, `getFirstItem()` or `count()`.

#### Scenario: Factory in loop
- **WHEN** a `foreach` body runs `$this->collectionFactory->create()->addFieldToFilter('parent_id', $id)->getItems()`
- **THEN** P-LOOP-02 reports an error

#### Scenario: Variable created in loop
- **WHEN** a loop body assigns `$c = $this->collectionFactory->create();` and later calls `$c->getSize()`
- **THEN** P-LOOP-02 reports an error

#### Scenario: Collection created before the loop
- **WHEN** the collection is created and loaded before the loop and the loop only iterates it
- **THEN** P-LOOP-02 reports nothing

#### Scenario: Collection created in the iterated expression
- **WHEN** `foreach ($this->collectionFactory->create()->load() as $item)` iterates a collection created in the `foreach` expression itself, outside any other loop
- **THEN** P-LOOP-02 reports nothing, because that expression runs once

#### Scenario: Iterated expression of an inner loop
- **WHEN** the same `foreach` is nested in the body of another loop
- **THEN** P-LOOP-02 reports an error, because the expression runs once per outer iteration
