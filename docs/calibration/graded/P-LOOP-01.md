# P-LOOP-01: calibration sample

30 of 121 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-sales/Ui/Component/Listing/Column/Price.php:88` (core)

[P-LOOP-01] Magento\Directory\Model\Currency::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    82                      $itemStoreId = $item['store_id'] ?? '';
    83                      $storeId = $itemStoreId && is_numeric($itemStoreId) ? $itemStoreId :
    84                          $this->context->getFilterParam('store_id', Store::DEFAULT_STORE_ID);
    85                      $store = $this->storeManager->getStore($storeId);
    86                      $currencyCode = $store->getBaseCurrency()->getCurrencyCode();
    87                  }
>   88                  $basePurchaseCurrency = $this->currency->load($currencyCode);
    89                  $item[$this->getData('name')] = $basePurchaseCurrency
    90                      ->format($item[$this->getData('name')], [], false);
    91              }
    92          }
    93  
    94          return $dataSource;
```

## 2. `vendor/magento/module-gift-message/Model/Plugin/OrderGet.php:99` (core)

[P-LOOP-01] Magento\GiftMessage\Api\OrderItemRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    93                      !$orderItem->getGiftMessageId()
    94                  ) {
    95                      continue;
    96                  }
    97  
    98                  try {
>   99                      $giftMessage = $this->giftMessageOrderItemRepository->get(
   100                          $order->getEntityId(),
   101                          $orderItem->getItemId()
   102                      );
   103                  } catch (NoSuchEntityException $e) {
   104                      continue;
   105                  }
```

## 3. `vendor/magento/module-catalog/Block/Adminhtml/Product/Options/Ajax.php:75` (core)

[P-LOOP-01] Magento\Catalog\Model\Product::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    69  
    70          $products = $this->_coreRegistry->registry('import_option_products');
    71          if (is_array($products)) {
    72              foreach ($products as $productId) {
    73                  $product = $this->_productFactory->create();
    74                  $product->setStoreId($this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID));
>   75                  $product->load((int)$productId);
    76                  if (!$product->getId()) {
    77                      continue;
    78                  }
    79  
    80                  $optionsBlock->setProduct($product);
    81                  $results = array_merge($results, $optionsBlock->getOptionValues());
```

## 4. `vendor/magento/module-cms/Controller/Adminhtml/Block/InlineEdit.php:66` (core)

