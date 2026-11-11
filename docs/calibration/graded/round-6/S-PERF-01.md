# S-PERF-01: calibration sample

27 of 27 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Yireo/GoogleTagManager2/ViewModel/DataLayer.php:88` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    82          $data = (array)$block->getData('data_layer_events');
    83          $processors = $this->getProcessors();
    84  
    85          $data = $this->variableParser->parse($data, $processors);
    86  
    87          foreach ($data as $eventId => $eventData) {
>   88              $data[$eventId] = array_merge($eventData, $this->xmlConfig->getEvent((string)$eventId));
    89          }
    90  
    91          return $data;
    92      }
    93  
    94      /**
```

## 2. `app/code/Swissup/Marketplace/Installer/Command/CmsBlock.php:89` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    83                      $block->save();
    84                  } catch (\Exception $e) {
    85                      $this->logger->warning($e->getMessage());
    86                  }
    87              }
    88  
>   89              $data = array_merge([
    90                  'is_active' => 1,
    91              ], $data);
    92  
    93              try {
    94                  $this->blockFactory->create()
    95                      ->setData($data)
```

## 3. `app/code/Magmodules/Channable/Helper/Product.php:197` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   191                      $value
   192                  );
   193              }
   194  
   195              if (!empty($attribute['collection'])) {
   196                  if ($dataCollection = $this->getAttributeCollection($type, $config, $productData)) {
>  197                      $dataRow = array_merge($dataRow, $dataCollection);
   198                  }
   199              }
   200  
   201              if (!empty($dataRow[$attribute['label']])) {
   202                  if ($value != null) {
   203                      if (is_array($dataRow[$attribute['label']])) {
```

## 4. `app/code/ADM/QuickDevBar/Helper/Data.php:306` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   300          }
   301  
   302          $content = [];
   303          foreach ($wrapperFiles as $jsonContent) {
   304              if($jsonContent) {
   305                  foreach ($this->serializer->unserialize($jsonContent) as $contentKey => $contentValue) {
>  306                      $content[$contentKey] =  empty($content[$contentKey]) ? $contentValue : array_merge($content[$contentKey], $contentValue);
   307                  }
   308              }
   309              //unlink($wrapperContent);
   310              //TODO: remove foreach
   311              //break;
   312          }
```

## 5. `app/code/Swissup/Core/Installer/Installer.php:186` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   180              $installer = $this->getInstaller($package);
   181  
   182              if (!$installer) {
   183                  continue;
   184              }
   185  
>  186              $result = array_merge($result, $installer);
   187          }
   188  
   189          return array_unique($result);
   190      }
   191  
   192      /**
```

## 6. `app/code/ShipperHQ/Shipper/Model/Synchronizer.php:531` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   525          if ($isSelect) {
   526              //account for multiselect values
   527              $separated = [];
   528              foreach ($usedAttributeValues as $key => $aValue) {
   529                  if (strstr((string) $aValue, ',')) {
   530                      $values = explode(',', (string) $aValue);
>  531                      $separated = array_merge($separated, $values);
   532                      unset($usedAttributeValues[$key]);
   533                  }
   534              }
   535              $usedAttributeValues = array_merge($usedAttributeValues, $separated);
   536          }
   537          return in_array($value, $usedAttributeValues);
```

## 7. `app/code/Clerk/Clerk/Model/ProductRealtimeSynchronizer.php:266` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   260       */
   261      protected function isSalable($product)
   262      {
   263          if ($product->getTypeId() === 'configurable') {
   264              $childIds = [];
   265              foreach ($this->_productModelConfigurable->getChildrenIds($product->getId()) as $group) {
>  266                  $childIds = array_merge($childIds, array_values($group));
   267              }
   268              if ($childIds) {
   269                  // Salable while any child is; a child without managed stock counts as in stock.
   270                  foreach ($childIds as $childId) {
   271                      if ($this->getLiveStockStatus($childId) !== false) {
   272                          return true;
```

## 8. `app/code/MSP/CmsImportExport/Model/Content.php:136` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   130          $pages = [];
   131          $media = [];
   132  
   133          foreach ($pageInterfaces as $pageInterface) {
   134              $pageInfo = $this->pageToArray($pageInterface);
   135              $pages[$this->_getPageKey($pageInterface)] = $pageInfo;
>  136              $media = array_merge($media, $pageInfo['media']);
   137          }
   138  
   139          return [
   140              'pages' => $pages,
   141              'media' => $media,
   142          ];
