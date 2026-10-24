# P-LOOP-07: calibration sample

4 of 4 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-catalog-rule-configurable/Plugin/CatalogRule/Model/Rule/ConfigurableProductHandler.php:82` (core)

[P-LOOP-07] Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::getChildrenIds() is called inside a loop: it queries the database for one product per call. Collect the IDs first; getChildrenIds() and getParentIdsByChild() accept arrays.

```
    76              }
    77          }
    78  
    79          foreach ($this->configurableProductsProvider->getIds(array_keys($productIds)) as $configurableProductId) {
    80              if (!isset($this->childrenProducts[$configurableProductId])) {
    81                  $this->childrenProducts[$configurableProductId] =
>   82                      $this->configurable->getChildrenIds($configurableProductId)[0];
    83              }
    84  
    85              $parentValidationResult = isset($productIds[$configurableProductId])
    86                  ? array_filter($productIds[$configurableProductId])
    87                  : [];
    88              $processAllChildren = !$productsFilter || in_array($configurableProductId, $productsFilter);
```

## 2. `app/code/Algolia/AlgoliaSearch/Helper/Entity/ProductHelper.php:325` (packagist)

[P-LOOP-07] Magento\Catalog\Model\Product\Type\AbstractType::getParentIdsByChild() is called inside a loop: it queries the database for one product per call. Collect the IDs first; getChildrenIds() and getParentIdsByChild() accept arrays.

```
   319       */
   320      public function getParentProductIds(array $productIds): array
   321      {
   322          $this->logger->startProfiling(__METHOD__);
   323          $parentIds = [];
   324          foreach ($this->getCompositeTypes() as $typeInstance) {
>  325              $parentIds = array_merge($parentIds, $typeInstance->getParentIdsByChild($productIds));
   326          }
   327  
   328          $this->logger->stopProfiling(__METHOD__);
   329  
   330          return $parentIds;
   331      }
```

## 3. `vendor/magento/module-catalog-inventory/Model/StockIndex.php:238` (core)

[P-LOOP-07] Magento\Catalog\Model\Product\Type\AbstractType::getParentIdsByChild() is called inside a loop: it queries the database for one product per call. Collect the IDs first; getChildrenIds() and getParentIdsByChild() accept arrays.

```
   232       */
   233      protected function processParents($productId, $websiteId)
   234      {
   235          $parentIds = [];
   236          foreach ($this->getProductTypeInstances() as $typeInstance) {
   237              /* @var ProductType\AbstractType $typeInstance */
>  238              $parentIds[] = $typeInstance->getParentIdsByChild($productId);
   239          }
   240  
   241          $parentIds = array_merge([], ...$parentIds);
   242  
   243          if (empty($parentIds)) {
   244              return;
```

## 4. `vendor/magento/module-configurable-product/Model/ResourceModel/Product/Type/Configurable/Attribute/Collection.php:254` (core)

[P-LOOP-07] Magento\ConfigurableProduct\Model\Product\Type\Configurable::getUsedProducts() is called inside a loop: it queries the database for one product per call. Collect the IDs first; getChildrenIds() and getParentIdsByChild() accept arrays.

```
   248       * @return $this
   249       * @deprecated 100.1.1
   250       */
   251      public function _addAssociatedProductFilters()
   252      {
   253          foreach ($this->products as $product) {
>  254              $this->getProductType()->getUsedProducts(
   255                  $product,
   256                  $this->getColumnValues('attribute_id') // Filter associated products
   257              );
   258          }
   259          return $this;
   260      }
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-catalog-rule-configurable/Plugin/CatalogRule/Model/Rule/ConfigurableProductHandler.php:82` |  |  | re-check |  |
| 2 | `app/code/Algolia/AlgoliaSearch/Helper/Entity/ProductHelper.php:325` |  |  | re-check |  |
| 3 | `vendor/magento/module-catalog-inventory/Model/StockIndex.php:238` |  |  | re-check |  |
| 4 | `vendor/magento/module-configurable-product/Model/ResourceModel/Product/Type/Configurable/Attribute/Collection.php:254` |  |  |  |  |
