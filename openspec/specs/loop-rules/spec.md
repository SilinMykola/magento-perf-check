# loop-rules Specification

## Purpose
N+1 rules: catch database work repeated once per iteration of a loop where one batched query would do.

## Requirements

### Requirement: P-LOOP-01 model load or repository get in a loop
P-LOOP-01 SHALL report, in loop context, either:
- A call to `load()` on an object whose type is a subtype of `Magento\Framework\Model\AbstractModel`.
- A call to `get()` or `getById()` on an object whose type is an interface, or implements an interface, whose short name ends with `RepositoryInterface`.

Severity: `error`. The message SHALL name the class and the method.

#### Scenario: Model load in foreach
- **WHEN** `$product->load($id)` is called inside a `foreach` body and `$product` is a `Magento\Catalog\Model\Product`
- **THEN** P-LOOP-01 reports an error

#### Scenario: Repository get in for
- **WHEN** `$this->productRepository->getById($id)` is called inside a `for` body and the property type is `Magento\Catalog\Api\ProductRepositoryInterface`
- **THEN** P-LOOP-01 reports an error

#### Scenario: Repository get inside array_map
- **WHEN** `$this->customerRepository->get($email)` is called inside a closure passed to `array_map`
- **THEN** P-LOOP-01 reports an error

### Requirement: P-LOOP-01 scope
P-LOOP-01 SHALL NOT report:
- The same calls outside loop context, including in the iterable expression of `foreach`.
- `load()` on collections or resource models.
- `get()` on types that are not repositories (for example a config or registry object).
- Receivers whose type PHPStan cannot resolve.
- `load()` on `Magento\Directory\Model\Currency`, which only sets the currency code.
- `get()` or `getById()` on `Magento\Store\Api\StoreRepositoryInterface`, `WebsiteRepositoryInterface` or `GroupRepositoryInterface`, which read cached configuration.

#### Scenario: Outside a loop
- **WHEN** `$this->productRepository->getById($id)` is called once in a method body without a loop
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Batch fetch before loop
- **WHEN** `foreach ($this->productRepository->getList($criteria)->getItems() as $product)` iterates the result
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Collection load in loop
- **WHEN** `$collection->load()` is called inside a loop
- **THEN** P-LOOP-01 reports nothing (collection rules cover this)

#### Scenario: Non-repository get
- **WHEN** `$this->scopeConfig->getValue(...)` or `$this->request->get('id')` is called inside a loop
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Unknown type
- **WHEN** the receiver's type is `mixed`
- **THEN** P-LOOP-01 reports nothing

#### Scenario: Load that runs no query
- **WHEN** `$currency->load($code)` or `$this->storeRepository->getById($id)` is called inside a loop
- **THEN** P-LOOP-01 reports nothing

### Requirement: P-LOOP-02 collection created and loaded in a loop
P-LOOP-02 SHALL report, as `error`, a collection that is created and then loaded within one loop iteration. A collection is created by `create()` returning a database collection, either directly in a chain or through a variable assigned inside the same loop body. It is loaded by `load()`, `getItems()`, `getSize()`, `getFirstItem()` or `count()`. One collection SHALL give one finding, on its first loading call.

#### Scenario: Factory in loop
- **WHEN** a `foreach` body runs `$this->collectionFactory->create()->addFieldToFilter('parent_id', $id)->getItems()`
- **THEN** P-LOOP-02 reports an error

#### Scenario: Variable created in loop
- **WHEN** a loop body assigns `$c = $this->collectionFactory->create();` and later calls `$c->getSize()`
- **THEN** P-LOOP-02 reports an error

#### Scenario: Collection created before the loop
- **WHEN** the collection is created and loaded before the loop and the loop only iterates it
- **THEN** P-LOOP-02 reports nothing

#### Scenario: Several calls on one collection
- **WHEN** a loop body assigns `$c = $this->collectionFactory->create()->load();` and later calls `$c->getSize()` and `$c->getItems()`
- **THEN** P-LOOP-02 reports one error, on `load()`

#### Scenario: Assigned again
- **WHEN** a loop body loads `$c`, assigns `$c = $this->collectionFactory->create();` again and loads it
- **THEN** P-LOOP-02 reports two errors