```

## 9. `app/code/ShipperHQ/Shipper/Model/Carrier/Processor/ShipperMapper.php:612` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   606                      $valueString = [];
   607                      foreach ($attributeValue as $aValue) {
   608                          $admin_value = $attribute->setStoreId(0)->getSource()->getOptionText($aValue);
   609                          // SHQ18-1335 - getOptionsText may return an array in some scenarios -- see vendor/magento/module-eav/Model/Entity/Attribute/Source/Table.php
   610                          // SHQ18-1310 - Don't sent HTML in request. Convert to actual character
   611                          if (is_array($admin_value)) {
>  612                              $valueString = array_merge(array_map("html_entity_decode", $valueString), $admin_value);
   613                          } else {
   614                              $valueString[] = html_entity_decode($admin_value);
   615                          }
   616                      }
   617                      $attributeValue = implode('#', $valueString);
   618                  } else {
```

## 10. `app/code/Mageplaza/Productslider/Block/CategoryId.php:134` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   128                  $collection->addAttributeToSelect('*')->addCategoryFilter($category);
   129  
   130                  foreach ($collection as $item) {
   131                      $productId[] = $item->getData('entity_id');
   132                  }
   133  
>  134                  $productIds = array_merge($productIds, $productId);
   135              }
   136          } else {
   137              $collection = $this->_productCollectionFactory->create();
   138              $category = $this->_categoryFactory->create()->load($catIds);
   139              $collection->addAttributeToSelect('*')->addCategoryFilter($category);
   140  
```

## 11. `app/code/Payone/Core/Helper/Toolkit.php:96` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    90       */
    91      public function getAllPayoneSecurityKeys()
    92      {
    93          $aKeys = $this->getConfigParamAllStores('key');
    94          $aShopIds = $this->storeManager->getStores(false, true);
    95          foreach ($aShopIds as $sStoreCode => $oStore) {
>   96              $aKeys = array_merge($aKeys, $this->getAllPayoneSecurityKeysByStoreCode($sStoreCode));
    97          }
    98          return array_unique($aKeys);
    99      }
   100  
   101      /**
   102       * Check wheither the given key is configured in the shop and thus valid
```

## 12. `app/code/Swissup/Marketplace/Installer/Command/ProductAttribute.php:72` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    66              $model = $this->attributeFactory->create()
    67                  ->load($data['attribute_code'], 'attribute_code');
    68              if ($model->getId()) {
    69                  continue;
    70              }
    71  
>   72              $data = array_merge([
    73                  'is_global'=> 0,
    74                  'frontend_input'=> 'boolean',
    75                  'is_configurable'=> 0,
    76                  'is_filterable'=> 0,
    77                  'is_filterable_in_search' => 0,
    78                  'sort_order' => 1,
```

## 13. `app/code/Magmodules/Channable/Controller/Adminhtml/Item/InlineEdit.php:62` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    56                  if (!$item->getId()) {
    57                      throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $itemId));
    58                  }
    59  
    60                  $allowedFields = ['exclude_for_update', 'needs_update', 'is_in_stock'];
    61                  $filteredData = array_intersect_key($postItems[$itemId], array_flip($allowedFields));
>   62                  $item->setData(array_merge($item->getData(), $filteredData));
    63                  $this->itemResource->save($item);
    64              } catch (\Exception $e) {
    65                  $messages[] = $this->getErrorWithItemId(
    66                      $item,
    67                      __($e->getMessage())
    68                  );
```

## 14. `app/code/ShipperHQ/Shipper/Model/Carrier/Processor/ShipperMapper.php:361` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   355              $taxPercentage = 0; // Not in first release of M2
   356              $fixedPrice = $magentoItem->getProduct()->getPriceType() == Price::PRICE_TYPE_FIXED;
   357              $fixedWeight = $magentoItem->getProduct()->getWeightType() == 1 ? true : false;
   358              $id = $magentoItem->getItemId() ? $magentoItem->getItemId() : $magentoItem->getQuoteItemId();
   359              $productType = $magentoItem->getProductType() ?
   360                  $magentoItem->getProductType() : $magentoItem->getProduct()->getTypeId();
>  361              $stdAttributes = array_merge($this->getDimensionalAttributes($magentoItem), self::$stdAttributeNames);
   362              $weight = $this->getAdjustedItemWeight($magentoItem);
   363              $warehouseDetails = $this->getWarehouseDetails($magentoItem);
   364              $pickupLocationDetails = $this->getPickupLocationDetails($magentoItem);
   365              $itemAttributes = $this->populateAttributes($stdAttributes, $magentoItem);
   366  
   367              if ($this->taxHelper->discountTax() && $magentoItem->getTaxPercent() > 0) {
```

## 15. `app/code/Swissup/Core/Installer/ComposerRepository.php:320` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   314          }
   315  
   316          foreach ($this->fetchAll($username, $includeRequests) as $i => $response) {
   317              $password = $includeRequests[$i]['password'];
   318              $packages[$password] = $response instanceof \Exception
   319                  ? $response
>  320                  : array_merge($packages[$password], $response['packages'] ?? []);
   321          }
   322  
   323          return $packages;
   324      }
   325  
   326      /**
```

## 16. `app/code/Trustpilot/Reviews/Helper/OrderData.php:263` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   257          $categoryIds = array();
   258  
   259          if (!empty($childProducts)) {
   260              foreach ($childProducts as $childProduct) {
   261                  $childCategoryIds = $childProduct->getCategoryIds();
   262                  if (!empty($childCategoryIds)) {
>  263                      $categoryIds = array_merge($categoryIds, $childCategoryIds);
   264                  }
   265              }
   266          } else {
   267              $categoryIds = $product->getCategoryIds();
   268          }
   269  
```

## 17. `app/code/MSP/CmsImportExport/Model/Content.php:236` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   230          $blocks = [];
   231          $media = [];
   232  
   233          foreach ($blockInterfaces as $blockInterface) {
   234              $blockInfo = $this->blockToArray($blockInterface);
   235              $blocks[$this->_getBlockKey($blockInterface)] = $blockInfo;
>  236              $media = array_merge($media, $blockInfo['media']);
   237          }
   238  
   239          return [
   240              'blocks' => $blocks,
   241              'media' => $media,
   242          ];
```

## 18. `app/code/ShipperHQ/Shipper/Helper/CarrierGroup.php:310` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   304              $data = $quoteDetail->getData();
   305  
   306              $existingOrderDetailCollection = $orderDetailModel->loadByOrder($orderId);
   307              if (!empty($existingOrderDetailCollection)) {
   308                  foreach ($existingOrderDetailCollection as $order) {
   309                      if ($order->getCarrierGroupId() == $quoteDetail->getCarrierGroupId()) {
>  310                          $data = array_merge($data, $order->getData());
   311                          break;
   312                      }
   313                  }
   314              }
   315  
   316              unset($data['quote_address_id']);
```

## 19. `app/code/ShipperHQ/Shipper/Helper/CarrierGroup.php:181` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   175              foreach ($arrayofShipDetails as $key => $detail) {
   176                  //records destination type returned on rate - not type from address validation or user selection
   177                  if (isset($detail['destination_type'])) {
   178                      $update['destination_type'] = $detail['destination_type'];
   179                  }
   180                  //SHQ18-69 include additional fields in carrier_group_detail
>  181                  $arrayofShipDetails[$key] = array_merge($detail, $additionalDetail);
   182              }
   183  
   184              $encodedShipDetails = $this->shipperDataHelper->encode($arrayofShipDetails);
   185              $update['carrier_group_detail'] = $encodedShipDetails;
   186              $update['carrier_group_html'] = $this->shipperDataHelper->getCarriergroupShippingHtml($encodedShipDetails);
   187  
```

## 20. `app/code/Bazaarvoice/Connector/Model/Dcc/CatalogData/CatalogProductBuilder.php:251` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   245          if ($product->getTypeId() == Configurable::TYPE_CODE
   246              && $this->configProvider->isFamiliesInheritEnabled($product->getStoreId())
   247          ) {
   248              $childProducts = $product->getTypeInstance()
   249                  ->getUsedProducts($product, [static::EAN, static::ISBN, static::UPC, static::MPN]);
   250              foreach ($childProducts as $childProduct) {
>  251                  $value = array_merge(
   252                      (array)$value,
   253                      (array)$this->getCustomAttributeData($childProduct, $attributeCode)
   254                  );
   255              }
   256          }
   257  
```

## 21. `app/code/Swissup/Marketplace/Installer/Command/CmsPage.php:134` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   128              }
   129  
   130              if (!$canUseExistingPage) {
   131                  $page = $this->pageFactory->create();
   132              }
   133  
>  134              $data = array_merge([
   135                  'is_active'         => 1,
   136                  'page_layout'       => '1column',
   137                  'content_heading'   => '',
   138                  'layout_update_xml' => '',
   139                  'custom_theme'      => null,
   140                  'custom_root_template' => null,
```

## 22. `app/code/Swissup/Core/Installer/Command/CmsPage.php:94` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    88              }
    89  
    90              if (!$canUseExistingPage) {
    91                  $page = $this->pageFactory->create();
    92              }
    93  
>   94              $data = array_merge([
    95                  'is_active'         => 1,
    96                  'page_layout'       => '1column',
    97                  'content_heading'   => '',
    98                  'layout_update_xml' => '',
    99                  'custom_theme'      => null,
   100                  'custom_root_template' => null,
```

## 23. `app/code/Swissup/Core/Installer/Command/ProductAttribute.php:38` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    32              $model = $this->attributeFactory->create()
    33                  ->load($data['attribute_code'], 'attribute_code');
    34              if ($model->getId()) {
    35                  continue;
    36              }
    37  
>   38              $data = array_merge([
    39                  'is_global'=> 0,
    40                  'frontend_input'=> 'boolean',
    41                  'is_configurable'=> 0,
    42                  'is_filterable'=> 0,
    43                  'is_filterable_in_search' => 0,
    44                  'sort_order' => 1,
```

## 24. `app/code/Bazaarvoice/Connector/Model/Feed/Product/Product.php:113` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   107                          $value = $child->getData($attribute);
   108                          if (is_string($value) && strpos($value, ',') !== false) {
   109                              $values = explode(',', $value);
   110                              if (empty($childrenValues[$attribute])) {
   111                                  $childrenValues[$attribute] = $values;
   112                              } else {
>  113                                  $childrenValues[$attribute] = array_merge($childrenValues[$attribute], $values);
   114                              }
   115                          } else {
   116                              $childrenValues[$attribute][] = $value;
   117                          }
   118                      }
   119                  }
```

## 25. `app/code/KiwiCommerce/AdminActivity/Model/Config.php:68` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    62       */
    63      public function getControllerActions()
    64      {
    65          $actions = [];
    66          foreach ($this->_xmlConfig as $module => $config) {
    67              if(isset($config['actions'])) {
>   68                  $actions = array_merge($actions, array_keys($config['actions']));
    69              }
    70          }
    71          return $actions;
    72      }
    73  
    74      /**
```

## 26. `app/code/Swissup/Core/Installer/Command/CmsBlock.php:55` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
    49                      $block->save();
    50                  } catch (\Exception $e) {
    51                      $this->logger->warning($e->getMessage());
    52                  }
    53              }
    54  
>   55              $data = array_merge([
    56                  'is_active' => 1,
    57              ], $data);
    58  
    59              try {
    60                  $this->blockFactory->create()
    61                      ->setData($data)
```

## 27. `app/code/Swissup/Marketplace/Installer/Installer.php:212` (extended)

[S-PERF-01] array_merge(...) is used in a loop and is a resources greedy construction. (Magento2.Performance.ForeachArrayMerge)

```
   206              $installer = $this->getInstaller($package);
   207  
   208              if (!$installer) {
   209                  continue;
   210              }
   211  
>  212              $result = array_merge($result, $installer);
   213          }
   214  
   215          return array_unique($result);
   216      }
   217  
   218      /**
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Yireo/GoogleTagManager2/ViewModel/DataLayer.php:88` | FP | Each event's config is merged into its own keyed slot $data[$eventId], so no array grows across iterations. |  |  |
| 2 | `app/code/Swissup/Marketplace/Installer/Command/CmsBlock.php:89` | FP | Defaults are merged into the current item's $data, which the foreach reassigns on every iteration, so nothing grows. |  |  |
| 3 | `app/code/Magmodules/Channable/Helper/Product.php:197` | FP | Only the price attribute has a collection source, so the merge into $dataRow runs once per product row and nothing grows across iterations. |  |  |
| 4 | `app/code/ADM/QuickDevBar/Helper/Data.php:306` | TP-low | $content[$contentKey] is merged into itself across the QuickDevBar wrapper files, a handful of files in a developer toolbar. |  |  |
| 5 | `app/code/Swissup/Core/Installer/Installer.php:186` | TP-low | $result is merged into itself over the packages of one installer run, a short list in admin and CLI code. |  |  |
| 6 | `app/code/ShipperHQ/Shipper/Model/Synchronizer.php:531` | TP-low | $separated grows over the distinct values of one attribute during the ShipperHQ attribute sync, an admin and cron task with a short value list. |  |  |
| 7 | `app/code/Clerk/Clerk/Model/ProductRealtimeSynchronizer.php:266` | FP | Configurable getChildrenIds() returns a single group under key 0, so the loop runs once and the merge never copies a grown array. |  |  |
| 8 | `app/code/MSP/CmsImportExport/Model/Content.php:136` | TP-low | $media grows over the CMS pages of an admin export, a small set run on demand. |  |  |
| 9 | `app/code/ShipperHQ/Shipper/Model/Carrier/Processor/ShipperMapper.php:612` | TP-low | $valueString grows over the selected options of one multiselect attribute of one item in the rate request, a few values. |  |  |
| 10 | `app/code/Mageplaza/Productslider/Block/CategoryId.php:134` | TP-low | $productIds grows over the slider's configured categories, a few per slider, and the copy is dwarfed by loading a full product collection per category. |  |  |
| 11 | `app/code/Payone/Core/Helper/Toolkit.php:96` | TP-low | $aKeys grows over the store views, a small fixed list. |  |  |
| 12 | `app/code/Swissup/Marketplace/Installer/Command/ProductAttribute.php:72` | FP | Defaults are merged into the current request item's $data, which the foreach reassigns on every iteration, so nothing grows. |  |  |
| 13 | `app/code/Magmodules/Channable/Controller/Adminhtml/Item/InlineEdit.php:62` | FP | Each item's own data is merged with its posted fields and saved, with no array that grows across iterations. |  |  |
| 14 | `app/code/ShipperHQ/Shipper/Model/Carrier/Processor/ShipperMapper.php:361` | FP | Each quote item gets a new $stdAttributes from its dimensional attributes and a constant list, so nothing grows across iterations. |  |  |
| 15 | `app/code/Swissup/Core/Installer/ComposerRepository.php:320` | TP-low | $packages[$password] grows over the include files of one license key in the admin module listing, a few responses. |  |  |
| 16 | `app/code/Trustpilot/Reviews/Helper/OrderData.php:263` | FP | A configurable order item has one child item, so the loop runs once and the merge never copies a grown array. |  |  |
| 17 | `app/code/MSP/CmsImportExport/Model/Content.php:236` | TP-low | $media grows over the CMS blocks of an admin export, a small set run on demand. |  |  |
| 18 | `app/code/ShipperHQ/Shipper/Helper/CarrierGroup.php:310` | FP | The loop breaks right after the merge, so array_merge() runs at most once per quote detail. |  |  |
| 19 | `app/code/ShipperHQ/Shipper/Helper/CarrierGroup.php:181` | FP | Each ship detail is merged into its own keyed slot $arrayofShipDetails[$key], so no array grows across iterations. |  |  |
| 20 | `app/code/Bazaarvoice/Connector/Model/Dcc/CatalogData/CatalogProductBuilder.php:251` | TP-low | $value grows by about one value per child of a configurable product, a few dozen at most, and the copy is dwarfed by loading the children. |  |  |
| 21 | `app/code/Swissup/Marketplace/Installer/Command/CmsPage.php:134` | FP | Defaults are merged into the current request item's $data, which the foreach reassigns on every iteration, so nothing grows. |  |  |
| 22 | `app/code/Swissup/Core/Installer/Command/CmsPage.php:94` | FP | Defaults are merged into the current request item's $data, which the foreach reassigns on every iteration, so nothing grows. |  |  |
| 23 | `app/code/Swissup/Core/Installer/Command/ProductAttribute.php:38` | FP | Defaults are merged into the current request item's $data, which the foreach reassigns on every iteration, so nothing grows. |  |  |
| 24 | `app/code/Bazaarvoice/Connector/Model/Feed/Product/Product.php:113` | TP-low | $childrenValues[$attribute] grows over the members of one product family during feed generation, a few children per family. |  |  |
| 25 | `app/code/KiwiCommerce/AdminActivity/Model/Config.php:68` | TP-low | $actions grows over the modules of the admin activity config, a short fixed list in admin code. |  |  |
| 26 | `app/code/Swissup/Core/Installer/Command/CmsBlock.php:55` | FP | Defaults are merged into the current request item's $data, which the foreach reassigns on every iteration, so nothing grows. |  |  |
| 27 | `app/code/Swissup/Marketplace/Installer/Installer.php:212` | TP-low | $result is merged into itself over the packages of one installer run, a short list in admin and CLI code. |  |  |
