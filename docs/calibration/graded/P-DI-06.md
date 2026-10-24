# P-DI-06: calibration sample

30 of 394 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Magefan/Blog/Model/ResourceModel/Tag.php:68` (packagist)

[P-DI-06] ObjectManager::getInstance() is used directly in Magefan\Blog\Model\ResourceModel\Tag. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
    62                  throw new \Magento\Framework\Exception\LocalizedException(
    63                      __('The tag is already exist.')
    64                  );
    65              }
    66          }
    67  
>   68          $identifierGenerator = \Magento\Framework\App\ObjectManager::getInstance()
    69                  ->create(\Magefan\Blog\Model\ResourceModel\PageIdentifierGenerator::class);
    70          $identifierGenerator->generate($object);
    71  
    72          if (!$this->isValidPageIdentifier($object)) {
    73              throw new \Magento\Framework\Exception\LocalizedException(
    74                  __('The tag URL key contains disallowed symbols.')
```

## 2. `vendor/magento/module-store/App/FrontController/Plugin/RequestPreprocessor.php:111` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Store\App\FrontController\Plugin\RequestPreprocessor. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   105       * @return \Magento\Store\Model\BaseUrlChecker
   106       * @deprecated 100.1.0
   107       */
   108      private function getBaseUrlChecker()
   109      {
   110          if ($this->baseUrlChecker === null) {
>  111              $this->baseUrlChecker = \Magento\Framework\App\ObjectManager::getInstance()->get(
   112                  \Magento\Store\Model\BaseUrlChecker::class
   113              );
   114          }
   115  
   116          return $this->baseUrlChecker;
   117      }
```

## 3. `app/code/Magefan/Blog/Model/AbstractManagement.php:41` (packagist)

[P-DI-06] ObjectManager::getInstance() is used directly in Magefan\Blog\Model\AbstractManagement. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
    35       * @param string $fileName Desired file name (without path)
    36       * @param string $fileContent Content of the file
    37       * @return string Saved file name with short path
    38       */
    39      public function saveFile($fileName, $fileContent)
    40      {
>   41          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    42  
    43          $file = $objectManager->get(\Magento\Framework\Filesystem\Driver\File::class);
    44          $directoryList = $objectManager->get(\Magento\Framework\Filesystem\DirectoryList::class);
    45  
    46          $targetDirectory = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . '/' . $this->_imagePath;
    47          $file->createDirectory($targetDirectory);
```

## 4. `vendor/magento/framework/Filter/Template.php:146` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Framework\Filter\Template. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   140  
   141          if (empty($directiveProcessors)) {
   142              $this->directiveProcessors = [
   143                  'depend' => ObjectManager::getInstance()->get(DependDirective::class),
   144                  'if' => ObjectManager::getInstance()->get(IfDirective::class),
   145                  'template' => ObjectManager::getInstance()->get(TemplateDirective::class),
>  146                  'legacy' => ObjectManager::getInstance()->get(LegacyDirective::class),
   147              ];
   148          }
   149      }
   150  
   151      /**
   152       * Set the template variables available to be resolved in this template via variable resolver directives
```

## 5. `vendor/magento/module-catalog-url-rewrite/Model/ProductScopeRewriteGenerator.php:138` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   132          $this->objectRegistryFactory = $objectRegistryFactory;
   133          $this->canonicalUrlRewriteGenerator = $canonicalUrlRewriteGenerator;
   134          $this->categoriesUrlRewriteGenerator = $categoriesUrlRewriteGenerator;
   135          $this->currentUrlRewritesRegenerator = $currentUrlRewritesRegenerator;
   136          $this->anchorUrlRewriteGenerator = $anchorUrlRewriteGenerator;
   137          if (!isset($mergeDataProviderFactory)) {
>  138              $mergeDataProviderFactory = ObjectManager::getInstance()->get(MergeDataProviderFactory::class);
   139          }
   140          $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
   141          $this->categoryRepository = $categoryRepository ?:
   142              ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
   143          $this->config = $config ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
   144          $this->productRepository = $productRepository ?:
```

## 6. `vendor/magento/framework-message-queue/Code/Generator/RemoteServiceGenerator.php:252` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Framework\MessageQueue\Code\Generator\RemoteServiceGenerator. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   246       * @deprecated 103.0.0
   247       * @see https://jira.corp.adobe.com/browse/MAGETWO-56305
   248       */
   249      private function getReflectionGenerator()
   250      {
   251          if ($this->reflectionGenerator === null) {
>  252              $this->reflectionGenerator = \Magento\Framework\App\ObjectManager::getInstance()
   253                  ->get(ReflectionGenerator::class);
   254          }
   255          return $this->reflectionGenerator;
   256      }
   257  }
