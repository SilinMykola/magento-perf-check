# P-COL-02: calibration sample

21 of 21 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/magento2-base/setup/src/Magento/Setup/Model/FixtureGenerator/CustomerTemplateGenerator.php:155` (core)

[P-COL-02] Magento\Directory\Model\ResourceModel\Region\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   149       * @return mixed
   150       */
   151      private function getFirstRegionId()
   152      {
   153          $regionsCollection = $this->regionsCollectionFactory->create();
   154          $regionsCollection->unshiftOrder('region_id', 'ASC');
>  155          $region = $regionsCollection->getFirstItem();
   156  
   157          return $region->getRegionId();
   158      }
   159  }
```

## 2. `vendor/magento/module-catalog/Model/CategoryManagement.php:105` (core)

[P-COL-02] Magento\Catalog\Model\ResourceModel\Category\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    99       *
   100       * @return \Magento\Catalog\Model\Category
   101       */
   102      private function getTopLevelCategory()
   103      {
   104          $categoriesCollection = $this->categoriesFactory->create();
>  105          return $categoriesCollection->addFilter('level', ['eq' => 0])->getFirstItem();
   106      }
   107  
   108      /**
   109       * @inheritdoc
   110       *
   111       * @param int $categoryId
```

## 3. `app/code/Adyen/Payment/view/adminhtml/templates/info/adyen_giftcard.phtml:32` (packagist)

[P-COL-02] Adyen\Payment\Model\ResourceModel\Order\Payment\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    26  
    27  <?php if ($block->getPartialPayments()->getSize() > 1): ?>
    28      <?= $block->renderPartialPaymentsHtml(); ?>
    29  <?php elseif ($block->getPartialPayments()->getSize() === 1): ?>
    30      <?php
    31          /** @var OrderPaymentInterface $payment */
>   32          $payment = $block->getPartialPayments()->getFirstItem();
    33      ?>
    34      <div><?= $block->escapeHtml(__('Payment Method: %1', $payment->getPaymentMethod())); ?></div>
    35      <div>
    36          <?php if ($_isDemoMode): ?>
    37              <?= $block->escapeHtml(__('Adyen PSP Reference: ')); ?>
    38              <a href="https://ca-test.adyen.com/ca/ca/accounts/showTx.shtml?pspReference=<?= $block->escapeHtml(
```

## 4. `vendor/magento/module-bundle/Model/LinkManagement.php:380` (core)

[P-COL-02] Magento\Bundle\Model\ResourceModel\Option\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   374  
   375          $options = $this->optionCollection->create();
   376  
   377          $options->setIdFilter($optionId);
   378          $options->setProductLinkFilter($product->getData($linkField));
   379  
>  380          $existingOption = $options->getFirstItem();
   381  
   382          if (!$existingOption->getId()) {
   383              throw new InputException(
   384                  __(
   385                      'Product with specified sku: "%1" does not contain option: "%2"',
   386                      [$product->getSku(), $optionId]
```

## 5. `vendor/magento/module-sales/Model/Order.php:1810` (core)

[P-COL-02] Magento\Sales\Model\ResourceModel\Order\Status\History\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
  1804       * @return $this
  1805       */
  1806      public function addStatusHistory(\Magento\Sales\Model\Order\Status\History $history)
  1807      {
  1808          $history->setOrder($this);
  1809          if (!$history->getStatus()) {
> 1810              $previousStatus = $this->getStatusHistoryCollection()->getFirstItem()->getData('status');
  1811              if (!$previousStatus) {
  1812                  $defaultStatus = $this->getConfig()->getStateDefaultStatus($this->getState());
  1813                  $history->setStatus($defaultStatus);
  1814              } else {
  1815                  $history->setStatus($previousStatus);
  1816              }
```

## 6. `vendor/magento/module-sales-graph-ql/Model/Resolver/DateOfFirstOrderResolver.php:58` (core)

[P-COL-02] Magento\Sales\Model\ResourceModel\Order\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    52      {
    53          $collection = $this->orderCollectionFactory->create()
    54              ->addFieldToFilter('customer_id', ['eq' => $userId])
    55              ->addFieldToFilter('store_id', ['in' => $storeIds])
    56              ->addOrder('created_at', Collection::SORT_ORDER_ASC);
    57  
>   58          return $collection->getTotalCount() ? $collection->getFirstItem()->getCreatedAt() : null;
    59      }
    60  }
```

## 7. `vendor/magento/module-integration/Model/AuthorizationService.php:180` (core)

[P-COL-02] Magento\Authorization\Model\ResourceModel\Role\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   174       * @return \Magento\Authorization\Model\Role|false Return false in case when no role associated with user was found.
   175       */
   176      protected function _getUserRole($integrationId)
   177      {
   178          $roleCollection = $this->_roleCollectionFactory->create();
   179          /** @var Role $role */
>  180          $role = $roleCollection
   181              ->setUserFilter($integrationId, UserContextInterface::USER_TYPE_INTEGRATION)
   182              ->getFirstItem();
   183          return $role->getId() ? $role : false;
   184      }
   185  
   186      /**
```

## 8. `vendor/magento/module-paypal-graph-ql/Model/Resolver/HostedProUrl.php:94` (core)

[P-COL-02] Magento\Sales\Model\ResourceModel\Order\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    88          $orderCollection->addFilter(Order::STORE_ID, $storeId);
    89  
    90          if ($orderCollection->getTotalCount() !== 1) {
    91              throw new GraphQlNoSuchEntityException(__('Could not find payment information for cart.'));
    92          }
    93          /** @var Order $order */
>   94          $order = $orderCollection->getFirstItem();
    95  
    96          return $order;
    97      }
    98  }