[P-LOOP-01] Magento\Cms\Api\BlockRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    60              if (!count($postItems)) {
    61                  $messages[] = __('Please correct the data sent.');
    62                  $error = true;
    63              } else {
    64                  foreach (array_keys($postItems) as $blockId) {
    65                      /** @var \Magento\Cms\Model\Block $block */
>   66                      $block = $this->blockRepository->getById($blockId);
    67                      try {
    68                          $block->setData(array_merge($block->getData(), $postItems[$blockId]));
    69                          $this->blockRepository->save($block);
    70                      } catch (\Exception $e) {
    71                          $messages[] = $this->getErrorWithBlockId(
    72                              $block,
```

## 5. `vendor/magento/module-catalog-url-rewrite/Model/Product/AnchorUrlRewriteGenerator.php:70` (core)

[P-LOOP-01] Magento\Catalog\Api\CategoryRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    64      {
    65          $urls = [];
    66          foreach ($productCategories->getList() as $category) {
    67              $anchorCategoryIds = $category->getAnchorsAbove();
    68              if ($anchorCategoryIds) {
    69                  foreach ($anchorCategoryIds as $anchorCategoryId) {
>   70                      $anchorCategory = $this->categoryRepository->get($anchorCategoryId, $storeId);
    71                      if (in_array(
    72                          (int)$anchorCategory->getParentId(),
    73                          [Category::TREE_ROOT_ID, Category::ROOT_CATEGORY_ID]
    74                      )) {
    75                          continue;
    76                      }
```

## 6. `vendor/magento/module-bundle/Model/Product/SaveHandler.php:205` (core)

[P-LOOP-01] Magento\Bundle\Api\ProductOptionRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   199       */
   200      private function processRemovedOptions(ProductInterface $entity, array $existingOptionsIds, array $optionIds): void
   201      {
   202          $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
   203          $parentId = $entity->getData($metadata->getLinkField());
   204          foreach (array_diff($existingOptionsIds, $optionIds) as $optionId) {
>  205              $option = $this->optionRepository->get($entity->getSku(), $optionId);
   206              $option->setParentId($parentId);
   207              $this->removeOptionLinks($entity->getSku(), $option);
   208              $this->optionRepository->delete($option);
   209          }
   210      }
   211  }
```

## 7. `vendor/magento/module-configurable-product/Controller/Adminhtml/Product/Initialization/Helper/Plugin/UpdateConfigurations.php:80` (core)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    74      ) {
    75          $configurations = $this->getConfigurations();
    76          $configurations = $this->variationHandler->duplicateImagesForVariations($configurations);
    77          if (count($configurations)) {
    78              foreach ($configurations as $productId => $productData) {
    79                  /** @var \Magento\Catalog\Model\Product $product */
>   80                  $product = $this->productRepository->getById($productId, false, $this->request->getParam('store', 0));
    81                  $productData = $this->variationHandler->processMediaGallery($product, $productData);
    82                  $product->addData($productData);
    83                  if ($product->hasDataChanges()) {
    84                      $product->save();
    85                  }
    86              }
```

## 8. `vendor/magento/module-media-gallery-catalog-ui/Ui/Component/Listing/Columns/Path.php:79` (core)

[P-LOOP-01] Magento\Catalog\Api\CategoryRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    73          $categoryPathWithName = '';
    74          $categoryIds = explode('/', $pathWithIds);
    75          foreach ($categoryIds as $id) {
    76              if ($id == Category::TREE_ROOT_ID) {
    77                  continue;
    78              }
>   79              $categoryName = $this->categoryRepository->get($id)->getName();
    80              $categoryPathWithName .=  ' / ' . $categoryName;
    81          }
    82          return $categoryPathWithName;
    83      }
    84  }
```

## 9. `app/code/Algolia/AlgoliaSearch/Controller/Adminhtml/Reindex/Save.php:81` (packagist)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    75          foreach ($skus as $sku) {
    76              $sku = trim($sku);
    77  
    78              try {
    79  
    80                  /** @var \Magento\Catalog\Model\Product $product */
>   81                  $product = $this->productRepository->get($sku);
    82                  if (! $product->getId()) {
    83                      throw new UnknownSkuException(__('Product with SKU "%1" was not found.', $sku));
    84                  }
    85  
    86                  $this->checkAndReindex($product, $stores);
    87              } catch (NoSuchEntityException $e) {
```

## 10. `vendor/magento/module-sales/CustomerData/LastOrderedItems.php:154` (core)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   148          if ($order) {
   149              $website = $this->_storeManager->getStore()->getWebsiteId();
   150              /** @var Item $item */
   151              foreach ($order->getParentItemsRandomCollection($limit) as $item) {
   152                  /** @var Product $product */
   153                  try {
>  154                      $product = $this->productRepository->getById(
   155                          $item->getProductId(),
   156                          false,
   157                          $this->_storeManager->getStore()->getId()
   158                      );
   159                  } catch (NoSuchEntityException $noEntityException) {
   160                      if ($this->skipDeletedProductLogging !== true) {
```

## 11. `vendor/magento/module-sales/Ui/Component/Listing/Column/PurchasedPrice.php:66` (core)

[P-LOOP-01] Magento\Directory\Model\Currency::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    60       */
    61      public function prepareDataSource(array $dataSource)
    62      {
    63          if (isset($dataSource['data']['items'])) {
    64              foreach ($dataSource['data']['items'] as & $item) {
    65                  $currencyCode = isset($item['order_currency_code']) ? $item['order_currency_code'] : null;
>   66                  $purchaseCurrency = $this->currency->load($currencyCode);
    67                  $item[$this->getData('name')] = $purchaseCurrency
    68                      ->format($item[$this->getData('name')], [], false);
    69              }
    70          }
    71  
    72          return $dataSource;
```

## 12. `vendor/magento/module-media-gallery-catalog-ui/Controller/Adminhtml/Product/GetSelected.php:70` (core)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    64  
    65          if (!is_array($productIds)) {
    66              return $this->resultJsonFactory->create()->setData('parameter ids must be type of array');
    67          }
    68          foreach ($productIds as $id) {
    69              try {
>   70                  $product = $this->productRepository->getById($id);
    71                  $options[] = [
    72                      'value' => $product->getId(),
    73                      'label' => $product->getName(),
    74                      'is_active' => $product->getSatus(),
    75                      'path' => $product->getSku()
    76                  ];
```

## 13. `vendor/magento/module-catalog-url-rewrite/Model/ProductScopeRewriteGenerator.php:195` (core)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   189                                  true
   190                              )
   191                          );
   192                      }
   193                  } else {
   194                      if (count($visibleForStores) === 0 || in_array((int)$id, $visibleForStores)) {
>  195                          $scopedProduct = $this->productRepository->getById($productId, false, $id);
   196                          $mergeDataProvider->merge(
   197                              $this->generateForSpecificStoreView(
   198                                  $id,
   199                                  $productCategories,
   200                                  $scopedProduct,
   201                                  $rootCategoryId,
```

## 14. `vendor/magento/module-catalog/Model/Indexer/Product/Eav/Plugin/AttributeSet/IndexableAttributeFilter.php:40` (core)

[P-LOOP-01] Magento\Catalog\Model\ResourceModel\Eav\Attribute::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    34          $groups = $set->getGroups();
    35          if (is_array($groups)) {
    36              foreach ($groups as $group) {
    37                  /** @var $group \Magento\Eav\Model\Entity\Attribute\Group */
    38                  foreach ($group->getAttributes() as $attribute) {
    39                      /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
>   40                      $catalogResource->load($attribute->getId());
    41                      if ($catalogResource->isIndexable()) {
    42                          // Attribute requires to be cloned for new dataset to maintain attribute set changes
    43                          $attributeClone = clone $attribute;
    44                          $attributeClone->load($attribute->getAttributeId());
    45                          $codes[] = $attributeClone->getAttributeCode();
    46                          unset($attributeClone);
```

## 15. `vendor/magento/module-catalog-url-rewrite/Model/Product/CurrentUrlRewritesRegenerator.php:193` (core)

[P-LOOP-01] Magento\Catalog\Model\CategoryRepository::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   187              )
   188          );
   189  
   190          foreach ($currentUrlRewrites as $currentUrlRewrite) {
   191              $metadata = $currentUrlRewrite->getMetadata();
   192              if (isset($metadata['category_id']) && $metadata['category_id'] > 0) {
>  193                  $category = $this->categoryRepository->get($metadata['category_id'], $storeId);
   194                  if (in_array($category->getId(), $anchorCategoryIds)) {
   195                      $mergeDataProvider->merge(
   196                          $currentUrlRewrite->getIsAutogenerated()
   197                          ? $this->generateForAutogenerated($currentUrlRewrite, $storeId, $category, $product)
   198                          : $this->generateForCustom($currentUrlRewrite, $storeId, $category, $product)
   199                      );
```

## 16. `vendor/magento/module-search/Model/SynonymGroupRepository.php:155` (core)

[P-LOOP-01] Magento\Search\Model\SynonymGroup::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   149      private function merge(SynonymGroupInterface $synonymGroupToMerge, array $matchingGroupIds)
   150      {
   151          $mergedSynonyms = [];
   152          foreach ($matchingGroupIds as $groupId) {
   153              /** @var SynonymGroup $synonymGroupModel */
   154              $synonymGroupModel = $this->synonymGroupFactory->create();
>  155              $synonymGroupModel->load($groupId);
   156              $mergedSynonyms[] = explode(',', $synonymGroupModel->getSynonymGroup() ?? '');
   157              $synonymGroupModel->delete();
   158          }
   159          $mergedSynonyms[] = explode(',', $synonymGroupToMerge->getSynonymGroup() ?? '');
   160  
   161          return array_unique(array_merge([], ...$mergedSynonyms));
```

## 17. `vendor/magento/module-media-gallery-catalog-ui/Ui/Component/Listing/Columns/Thumbnail.php:102` (core)

[P-LOOP-01] Magento\Catalog\Model\CategoryRepository::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    96                  $item[$fieldName . '_src'] = $this->getUrl($item[$fieldName]);
    97                  continue;
    98              }
    99  
   100              if (isset($item['entity_id'])) {
   101                  $src = $this->categoryImage->getUrl(
>  102                      $this->categoryRepository->get($item['entity_id'])
   103                  );
   104  
   105                  if (!empty($src)) {
   106                      $item[$fieldName . '_src'] = $src;
   107                      continue;
   108                  }
```

## 18. `vendor/magento/module-catalog/Model/Product/Price/Validation/InvalidSkuProcessor.php:60` (core)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    54          foreach ($idsBySku as $sku => $ids) {
    55              foreach ($ids as $type) {
    56                  $valueTypeIsAllowed = false;
    57  
    58                  if ($allowedPriceTypeValue
    59                      && $type == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
>   60                      && $this->productRepository->get($sku)->getPriceType() != $allowedPriceTypeValue
    61                  ) {
    62                      $valueTypeIsAllowed = true;
    63                  }
    64  
    65                  if (!in_array($type, $allowedProductTypes) || $valueTypeIsAllowed) {
    66                      $skuDiff[] = $sku;
```

## 19. `vendor/magento/module-customer/Controller/Adminhtml/Index/MassSubscribe.php:61` (core)

[P-LOOP-01] Magento\Customer\Api\CustomerRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    55       * @return \Magento\Backend\Model\View\Result\Redirect
    56       */
    57      protected function massAction(AbstractCollection $collection)
    58      {
    59          $customersUpdated = 0;
    60          foreach ($collection->getAllIds() as $customerId) {
>   61              $customer = $this->customerRepository->getById($customerId);
    62              $storeId = (int)$customer->getStoreId();
    63              $this->subscriptionManager->subscribeCustomer($customerId, $storeId);
    64              $customersUpdated++;
    65          }
    66  
    67          if ($customersUpdated) {
```

## 20. `vendor/magento/module-customer/Model/Address/DataProvider.php:147` (core)

[P-LOOP-01] Magento\Customer\Model\Address::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   141              return $this->loadedData;
   142          }
   143          $items = $this->collection->getItems();
   144          /** @var Address $item */
   145          foreach ($items as $item) {
   146              $addressId = $item->getEntityId();
>  147              $item->load($addressId);
   148              $this->loadedData[$addressId] = $item->getData();
   149              $customerId = $this->loadedData[$addressId]['parent_id'];
   150              /** @var \Magento\Customer\Model\Customer $customer */
   151              $customer = $this->customerRepository->getById($customerId);
   152              $defaultBilling = $customer->getDefaultBilling();
   153              $defaultShipping = $customer->getDefaultShipping();
```

## 21. `vendor/magento/module-catalog/Ui/DataProvider/Product/Form/Modifier/Related.php:220` (core)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   214              }
   215              usort($linkItems, function ($a, $b) {
   216                  return $a->getPosition() <=> $b->getPosition();
   217              });
   218              foreach ($linkItems as $linkItem) {
   219                  /** @var \Magento\Catalog\Model\Product $linkedProduct */
>  220                  $linkedProduct = $this->productRepository->get(
   221                      $linkItem->getLinkedProductSku(),
   222                      false,
   223                      $this->locator->getStore()->getId()
   224                  );
   225                  $data[$productId]['links'][$dataScope][] = $this->fillData($linkedProduct, $linkItem);
   226              }
```

## 22. `vendor/magento/module-gift-message/Model/Save.php:284` (core)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   278       */
   279      public function importAllowQuoteItemsFromProducts($products)
   280      {
   281          $allowedItems = $this->getAllowQuoteItems();
   282          $deleteAllowedItems = [];
   283          foreach ($products as $productId => $data) {
>  284              $product = $this->productRepository->getById($productId, false, $this->_session->getStore()->getId());
   285              $item = $this->_getQuote()->getItemByProduct($product);
   286  
   287              if (!$item) {
   288                  continue;
   289              }
   290  
```

## 23. `app/code/Ebizmarts/MailChimp/Block/Pixel/Order.php:96` (packagist)

[P-LOOP-01] Magento\Catalog\Api\ProductRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    90              // configurable, the parent id for everything else.
    91              $leafId     = $this->leafProductIdResolver->forOrderItem($item);
    92              $variantId  = $leafId !== null ? (string)$leafId : $productId;
    93              $imageUrl   = '';
    94              $productUrl = '';
    95              try {
>   96                  $product = $this->productRepository->getById((int)$item->getProductId());
    97                  $img     = $product->getImage();
    98                  if ($img && $img !== 'no_selection') {
    99                      $imageUrl = $mediaBaseUrl . 'catalog/product' . $img;
   100                  }
   101                  $productUrl = (string)$product->getProductUrl();
   102              } catch (\Exception $e) {
```

## 24. `vendor/magento/module-catalog-inventory-graph-ql/Model/Resolver/StockStatusProvider.php:95` (core)

[P-LOOP-01] Magento\CatalogInventory\Api\StockStatusRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    89       * @return string
    90       */
    91      private function getBundleProductStockStatus(Item $cartItem): string
    92      {
    93          $qtyOptions = $cartItem->getQtyOptions();
    94          foreach ($qtyOptions as $qtyOption) {
>   95              $stockStatus = $this->stockStatusRepository->get($qtyOption->getProduct()->getId());
    96              if (!(int)$stockStatus->getStockStatus()) {
    97                  return self::OUT_OF_STOCK;
    98              }
    99          }
   100  
   101          return self::IN_STOCK;
```

## 25. `vendor/magento/module-inventory-admin-ui/Controller/Adminhtml/Source/InlineEdit.php:81` (core)

[P-LOOP-01] Magento\InventoryApi\Api\SourceRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    75  
    76          if ($request->isXmlHttpRequest() && $request->isPost() && $requestData) {
    77              foreach ($requestData as $itemData) {
    78                  try {
    79                      $sourceCode = $itemData[SourceInterface::SOURCE_CODE];
    80                      $itemData = $this->sourceCoordinatesDataProcessor->execute($itemData);
>   81                      $source = $this->sourceRepository->get($sourceCode);
    82                      $this->dataObjectHelper->populateWithArray($source, $itemData, SourceInterface::class);
    83                      $this->sourceRepository->save($source);
    84                  } catch (NoSuchEntityException $e) {
    85                      $errorMessages[] = __(
    86                          '[ID: %value] The Source does not exist.',
    87                          ['value' => $sourceCode]
```

## 26. `vendor/magento/module-catalog/Model/Product/Price/BasePriceStorage.php:288` (core)

[P-LOOP-01] Magento\Store\Api\StoreRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   282      {
   283          foreach ($formattedPrices as $price) {
   284              if ($price['store_id'] == Store::DEFAULT_STORE_ID) {
   285                  continue;
   286              }
   287  
>  288              $storeIds = $this->storeRepository->getById($price['store_id'])->getWebsite()->getStoreIds();
   289  
   290              // Unset origin store view to get rid of duplicate
   291              unset($storeIds[$price['store_id']]);
   292  
   293              foreach ($storeIds as $storeId) {
   294                  $price['store_id'] = (int)$storeId;
```

## 27. `vendor/magento/module-ui/Model/ResourceModel/BookmarkRepository.php:122` (core)

[P-LOOP-01] Magento\Ui\Model\ResourceModel\BookmarkRepository::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   116          $this->collectionProcessor->process($searchCriteria, $collection);
   117          $searchResults->setTotalCount($collection->getSize());
   118  
   119          $bookmarks = [];
   120          /** @var BookmarkInterface $bookmark */
   121          foreach ($collection->getItems() as $bookmark) {
>  122              $bookmarks[] = $this->getById($bookmark->getId());
   123          }
   124          $searchResults->setItems($bookmarks);
   125  
   126          return $searchResults;
   127      }
   128  
```

## 28. `vendor/magento/module-newsletter/Model/ResourceModel/Problem/Collection.php:163` (core)

[P-LOOP-01] Magento\Customer\Api\CustomerRepositoryInterface::getById() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
   157          }
   158          $this->_loadCustomersDataFlag = true;
   159          foreach ($this->getItems() as $item) {
   160              if ($item->getCustomerId()) {
   161                  $customerId = $item->getCustomerId();
   162                  try {
>  163                      $customer = $this->customerRepository->getById($customerId);
   164                      $problems = $this->getItemsByColumnValue('customer_id', $customerId);
   165                      $customerName = $this->_customerView->getCustomerName($customer);
   166                      foreach ($problems as $problem) {
   167                          $problem->setCustomerName($customerName)
   168                              ->setCustomerFirstName($customer->getFirstname())
   169                              ->setCustomerLastName($customer->getLastname());
```

## 29. `app/code/Magefan/Community/Model/GetCategoryByProduct.php:73` (packagist)

[P-LOOP-01] Magento\Catalog\Api\CategoryRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    67              if ($categoryIds) {
    68                  $level = -1;
    69                  $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();
    70  
    71                  foreach ($categoryIds as $categoryId) {
    72                      try {
>   73                          $category = $this->categoryRepository->get($categoryId, $storeId);
    74                          if ($category->getIsActive()
    75                              && $category->getLevel() > $level
    76                              && in_array($rootCategoryId, $category->getPathIds())
    77                          ) {
    78                              $level = $category->getLevel();
    79                              $this->productCategory[$key] = $category;
```

## 30. `vendor/magento/module-inventory/Model/Source/Command/GetSourcesAssignedToStockOrderedByPriority.php:79` (core)

[P-LOOP-01] Magento\InventoryApi\Api\SourceRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().

```
    73      public function execute(int $stockId): array
    74      {
    75          try {
    76              $stockSourceLinks = $this->getStockSourceLinks($stockId);
    77              $sources = [];
    78              foreach ($stockSourceLinks as $link) {
>   79                  $sources[] = $this->sourceRepository->get($link->getSourceCode());
    80              }
    81  
    82              return $sources;
    83          } catch (\Exception $e) {
    84              $this->logger->error($e->getMessage());
    85              throw new LocalizedException(__('Could not load Sources for Stock'), $e);
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-sales/Ui/Component/Listing/Column/Price.php:88` | FP | Directory Currency::load() is overridden to only set currency_code in memory, so no database query runs. | re-check |  |
| 2 | `vendor/magento/module-gift-message/Model/Plugin/OrderGet.php:99` | TP | Gift message repository get() loads the gift message once per order item that has one, on every order get. | re-check |  |
| 3 | `vendor/magento/module-catalog/Block/Adminhtml/Product/Options/Ajax.php:75` | TP-low | Admin AJAX that imports custom options from a user-selected set of products; one product load each. | re-check |  |
| 4 | `vendor/magento/module-cms/Controller/Adminhtml/Block/InlineEdit.php:66` | TP-low | Admin inline edit of CMS blocks bounded by the rows edited in the grid at once. |  |  |
| 5 | `vendor/magento/module-catalog-url-rewrite/Model/Product/AnchorUrlRewriteGenerator.php:70` | TP | Category repository get() per anchor category of every product category during URL rewrite generation on product save and import. |  |  |
| 6 | `vendor/magento/module-bundle/Model/Product/SaveHandler.php:205` | TP-low | Admin bundle save loads only the options removed in this save, then deletes each. |  |  |
| 7 | `vendor/magento/module-configurable-product/Controller/Adminhtml/Product/Initialization/Helper/Plugin/UpdateConfigurations.php:80` | TP | One getById() per configurable variation on admin product save; variations can number in the hundreds. |  |  |
| 8 | `vendor/magento/module-media-gallery-catalog-ui/Ui/Component/Listing/Columns/Path.php:79` | TP-low | Loop bounded by the depth of one category path and the category repository caches by id. |  |  |
| 9 | `app/code/Algolia/AlgoliaSearch/Controller/Adminhtml/Reindex/Save.php:81` | TP-low | Admin reindex form capped at MAX_SKUS = 10 SKUs. |  |  |
| 10 | `vendor/magento/module-sales/CustomerData/LastOrderedItems.php:154` | TP-low | Loop capped at SIDEBAR_ORDER_LIMIT = 5 items of the last order. |  |  |
| 11 | `vendor/magento/module-sales/Ui/Component/Listing/Column/PurchasedPrice.php:66` | FP | Directory Currency::load() is overridden to only set currency_code in memory, so no database query runs. |  |  |
| 12 | `vendor/magento/module-media-gallery-catalog-ui/Controller/Adminhtml/Product/GetSelected.php:70` | TP-low | Admin AJAX resolving the product ids already selected in a media gallery filter. |  |  |
| 13 | `vendor/magento/module-catalog-url-rewrite/Model/ProductScopeRewriteGenerator.php:195` | TP-low | Bounded by the store views that override the product URL key; one scoped product load each. |  |  |
| 14 | `vendor/magento/module-catalog/Model/Indexer/Product/Eav/Plugin/AttributeSet/IndexableAttributeFilter.php:40` | TP | Two attribute loads per attribute of every group in the set on attribute set save; sets hold hundreds of attributes. |  |  |
| 15 | `vendor/magento/module-catalog-url-rewrite/Model/Product/CurrentUrlRewritesRegenerator.php:193` | TP | Category repository get() per existing category URL rewrite of the product during URL regeneration. |  |  |
| 16 | `vendor/magento/module-search/Model/SynonymGroupRepository.php:155` | TP-low | Admin synonym group merge over the few matching groups, each loaded and deleted. |  |  |
| 17 | `vendor/magento/module-media-gallery-catalog-ui/Ui/Component/Listing/Columns/Thumbnail.php:102` | TP | One category repository load per row of the media gallery category grid. |  |  |
| 18 | `vendor/magento/module-catalog/Model/Product/Price/Validation/InvalidSkuProcessor.php:60` | TP | Product repository get() per bundle SKU in the price storage API, which takes arbitrary SKU lists. |  |  |
| 19 | `vendor/magento/module-customer/Controller/Adminhtml/Index/MassSubscribe.php:61` | TP | Admin mass subscribe loads every selected customer one by one; selections can be thousands. |  |  |
| 20 | `vendor/magento/module-customer/Model/Address/DataProvider.php:147` | TP-low | Form data provider whose collection is filtered to the one address being edited. |  |  |
| 21 | `vendor/magento/module-catalog/Ui/DataProvider/Product/Form/Modifier/Related.php:220` | TP | One product repository get() per related, up-sell or cross-sell link on the admin product form. |  |  |
| 22 | `vendor/magento/module-gift-message/Model/Save.php:284` | TP | One product getById() per cart item when importing gift message permissions. |  |  |
| 23 | `app/code/Ebizmarts/MailChimp/Block/Pixel/Order.php:96` | TP | One product getById() per visible order item on the checkout success page. |  |  |
| 24 | `vendor/magento/module-catalog-inventory-graph-ql/Model/Resolver/StockStatusProvider.php:95` | TP | Stock status repository get() runs resource load() for every bundle selection of a cart item. |  |  |
| 25 | `vendor/magento/module-inventory-admin-ui/Controller/Adminhtml/Source/InlineEdit.php:81` | TP-low | Admin inline edit of inventory sources bounded by the rows edited at once. |  |  |
| 26 | `vendor/magento/module-catalog/Model/Product/Price/BasePriceStorage.php:288` | FP | StoreRepository::getById() reads store data from app config and caches it; no database query. |  |  |
| 27 | `vendor/magento/module-ui/Model/ResourceModel/BookmarkRepository.php:122` | TP-low | getList() reloads each bookmark by id, but one admin user has only a few bookmarks per grid. |  |  |
| 28 | `vendor/magento/module-newsletter/Model/ResourceModel/Problem/Collection.php:163` | TP | Customer repository getById() per newsletter problem row in the admin problem grid. |  |  |
| 29 | `app/code/Magefan/Community/Model/GetCategoryByProduct.php:73` | TP | Category repository get() for every category the product is assigned to, per product. |  |  |
| 30 | `vendor/magento/module-inventory/Model/Source/Command/GetSourcesAssignedToStockOrderedByPriority.php:79` | TP-low | Bounded by the few sources assigned to one stock and the command is wrapped by a caching preference. |  |  |