#### Scenario: Batches
- **WHEN** a `foreach` over `array_chunk($ids, 1000)`, directly or through a variable, creates and loads one collection per batch
- **THEN** P-LOOP-02 reports nothing

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

### Requirement: Batch loops are not loop context
The body of a `foreach` over `array_chunk()`, called directly or held by a variable assigned earlier in the same function, SHALL NOT count as loop context for the loop rules: it runs once per batch. A loop inside that body SHALL count.

#### Scenario: Load per batch
- **WHEN** a `foreach` over `array_chunk($ids, 100)` calls `$this->repository->getList($criteria)`
- **THEN** P-LOOP-09 reports nothing

#### Scenario: Loop inside a batch
- **WHEN** a `foreach` over a batch, inside a `foreach` over `array_chunk($ids, 100)`, creates and loads a collection
- **THEN** P-LOOP-02 reports an error

### Requirement: P-LOOP-09 repository getList per item
P-LOOP-09 SHALL report, as `warning`, `getList()` whose first parameter is a `Magento\Framework\Api\SearchCriteriaInterface`, on a type that is, or implements, an interface named `*RepositoryInterface`, in the body of a `foreach` or of a closure passed straight to `array_map()`, `array_walk()` or `walk()`. It SHALL NOT report `while`, `do` or `for` loops.

#### Scenario: List per item
- **WHEN** a `foreach` body calls `$this->repository->getList($criteria)` on a `ProductRepositoryInterface`
- **THEN** P-LOOP-09 reports a warning

#### Scenario: Paging
- **WHEN** a `do` loop calls `getList()` until a page is empty
- **THEN** P-LOOP-09 reports nothing

#### Scenario: Iterated once
- **WHEN** `foreach ($this->repository->getList($criteria)->getItems() as $item)` iterates the result
- **THEN** P-LOOP-09 reports nothing

#### Scenario: Other first parameter
- **WHEN** a `foreach` body calls `$this->optionRepository->getList($sku)` or `$this->attributeRepository->getList($entityType, $criteria)`
- **THEN** P-LOOP-09 reports nothing

### Requirement: P-LOOP-10 resource model load in a loop
P-LOOP-10 SHALL report, as `warning`, `load()` in loop context on a subtype of `Magento\Framework\Model\ResourceModel\Db\AbstractDb`.

#### Scenario: Load in foreach
- **WHEN** a `foreach` body calls `$this->resource->load($product, $id)`
- **THEN** P-LOOP-10 reports a warning

#### Scenario: Unknown type
- **WHEN** the receiver's type is `object`
- **THEN** P-LOOP-10 reports nothing

### Requirement: P-LOOP-05 getCategoryIds in a loop
P-LOOP-05 SHALL report, as `warning`, `getCategoryIds()` in loop context on `Magento\Catalog\Model\Product`, or on the value variable of a `foreach` over a product collection or its `getItems()`. It SHALL NOT report the call when `addCategoryIds()` is called earlier in the same function.

#### Scenario: Product collection item
- **WHEN** `foreach ($productCollection as $product)` calls `$product->getCategoryIds()`
- **THEN** P-LOOP-05 reports a warning

#### Scenario: Category ids added
- **WHEN** `$collection->addCategoryIds();` precedes that loop in the same function
- **THEN** P-LOOP-05 reports nothing

#### Scenario: Something else
- **WHEN** the receiver is not known to be a product
- **THEN** P-LOOP-05 reports nothing

### Requirement: P-LOOP-04 product URL inside a loop
P-LOOP-04 SHALL report, as `warning`, `getProductUrl()` or `getUrlInStore()` on a product, or `getProductUrl()`, `getUrl()` or `getUrlInStore()` on `Magento\Catalog\Model\Product\Url`, inside a loop, unless `addUrlRewrite()` was called earlier in the same function. It is off by default.

#### Scenario: Loop over a product collection
- **WHEN** a function iterates a product collection and calls `$product->getProductUrl()`
- **THEN** P-LOOP-04 reports the call

#### Scenario: Rewrites added
- **WHEN** the function called `$collection->addUrlRewrite()` before the loop
- **THEN** P-LOOP-04 reports nothing

