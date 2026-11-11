# P-LOOP-12: calibration sample

30 of 52 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/FireGento/FastSimpleImport/Model/Import/Category.php:1229` (extended)

[P-LOOP-12] FireGento\FastSimpleImport\Model\Import\Category::reindexUpdatedCategories() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\CategoryRepositoryInterface::get() on line 624 of Category.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
  1223  
  1224              $this->_connection->insertOnDuplicate($tableName, $tableData, ['value']);
  1225          }
  1226  
  1227          $entityIds = array_unique($entityIds);
  1228          foreach ($entityIds as $entityId) {
> 1229              $this->reindexUpdatedCategories($entityId);
  1230          }
  1231          return $this;
  1232      }
  1233  
  1234      /**
  1235       * Stock item saving.
```

## 2. `app/code/Bazaarvoice/Connector/ViewModel/Pixel.php:159` (extended)

[P-LOOP-12] Bazaarvoice\Connector\ViewModel\Pixel::getReviewableProductFromOrderItem() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Model\ProductRepository::getById() on line 251 of Pixel.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   153              $items = $order->getAllItems();
   154          } else {
   155              $items = $order->getAllVisibleItems();
   156          }
   157          foreach ($items as $itemId => $item) {
   158              try {
>  159                  $product = $this->getReviewableProductFromOrderItem($item);
   160              } catch (NoSuchEntityException $e) {
   161                  continue;
   162              }
   163              /**
   164               * skip configurable items if families are enabled
   165               */
```

## 3. `app/code/Dotdigitalgroup/Email/Model/Product/ParentFinder.php:183` (extended)

[P-LOOP-12] Dotdigitalgroup\Email\Model\Product\ParentFinder::getConfigurableParentProduct() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::getById() on line 109 of ParentFinder.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   177                  );
   178                  continue;
   179              }
   180  
   181              if ($product instanceof Product) {
   182                  /** @var Product|null $parentProduct */
>  183                  $parentProduct = $this->getConfigurableParentProduct($product);
   184                  if (isset($parentProduct)) {
   185                      $configurableParentIds[] = $parentProduct->getEntityId();
   186                  }
   187              }
   188          }
   189  
```

## 4. `app/code/FireGento/MageSetup/Model/Setup/SubProcessor/CmsSubProcessor.php:96` (extended)

[P-LOOP-12] FireGento\MageSetup\Model\Setup\SubProcessor\CmsSubProcessor::createCmsBlock() is called inside a loop and runs a query on every call: it calls Magento\Cms\Model\Block::load() on line 206 of CmsSubProcessor.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    90              }
    91          }
    92  
    93          $cmsBlocks = $config->getCmsBlocks();
    94          if ($cmsBlocks) {
    95              foreach ($cmsBlocks as $blockData) {
>   96                  $this->createCmsBlock($blockData);
    97              }
    98          }
    99      }
   100  
   101      /**
   102       * Create a cms page with the given data
```

## 5. `app/code/CtiDigital/Configurator/Component/ProductLinks.php:88` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\ProductLinks::doesProductExist() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::get() on line 149 of ProductLinks.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    82      private function processSkus(array $data, $linkType)
    83      {
    84          try {
    85              // Loop through the SKUs in the link type
    86              foreach ($data as $sku => $linkSkus) {
    87                  // Check if the product exists
>   88                  if (!$this->doesProductExist($sku)) {
    89                      throw new ComponentException(sprintf('SKU (%s) for products to link to is not found', $sku));
    90                  }
    91                  $this->log->logInfo(sprintf('Creating product links for %s', $sku));
    92  
    93                  // Process the links for that product
    94                  $this->processLinks($sku, $linkSkus, $linkType);
```

## 6. `app/code/Bazaarvoice/Connector/Model/Feed/Product/Category.php:181` (extended)

[P-LOOP-12] Bazaarvoice\Connector\Model\Feed\Product\Category::getCategoryCollection() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Model\Category::load() on line 320 of Category.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   175              foreach ($localeStores[$defaultStore->getId()] as $localeCode => $localeStore) {
   176                  /**
   177                   * @var Store $localeStore
   178                  */
   179                  $localeBaseUrl = $localeStore->getBaseUrl();
   180                  $localeStoreCode = $localeStore->getCode();