```

## 9. `vendor/magento/module-webapi/Model/WebapiRoleLocator.php:50` (core)

[P-COL-02] Magento\Authorization\Model\ResourceModel\Role\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    44      {
    45          $userId = $this->userContext->getUserId();
    46          $userType = $this->userContext->getUserType();
    47  
    48          $roleCollection = $this->roleCollectionFactory->create();
    49          /** @var Role $role */
>   50          $role = $roleCollection->setUserFilter($userId, $userType)->getFirstItem();
    51  
    52          if (!$role->getId()) {
    53              return null;
    54          }
    55  
    56          return $role->getId();
```

## 10. `app/code/Adyen/Payment/view/frontend/templates/info/adyen_giftcard.phtml:27` (packagist)

[P-COL-02] Adyen\Payment\Model\ResourceModel\Order\Payment\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    21  <dl class="payment-method adyen_hpp">
    22      <?php if ($block->getPartialPayments()->getSize() > 1): ?>
    23         <?= $block->renderPartialPaymentsHtml(); ?>
    24      <?php elseif ($block->getPartialPayments()->getSize() === 1): ?>
    25          <?php
    26              /** @var OrderPaymentInterface $payment */
>   27              $payment = $block->getPartialPayments()->getFirstItem();
    28              echo $block->escapeHtml(
    29                  sprintf("%s: %s", $block->getMethod()->getTitle(), ucwords($payment->getPaymentMethod()))
    30              );
    31          ?>
    32      <?php else: ?>
    33          <?= $block->escapeHtml($block->getInfo()->getMethodInstance()->getTitle()); ?>
```

## 11. `vendor/magento/module-bundle/Model/LinkManagement.php:422` (core)

[P-COL-02] Magento\Bundle\Model\ResourceModel\Option\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   416          }
   417  
   418          $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
   419          $options = $this->optionCollection->create();
   420          $options->setIdFilter($optionId);
   421          $options->setProductLinkFilter($product->getData($linkField));
>  422          $existingOption = $options->getFirstItem();
   423  
   424          if (!$existingOption->getId()) {
   425              throw new InputException(
   426                  __(
   427                      'Product with specified sku: "%1" does not contain option: "%2"',
   428                      [$product->getSku(), $optionId]
```

## 12. `app/code/Adyen/Payment/Model/ResourceModel/StateData/Collection.php:34` (packagist)

[P-COL-02] Adyen\Payment\Model\ResourceModel\StateData\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    28  
    29      /**
    30       * Fetch the most recent state data with quote ID or return an empty array
    31       */
    32      public function getStateDataArrayWithQuoteId(int $quoteId): array
    33      {
>   34          $stateData = $this->getStateDataRowsWithQuoteId($quoteId)
    35              ->getFirstItem()
    36              ->getData(StateDataInterface::STATE_DATA);
    37          return !empty($stateData) ? json_decode((string) $stateData, true) : [];
    38      }
    39  
    40      public function getStateDataRowsWithQuoteId(int $quoteId, string $sorting = 'DESC'): Collection
```

## 13. `vendor/magento/module-theme/Model/ResourceModel/Theme/Collection.php:146` (core)

[P-COL-02] Magento\Theme\Model\ResourceModel\Theme\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   140      {
   141          $this->_reset()->clear();
   142          list($area, $themePath) = explode('/', $fullPath, 2);
   143          $this->addFieldToFilter('area', $area);
   144          $this->addFieldToFilter('theme_path', $themePath);
   145  
>  146          return $this->getFirstItem();
   147      }
   148  
   149      /**
   150       * Set page size
   151       *
   152       * @param int $size
```