```

## 7. `vendor/magento/module-customer/Controller/Account/LoginPost.php:108` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Customer\Controller\Account\LoginPost. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   102       * @return ScopeConfigInterface
   103       * @deprecated 100.0.10
   104       */
   105      private function getScopeConfig()
   106      {
   107          if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
>  108              return \Magento\Framework\App\ObjectManager::getInstance()->get(
   109                  \Magento\Framework\App\Config\ScopeConfigInterface::class
   110              );
   111          } else {
   112              return $this->scopeConfig;
   113          }
   114      }
```

## 8. `vendor/magento/module-downloadable/Model/Product/TypeHandler/AbstractTypeHandler.php:205` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Downloadable\Model\Product\TypeHandler\AbstractTypeHandler. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   199       * Get MetadataPool instance
   200       * @return MetadataPool
   201       */
   202      protected function getMetadataPool()
   203      {
   204          if (!$this->metadataPool) {
>  205              $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
   206          }
   207          return $this->metadataPool;
   208      }
   209  }
```

## 9. `vendor/magento/module-customer/Controller/Account/Confirm.php:144` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Customer\Controller\Account\Confirm. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   138       *
   139       * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
   140       */
   141      private function getCookieMetadataFactory()
   142      {
   143          if (!$this->cookieMetadataFactory) {
>  144              $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
   145                  \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
   146              );
   147          }
   148          return $this->cookieMetadataFactory;
   149      }
   150  
```

## 10. `vendor/magento/module-vault/Model/Ui/Adminhtml/TokensConfigProvider.php:291` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   285       * @deprecated 100.1.0
   286       * @see MAGETWO-71174
   287       */
   288      private function getPaymentDataHelper()
   289      {
   290          if ($this->paymentDataHelper === null) {
>  291              $this->paymentDataHelper = ObjectManager::getInstance()->get(Data::class);
   292          }
   293          return $this->paymentDataHelper;
   294      }
   295  
   296      /**
   297       * Returns order repository instance
```

## 11. `vendor/magento/module-async-config/Model/Consumer.php:65` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\AsyncConfig\Model\Consumer. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
    59      ) {
    60          $this->configFactory = $configFactory;
    61          $this->serializer = $json;
    62          $this->scope = $scope;
    63          $this->output = $output;
    64          $this->scope->setCurrentScope('adminhtml');
>   65          $this->save = ObjectManager::getInstance()->get(Save::class);
    66          $this->scope->setCurrentScope('global');
    67      }
    68      /**
    69       * Process Consumer
    70       *
    71       * @param AsyncConfigMessageInterface $asyncConfigMessage
```

## 12. `app/code/Mageplaza/Smtp/Model/Log.php:324` (packagist)

[P-DI-06] ObjectManager::getInstance() is used directly in Mageplaza\Smtp\Model\Log. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   318       *
   319       * @return LogAttachmentFactory
   320       */
   321      protected function getAttachmentFactory()
   322      {
   323          if ($this->attachmentFactory === null) {
>  324              $this->attachmentFactory = ObjectManager::getInstance()->get(LogAttachmentFactory::class);
   325          }
   326  
   327          return $this->attachmentFactory;
   328      }
   329  
   330      /**
```

## 13. `vendor/magento/module-catalog-rule/Model/Rule.php:926` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\CatalogRule\Model\Rule. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   920       * @deprecated 100.1.0
   921       * @see getRuleCondition, setRuleCondition
   922       */
   923      private function getRuleConditionConverter()
   924      {
   925          if (null === $this->ruleConditionConverter) {
>  926              $this->ruleConditionConverter = ObjectManager::getInstance()
   927                  ->get(Converter::class);
   928          }
   929          return $this->ruleConditionConverter;
   930      }
   931  
   932      /**
```

## 14. `vendor/magento/module-catalog/Model/CategoryManagement.php:90` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Catalog\Model\CategoryManagement. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
    84       *
    85       * @return \Magento\Framework\App\ScopeResolverInterface
    86       */
    87      private function getScopeResolver()
    88      {
    89          if ($this->scopeResolver == null) {
>   90              $this->scopeResolver = \Magento\Framework\App\ObjectManager::getInstance()
    91                  ->get(\Magento\Framework\App\ScopeResolverInterface::class);
    92          }
    93  
    94          return $this->scopeResolver;
    95      }
    96  