#### Scenario: Outside a loop
- **WHEN** `getProductUrl()` is called on one product outside a loop
- **THEN** P-LOOP-04 reports nothing

#### Scenario: Another class
- **WHEN** a block's own `getProductUrl($product)` is called in a loop
- **THEN** P-LOOP-04 reports nothing

### Requirement: P-LOOP-06 attribute option text inside a loop
P-LOOP-06 SHALL report, as `warning`, `getAttributeText()` on a product, or `getOptionText()` on `Magento\Eav\Model\Entity\Attribute\Source\Table`, inside a loop. It is off by default.

#### Scenario: Attribute text per product
- **WHEN** a loop over products calls `$product->getAttributeText('color')`
- **THEN** P-LOOP-06 reports the call

#### Scenario: Option text on the table source
- **WHEN** a loop calls `getOptionText()` on a `Source\Table`
- **THEN** P-LOOP-06 reports the call

#### Scenario: Other source model
- **WHEN** a loop calls `getOptionText()` on another source model
- **THEN** P-LOOP-06 reports nothing

#### Scenario: Outside a loop
- **WHEN** `getAttributeText()` is called outside a loop
- **THEN** P-LOOP-06 reports nothing

### Requirement: P-LOOP-02 paging
P-LOOP-02 SHALL not report a collection created in a `while`, `do` or `for` loop outside a loop over items, when `setPageSize()`, `setPage()`, `limit()` or `limitPage()` sets a row count other than the literal 1 on it before it loads: one query per page is what paging is for. A `foreach` and a `for` with `count()` or `sizeof()` in its condition are loops over items. A limit in a loop over items, a limit of one row, and `setCurPage()` alone SHALL still be reported.

#### Scenario: Pages in a while loop
- **WHEN** a `while` loop creates a collection, calls `getSelect()->limit(500)` and loads it
- **THEN** P-LOOP-02 reports nothing

#### Scenario: One row per item
- **WHEN** a `foreach` over ids creates a collection, calls `setPageSize(1)` and `getFirstItem()`
- **THEN** P-LOOP-02 reports the call

#### Scenario: Written in one chain
- **WHEN** a `do` loop has `$c = $factory->create(); $c->setPageSize(100)->setCurPage($p)->load();`
- **THEN** P-LOOP-02 reports nothing

#### Scenario: One row in a while loop
- **WHEN** a `while` loop creates a collection and calls `setPageSize(1)` and `load()`
- **THEN** P-LOOP-02 reports the call

#### Scenario: Loop over a count
- **WHEN** a `for` with `count($ids)` in its condition creates a collection and calls `setPageSize(100)` and `load()`
- **THEN** P-LOOP-02 reports the call

### Requirement: Product interface counts as a product
P-LOOP-04 and P-LOOP-06 SHALL treat a receiver typed `Magento\Catalog\Api\Data\ProductInterface` as a product, because a repository returns that type and the model behind it runs the same queries. P-LOOP-05 SHALL NOT, because `ProductRepository::getList()` returns that type after calling `addCategoryIds()`.

#### Scenario: Product URL on the interface
- **WHEN** a loop calls `getProductUrl()` on a variable typed `ProductInterface`
- **THEN** P-LOOP-04 reports that call

#### Scenario: Category IDs on the interface
- **WHEN** a loop calls `getCategoryIds()` on a variable typed `ProductInterface`
- **THEN** P-LOOP-05 reports nothing

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

### Requirement: P-LOOP-11 object manager in a loop
P-LOOP-11 SHALL report, as `error`, a call of `get()` or `create()` on `Magento\Framework\ObjectManagerInterface` in loop context, whether the object manager comes from `ObjectManager::getInstance()` or is injected. Classes whose name ends in `Factory` or `\Proxy` SHALL be left out. A call the loop is left right after SHALL NOT count, as for the other loop rules.

#### Scenario: Injected object manager creates per row
- **WHEN** a `foreach` over rows calls `$this->objectManager->create(Item::class, ['data' => $row])`
- **THEN** P-LOOP-11 reports an error

#### Scenario: Factory builds a list
- **WHEN** a class named `ThingFactory` calls `$this->objectManager->create()` in a loop
- **THEN** P-LOOP-11 reports nothing