## 14. `vendor/magento/module-configurable-product/Model/Product/Type/Configurable.php:932` (core)

[P-COL-02] Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   926      public function getProductByAttributes($attributesInfo, $product)
   927      {
   928          if (is_array($attributesInfo) && !empty($attributesInfo)) {
   929              $productCollection = $this->getUsedProductCollection($product)->addAttributeToSelect('name');
   930              $this->addAttributesToFilter($productCollection, $attributesInfo);
   931              /** @var \Magento\Catalog\Model\Product $productObject */
>  932              $productObject = $productCollection->getFirstItem();
   933              $productLinkFieldId = $productObject->getId();
   934              if ($productLinkFieldId) {
   935                  return $this->productRepository->getById(
   936                      $productLinkFieldId,
   937                      storeId: $product->hasStoreId() ? $product->getStoreId() : null
   938                  );
```

## 15. `vendor/magento/module-sales/Model/Order.php:580` (core)

[P-COL-02] Magento\Sales\Model\ResourceModel\Order\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   574          $orderCollection = $this->getSalesOrderCollection(
   575              [
   576                  'increment_id' => $incrementId,
   577                  'store_id' => $storeId
   578              ]
   579          );
>  580          return $orderCollection->getFirstItem();
   581      }
   582  
   583      /**
   584       * Get sales Order collection model populated with data
   585       *
   586       * @param array $filters
```

## 16. `vendor/magento/module-authorization/Model/Acl/AclRetriever.php:163` (core)

[P-COL-02] Magento\Authorization\Model\ResourceModel\Role\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   157              throw new \LogicException(
   158                  "The role with user type '{$userType}' does not exist and cannot be created"
   159              );
   160          }
   161          $roleCollection = $this->roleCollectionFactory->create();
   162          /** @var Role $role */
>  163          $role = $roleCollection->setUserFilter($userId, $userType)->getFirstItem();
   164          return $role->getId() ? $role : false;
   165      }
   166  
   167      /**
   168       * Check if the role can be associated with user having provided user type.
   169       *
```

## 17. `vendor/magento/module-theme/Model/Theme.php:238` (core)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   232      public function getStagingVersion()
   233      {
   234          if ($this->getId()) {
   235              $collection = $this->getCollection();
   236              $collection->addFieldToFilter('parent_id', $this->getId());
   237              $collection->addFieldToFilter('type', self::TYPE_STAGING);
>  238              $stagingTheme = $collection->getFirstItem();
   239              if ($stagingTheme->getId()) {
   240                  return $stagingTheme;
   241              }
   242          }
   243          return null;
   244      }
```

## 18. `vendor/magento/module-security/Model/SecurityChecker/Frequency.php:101` (core)

[P-COL-02] Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    95       * @return int
    96       */
    97      private function loadLastRecordCreationTimestamp($securityEventType, $accountReference, $longIp)
    98      {
    99          $collection = $this->collectionFactory->create($securityEventType, $accountReference, $longIp);
   100          /** @var \Magento\Security\Model\PasswordResetRequestEvent $record */
>  101          $record = $collection->filterLastItem()->getFirstItem();
   102  
   103          return (int) strtotime($record->getCreatedAt() ?? '');
   104      }
   105  }
```

## 19. `vendor/magento/module-paypal-graph-ql/Model/Resolver/PayflowLinkToken.php:116` (core)

[P-COL-02] Magento\Sales\Model\ResourceModel\Order\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   110          $orderCollection->addFilter(Order::STORE_ID, $storeId);
   111  
   112          if ($orderCollection->getTotalCount() !== 1) {
   113              throw new GraphQlNoSuchEntityException(__('Could not find payment information for cart.'));
   114          }
   115          /** @var Order $order */
>  116          $order = $orderCollection->getFirstItem();
   117  
   118          return $order;
   119      }
   120  
   121      /**
   122       * Get payment mode based on sandbox flag
```

## 20. `vendor/magento/module-swatches/Helper/Data.php:269` (core)

[P-COL-02] Magento\Catalog\Model\ResourceModel\Product\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   263                  $resultAttributesToFilter[$attributeCode] = $attributes[$attributeCode];
   264              }
   265          }
   266  
   267          $this->addFilterByAttributes($productCollection, $resultAttributesToFilter);
   268  
>  269          $variationProduct = $productCollection->getFirstItem();
   270          if ($variationProduct && $variationProduct->getId()) {
   271              return $this->productRepository->getById($variationProduct->getId());
   272          }
   273  
   274          return false;
   275      }
