# P-COL-03: calibration sample

13 of 13 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Dotdigitalgroup/Email/Model/ResourceModel/Review.php:152` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   146          //get the product ids for the collection
   147          foreach ($items as $item) {
   148              $productIds[] = $item->getProductId();
   149          }
   150  
   151          if (!empty($productIds)) {
>  152              $products = $this->productCollectionFactory->create()
   153                  ->addAttributeToSelect('*')
   154                  ->addFieldToFilter('entity_id', ['in' => $productIds]);
   155          }
   156  
   157          return $products;
   158      }
```

## 2. `app/code/Mageplaza/Productslider/Block/CategoryId.php:139` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   133  
   134                  $productIds = array_merge($productIds, $productId);
   135              }
   136          } else {
   137              $collection = $this->_productCollectionFactory->create();
   138              $category = $this->_categoryFactory->create()->load($catIds);
>  139              $collection->addAttributeToSelect('*')->addCategoryFilter($category);
   140  
   141              foreach ($collection as $item) {
   142                  $productIds[] = $item->getData('entity_id');
   143              }
   144          }
   145  
```

## 3. `app/code/Mageplaza/Productslider/Block/CustomProducts.php:108` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   102              ->addIdFilter($productIds)
   103              ->setPageSize($this->getProductsCount());
   104  
   105          $mpProductIds = $this->getProductParentIds($collection);
   106  
   107          $collection = $this->_productCollectionFactory->create()->addIdFilter($mpProductIds)->setVisibility($visibleProducts);
>  108          $collection->addMinimalPrice()
   109              ->addFinalPrice()
   110              ->addTaxPercents()
   111              ->addAttributeToSelect('*')
   112              ->addStoreFilter($this->getStoreId())->setPageSize($this->getProductsCount());
   113  
   114          return $collection;
```

