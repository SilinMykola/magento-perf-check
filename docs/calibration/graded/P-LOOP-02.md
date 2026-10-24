# P-LOOP-02: calibration sample

6 of 6 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:243` (core)

[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection is created and loaded (getItems()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.

```
   237              $nodeChildren = $this->_collectionFactory->create()->setAttributeGroupFilter(
   238                  $node->getId()
   239              )->addVisibleFilter()->load();
   240  
   241              if ($nodeChildren->getSize() > 0) {
   242                  $item['children'] = [];
>  243                  foreach ($nodeChildren->getItems() as $child) {
   244                      $item['children'][] = $this->attributeMapper->map($child);
   245                  }
   246              }
   247  
   248              $items[] = $item;
   249          }
```

## 2. `vendor/magento/module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:241` (core)

[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection is created and loaded (getSize()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.

```
   235              $item['allowDrag'] = true;
   236  
   237              $nodeChildren = $this->_collectionFactory->create()->setAttributeGroupFilter(
   238                  $node->getId()
   239              )->addVisibleFilter()->load();
   240  
>  241              if ($nodeChildren->getSize() > 0) {
   242                  $item['children'] = [];
   243                  foreach ($nodeChildren->getItems() as $child) {
   244                      $item['children'][] = $this->attributeMapper->map($child);
   245                  }
   246              }
   247  
```

## 3. `vendor/magento/module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:237` (core)

[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection is created and loaded (load()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.

```
   231              $item['text'] = $this->escapeHtml($node->getAttributeGroupName());
   232              $item['id'] = $node->getAttributeGroupId();
   233              $item['cls'] = 'folder';
   234              $item['allowDrop'] = true;
   235              $item['allowDrag'] = true;
   236  
>  237              $nodeChildren = $this->_collectionFactory->create()->setAttributeGroupFilter(
   238                  $node->getId()
   239              )->addVisibleFilter()->load();
   240  
   241              if ($nodeChildren->getSize() > 0) {
   242                  $item['children'] = [];
   243                  foreach ($nodeChildren->getItems() as $child) {
```

## 4. `vendor/magento/module-catalog-import-export/Model/Import/Product/Type/AbstractType.php:402` (core)

[P-LOOP-02] Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection is created and loaded (getItems()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.

```
   396          }
   397  
   398          foreach (array_chunk($attributesToLoadFromTable, 1000) as $ids) {
   399              $collection = $this->attributeOptionCollectionFactory->create();
   400              $collection->setAttributeFilter(['in' => $ids]);
   401              $collection->setStoreFilter(Store::DEFAULT_STORE_ID);
>  402              $options = $collection->getItems();
   403              foreach ($options as $option) {
   404                  $attributeId = $option->getAttributeId();
   405                  $value = strtolower($option->getValue());
   406                  $commonAttributes[$attributeId]['options'][$value] = $option->getOptionId();
   407              }
   408          }
```

## 5. `vendor/magento/module-review/Model/ResourceModel/Rating/Option/Vote/Collection.php:181` (core)

[P-LOOP-02] Magento\Review\Model\ResourceModel\Rating\Option\Collection is created and loaded (load()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.

```
   175          if (!$this->getSize()) {
   176              return $this;
   177          }
   178          foreach ($this->getItems() as $item) {
   179              /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $options */
   180              $options = $this->_ratingCollectionF->create();
>  181              $options->addRatingFilter($item->getRatingId())->load();
   182  
   183              if ($item->getRatingId()) {
   184                  $item->setRatingOptions($options);
   185              } else {
   186                  return $this;
   187              }
```

## 6. `vendor/magento/module-catalog/Model/ProductLink/CollectionProvider/LinkedMapProvider.php:150` (core)

[P-LOOP-02] Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection is created and loaded (getItems()) inside a loop: one query per iteration. Load one collection for all items before the loop and look items up in it.

```
   144  
   145              /** @var LinkedProductCollection $collection */
   146              $collection = $this->productCollectionFactory->create(['productIds' => $productIds[$type]]);
   147              $this->linkModel->setLinkTypeId($typeId);
   148              $collection->setLinkModel($this->linkModel);
   149              $collection->setIsStrongMode();
>  150              $found[$type] = $collection->getItems();
   151          }
   152  
   153          return $found;
   154      }
   155  
   156      /**
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:243` |  |  | re-check |  |
| 2 | `vendor/magento/module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:241` |  |  | re-check |  |
| 3 | `vendor/magento/module-catalog/Block/Adminhtml/Product/Attribute/Set/Main.php:237` |  |  | re-check |  |
| 4 | `vendor/magento/module-catalog-import-export/Model/Import/Product/Type/AbstractType.php:402` |  |  |  |  |
| 5 | `vendor/magento/module-review/Model/ResourceModel/Rating/Option/Vote/Collection.php:181` |  |  |  |  |
| 6 | `vendor/magento/module-catalog/Model/ProductLink/CollectionProvider/LinkedMapProvider.php:150` |  |  |  |  |