```

## 21. `vendor/magento/module-bundle/Pricing/Adjustment/DefaultSelectionPriceListProvider.php:152` (core)

[P-COL-02] Magento\Bundle\Model\ResourceModel\Selection\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   146          $selectionsCollection->addPriceFilter($bundleProduct, $searchMin, $useRegularPrice);
   147          if ($bundleProduct->isSalable()) {
   148              $selectionsCollection->addQuantityFilter();
   149          }
   150          $selectionsCollection->setPage(0, 1);
   151  
>  152          $selection = $selectionsCollection->getFirstItem();
   153  
   154          if (!$selection->isEmpty()) {
   155              $this->priceList[] = $this->selectionFactory->create(
   156                  $bundleProduct,
   157                  $selection,
   158                  $selection->getSelectionQty(),
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/magento2-base/setup/src/Magento/Setup/Model/FixtureGenerator/CustomerTemplateGenerator.php:155` | TP-low | Loads all directory regions to read one id, but only in the setup fixture generator. | re-check |  |
| 2 | `vendor/magento/module-catalog/Model/CategoryManagement.php:105` | FP | Filter level = 0 matches only the single tree root category. | re-check |  |
| 3 | `app/code/Adyen/Payment/view/adminhtml/templates/info/adyen_giftcard.phtml:32` | FP | Guarded by getSize() === 1, so at most one row is loaded. | re-check |  |
| 4 | `vendor/magento/module-bundle/Model/LinkManagement.php:380` | FP | setIdFilter() filters on option_id, the primary key, so at most one row. |  |  |
| 5 | `vendor/magento/module-sales/Model/Order.php:1810` | FP | getStatusHistoryCollection() already iterates the collection for saved orders, so it is loaded. |  |  |
| 6 | `vendor/magento/module-sales-graph-ql/Model/Resolver/DateOfFirstOrderResolver.php:58` | TP | Loads all orders of the customer to read the earliest created_at; setPageSize(1) would do. |  |  |
| 7 | `vendor/magento/module-integration/Model/AuthorizationService.php:180` | FP | setUserFilter() matches the single role row of one integration user. |  |  |
| 8 | `vendor/magento/module-paypal-graph-ql/Model/Resolver/HostedProUrl.php:94` | FP | Guarded by getTotalCount() !== 1, so at most one row is loaded. |  |  |
| 9 | `vendor/magento/module-webapi/Model/WebapiRoleLocator.php:50` | FP | setUserFilter() matches the single role row of the current user. |  |  |
| 10 | `app/code/Adyen/Payment/view/frontend/templates/info/adyen_giftcard.phtml:27` | FP | Guarded by getSize() === 1, so at most one row is loaded. |  |  |
| 11 | `vendor/magento/module-bundle/Model/LinkManagement.php:422` | FP | setIdFilter() filters on option_id, the primary key, so at most one row. |  |  |
| 12 | `app/code/Adyen/Payment/Model/ResourceModel/StateData/Collection.php:34` | TP-low | Loads all state data rows of one quote, usually one or a few. |  |  |
| 13 | `vendor/magento/module-theme/Model/ResourceModel/Theme/Collection.php:146` | FP | area plus theme_path identifies a single theme. |  |  |
| 14 | `vendor/magento/module-configurable-product/Model/Product/Type/Configurable.php:932` | TP | Loads every child matching the attributes given; partial attribute sets match many children. |  |  |
| 15 | `vendor/magento/module-sales/Model/Order.php:580` | FP | getSalesOrderCollection() already calls load() and increment_id with store_id is unique. |  |  |
| 16 | `vendor/magento/module-authorization/Model/Acl/AclRetriever.php:163` | FP | setUserFilter() matches the single role row of one user. |  |  |
| 17 | `vendor/magento/module-theme/Model/Theme.php:238` | FP | A theme has at most one staging child of type staging. |  |  |
| 18 | `vendor/magento/module-security/Model/SecurityChecker/Frequency.php:101` | FP | filterLastItem() applies getSelect()->limit(1) in the helper. |  |  |
| 19 | `vendor/magento/module-paypal-graph-ql/Model/Resolver/PayflowLinkToken.php:116` | FP | Guarded by getTotalCount() !== 1, so at most one row is loaded. |  |  |
| 20 | `vendor/magento/module-swatches/Helper/Data.php:269` | TP | Fallback lookup by a partial attribute set loads all matching children of the configurable. |  |  |
| 21 | `vendor/magento/module-bundle/Pricing/Adjustment/DefaultSelectionPriceListProvider.php:152` | FP | setPage(0, 1) sets a page size of 1 through setPageSize(). |  |  |