```

## 15. `vendor/magento/framework-message-queue/Publisher.php:134` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Framework\MessageQueue\Publisher. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   128       *
   129       * @deprecated 100.2.0 103.0.0
   130       */
   131      private function getAmqpConfig()
   132      {
   133          if ($this->amqpConfig === null) {
>  134              $this->amqpConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(AmqpConfig::class);
   135          }
   136  
   137          return $this->amqpConfig;
   138      }
   139  }
```

## 16. `vendor/magento/module-cms/Model/BlockRepository.php:222` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Cms\Model\BlockRepository. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   216       * @return CollectionProcessorInterface
   217       */
   218      private function getCollectionProcessor()
   219      {
   220          //phpcs:disable Magento2.PHP.LiteralNamespaces
   221          if (!$this->collectionProcessor) {
>  222              $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
   223                  'Magento\Cms\Model\Api\SearchCriteria\BlockCollectionProcessor'
   224              );
   225          }
   226          return $this->collectionProcessor;
   227      }
   228  }
```

## 17. `vendor/magento/module-downloadable/Block/Sales/Order/Email/Items/Order/Downloadable.php:121` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Downloadable\Block\Sales\Order\Email\Items\Order\Downloadable. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   115       * @return \Magento\Framework\UrlInterface
   116       * @deprecated 100.1.0
   117       */
   118      private function getFrontendUrlBuilder()
   119      {
   120          if (!$this->frontendUrlBuilder) {
>  121              $this->frontendUrlBuilder = ObjectManager::getInstance()->get(\Magento\Framework\Url::class);
   122          }
   123          return $this->frontendUrlBuilder;
   124      }
   125  }
```