## 4. `app/code/Dotdigitalgroup/Email/Model/Sync/Catalog/Exporter.php:184` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   178      private function filterProductsByStoreTypeAndVisibility(
   179          ?int $storeId,
   180          array $productIds,
   181          array $types,
   182          array $visibilities
   183      ): ProductCollection {
>  184          $productCollection = $this->productCollectionFactory->create()
   185              ->addAttributeToSelect('*')
   186              ->addAttributeToFilter(
   187                  'entity_id',
   188                  ['in' => $productIds]
   189              )->addUrlRewrite();
   190  
```

## 5. `app/code/Mageplaza/Productslider/Block/FeaturedProducts.php:39` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
    33       */
    34      public function getProductCollection()
    35      {
    36          $visibleProducts = $this->_catalogProductVisibility->getVisibleInCatalogIds();
    37  
    38          $collection = $this->_productCollectionFactory->create()->setVisibility($visibleProducts);
>   39          $collection->addMinimalPrice()
    40              ->addFinalPrice()
    41              ->addTaxPercents()
    42              ->addAttributeToSelect('*')
    43              ->addStoreFilter($this->getStoreId())
    44              ->setPageSize($this->getProductsCount())
    45              ->addAttributeToFilter('is_featured', '1');
```

## 6. `app/code/Dotdigitalgroup/Email/Model/Sync/Customer/CustomerDataManager.php:61` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
    55       * @param array $customerIds
    56       *
    57       * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
    58       */
    59      public function buildCustomerCollection(array $customerIds)
    60      {
>   61          $customerCollection = $this->customerCollectionFactory->create()
    62              ->addAttributeToSelect('*')
    63              ->addNameToSelect();
    64  
    65          $this->addBillingJoinAttributesToCustomerCollection($customerCollection);
    66          $this->addShippingJoinAttributesToCustomerCollection($customerCollection);
    67  
```

## 7. `app/code/Dotdigitalgroup/Email/Ui/DataProvider/WebBehaviourTracking.php:152` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   146              return null;
   147          }
   148  
   149          $categories = [];
   150  
   151          try {
>  152              $categoryCollection = $this->categoryCollectionFactory->create()
   153                  ->addAttributeToSelect('*')
   154                  ->addIdFilter($categoryIds);
   155  
   156              foreach ($categoryCollection as $category) {
   157                  $categories[] = $category->getName();
   158              }
```

## 8. `app/code/Mageplaza/Productslider/Block/MostViewedProducts.php:108` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   102              ->addStoreFilter($this->getStoreId())
   103              ->setPageSize($this->getProductsCount());
   104  
   105          $productIds = $this->getProductParentIds($collection);
   106  
   107          $collection = $this->_productCollectionFactory->create()->addIdFilter($productIds);
>  108          $collection->addMinimalPrice()
   109              ->addFinalPrice()
   110              ->addTaxPercents()
   111              ->addAttributeToSelect('*')
   112              ->addStoreFilter($this->getStoreId());
   113  
   114          return $collection;
```

## 9. `app/code/Mageplaza/Productslider/Block/CategoryId.php:128` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   122          if (is_array($catIds)) {
   123              $productId = [];
   124  
   125              foreach ($catIds as $cat) {
   126                  $collection = $this->_productCollectionFactory->create();
   127                  $category = $this->_categoryFactory->create()->load($cat);
>  128                  $collection->addAttributeToSelect('*')->addCategoryFilter($category);
   129  
   130                  foreach ($collection as $item) {
   131                      $productId[] = $item->getData('entity_id');
   132                  }
   133  
   134                  $productIds = array_merge($productIds, $productId);
```

## 10. `app/code/Mageplaza/Reports/Model/Api/CardManagement.php:302` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   296                  $result->setData($collection->getData());
   297                  break;
   298              case 'lifetimeSales':
   299                  $result->setData(['total' => $this->lifetimeSales->getTotal(false)]);
   300                  break;
   301              case 'mostViewedProducts':
>  302                  $collection = $this->productsFactory->create()->addAttributeToSelect(
   303                      '*'
   304                  )->addViewsCount();
   305                  if ($storeFilter !== null) {
   306                      $collection->addViewsCount()->setStoreId($storeId)->addStoreFilter($storeId);
   307                  }
   308                  $collection->setPageSize(5)->load();
```

## 11. `app/code/Reviewscouk/Reviews/Model/Feed/Generator.php:143` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   137      }
   138  
   139      // Keyset on entity_id: stable order, no overlapping/skipped pages, no separate count query.
   140      private function getProductCollection(StoreInterface $store, int $lastId)
   141      {
   142          $collection = $this->productCollectionFactory->create();
>  143          $collection
   144              ->setStoreId($store->getId())
   145              ->addMinimalPrice()
   146              ->addFinalPrice()
   147              ->addTaxPercents()
   148              ->addAttributeToSelect('*')
   149              ->addUrlRewrite()
```

## 12. `app/code/Buckaroo/Magento2/Model/Config/Source/Categorylist.php:115` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   109       *
   110       * @return mixed
   111       */
   112      public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
   113      {
   114          $collection = $this->categoryCollectionFactory->create();
>  115          $collection->addAttributeToSelect('*');
   116  
   117          // select only active categories
   118          if ($isActive) {
   119              $collection->addIsActiveFilter();
   120          }
   121  
```

## 13. `app/code/Mageplaza/Blog/Block/Post/RelatedProduct.php:151` (extended)

[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.

```
   145       * @return mixed
   146       */
   147      public function _getProductCollection()
   148      {
   149          if ($this->_productCollection === null) {
   150              $postId     = (int) $this->getRequest()->getParam('id');
>  151              $collection = $this->_productCollectionFactory->create()
   152                  ->addAttributeToSelect('*')
   153                  ->addStoreFilter();
   154  
   155              $collection->getSelect()
   156                  ->join(
   157                      ['product_post' => $collection->getTable('mageplaza_blog_post_product')],
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Dotdigitalgroup/Email/Model/ResourceModel/Review.php:152` | TP-low | Loads all attributes for the few products of one quote or order, used only for name, sku and URL in an email block. |  |  |
| 2 | `app/code/Mageplaza/Productslider/Block/CategoryId.php:139` | TP | Storefront slider loads every product of each slider category with all attributes only to read entity_id. |  |  |
| 3 | `app/code/Mageplaza/Productslider/Block/CustomProducts.php:108` | TP-low | Storefront slider collection is limited by setPageSize() to the slider's product count. |  |  |
| 4 | `app/code/Dotdigitalgroup/Email/Model/Sync/Catalog/Exporter.php:184` | TP | Catalog sync cron loads whole batches of products with all attributes while only the configured sync attributes and a fixed set of fields are exported. |  |  |
| 5 | `app/code/Mageplaza/Productslider/Block/FeaturedProducts.php:39` | TP-low | Storefront slider collection is limited by setPageSize() to the slider's product count. |  |  |
| 6 | `app/code/Dotdigitalgroup/Email/Model/Sync/Customer/CustomerDataManager.php:61` | TP-low | Customer entities have few non-static EAV attributes, so '*' adds little to the customer sync batch. |  |  |
| 7 | `app/code/Dotdigitalgroup/Email/Ui/DataProvider/WebBehaviourTracking.php:152` | TP-low | Loads only the few categories of one product with all attributes to read their names. |  |  |
| 8 | `app/code/Mageplaza/Productslider/Block/MostViewedProducts.php:108` | TP-low | Product ids come from a most-viewed collection limited by setPageSize(), so a slider's few products get all attributes. |  |  |
| 9 | `app/code/Mageplaza/Productslider/Block/CategoryId.php:128` | TP | Storefront slider loads every product of the slider category with all attributes only to read entity_id. |  |  |
| 10 | `app/code/Mageplaza/Reports/Model/Api/CardManagement.php:302` | TP-low | Admin dashboard card limited to 5 products by setPageSize(5). |  |  |
| 11 | `app/code/Reviewscouk/Reviews/Model/Feed/Generator.php:143` | TP | Product feed pages through the whole catalog with all attributes while writing a fixed set of fields. |  |  |
| 12 | `app/code/Buckaroo/Magento2/Model/Config/Source/Categorylist.php:115` | TP-low | Admin config source model loads all active categories with all attributes to read name and path, only when the config page renders. |  |  |
| 13 | `app/code/Mageplaza/Blog/Block/Post/RelatedProduct.php:151` | TP-low | Related products of one blog post are limited by getSelect()->limit() to the configured product limit. |  |  |
