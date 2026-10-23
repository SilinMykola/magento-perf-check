# Spec Delta

## ADDED Requirements

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

### Requirement: P-LOOP-03 save or delete in a loop
P-LOOP-03 SHALL report, as `warning`, in loop context:
- `save()` or `delete()` on an `AbstractModel` subtype;
- `save()`, `delete()` or `deleteById()` on a type named `*RepositoryInterface`, or one that implements such an interface;
- `save()` or `delete()` on a resource model (subtype of `Magento\Framework\Model\ResourceModel\Db\AbstractDb`).

#### Scenario: Repository save in foreach
- **WHEN** `$this->productRepository->save($product)` is called inside a `foreach`
- **THEN** P-LOOP-03 reports a warning

#### Scenario: Save outside loop
- **WHEN** `save()` is called once after the loop
- **THEN** P-LOOP-03 reports nothing

### Requirement: P-LOOP-07 composite product type methods in a loop
P-LOOP-07 SHALL report, as `warning`, calls in loop context to:
- `getUsedProducts`, `getConfigurableAttributes`, `getChildrenIds`, `getParentIdsByChild`, `getUsedProductIds` or `getAssociatedProducts`, on a subtype of `Magento\Catalog\Model\Product\Type\AbstractType` or on a configurable product type resource model;
- `getIdBySku`, on `Magento\Catalog\Model\Product` or `Magento\Catalog\Model\ResourceModel\Product`.

#### Scenario: Used products per parent
- **WHEN** `$this->configurableType->getUsedProducts($product)` is called inside a `foreach` over products
- **THEN** P-LOOP-07 reports a warning

#### Scenario: getIdBySku in loop
- **WHEN** `$this->productResource->getIdBySku($sku)` is called inside a loop
- **THEN** P-LOOP-07 reports a warning

#### Scenario: Batch call outside loop
- **WHEN** `getParentIdsByChild($childIds)` is called once with an array before the loop
- **THEN** P-LOOP-07 reports nothing