## 18. `vendor/magento/module-customer/Controller/Ajax/Login.php:117` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Customer\Controller\Ajax\Login. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   111       * @deprecated 100.0.10
   112       * @return AccountRedirect
   113       */
   114      protected function getAccountRedirect()
   115      {
   116          if (!is_object($this->accountRedirect)) {
>  117              $this->accountRedirect = ObjectManager::getInstance()->get(AccountRedirect::class);
   118          }
   119          return $this->accountRedirect;
   120      }
   121  
   122      /**
   123       * Account redirect setter for unit tests.
```

## 19. `vendor/magento/module-catalog/Model/ProductOptionProcessor.php:141` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Catalog\Model\ProductOptionProcessor. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   135       *
   136       * @deprecated 101.0.0
   137       */
   138      private function getUrlBuilder()
   139      {
   140          if ($this->urlBuilder === null) {
>  141              $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
   142                  ->get(\Magento\Catalog\Model\Product\Option\UrlBuilder::class);
   143          }
   144          return $this->urlBuilder;
   145      }
   146  
   147      /**
```

## 20. `vendor/magento/module-catalog/Ui/DataProvider/Product/Form/Modifier/Categories.php:139` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   133       * @deprecated 101.0.3
   134       * @see getCategoriesTree
   135       */
   136      private function getCacheManager(): CacheInterface
   137      {
   138          if (!$this->cacheManager) {
>  139              $this->cacheManager = ObjectManager::getInstance()
   140                  ->get(CacheInterface::class);
   141          }
   142          return $this->cacheManager;
   143      }
   144  
   145      /**
```

## 21. `vendor/magento/framework/Url.php:1188` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Framework\Url. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
  1182       * @deprecated 101.0.0
  1183       * @see \Magento\Framework\Escaper
  1184       */
  1185      private function getEscaper()
  1186      {
  1187          if ($this->escaper == null) {
> 1188              $this->escaper = \Magento\Framework\App\ObjectManager::getInstance()
  1189                  ->get(\Magento\Framework\Escaper::class);
  1190          }
  1191          return $this->escaper;
  1192      }
  1193  
  1194      /**
```

## 22. `vendor/magento/module-customer/Controller/Adminhtml/Index/Save.php:157` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Customer\Controller\Adminhtml\Index\Save. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   151          JsonFactory $resultJsonFactory,
   152          SubscriptionManagerInterface $subscriptionManager,
   153          ?AddressRegistry $addressRegistry = null,
   154          ?StoreManagerInterface $storeManager = null,
   155          ?SetCustomerStore $customerStore = null
   156      ) {
>  157          $this->validatorExceptionProcessor = ObjectManager::getInstance()->get(ValidatorExceptionProcessor::class);
   158          if ($this->validatorExceptionProcessor !== null) {
   159              $this->validatorExceptionProcessor->setMessageManager($context->getMessageManager());
   160          }
   161          parent::__construct(
   162              $context,
   163              $coreRegistry,
```

## 23. `vendor/magento/module-newsletter/Model/Queue.php:171` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Newsletter\Model\Queue. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   165          $this->_date = $date;
   166          $this->_templateFactory = $templateFactory;
   167          $this->_problemFactory = $problemFactory;
   168          $this->_subscribersCollection = $subscriberCollectionFactory->create();
   169          $this->_transportBuilder = $transportBuilder;
   170  
>  171          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
   172          $this->timezone = $timezone ?: $objectManager->get(TimezoneInterface::class);
   173          $this->utcConverter = $utcConverter ?? $objectManager->get(LocalizedDateToUtcConverterInterface::class);
   174      }
   175  
   176      /**
   177       * Initialize resource model
```

## 24. `vendor/magento/module-sales/Model/Order/Payment/Transaction/Repository.php:242` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Sales\Model\Order\Payment\Transaction\Repository. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   236       * @deprecated 101.0.0
   237       * @return CollectionProcessorInterface
   238       */
   239      private function getCollectionProcessor()
   240      {
   241          if (!$this->collectionProcessor) {
>  242              $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
   243                  \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
   244              );
   245          }
   246          return $this->collectionProcessor;
   247      }
   248  }
```

## 25. `vendor/magento/module-catalog-inventory/Model/StockIndex.php:284` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\CatalogInventory\Model\StockIndex. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   278       *
   279       * @return StockStatusResourceModel
   280       */
   281      protected function getStockStatusResource()
   282      {
   283          if (empty($this->stockStatusResource)) {
>  284              $this->stockStatusResource = ObjectManager::getInstance()->get(StockStatusResourceModel::class);
   285          }
   286          return $this->stockStatusResource;
   287      }
   288  }
```