>  181                  $localeCollection = $this->getCategoryCollection($localeStore);
   182                  $localeCode = $this->configProvider->getLocale($localeStore->getId());
   183                  foreach ($localeCollection as $category) {
   184                      /**
   185                       * Skip categories not in main store
   186                      */
   187                      if (!isset($categories[$category->getId()])) {
```

## 7. `app/code/CtiDigital/Configurator/Component/ProductLinks.php:94` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\ProductLinks::processLinks() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::get() on line 131 of ProductLinks.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    88                  if (!$this->doesProductExist($sku)) {
    89                      throw new ComponentException(sprintf('SKU (%s) for products to link to is not found', $sku));
    90                  }
    91                  $this->log->logInfo(sprintf('Creating product links for %s', $sku));
    92  
    93                  // Process the links for that product
>   94                  $this->processLinks($sku, $linkSkus, $linkType);
    95              }
    96          } catch (ComponentException $e) {
    97              $this->log->logError($e->getMessage());
    98          } catch (\Exception $e) {
    99              $this->log->logError($e->getMessage());
   100          }
```

## 8. `app/code/TIG/PostNL/Model/Shipment.php:847` (extended)

[P-LOOP-12] TIG\PostNL\Model\Shipment::getBundledPrice() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::get() on line 899 of Shipment.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   841          $productPrices = [];
   842          array_walk(
   843              $orderItems,
   844              function ($orderItem) use (&$productPrices) {
   845                  /** @var \Magento\Sales\Model\Order\Item $orderItem */
   846                  if ($orderItem->getProductType() == 'bundle') {
>  847                      $productPrices[$orderItem->getSku()] = $this->getBundledPrice($orderItem);
   848  
   849                      return;
   850                  }
   851  
   852                  if ($orderItem->getProductType() != 'simple') {
   853                      return;
```

## 9. `app/code/Buckaroo/Magento2/Service/Push/OrderRequestService.php:230` (extended)

[P-LOOP-12] Buckaroo\Magento2\Service\Push\KlarnaMorOrderService::getOrderByDataRequestKey() is called inside a loop and runs a query on every call: it calls Magento\Sales\Api\OrderRepositoryInterface::getList() on line 86 of KlarnaMorOrderService.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   224       *
   225       * @return Order|null
   226       */
   227      protected function getOrderByKlarnaMorDataRequestKey($pushRequest): ?Order
   228      {
   229          foreach ($this->getKlarnaMorDataRequestLookupKeys($pushRequest) as $dataRequestKey) {
>  230              $order = $this->klarnaMorOrderService->getOrderByDataRequestKey($dataRequestKey);
   231  
   232              if ($order === null) {
   233                  $order = $this->klarnaMorOrderService->getOrderByPendingDataRequestPushKey($dataRequestKey);
   234              }
   235  
   236              if ($order !== null) {
```

## 10. `app/code/Buckaroo/Magento2/Model/Export/ConvertToCsv.php:150` (extended)

[P-LOOP-12] Buckaroo\Magento2\Model\Export\ConvertToCsv::convertGiftCardsValue() is called inside a loop and runs a query on every call: it calls Magento\Sales\Api\OrderRepositoryInterface::get() on line 177 of ConvertToCsv.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   144              ->setPageSize($this->pageSize);
   145          $totalCount = (int)$dataProvider->getSearchResult()->getTotalCount();
   146          while ($totalCount > 0) {
   147              $items = $dataProvider->getSearchResult()->getItems();
   148              foreach ($items as $item) {
   149                  if ($this->giftcardConfig->hasAdvancedExportGiftcards($this->storeManager->getStore())) {
>  150                      $this->convertGiftCardsValue($item);
   151                  }
   152                  $this->metadataProvider->convertDate($item, $component->getName());
   153                  $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
   154              }
   155              $searchCriteria->setCurrentPage(++$i);
   156              $totalCount = $totalCount - $this->pageSize;
```

## 11. `app/code/Dotdigitalgroup/Email/view/frontend/templates/email/review.phtml:96` (extended)

[P-LOOP-12] Dotdigitalgroup\Email\Block\Review::getReviewItemUrl() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Model\ProductRepository::getById() on line 225 of Review.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    90          <td style="vertical-align: text-top; padding-right: 2px;">
    91              <img src="<?= $block->escapeUrl($block->getProductImageUrl($item, 'category_page_grid')) ?>"
    92                   alt="<?= $block->escapeHtml($item->getName()) ?>">
    93              <div class="product-name" align="center"><b>
    94                      <?= $block->escapeHtml(mb_substr($item->getName(), 0, 40)) ?></b></div>
    95              <div align="center">
>   96                  <?php $url = $block->getReviewItemUrl($item->getId()) . $block->getReviewReminderAnchor(); ?>
    97                  <?php $linkText = $block->getReviewItemLinkText(); ?>
    98                  <a class="product-url" href="<?= $block->escapeUrl($url); ?>">
    99                      <?= $block->escapeHtml(__($linkText)); ?>
   100                  </a>
   101              </div>
   102          </td>
```

## 12. `app/code/CtiDigital/Configurator/Component/Websites.php:99` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\Websites::setDefaultStore() is called inside a loop and runs a query on every call: it calls Magento\Store\Model\Store::load() on line 360 of Websites.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    93                          // Process the store view
    94                          $this->processStoreView($code, $storeViewData, $storeGroup);
    95                      }
    96  
    97                      // As the store may not be created yet, associated the default store to the store group
    98                      // has to be completed after all stores for the store group have been created.
>   99                      $this->setDefaultStore($storeGroup, $storeGroupData);
   100                  }
   101              }
   102  
   103              if ($this->reindex === true) {
   104                  $this->log->logInfo('Running a reindex of the catalog_product_price table.');
   105                  $indexProcess = $this->indexer->create();
```

## 13. `app/code/TIG/PostNL/Controller/Adminhtml/PdfDownload.php:154` (extended)

[P-LOOP-12] TIG\PostNL\Model\Shipment::getShipment() is called inside a loop and runs a query on every call: it calls Magento\Sales\Model\Order\ShipmentRepository::get() on line 171 of Shipment.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   148       */
   149      private function getShipmentIds()
   150      {
   151          $shipmentIds = [];
   152          foreach ($this->filteredLabels as $identifier) {
   153              $postNLShipment = $this->shipment->getPostNLShipment($identifier);
>  154              $shipment       = $postNLShipment->getShipment();
   155              $shipmentIds[]  = $shipment->getIncrementId();
   156          }
   157  
   158          return implode(", ", $shipmentIds);
   159      }
   160  
```

## 14. `app/code/CtiDigital/Configurator/Component/Categories.php:59` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\Categories::getDefaultCategory() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Model\Category::load() on line 88 of Categories.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    53      {
    54          if (isset($data['categories'])) {
    55              foreach ($data['categories'] as $store) {
    56                  try {
    57                      $group = $this->getStoreGroup($store);
    58                      // Get the default category
>   59                      $category = $this->getDefaultCategory($group);
    60                      if ($category === false) {
    61                          throw new ComponentException(
    62                              sprintf('No default category was found for the store group "%s"', $group)
    63                          );
    64                      }
    65                      if (isset($store['categories'])) {
```

## 15. `app/code/TIG/PostNL/Controller/Adminhtml/PdfDownload.php:153` (extended)

[P-LOOP-12] TIG\PostNL\Service\Shipment\ShipmentServiceAbstract::getPostNLShipment() is called inside a loop and runs a query on every call: it calls TIG\PostNL\Model\ShipmentRepository::getById() on line 63 of ShipmentServiceAbstract.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   147       * @throws NoSuchEntityException
   148       */
   149      private function getShipmentIds()
   150      {
   151          $shipmentIds = [];
   152          foreach ($this->filteredLabels as $identifier) {
>  153              $postNLShipment = $this->shipment->getPostNLShipment($identifier);
   154              $shipment       = $postNLShipment->getShipment();
   155              $shipmentIds[]  = $shipment->getIncrementId();
   156          }
   157  
   158          return implode(", ", $shipmentIds);
   159      }
```

## 16. `app/code/Buckaroo/Magento2/Service/Push/OrderRequestService.php:233` (extended)

[P-LOOP-12] Buckaroo\Magento2\Service\Push\KlarnaMorOrderService::getOrderByPendingDataRequestPushKey() is called inside a loop and runs a query on every call: it calls Magento\Sales\Api\OrderRepositoryInterface::get() on line 137 of KlarnaMorOrderService.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   227      protected function getOrderByKlarnaMorDataRequestKey($pushRequest): ?Order
   228      {
   229          foreach ($this->getKlarnaMorDataRequestLookupKeys($pushRequest) as $dataRequestKey) {
   230              $order = $this->klarnaMorOrderService->getOrderByDataRequestKey($dataRequestKey);
   231  
   232              if ($order === null) {
>  233                  $order = $this->klarnaMorOrderService->getOrderByPendingDataRequestPushKey($dataRequestKey);
   234              }
   235  
   236              if ($order !== null) {
   237                  $this->order = $order;
   238                  return $order;
   239              }
```

## 17. `app/code/Dotdigitalgroup/Email/Model/AbandonedCart/CartInsight/Data.php:222` (extended)

[P-LOOP-12] Dotdigitalgroup\Email\Model\Product\ImageFinder::getCartImageUrl() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::getById() on line 154 of ImageFinder.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   216          foreach ($visibleItems as $item) {
   217              $product = $this->loadProduct($item, $store->getId());
   218              $discountTotal += $item->getDiscountAmount();
   219  
   220              $lineItems[] = [
   221                  'sku' => $item->getSku(),
>  222                  'imageUrl' => $this->imageFinder->getCartImageUrl($item, $store->getId(), $imageType),
   223                  'productUrl' => $this->urlFinder->fetchFor($product),
   224                  'name' => $this->getItemName($item),
   225                  'unitPrice' => $this->getConvertedPrice($product->getPrice(), $store->getId(), $quoteCurrency),
   226                  'unitPrice_incl_tax' => $this->getUnitPriceIncTax($item, $product),
   227                  'quantity' => $item->getQty(),
   228                  'salePrice' => $this->getConvertedPrice($item->getBasePrice(), $store->getId(), $quoteCurrency),
```

## 18. `app/code/Magmodules/Channable/Controller/Adminhtml/Returns/MassCreditmemo.php:73` (extended)

[P-LOOP-12] Magmodules\Channable\Service\Returns\CreateCreditmemo::execute() is called inside a loop and runs a query on every call: it calls Magento\Sales\Api\CreditmemoRepositoryInterface::get() on line 92 of CreateCreditmemo.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    67       * @return Redirect
    68       */
    69      public function execute(): Redirect
    70      {
    71          try {
    72              foreach ($this->getCollection() as $return) {
>   73                  $this->createCreditmemo->execute(
    74                      $return,
    75                      $this->getRequest()->getParam('status')
    76                  );
    77              }
    78              $this->messageManager->addSuccessMessage(__('Returns Processed!'));
    79          } catch (\Exception $e) {
```

## 19. `app/code/Mageplaza/Blog/Block/Post/View.php:295` (extended)

[P-LOOP-12] Mageplaza\Blog\Block\Post\View::getUserComment() is called inside a loop and runs a query on every call: it calls Magento\Customer\Api\CustomerRepositoryInterface::getById() on line 192 of View.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   289                  $isReply   = (bool) $comment['is_reply'];
   290                  $commentId = (int) $comment['comment_id'];
   291                  $replyId   = $isReply ? (int) $comment['reply_id'] : '';
   292                  if ($comment['entity_id'] == 0) {
   293                      $userName = $comment['user_name'];
   294                  } else {
>  295                      $userCmt  = $this->getUserComment($comment['entity_id']);
   296                      $userName = $userCmt->getFirstName() . ' '
   297                          . $userCmt->getLastName();
   298                  }
   299  
   300                  $userName          = $this->_escaper->escapeHtml($userName);
   301                  $countLikes        = $this->getCommentLikes($comment['comment_id']);
```

## 20. `app/code/Yireo/GoogleTagManager2/DataLayer/Tag/Order/OrderItems.php:42` (extended)

[P-LOOP-12] Yireo\GoogleTagManager2\DataLayer\Mapper\OrderItemDataMapper::mapByOrderItem() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::get() on line 69 of OrderItemDataMapper.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    36              $order = $this->checkoutSession->getLastRealOrder();
    37          }
    38  
    39          /** @var Order $order */
    40          $orderItemsData = [];
    41          foreach ($order->getAllVisibleItems() as $orderItem) {
>   42              $orderItemsData[] = $this->orderItemDataMapper->mapByOrderItem($orderItem);
    43          }
    44  
    45          return $orderItemsData;
    46      }
    47  
    48      /**
```

## 21. `app/code/CtiDigital/Configurator/Component/Pages.php:70` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\Pages::processPage() is called inside a loop and runs a query on every call: it calls Magento\Cms\Api\PageRepositoryInterface::getById() on line 100 of Pages.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    64       * @return void
    65       */
    66      public function execute($data = null)
    67      {
    68          try {
    69              foreach ($data as $identifier => $data) {
>   70                  $this->processPage($identifier, $data);
    71              }
    72          } catch (ComponentException $e) {
    73              $this->log->logError($e->getMessage());
    74          }
    75      }
    76  
```

## 22. `app/code/Magmodules/Channable/Service/Order/Items/Add.php:100` (extended)

[P-LOOP-12] Magmodules\Channable\Service\Order\Items\Add::getProductById() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::getById() on line 185 of Add.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    94  
    95          $orderCurrency = $data['price']['currency'] ?? '';
    96          $storeId = (int)$store->getId();
    97  
    98          try {
    99              foreach ($data['products'] as $item) {
>  100                  $product = $this->getProductById((int)$item['id'], (int)$store->getStoreId());
   101                  $price = $this->getProductPrice($item, $product, $store, $quote, $isBusinessOrder);
   102                  $basePrice = $this->currencyConverter->convertToBase($price, $orderCurrency, $storeId);
   103                  $channableBasePrice = $this->currencyConverter->convertToBase(
   104                      (float)$item['price'],
   105                      $orderCurrency,
   106                      $storeId
```

## 23. `app/code/TIG/PostNL/Service/Shipment/Track/DeleteTrack.php:79` (extended)

[P-LOOP-12] TIG\PostNL\Service\Shipment\Track\DeleteTrack::delete() is called inside a loop and runs a query on every call: it calls Magento\Sales\Model\Order\Shipment\Track::load() on line 53 of DeleteTrack.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    73          $shipment = $this->getShipment($shipmentId);
    74          $tracks   = $shipment->getAllTracks();
    75  
    76          /** @var Track $track */
    77          foreach ($tracks as $track) {
    78              // @codingStandardsIgnoreLine
>   79              $this->delete($track->getId());
    80          }
    81      }
    82  }
```

## 24. `app/code/Dotdigitalgroup/Email/Model/AbandonedCart/CartInsight/Data.php:217` (extended)

[P-LOOP-12] Dotdigitalgroup\Email\Model\AbandonedCart\CartInsight\Data::loadProduct() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::get() on line 274 of Data.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   211          $discountTotal = 0;
   212          $lineItems = [];
   213          $imageType = $this->imageType->getImageType($store->getWebsiteId());
   214          $visibleItems = $quote->getAllVisibleItems();
   215  
   216          foreach ($visibleItems as $item) {
>  217              $product = $this->loadProduct($item, $store->getId());
   218              $discountTotal += $item->getDiscountAmount();
   219  
   220              $lineItems[] = [
   221                  'sku' => $item->getSku(),
   222                  'imageUrl' => $this->imageFinder->getCartImageUrl($item, $store->getId(), $imageType),
   223                  'productUrl' => $this->urlFinder->fetchFor($product),
```

## 25. `app/code/CtiDigital/Configurator/Component/Websites.php:89` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\Websites::processStoreGroup() is called inside a loop and runs a query on every call: it calls Magento\Store\Model\Group::load() on line 208 of Websites.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    83                  // Process the website
    84                  $website = $this->processWebsite($code, $websiteData);
    85  
    86                  // Loop through the store groups
    87                  foreach ($websiteData['store_groups'] as $storeGroupData) {
    88                      // Process the store group
>   89                      $storeGroup = $this->processStoreGroup($storeGroupData, $website);
    90  
    91                      // Loop through the store views
    92                      foreach ($storeGroupData['store_views'] as $code => $storeViewData) {
    93                          // Process the store view
    94                          $this->processStoreView($code, $storeViewData, $storeGroup);
    95                      }
```

## 26. `app/code/TIG/PostNL/Service/Shipment/Customs/SortItems.php:89` (extended)

[P-LOOP-12] TIG\PostNL\Service\Options\ProductDictionary::getProductId() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Model\ProductRepository::get() on line 71 of ProductDictionary.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    83              $attributeValues[$product->getId()] = $product->getDataUsingMethod($this->attributeToSort);
    84          }
    85  
    86          $sortItems = [];
    87          /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
    88          foreach ($items as $item) {
>   89              $sortItems[$item->getId()] = $attributeValues[$this->productDictionary->getProductId($item)];
    90          }
    91  
    92          natsort($sortItems);
    93          foreach ($items as $item) {
    94              $sortItems[$item->getId()] = $item;
    95          }
```

## 27. `app/code/Dotdigitalgroup/Email/Model/Email/Template.php:287` (extended)

[P-LOOP-12] Dotdigitalgroup\Email\Model\Email\Template::syncEmailTemplate() is called inside a loop and runs a query on every call: it calls Magento\Email\Model\ResourceModel\Template::load() on line 333 of Template.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   281                  $campaignId = $this->getConfigValue($dotConfigPath, $storeId);
   282                  $configPath = $this->templateConfigMapping[$configTemplateId];
   283                  $emailTemplateId = $this->getConfigValue($configPath, $storeId);
   284  
   285                  if ($campaignId && $emailTemplateId && ! in_array($campaignId, $this->processedCampaigns)) {
   286                      //sync template for store
>  287                      $this->syncEmailTemplate($campaignId, $emailTemplateId, $store);
   288                      $result['store'] .= ', ' . $store->getCode();
   289  
   290                      $this->processedCampaigns[$campaignId] = $campaignId;
   291                  }
   292              }
   293          }
```

## 28. `app/code/CtiDigital/Configurator/Component/Websites.php:84` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\Websites::processWebsite() is called inside a loop and runs a query on every call: it calls Magento\Store\Model\Website::load() on line 128 of Websites.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    78                  throw new ComponentException("No websites found.");
    79              }
    80  
    81              // Loop through the websites
    82              foreach ($data['websites'] as $code => $websiteData) {
    83                  // Process the website
>   84                  $website = $this->processWebsite($code, $websiteData);
    85  
    86                  // Loop through the store groups
    87                  foreach ($websiteData['store_groups'] as $storeGroupData) {
    88                      // Process the store group
    89                      $storeGroup = $this->processStoreGroup($storeGroupData, $website);
    90  
```

## 29. `app/code/CtiDigital/Configurator/Component/ReviewRating.php:86` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\ReviewRating::getReviewRating() is called inside a loop and runs a query on every call: it calls Magento\Review\Model\Rating::load() on line 129 of ReviewRating.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
    80  
    81          foreach ($reviewRatings as $code => $reviewRating) {
    82              try {
    83                  /**
    84                   * @var Rating $ratingModel
    85                   */
>   86                  $ratingModel = $this->getReviewRating($code);
    87                  $ratingModel = $this->updateOrCreateRating($ratingModel, $code, $reviewRating);
    88                  $ratingModel->save();
    89                  $this->setOptions($ratingModel);
    90                  $this->log->logInfo(__('Updated review rating "%1"', $code));
    91              } catch (\Exception $e) {
    92                  $this->log->logError(
```

## 30. `app/code/CtiDigital/Configurator/Component/ProductLinks.php:118` (extended)

[P-LOOP-12] CtiDigital\Configurator\Component\ProductLinks::doesProductExist() is called inside a loop and runs a query on every call: it calls Magento\Catalog\Api\ProductRepositoryInterface::get() on line 149 of ProductLinks.php. Fetch the data for all items before the loop, with a collection or getList(), and pass it in.

```
   112          try {
   113              $productLinks = [];
   114  
   115              // Loop through all the products that require linking to a product
   116              foreach ($linkSkus as $position => $linkSku) {
   117                  // Check if the product exists
>  118                  if (!$this->doesProductExist($linkSku)) {
   119                      throw new ComponentException(sprintf('SKU (%s) to link does not exist', $linkSku));
   120                  }
   121  
   122                  // Create an array of product link objects
   123                  $productLinks[] = $this->productLinkFactory->create()->setSku($sku)
   124                      ->setLinkedProductSku($linkSku)
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/FireGento/FastSimpleImport/Model/Import/Category.php:1229` | TP | Every imported category id runs CategoryRepository::get() for a distinct, uncached id (plus one more per store), over an unbounded import batch. |  |  |
| 2 | `app/code/Bazaarvoice/Connector/ViewModel/Pixel.php:159` | TP-low | Loops over the items of one order in the checkout success pixel, one getById() per item. |  |  |
| 3 | `app/code/Dotdigitalgroup/Email/Model/Product/ParentFinder.php:183` | unclear | No caller of getConfigurableParentIdsFromProductIds() exists in the corpus, so the size of the id list is unknown, and repeated parents hit the repository cache. |  |  |
| 4 | `app/code/FireGento/MageSetup/Model/Setup/SubProcessor/CmsSubProcessor.php:96` | TP-low | One-off setup command looping over the CMS blocks of a setup config. |  |  |
| 5 | `app/code/CtiDigital/Configurator/Component/ProductLinks.php:88` | TP-low | One-off configurator command looping over configured product link SKUs. |  |  |
| 6 | `app/code/Bazaarvoice/Connector/Model/Feed/Product/Category.php:181` | TP-low | Feed generation loads one root category per locale store, bounded by the store count. |  |  |
| 7 | `app/code/CtiDigital/Configurator/Component/ProductLinks.php:94` | FP | The get($sku) on line 131 returns the product that doesProductExist($sku) on line 88 just put in ProductRepository's local cache, so the named call runs no query. |  |  |
| 8 | `app/code/TIG/PostNL/Model/Shipment.php:847` | TP-low | Loops over the items of one order when an admin shipment computes extra cover. |  |  |
| 9 | `app/code/Buckaroo/Magento2/Service/Push/OrderRequestService.php:230` | TP-low | The loop runs over at most two lookup keys (datarequest and related datarequest) and returns on the first found order. |  |  |
| 10 | `app/code/Buckaroo/Magento2/Model/Export/ConvertToCsv.php:150` | TP | When advanced gift card export is on, the admin grid CSV export loads one order per exported row, over every row of the grid. |  |  |
| 11 | `app/code/Dotdigitalgroup/Email/view/frontend/templates/email/review.phtml:96` | TP-low | Loops over the items of one order in a review email block, and getById() runs only when product-page links are enabled. |  |  |
| 12 | `app/code/CtiDigital/Configurator/Component/Websites.php:99` | TP-low | One-off configurator command looping over a few configured store groups. |  |  |
| 13 | `app/code/TIG/PostNL/Controller/Adminhtml/PdfDownload.php:154` | TP-low | Loops only over skipped labels of one admin label download to build a warning message. |  |  |
| 14 | `app/code/CtiDigital/Configurator/Component/Categories.php:59` | TP-low | One-off configurator command looping over a few configured store groups. |  |  |
| 15 | `app/code/TIG/PostNL/Controller/Adminhtml/PdfDownload.php:153` | TP-low | Loops only over skipped labels of one admin label download to build a warning message. |  |  |
| 16 | `app/code/Buckaroo/Magento2/Service/Push/OrderRequestService.php:233` | FP | get() runs only when an order id is found, and the loop returns on the first found order, so at most one call. |  |  |
| 17 | `app/code/Dotdigitalgroup/Email/Model/AbandonedCart/CartInsight/Data.php:222` | TP-low | Loops over the visible items of one cart, and for simple items the getById() hits the cache that loadProduct() on line 217 filled. |  |  |
| 18 | `app/code/Magmodules/Channable/Controller/Adminhtml/Returns/MassCreditmemo.php:73` | TP-low | Admin mass action over selected returns, where each iteration also creates a credit memo that costs far more than the get(). |  |  |
| 19 | `app/code/Mageplaza/Blog/Block/Post/View.php:295` | TP | The storefront post page renders all comments without paging and loads one customer per distinct commenting customer. |  |  |
| 20 | `app/code/Yireo/GoogleTagManager2/DataLayer/Tag/Order/OrderItems.php:42` | TP-low | Loops over the visible items of one order on the checkout success data layer. |  |  |
| 21 | `app/code/CtiDigital/Configurator/Component/Pages.php:70` | TP-low | One-off configurator command, and getById() runs only for pages that already exist. |  |  |
| 22 | `app/code/Magmodules/Channable/Service/Order/Items/Add.php:100` | TP-low | Loops over the products of one imported marketplace order. |  |  |
| 23 | `app/code/TIG/PostNL/Service/Shipment/Track/DeleteTrack.php:79` | TP-low | Loops over the tracks of one shipment when a PostNL shipment is reset, and each iteration also deletes the track. |  |  |
| 24 | `app/code/Dotdigitalgroup/Email/Model/AbandonedCart/CartInsight/Data.php:217` | TP-low | Loops over the visible items of one cart for cart insight data. |  |  |
| 25 | `app/code/CtiDigital/Configurator/Component/Websites.php:89` | TP-low | One-off configurator command looping over a few configured store groups. |  |  |
| 26 | `app/code/TIG/PostNL/Service/Shipment/Customs/SortItems.php:89` | TP-low | Loops over the items of one shipment when sorting GlobalPack customs items. |  |  |
| 27 | `app/code/Dotdigitalgroup/Email/Model/Email/Template.php:287` | TP-low | Cron every six hours over stores and configured templates, once per campaign, and each call also makes a remote API request that costs more. |  |  |
| 28 | `app/code/CtiDigital/Configurator/Component/Websites.php:84` | TP-low | One-off configurator command looping over a few configured websites. |  |  |
| 29 | `app/code/CtiDigital/Configurator/Component/ReviewRating.php:86` | TP-low | One-off configurator command looping over a few configured review ratings. |  |  |
| 30 | `app/code/CtiDigital/Configurator/Component/ProductLinks.php:118` | TP-low | One-off configurator command checking each configured link SKU once. |  |  |
