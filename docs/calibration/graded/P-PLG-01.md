# P-PLG-01: calibration sample

30 of 35 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-catalog-search/Model/Attribute/SearchWeight.php:54` (core)

[P-PLG-01] Magento\CatalogSearch\Model\Attribute\SearchWeight::aroundSave() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    48       * @param \Closure $proceed
    49       * @param \Magento\Framework\Model\AbstractModel $attribute
    50       * @return \Magento\Catalog\Model\ResourceModel\Attribute
    51       *
    52       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    53       */
>   54      public function aroundSave(
    55          \Magento\Catalog\Model\ResourceModel\Attribute $subject,
    56          \Closure $proceed,
    57          \Magento\Framework\Model\AbstractModel $attribute
    58      ) {
    59          $isNew = $attribute->isObjectNew();
    60          $isWeightChanged = $attribute->dataHasChangedFor('search_weight');
```

## 2. `vendor/magento/module-configurable-product/Plugin/Model/ResourceModel/Product.php:196` (core)

[P-PLG-01] Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product::aroundDelete() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
   190       * @param ProductResource $subject
   191       * @param \Closure $proceed
   192       * @param ProductModel $product
   193       * @return ProductResource
   194       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   195       */
>  196      public function aroundDelete(
   197          ProductResource $subject,
   198          \Closure $proceed,
   199          ProductModel $product
   200      ) {
   201          $configurableProductIds = $this->configurable->getParentIdsByChild($product->getId());
   202          $result = $proceed($product);
```

## 3. `vendor/magento/module-inventory-bundle-product-indexer/Plugin/Bundle/Model/ResourceModel/Indexer/StockPlugin.php:38` (core)

[P-PLG-01] Magento\InventoryBundleProductIndexer\Plugin\Bundle\Model\ResourceModel\Indexer\StockPlugin::aroundReindexEntity() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    32       * @param Stock $subject
    33       * @param callable $proceed
    34       * @param array|int $entityIds
    35       * @return void
    36       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    37       */
>   38      public function aroundReindexEntity(Stock $subject, callable $proceed, $entityIds = []): void
    39      {
    40          $stockId = $this->defaultStockProvider->getId();
    41          $this->reservationsIndexTable->createTable($stockId);
    42          $this->prepareReservationsIndexData->execute($stockId);
    43          $proceed($entityIds);
    44          $this->reservationsIndexTable->dropTable($stockId);
```

## 4. `vendor/magento/module-customer/Plugin/ValidateDobOnSave.php:68` (core)

[P-PLG-01] Magento\Customer\Plugin\ValidateDobOnSave::aroundSave() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    62       * @throws InputException
    63       * @throws \Magento\Framework\Exception\LocalizedException
    64       * @SuppressWarnings(PHPMD.CyclomaticComplexity)
    65       * @SuppressWarnings(PHPMD.NPathComplexity)
    66       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    67       */
>   68      public function aroundSave(
    69          CustomerRepositoryInterface $subject,
    70          callable $proceed,
    71          CustomerInterface $customer,
    72          $passwordHash = null
    73      ) {
    74          $dobRaw = $customer->getDob();
```

## 5. `vendor/magento/module-configurable-product/Model/Entity/Product/Attribute/Group/AttributeMapper/Plugin.php:48` (core)

[P-PLG-01] Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin::aroundMap() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    42       * @param callable $proceed
    43       * @param \Magento\Eav\Model\Entity\Attribute $attribute
    44       *
    45       * @return array
    46       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    47       */
>   48      public function aroundMap(
    49          \Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface $subject,
    50          \Closure $proceed,
    51          \Magento\Eav\Model\Entity\Attribute $attribute
    52      ) {
    53          $setId = $this->registry->registry('current_attribute_set')->getId();
    54          $result = $proceed($attribute);
```

## 6. `vendor/magento/module-media-content/Plugin/MediaGalleryAssetDeleteByDirectoryPath.php:65` (core)

[P-PLG-01] Magento\MediaContent\Plugin\MediaGalleryAssetDeleteByDirectoryPath::aroundExecute() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    59       * @param array $paths
    60       * @throws CouldNotDeleteException
    61       * @return void
    62       *
    63       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    64       */
>   65      public function aroundExecute(
    66          DeleteDirectoriesByPathsInterface $subject,
    67          \Closure $proceed,
    68          array $paths
    69      ) : void {
    70          $assetIdsArrays =[];
    71          foreach ($paths as $path) {
```

## 7. `vendor/magento/module-media-content/Plugin/MediaGalleryAssetDeleteByPath.php:65` (core)

[P-PLG-01] Magento\MediaContent\Plugin\MediaGalleryAssetDeleteByPath::aroundExecute() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    59       * @throws CouldNotDeleteException
    60       * @throws LocalizedException
    61       * @return void
    62       *
    63       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    64       */
>   65      public function aroundExecute(
    66          DeleteAssetsByPathsInterface $subject,
    67          \Closure $proceed,
    68          array $paths
    69      ) : void {
    70          $assets = $this->getByPaths->execute($paths);
    71  
```

## 8. `vendor/magento/module-security/Model/Plugin/AdminUserForm.php:59` (core)

[P-PLG-01] Magento\Security\Model\Plugin\AdminUserForm::aroundGetFormHtml() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    53       * Add the `expires_at` field to the admin user edit form.
    54       *
    55       * @param \Magento\User\Block\User\Edit\Tab\Main $subject
    56       * @param \Closure $proceed
    57       * @return mixed
    58       */
>   59      public function aroundGetFormHtml(
    60          \Magento\User\Block\User\Edit\Tab\Main $subject,
    61          \Closure $proceed
    62      ) {
    63          /** @var \Magento\Framework\Data\Form $form */
    64          $form = $subject->getForm();
    65          if (is_object($form)) {
```

## 9. `app/code/Mageplaza/Smtp/Plugin/Model/Resolver/Products/DataProvider/ProductSearch/ProductSearchCriteriaBuilder.php:77` (packagist)

[P-PLG-01] Mageplaza\Smtp\Plugin\Model\Resolver\Products\DataProvider\ProductSearch\ProductSearchCriteriaBuilder::aroundBuild() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    71       * @param ProductCollectionSearchCriteriaBuilder $subject
    72       * @param callable $proceed
    73       * @param SearchCriteriaInterface $searchCriteria
    74       *
    75       * @return SearchCriteria
    76       */
>   77      public function aroundBuild(
    78          ProductCollectionSearchCriteriaBuilder $subject,
    79          callable $proceed,
    80          SearchCriteriaInterface $searchCriteria
    81      ) {
    82          /** @var SearchCriteria $searchCriteriaForCollection */
    83          $searchCriteriaForCollection = $proceed($searchCriteria);
```

## 10. `vendor/magento/module-inventory-low-quantity-notification/Plugin/InventoryCatalogApi/BulkConfigurationTransferInterfacePlugin.php:54` (core)

[P-PLG-01] Magento\InventoryLowQuantityNotification\Plugin\InventoryCatalogApi\BulkConfigurationTransferInterfacePlugin::aroundExecute() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    48       * @param string $originSource
    49       * @param string $destinationSource
    50       * @param bool $unassignFromOrigin
    51       * @return bool
    52       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    53       */
>   54      public function aroundExecute(
    55          BulkInventoryTransferInterface $subject,
    56          callable $proceed,
    57          array $skus,
    58          string $originSource,
    59          string $destinationSource,
    60          bool $unassignFromOrigin
```

## 11. `vendor/magento/module-persistent/Plugin/AddPersistentRememberMeInitPlugin.php:39` (core)

[P-PLG-01] Magento\Persistent\Plugin\AddPersistentRememberMeInitPlugin::aroundGenerateElements() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    33       * Add the RememberMeInit block to the layout.
    34       *
    35       * @param Layout $subject
    36       * @param callable $proceed
    37       * @return void
    38       */
>   39      public function aroundGenerateElements(Layout $subject, callable $proceed)
    40      {
    41          $proceed();
    42  
    43          if (!$this->customerSession->isLoggedIn()
    44              && $this->persistentData->isEnabled()
    45              && $this->persistentData->isRememberMeEnabled()
```

## 12. `vendor/magento/module-catalog-rule-configurable/Plugin/CatalogRule/Model/Rule/ConfigurableProductHandler.php:56` (core)

[P-PLG-01] Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\ConfigurableProductHandler::aroundGetMatchingProductIds() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    50       * @return array
    51       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    52       * @SuppressWarnings(PHPMD.CyclomaticComplexity)
    53       * @SuppressWarnings(PHPMD.NPathComplexity)
    54       * @SuppressWarnings(PHPMD.UnusedLocalVariable)
    55       */
>   56      public function aroundGetMatchingProductIds(
    57          \Magento\CatalogRule\Model\Rule $rule,
    58          \Closure $proceed
    59      ): array {
    60          $productsFilter = $rule->getProductsFilter() ? (array) $rule->getProductsFilter() : [];
    61          if ($productsFilter) {
    62              $rule->setProductsFilter(
```

## 13. `vendor/magento/module-catalog/Plugin/RemoveImagesFromGalleryAfterRemovingProduct.php:49` (core)

[P-PLG-01] Magento\Catalog\Plugin\RemoveImagesFromGalleryAfterRemovingProduct::aroundDelete() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    43       *
    44       * @param ProductRepositoryInterface $subject
    45       * @param callable $proceed
    46       * @param ProductInterface $product
    47       * @return bool
    48       */
>   49      public function aroundDelete(
    50          ProductRepositoryInterface $subject,
    51          callable $proceed,
    52          ProductInterface $product
    53      ): bool {
    54          $mediaGalleryAttributeId = $this->mediaGalleryReadHandler->getAttribute()->getAttributeId();
    55          $mediaGallery = $this->galleryResource->loadProductGalleryByAttributeId($product, $mediaGalleryAttributeId);
```

## 14. `vendor/magento/module-re-captcha-webapi-rest/Plugin/RestValidationPlugin.php:81` (core)

[P-PLG-01] Magento\ReCaptchaWebapiRest\Plugin\RestValidationPlugin::aroundValidate() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    75       * @param callable $proceed
    76       * @throws WebapiException
    77       * @return void
    78       *
    79       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    80       */
>   81      public function aroundValidate(RequestValidator $subject, callable $proceed): void
    82      {
    83          $request = clone $this->request;
    84          $proceed();
    85          $route = $this->restRouter->match($request);
    86          $endpoint = $this->endpointFactory->create([
    87              'class' => $route->getServiceClass(),
```

## 15. `vendor/magento/module-catalog-url-rewrite/Model/Category/Plugin/Category/Remove.php:61` (core)

[P-PLG-01] Magento\CatalogUrlRewrite\Model\Category\Plugin\Category\Remove::aroundDelete() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    55       * @param \Magento\Catalog\Model\ResourceModel\Category $subject
    56       * @param \Closure $proceed
    57       * @param \Magento\Catalog\Api\Data\CategoryInterface $category
    58       * @return mixed
    59       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    60       */
>   61      public function aroundDelete(
    62          \Magento\Catalog\Model\ResourceModel\Category $subject,
    63          \Closure $proceed,
    64          \Magento\Catalog\Api\Data\CategoryInterface $category
    65      ) {
    66          $categoryIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
    67          $categoryIds[] = $category->getId();
```

## 16. `vendor/magento/module-catalog/Model/Indexer/Product/Price/Plugin/CustomerGroup.php:81` (core)

[P-PLG-01] Magento\Catalog\Model\Indexer\Product\Price\Plugin\CustomerGroup::aroundSave() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    75       * @param \Closure $proceed
    76       * @param GroupInterface $group
    77       *
    78       * @return GroupInterface
    79       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    80       */
>   81      public function aroundSave(
    82          GroupRepositoryInterface $subject,
    83          \Closure $proceed,
    84          GroupInterface $group
    85      ) {
    86          $isGroupNew = $group->getId() === null;
    87          $group = $proceed($group);
```

## 17. `vendor/magento/module-inventory-sales/Plugin/Sales/OrderManagement/DeductSourceItemQuantityOnRefundPlugin.php:98` (core)

[P-PLG-01] Magento\InventorySales\Plugin\Sales\OrderManagement\DeductSourceItemQuantityOnRefundPlugin::aroundSave() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    92       * @param CreditmemoRepositoryInterface $subject
    93       * @param callable $proceed
    94       * @param CreditmemoInterface $entity
    95       * @return mixed
    96       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    97       */
>   98      public function aroundSave(
    99          CreditmemoRepositoryInterface $subject,
   100          callable $proceed,
   101          CreditmemoInterface $entity
   102      ) {
   103          $isNewCreditMemo = !(bool)$entity->getEntityId();
   104          $result = $proceed($entity);
```

## 18. `vendor/magento/module-inventory-indexer/Plugin/InventoryApi/ReindexAfterSourceItemsDeletePlugin.php:43` (core)

[P-PLG-01] Magento\InventoryIndexer\Plugin\InventoryApi\ReindexAfterSourceItemsDeletePlugin::aroundExecute() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    37       * @param SourceItemsDeleteInterface $subject
    38       * @param callable $proceed
    39       * @param SourceItemInterface[] $sourceItems
    40       * @return void
    41       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    42       */
>   43      public function aroundExecute(
    44          SourceItemsDeleteInterface $subject,
    45          callable $proceed,
    46          array $sourceItems
    47      ) {
    48          $sourceItemIds = $this->getSourceItemIds->execute($sourceItems);
    49          $skuListInStockList = $this->getSkuListInStock->execute($sourceItemIds);
```

## 19. `vendor/magento/module-theme/Model/Indexer/Design/Config/Plugin/Website.php:36` (core)

[P-PLG-01] Magento\Theme\Model\Indexer\Design\Config\Plugin\Website::aroundSave() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    30       *
    31       * @param StoreWebsite $subject
    32       * @param \Closure $proceed
    33       * @return StoreWebsite
    34       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    35       */
>   36      public function aroundSave(StoreWebsite $subject, \Closure $proceed)
    37      {
    38          $isObjectNew = $subject->getId() == 0;
    39          $result = $proceed();
    40          if ($isObjectNew) {
    41              $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID)->invalidate();
    42          }
```

## 20. `vendor/magento/module-grouped-product/Model/ResourceModel/Product/Link/RelationPersister.php:70` (core)

[P-PLG-01] Magento\GroupedProduct\Model\ResourceModel\Product\Link\RelationPersister::aroundDeleteProductLink() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    64       *
    65       * @param Link $subject
    66       * @param \Closure $proceed
    67       * @param int $linkId
    68       * @return Link
    69       */
>   70      public function aroundDeleteProductLink(Link $subject, \Closure $proceed, $linkId)
    71      {
    72          /** @var \Magento\Catalog\Model\ProductLink\Link $link */
    73          $link = $this->linkFactory->create();
    74          $subject->load($link, $linkId, $subject->getIdFieldName());
    75          $result = $proceed($linkId);
    76          if ($link->getLinkTypeId() == GroupedLink::LINK_TYPE_GROUPED) {
```

## 21. `vendor/magento/module-inventory-cache/Plugin/InventoryIndexer/Indexer/Stock/Strategy/Sync/CacheFlush.php:51` (core)

[P-PLG-01] Magento\InventoryCache\Plugin\InventoryIndexer\Indexer\Stock\Strategy\Sync\CacheFlush::aroundExecuteList() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    45       * @param callable $proceed
    46       * @param array $stockIds
    47       * @return void
    48       * @throws \Exception in case product entity type hasn't been initialize.
    49       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    50       */
>   51      public function aroundExecuteList(Sync $subject, callable $proceed, array $stockIds)
    52      {
    53          $beforeReindexProductIds = $this->getProductIdsByStockIds->execute($stockIds);
    54          $proceed($stockIds);
    55          $afterReindexProductIds = $this->getProductIdsByStockIds->execute($stockIds);
    56          $productIdsForCacheClean = array_diff($beforeReindexProductIds, $afterReindexProductIds);
    57          if ($productIdsForCacheClean) {
```

## 22. `vendor/magento/module-inventory-configuration/Plugin/CatalogInventory/Model/System/Config/Backend/Minqty/AllowNegativeMinQtyInConfigPlugin.php:21` (core)

[P-PLG-01] Magento\InventoryConfiguration\Plugin\CatalogInventory\Model\System\Config\Backend\Minqty\AllowNegativeMinQtyInConfigPlugin::aroundBeforeSave() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    15       * Allow min_qty to be assigned a value below 0.
    16       *
    17       * @param Minqty $subject
    18       * @param callable $proceed
    19       * @return mixed
    20       */
>   21      public function aroundBeforeSave(
    22          Minqty $subject,
    23          callable $proceed
    24      ) {
    25          $originalMinQty = $proceed();
    26          $originalMinQty->setValue($subject->getFieldsetDataValue('min_qty'));
    27          return $originalMinQty;
```

## 23. `vendor/magento/module-customer/Model/Plugin/CustomerGridIndexAfterWebsiteDelete.php:49` (core)

[P-PLG-01] Magento\Customer\Model\Plugin\CustomerGridIndexAfterWebsiteDelete::aroundDelete() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    43       * Run customer_grid indexer after deleting website
    44       *
    45       * @param Website $subject
    46       * @param callable $proceed
    47       * @return Website
    48       */
>   49      public function aroundDelete(Website $subject, callable $proceed): Website
    50      {
    51          $customerIds = $this->getCustomerIdsByWebsiteId((int) $subject->getId());
    52          $result = $proceed();
    53  
    54          if ($customerIds) {
    55              $this->indexerRegistry->get(self::CUSTOMER_GRID_INDEXER_ID)
```

## 24. `vendor/magento/module-customer/Model/Plugin/CustomerFlushFormKey.php:43` (core)

[P-PLG-01] Magento\Customer\Model\Plugin\CustomerFlushFormKey::aroundExecute() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    37      /**
    38       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    39       * @param FlushFormKey $subject
    40       * @param callable $proceed
    41       * @param $args
    42       */
>   43      public function aroundExecute(FlushFormKey $subject, callable $proceed, ...$args)
    44      {
    45          $currentFormKey = $this->dataFormKey->getFormKey();
    46          $proceed(...$args);
    47          $beforeParams = $this->session->getBeforeRequestParams();
    48          if (isset($beforeParams['form_key']) && $beforeParams['form_key'] === $currentFormKey) {
    49              $beforeParams['form_key'] = $this->dataFormKey->getFormKey();
```

## 25. `app/code/Fastly/Cdn/Plugin/ExcludeFilesFromMinification.php:38` (packagist)

[P-PLG-01] Fastly\Cdn\Plugin\ExcludeFilesFromMinification::aroundGetExcludes() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    32      /**
    33       * @param Minification $subject
    34       * @param callable $proceed
    35       * @param $contentType
    36       * @return array
    37       */
>   38      public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType)
    39      {
    40          $result = $proceed($contentType);
    41          if ($contentType != 'js' && !$subject->isEnabled($contentType)) {
    42              return $result;
    43          }
    44          $result[] = 'https://www.gstatic.com/charts/loader.js';
```

## 26. `app/code/Magefan/Blog/Plugin/Magento/AdminGws/Model/ModelsPlugin.php:19` (packagist)

[P-PLG-01] Magefan\Blog\Plugin\Magento\AdminGws\Model\ModelsPlugin::aroundCmsPageSaveBefore() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    13      /**
    14       * @param $subject
    15       * @param callable $proceed
    16       * @param $model
    17       * @return callable
    18       */
>   19      public function aroundCmsPageSaveBefore($subject, callable $proceed, $model)
    20      {
    21          $isBlogModel = ($model instanceof \Magefan\Blog\Model\Post
    22              || $model instanceof \Magefan\Blog\Model\Category
    23          );
    24          if ($isBlogModel) {
    25              $storeId = $model->getStoreId();
```

## 27. `vendor/magento/module-checkout/Model/Plugin/RecollectQuoteOnCustomerGroupChange.php:83` (core)

[P-PLG-01] Magento\Checkout\Model\Plugin\RecollectQuoteOnCustomerGroupChange::aroundSave() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    77       *
    78       * @throws LocalizedException
    79       * @throws NoSuchEntityException
    80       * @SuppressWarnings(PHPMD.CyclomaticComplexity)
    81       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    82       */
>   83      public function aroundSave(
    84          CustomerResource $subject,
    85          callable $proceed,
    86          AbstractModel $customer
    87      ): CustomerResource {
    88          $customerId = $customer->getId() ?: $customer->getEntityId();
    89          /** @var Customer $customer */
```

## 28. `vendor/magento/module-login-as-customer-frontend-ui/Plugin/KeepLoginAsCustomerSessionDataPlugin.php:60` (core)

[P-PLG-01] Magento\LoginAsCustomerFrontendUi\Plugin\KeepLoginAsCustomerSessionDataPlugin::aroundClearStorage() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    54       * @param SessionManagerInterface $subject
    55       * @param \Closure $proceed
    56       * @return SessionManagerInterface
    57       *
    58       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    59       */
>   60      public function aroundClearStorage(
    61          SessionManagerInterface $subject,
    62          \Closure $proceed
    63      ): SessionManagerInterface {
    64          $enabled = $this->config->isEnabled();
    65          $adminId = $enabled ? $this->getLoggedAsCustomerAdminId->execute() : null;
    66          $result = $proceed();
```

## 29. `vendor/magento/module-inventory-sales/Plugin/Store/Model/ResourceModel/Website/UpdateSalesChannelWebsiteCodePlugin.php:56` (core)

[P-PLG-01] Magento\InventorySales\Plugin\Store\Model\ResourceModel\Website\UpdateSalesChannelWebsiteCodePlugin::aroundSave() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    50       * @return WebsiteResourceModel
    51       * @throws CouldNotSaveException
    52       * @throws NoSuchEntityException
    53       * @throws ValidationException
    54       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    55       */
>   56      public function aroundSave(
    57          WebsiteResourceModel $subject,
    58          callable $proceed,
    59          AbstractModel $website
    60      ) {
    61          $newCode = $website->getCode();
    62          $oldCode = null;
```

## 30. `vendor/magento/module-re-captcha-frontend-ui/Plugin/ExcludeFromMinification.php:27` (core)

[P-PLG-01] Magento\ReCaptchaFrontendUi\Plugin\ExcludeFromMinification::aroundGetExcludes() calls $proceed once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.

```
    21       * @param Minification $subject
    22       * @param callable $proceed
    23       * @param string $contentType
    24       * @return array
    25       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    26       */
>   27      public function aroundGetExcludes(Minification $subject, callable $proceed, $contentType)
    28      {
    29          $result = $proceed($contentType);
    30          if ($contentType !== 'js') {
    31              return $result;
    32          }
    33          $result[] = 'https://www.google.com/recaptcha/api.js';
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-catalog-search/Model/Attribute/SearchWeight.php:54` | FP | isObjectNew and dataHasChangedFor are read before save, and save resets them, so the after-logic needs pre-call state. | re-check |  |
| 2 | `vendor/magento/module-configurable-product/Plugin/Model/ResourceModel/Product.php:196` | FP | Parent ids of the child must be read before the product is deleted and are reindexed after it. | re-check |  |
| 3 | `vendor/magento/module-inventory-bundle-product-indexer/Plugin/Bundle/Model/ResourceModel/Indexer/StockPlugin.php:38` | TP | Table create/prepare before and drop after depend only on the default stock id, which an after plugin can fetch again. | re-check |  |
| 4 | `vendor/magento/module-customer/Plugin/ValidateDobOnSave.php:68` | TP | Only validates and normalizes the dob before an unconditional return of proceed, which a beforeSave plugin can do. |  |  |
| 5 | `vendor/magento/module-configurable-product/Model/Entity/Product/Attribute/Group/AttributeMapper/Plugin.php:48` | TP | The attribute set id comes from the registry, which proceed does not change, so an afterMap plugin could read it and modify the result. |  |  |
| 6 | `vendor/magento/module-media-content/Plugin/MediaGalleryAssetDeleteByDirectoryPath.php:65` | FP | Asset ids are looked up by directory path before the directories are deleted and are needed after the call. |  |  |
| 7 | `vendor/magento/module-media-content/Plugin/MediaGalleryAssetDeleteByPath.php:65` | FP | Assets are looked up by path before they are deleted, so their ids are not available in an after plugin. |  |  |
| 8 | `vendor/magento/module-security/Model/Plugin/AdminUserForm.php:59` | TP | Adds a field to the form and then returns proceed unconditionally, which beforeGetFormHtml can do. |  |  |
| 9 | `app/code/Mageplaza/Smtp/Plugin/Model/Resolver/Products/DataProvider/ProductSearch/ProductSearchCriteriaBuilder.php:77` | TP | All logic runs after proceed and uses only the result and the searchCriteria argument, which an after plugin also receives. |  |  |
| 10 | `vendor/magento/module-inventory-low-quantity-notification/Plugin/InventoryCatalogApi/BulkConfigurationTransferInterfacePlugin.php:54` | TP | The pre-call transfer and post-call unassign use only the method arguments, so a before plugin plus an after plugin would do the same without shared state. |  |  |
| 11 | `vendor/magento/module-persistent/Plugin/AddPersistentRememberMeInitPlugin.php:39` | TP | Calls proceed first and only adds a block afterwards, so afterGenerateElements would do the same. |  |  |
| 12 | `vendor/magento/module-catalog-rule-configurable/Plugin/CatalogRule/Model/Rule/ConfigurableProductHandler.php:56` | FP | The original products filter is read before it is widened and used again after proceed, so the after-logic needs pre-call state. |  |  |
| 13 | `vendor/magento/module-catalog/Plugin/RemoveImagesFromGalleryAfterRemovingProduct.php:49` | FP | The gallery rows are loaded before the product is deleted and removed after it, so the after-logic needs pre-call state. |  |  |
| 14 | `vendor/magento/module-re-captcha-webapi-rest/Plugin/RestValidationPlugin.php:81` | unclear | The request is cloned before validate and matched after it; whether proceed changes the request so that the clone is needed is not visible here. |  |  |
| 15 | `vendor/magento/module-catalog-url-rewrite/Model/Category/Plugin/Category/Remove.php:61` | FP | Children category ids are read before the category is deleted and their rewrites removed after it. |  |  |
| 16 | `vendor/magento/module-catalog/Model/Indexer/Product/Price/Plugin/CustomerGroup.php:81` | FP | isGroupNew is computed from the id before save and used after it, when the id is already assigned. |  |  |
| 17 | `vendor/magento/module-inventory-sales/Plugin/Sales/OrderManagement/DeductSourceItemQuantityOnRefundPlugin.php:98` | FP | isNewCreditMemo is computed from the entity id before save and used after it, when the id is already set. |  |  |
| 18 | `vendor/magento/module-inventory-indexer/Plugin/InventoryApi/ReindexAfterSourceItemsDeletePlugin.php:43` | FP | SKU list is read from the source items before they are deleted and used after the call, so the state must be captured before proceed. |  |  |
| 19 | `vendor/magento/module-theme/Model/Indexer/Design/Config/Plugin/Website.php:36` | FP | isObjectNew is computed from getId() before save and used after it, when the id is already set. |  |  |
| 20 | `vendor/magento/module-grouped-product/Model/ResourceModel/Product/Link/RelationPersister.php:70` | FP | The link row is loaded before deleteProductLink removes it and its type and product ids are used after. |  |  |
| 21 | `vendor/magento/module-inventory-cache/Plugin/InventoryIndexer/Indexer/Stock/Strategy/Sync/CacheFlush.php:51` | FP | Product ids are collected before and after the reindex and diffed, so the pre-call state is needed. |  |  |
| 22 | `vendor/magento/module-inventory-configuration/Plugin/CatalogInventory/Model/System/Config/Backend/Minqty/AllowNegativeMinQtyInConfigPlugin.php:21` | TP | Only modifies the result of beforeSave after the call, so afterBeforeSave would do the same. |  |  |
| 23 | `vendor/magento/module-customer/Model/Plugin/CustomerGridIndexAfterWebsiteDelete.php:49` | FP | Customer ids of the website are read before the website is deleted and reindexed after it. |  |  |
| 24 | `vendor/magento/module-customer/Model/Plugin/CustomerFlushFormKey.php:43` | FP | The current form key is captured before the flush regenerates it and compared afterwards. |  |  |
| 25 | `app/code/Fastly/Cdn/Plugin/ExcludeFilesFromMinification.php:38` | TP | Calls proceed first and only appends a URL using the subject and the contentType argument, which afterGetExcludes also receives. |  |  |
| 26 | `app/code/Magefan/Blog/Plugin/Magento/AdminGws/Model/ModelsPlugin.php:19` | TP | Only adjusts the model before an unconditional return of proceed (the captured storeId is unused), so a before plugin would do. |  |  |
| 27 | `vendor/magento/module-checkout/Model/Plugin/RecollectQuoteOnCustomerGroupChange.php:83` | FP | Previous customer data is loaded from the repository before save and compared with the new group after it. |  |  |
| 28 | `vendor/magento/module-login-as-customer-frontend-ui/Plugin/KeepLoginAsCustomerSessionDataPlugin.php:60` | FP | The admin id must be read before clearStorage wipes the session and restored after it. |  |  |
| 29 | `vendor/magento/module-inventory-sales/Plugin/Store/Model/ResourceModel/Website/UpdateSalesChannelWebsiteCodePlugin.php:56` | FP | The old website code is loaded before save and compared with the new code after it. |  |  |
| 30 | `vendor/magento/module-re-captcha-frontend-ui/Plugin/ExcludeFromMinification.php:27` | TP | Only appends to the result after proceed, depending on the contentType argument, which afterGetExcludes also receives. |  |  |