## 26. `vendor/magento/module-sales/Model/Order/AddressRepository.php:249` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Sales\Model\Order\AddressRepository. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   243       * @deprecated 101.0.0
   244       * @return CollectionProcessorInterface
   245       */
   246      private function getCollectionProcessor()
   247      {
   248          if (!$this->collectionProcessor) {
>  249              $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
   250                  \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
   251              );
   252          }
   253          return $this->collectionProcessor;
   254      }
   255  }
```

## 27. `vendor/magento/module-customer/Controller/Adminhtml/Index/InlineEdit.php:134` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Customer\Controller\Adminhtml\Index\InlineEdit. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   128       * @deprecated 100.1.0
   129       * @see no alternative
   130       */
   131      private function getEmailNotification()
   132      {
   133          if (!($this->emailNotification instanceof EmailNotificationInterface)) {
>  134              return \Magento\Framework\App\ObjectManager::getInstance()->get(
   135                  EmailNotificationInterface::class
   136              );
   137          } else {
   138              return $this->emailNotification;
   139          }
   140      }
```

## 28. `vendor/magento/module-catalog/Model/Product/Option/Repository.php:254` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Catalog\Model\Product\Option\Repository. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   248       * @deprecated 101.0.0
   249       * @see MAGETWO-71174
   250       */
   251      private function getHydratorPool()
   252      {
   253          if (null === $this->hydratorPool) {
>  254              $this->hydratorPool = \Magento\Framework\App\ObjectManager::getInstance()
   255                  ->get(\Magento\Framework\EntityManager\HydratorPool::class);
   256          }
   257          return $this->hydratorPool;
   258      }
   259  
   260      /**
```

## 29. `vendor/magento/framework-message-queue/BatchConsumer.php:295` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Framework\MessageQueue\BatchConsumer. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   289       * @deprecated 103.0.0 Use constructor injection instead.
   290       * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface
   291       */
   292      private function getConsumerConfig()
   293      {
   294          if ($this->consumerConfig === null) {
>  295              $this->consumerConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(ConsumerConfig::class);
   296          }
   297          return $this->consumerConfig;
   298      }
   299  
   300      /**
   301       * Get message controller.
```

## 30. `vendor/magento/module-sales/Model/Order/Payment/Repository.php:140` (core)

[P-DI-06] ObjectManager::getInstance() is used directly in Magento\Sales\Model\Order\Payment\Repository. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).

```
   134       * @deprecated 101.0.0
   135       * @return CollectionProcessorInterface
   136       */
   137      private function getCollectionProcessor()
   138      {
   139          if (!$this->collectionProcessor) {
>  140              $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
   141                  \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
   142              );
   143          }
   144          return $this->collectionProcessor;
   145      }
   146  }
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Magefan/Blog/Model/ResourceModel/Tag.php:68` | TP | _beforeSave() creates PageIdentifierGenerator through ObjectManager::getInstance()->create() instead of injecting a factory, a hidden dependency in regular module code. | re-check |  |
| 2 | `vendor/magento/module-store/App/FrontController/Plugin/RequestPreprocessor.php:111` | TP-low | getBaseUrlChecker() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. | re-check |  |
| 3 | `app/code/Magefan/Blog/Model/AbstractManagement.php:41` | TP | saveFile() pulls the File driver and DirectoryList from ObjectManager::getInstance() instead of constructor injection, a hidden dependency in regular module code. | re-check |  |
| 4 | `vendor/magento/framework/Filter/Template.php:146` | FP | The calls fill the default directive processors inside if (empty($directiveProcessors)) in the constructor, which is the documented BC fallback for an optional argument written as an if block. |  |  |
| 5 | `vendor/magento/module-catalog-url-rewrite/Model/ProductScopeRewriteGenerator.php:138` | FP | This is the constructor BC fallback for the optional mergeDataProviderFactory argument written as if (!isset(...)) instead of ?: or ??. |  |  |
| 6 | `vendor/magento/framework-message-queue/Code/Generator/RemoteServiceGenerator.php:252` | TP-low | getReflectionGenerator() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation, in a code generator that runs only at compile time. |  |  |
| 7 | `vendor/magento/module-customer/Controller/Account/LoginPost.php:108` | TP-low | getScopeConfig() is a BC getter that falls back to the object manager only when the property was not injected, a design issue without performance cost. |  |  |
| 8 | `vendor/magento/module-downloadable/Model/Product/TypeHandler/AbstractTypeHandler.php:205` | TP-low | getMetadataPool() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 9 | `vendor/magento/module-customer/Controller/Account/Confirm.php:144` | TP-low | getCookieMetadataFactory() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 10 | `vendor/magento/module-vault/Model/Ui/Adminhtml/TokensConfigProvider.php:291` | TP-low | getPaymentDataHelper() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 11 | `vendor/magento/module-async-config/Model/Consumer.php:65` | TP-low | The consumer constructor deliberately switches to the adminhtml scope to build the Save controller through the object manager, a hidden dependency in a class built once per consumer process. |  |  |
| 12 | `app/code/Mageplaza/Smtp/Model/Log.php:324` | TP-low | getAttachmentFactory() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 13 | `vendor/magento/module-catalog-rule/Model/Rule.php:926` | TP-low | getRuleConditionConverter() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 14 | `vendor/magento/module-catalog/Model/CategoryManagement.php:90` | TP-low | getScopeResolver() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 15 | `vendor/magento/framework-message-queue/Publisher.php:134` | TP-low | getAmqpConfig() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 16 | `vendor/magento/module-cms/Model/BlockRepository.php:222` | TP-low | getCollectionProcessor() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 17 | `vendor/magento/module-downloadable/Block/Sales/Order/Email/Items/Order/Downloadable.php:121` | TP-low | getFrontendUrlBuilder() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 18 | `vendor/magento/module-customer/Controller/Ajax/Login.php:117` | TP-low | getAccountRedirect() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 19 | `vendor/magento/module-catalog/Model/ProductOptionProcessor.php:141` | TP-low | getUrlBuilder() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 20 | `vendor/magento/module-catalog/Ui/DataProvider/Product/Form/Modifier/Categories.php:139` | TP-low | getCacheManager() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 21 | `vendor/magento/framework/Url.php:1188` | TP-low | getEscaper() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 22 | `vendor/magento/module-customer/Controller/Adminhtml/Index/Save.php:157` | TP | The constructor fetches ValidatorExceptionProcessor unconditionally from ObjectManager::getInstance() with no matching constructor argument, so it is neither the BC fallback nor replaceable through DI. |  |  |
| 23 | `vendor/magento/module-newsletter/Model/Queue.php:171` | FP | ObjectManager::getInstance() is stored in a local variable and used only in the ?: and ?? constructor fallbacks for the optional timezone and utcConverter arguments, the allowed BC pattern. |  |  |
| 24 | `vendor/magento/module-sales/Model/Order/Payment/Transaction/Repository.php:242` | TP-low | getCollectionProcessor() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 25 | `vendor/magento/module-catalog-inventory/Model/StockIndex.php:284` | TP-low | getStockStatusResource() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 26 | `vendor/magento/module-sales/Model/Order/AddressRepository.php:249` | TP-low | getCollectionProcessor() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 27 | `vendor/magento/module-customer/Controller/Adminhtml/Index/InlineEdit.php:134` | TP-low | getEmailNotification() is a BC getter that falls back to the object manager only when the property was not injected, a design issue without performance cost. |  |  |
| 28 | `vendor/magento/module-catalog/Model/Product/Option/Repository.php:254` | TP-low | getHydratorPool() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 29 | `vendor/magento/framework-message-queue/BatchConsumer.php:295` | TP-low | getConsumerConfig() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
| 30 | `vendor/magento/module-sales/Model/Order/Payment/Repository.php:140` | TP-low | getCollectionProcessor() is a lazy BC getter that creates the dependency only on first use, so it hides it from DI but adds no early object creation. |  |  |
