# P-DI-02: calibration sample

30 of 1445 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-catalog-url-rewrite/Model/Category/ChildrenUrlRewriteGenerator.php:54` (core)

[P-DI-02] The constructor of Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    48          CategoryUrlRewriteGeneratorFactory $categoryUrlRewriteGeneratorFactory,
    49          ?MergeDataProviderFactory $mergeDataProviderFactory = null,
    50          ?CategoryRepositoryInterface $categoryRepository = null
    51      ) {
    52          $this->childrenCategoriesProvider = $childrenCategoriesProvider;
    53          $this->categoryUrlRewriteGeneratorFactory = $categoryUrlRewriteGeneratorFactory;
>   54          if (!isset($mergeDataProviderFactory)) {
    55              $mergeDataProviderFactory = ObjectManager::getInstance()->get(MergeDataProviderFactory::class);
    56          }
    57          $this->mergeDataProviderPrototype = $mergeDataProviderFactory->create();
    58          $this->categoryRepository = $categoryRepository
    59              ?: ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
    60      }
```

## 2. `vendor/magento/module-catalog-graph-ql/Model/Config/FilterAttributeReader.php:64` (core)

[P-DI-02] The constructor of Magento\CatalogGraphQl\Model\Config\FilterAttributeReader does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    58          MapperInterface $mapper,
    59          CollectionFactory $collectionFactory,
    60          array $exactMatchAttributes = []
    61      ) {
    62          $this->mapper = $mapper;
    63          $this->collectionFactory = $collectionFactory;
>   64          $this->exactMatchAttributes = array_merge($this->exactMatchAttributes, $exactMatchAttributes);
    65      }
    66  
    67      /**
    68       * Read configuration scope
    69       *
    70       * @param string|null $scope
```

## 3. `vendor/magento/zend-log/library/Zend/Log/Writer/Stream.php:57` (core)

[P-DI-02] The constructor of Zend_Log_Writer_Stream does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    51       * @return void
    52       * @throws Zend_Log_Exception
    53       */
    54      public function __construct($streamOrUrl, $mode = null)
    55      {
    56          // Setting the default
>   57          if (null === $mode) {
    58              $mode = 'a';
    59          }
    60  
    61          if (is_resource($streamOrUrl)) {
    62              if (get_resource_type($streamOrUrl) != 'stream') {
    63                  #require_once 'Zend/Log/Exception.php';
```

## 4. `vendor/magento/module-catalog-rule/Model/Rule/Condition/Combine.php:31` (core)

[P-DI-02] The constructor of Magento\CatalogRule\Model\Rule\Condition\Combine does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    25          \Magento\Rule\Model\Condition\Context $context,
    26          \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
    27          array $data = []
    28      ) {
    29          $this->_productFactory = $conditionFactory;
    30          parent::__construct($context, $data);
>   31          $this->setType(\Magento\CatalogRule\Model\Rule\Condition\Combine::class);
    32      }
    33  
    34      /**
    35       * @return array
    36       */
    37      public function getNewChildSelectOptions()
```

## 5. `vendor/magento/framework/Image/Adapter/AbstractAdapter.php:287` (core)

[P-DI-02] The constructor of Magento\Framework\Image\Adapter\AbstractAdapter does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   281          Filesystem $filesystem,
   282          LoggerInterface $logger,
   283          array $data = []
   284      ) {
   285          $this->_filesystem = $filesystem;
   286          $this->logger = $logger;
>  287          $this->directoryWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
   288      }
   289  
   290      /**
   291       * Assign image width, height, fileMimeType to object properties
   292       *
   293       * @return string|null
```

## 6. `vendor/magento/framework-message-queue/Publisher/Config/PublisherConfigItem/Iterator.php:39` (core)

[P-DI-02] The constructor of Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItem\Iterator does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    33       *
    34       * @param Data $configData
    35       * @param PublisherConfigItemFactory $itemFactory
    36       */
    37      public function __construct(Data $configData, PublisherConfigItemFactory $itemFactory)
    38      {
>   39          $this->data = $configData->get();
    40          $this->object = $itemFactory->create();
    41          $this->rewind();
    42      }
    43  
    44      /**
    45       * Get current item.
```

## 7. `vendor/magento/module-widget/Model/ResourceModel/Widget/Instance/Options/ThemeId.php:27` (core)

[P-DI-02] The constructor of Magento\Widget\Model\ResourceModel\Widget\Instance\Options\ThemeId does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    21  
    22      /**
    23       * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $widgetResourceModel
    24       */
    25      public function __construct(\Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $widgetResourceModel)
    26      {
>   27          $this->_resourceModel = $widgetResourceModel->create();
    28      }
    29  
    30      /**
    31       * @return array
    32       */
    33      public function toOptionArray()
```

## 8. `vendor/magento/framework/Mail/Template/TransportBuilder.php:165` (core)

[P-DI-02] The constructor of Magento\Framework\Mail\Template\TransportBuilder does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   159          ?AddressConverter $addressConverter = null
   160      ) {
   161          $this->templateFactory = $templateFactory;
   162          $this->objectManager = $objectManager;
   163          $this->_senderResolver = $senderResolver;
   164          $this->mailTransportFactory = $mailTransportFactory;
>  165          $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory ?: $this->objectManager
   166              ->get(EmailMessageInterfaceFactory::class);
   167          $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory ?: $this->objectManager
   168              ->get(MimeMessageInterfaceFactory::class);
   169          $this->mimePartInterfaceFactory = $mimePartInterfaceFactory ?: $this->objectManager
   170              ->get(MimePartInterfaceFactory::class);
   171          $this->addressConverter = $addressConverter ?: $this->objectManager
```

## 9. `vendor/magento/module-paypal/Model/Report/Settlement.php:203` (core)

[P-DI-02] The constructor of Magento\Paypal\Model\Report\Settlement does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   197          \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
   198          ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
   199          ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
   200          array $data = [],
   201          ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
   202      ) {
>  203          $this->_tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
   204          $this->_storeManager = $storeManager;
   205          $this->_scopeConfig = $scopeConfig;
   206          parent::__construct($context, $registry, $resource, $resourceCollection, $data);
   207          $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
   208              ->get(\Magento\Framework\Serialize\Serializer\Json::class);
   209      }
```

## 10. `vendor/magento/framework/Jwt/Jwe/Jwe.php:53` (core)

[P-DI-02] The constructor of Magento\Framework\Jwt\Jwe\Jwe does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    47          ?HeaderInterface $unprotectedHeader,
    48          ?array $recipientHeaders,
    49          PayloadInterface $payload
    50      ) {
    51          $this->protectedHeader = $protectedHeader;
    52          $this->unprotectedHeader = $unprotectedHeader;
>   53          $this->recipientHeaders = $recipientHeaders ? array_values($recipientHeaders) : null;
    54          $this->payload = $payload;
    55      }
    56  
    57      /**
    58       * @inheritDoc
    59       */
```

## 11. `vendor/magento/module-admin-adobe-ims/ViewModel/LinkViewModel.php:46` (core)

[P-DI-02] The constructor of Magento\AdminAdobeIms\ViewModel\LinkViewModel does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    40          LoggerInterface $logger,
    41          MessageManagerInterface $messageManager
    42      ) {
    43          $this->logger = $logger;
    44          $this->messageManager = $messageManager;
    45  
>   46          try {
    47              $this->authUrl = $connection->auth();
    48          } catch (InvalidArgumentException $e) {
    49              $this->logger->error($e->getMessage());
    50              $this->authUrl = null;
    51              $this->addImsErrorMessage(
    52                  'Could not connect to Adobe IMS.',
```

## 12. `vendor/magento/module-customer-import-export/Model/Import/Address.php:311` (core)

[P-DI-02] The constructor of Magento\CustomerImportExport\Model\Import\Address does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   305          $this->_resourceHelper = $resourceHelper;
   306          $this->dateTime = $dateTime;
   307          $this->postcodeValidator = $postcodeValidator;
   308          $this->countryWithWebsites = $countryWithWebsites ?:
   309              ObjectManager::getInstance()->get(CountryWithWebsitesSource::class);
   310  
>  311          if (!isset($data['attribute_collection'])) {
   312              /** @var $attributeCollection \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection */
   313              $attributeCollection = $attributesFactory->create();
   314              $attributeCollection->addSystemHiddenFilter()->addExcludeHiddenFrontendFilter();
   315              $data['attribute_collection'] = $attributeCollection;
   316          }
   317          parent::__construct(
```

## 13. `vendor/magento/framework/View/Page/Config.php:196` (core)

[P-DI-02] The constructor of Magento\Framework\View\Page\Config does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   190          $this->pageAssets = $pageAssets;
   191          $this->scopeConfig = $scopeConfig;
   192          $this->favicon = $favicon;
   193          $this->title = $title;
   194          $this->localeResolver = $localeResolver;
   195          $this->isIncludesAvailable = $isIncludesAvailable;
>  196          $locale = $this->localeResolver->getLocale();
   197          $this->setElementAttribute(
   198              self::ELEMENT_TYPE_HTML,
   199              self::HTML_ATTRIBUTE_LANG,
   200              $locale !== null ? strstr($locale, '_', true) : false
   201          );
   202          $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
```

## 14. `vendor/magento/framework/DB/AbstractMapper.php:103` (core)

[P-DI-02] The constructor of Magento\Framework\DB\AbstractMapper does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    97      ) {
    98          $this->logger = $logger;
    99          $this->fetchStrategy = $fetchStrategy;
   100          $this->objectFactory = $objectFactory;
   101          $this->mapperFactory = $mapperFactory;
   102          $this->select = $select;
>  103          $this->init();
   104      }
   105  
   106      /**
   107       * Set initial conditions
   108       *
   109       * @return void
```

## 15. `vendor/magento/module-eav/Model/Attribute/Data/File.php:71` (core)

[P-DI-02] The constructor of Magento\Eav\Model\Attribute\Data\File does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    65          \Magento\Framework\Filesystem $filesystem,
    66          FileIo $fileIo
    67      ) {
    68          parent::__construct($localeDate, $logger, $localeResolver);
    69          $this->urlEncoder = $urlEncoder;
    70          $this->_fileValidator = $fileValidator;
>   71          $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    72          $this->fileIo = $fileIo;
    73      }
    74  
    75      /**
    76       * Extract data from request and return value
    77       *
```

## 16. `vendor/magento/module-payment-services-paypal/Model/ConfigProvider.php:63` (core)

[P-DI-02] The constructor of Magento\PaymentServicesPaypal\Model\ConfigProvider does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    57      ) {
    58          $this->config = $config;
    59          $this->paymentOptionsBuilderFactory = $paymentOptionsBuilderFactory;
    60          $this->sdkService = $sdkService;
    61          $this->storeManager = $storeManager;
    62          //TODO:Just to be compatible with 2.4.6. Remove in future
>   63          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    64          try {
    65              $this->cspNonceProvider = $objectManager->get("\Magento\Csp\Helper\CspNonceProvider");
    66          } catch (\Throwable $e) {
    67              $this->cspNonceProvider = null;
    68          }
    69      }
```

## 17. `vendor/magento/module-customer/Block/Address/Edit.php:99` (core)

[P-DI-02] The constructor of Magento\Customer\Block\Address\Edit does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    93          $this->_customerSession = $customerSession;
    94          $this->_addressRepository = $addressRepository;
    95          $this->addressDataFactory = $addressDataFactory;
    96          $this->currentCustomer = $currentCustomer;
    97          $this->dataObjectHelper = $dataObjectHelper;
    98          $this->addressMetadata = $addressMetadata ?: ObjectManager::getInstance()->get(AddressMetadataInterface::class);
>   99          $data['addressHelper'] = $addressHelper ?: ObjectManager::getInstance()->get(Address::class);
   100          $data['directoryHelper'] = $directoryHelper;
   101          parent::__construct(
   102              $context,
   103              $directoryHelper,
   104              $jsonEncoder,
   105              $configCacheType,
```

## 18. `vendor/magento/module-tax-import-export/Model/Rate/CsvImportHandler.php:68` (core)

[P-DI-02] The constructor of Magento\TaxImportExport\Model\Rate\CsvImportHandler does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    62          \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
    63          \Magento\Directory\Model\CountryFactory $countryFactory,
    64          \Magento\Tax\Model\Calculation\RateFactory $taxRateFactory,
    65          \Magento\Framework\File\Csv $csvProcessor
    66      ) {
    67          // prevent admin store from loading
>   68          $this->_publicStores = $storeCollection->setLoadDefault(false);
    69          $this->_regionCollection = $regionCollection;
    70          $this->_countryFactory = $countryFactory;
    71          $this->_taxRateFactory = $taxRateFactory;
    72          $this->csvProcessor = $csvProcessor;
    73      }
    74  
```

## 19. `vendor/magento/module-config/Model/Config/Structure/Element/Dependency/Field.php:45` (core)

[P-DI-02] The constructor of Magento\Config\Model\Config\Structure\Element\Dependency\Field does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    39      /**
    40       * @param array $fieldData
    41       * @param string $fieldPrefix
    42       */
    43      public function __construct(array $fieldData = [], $fieldPrefix = "")
    44      {
>   45          if (isset($fieldData['separator'])) {
    46              $this->_values = isset($fieldData['value']) ? explode($fieldData['separator'], $fieldData['value']) : [''];
    47          } else {
    48              $this->_values = [isset($fieldData['value']) ? $fieldData['value'] : ''];
    49          }
    50          $fieldId = $fieldPrefix . (isset(
    51              $fieldData['dependPath']
```

## 20. `vendor/magento/framework/Image.php:33` (core)

[P-DI-02] The constructor of Magento\Framework\Image does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    27       * @param string|null $fileName
    28       */
    29      public function __construct(\Magento\Framework\Image\Adapter\AdapterInterface $adapter, $fileName = null)
    30      {
    31          $this->_adapter = $adapter;
    32          $this->_fileName = $fileName;
>   33          if (isset($fileName)) {
    34              $this->open();
    35          }
    36      }
    37  
    38      /**
    39       * Opens an image and creates image handle
```

## 21. `vendor/magento/module-checkout/Controller/Cart/UpdatePost.php:49` (core)

[P-DI-02] The constructor of Magento\Checkout\Controller\Cart\UpdatePost does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    43              $checkoutSession,
    44              $storeManager,
    45              $formKeyValidator,
    46              $cart
    47          );
    48  
>   49          $this->quantityProcessor = $quantityProcessor ?: $this->_objectManager->get(RequestQuantityProcessor::class);
    50      }
    51  
    52      /**
    53       * Empty customer's shopping cart
    54       *
    55       * @return void
```

## 22. `vendor/magento/magento2-functional-testing-framework/src/Magento/FunctionalTestingFramework/Util/ModulePathExtractor.php:29` (core)

[P-DI-02] The constructor of Magento\FunctionalTestingFramework\Util\ModulePathExtractor does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    23      /**
    24       * ModulePathExtractor constructor
    25       */
    26      public function __construct()
    27      {
    28          $verbosePath = true;
>   29          if (empty($this->testModulePaths)) {
    30              $this->testModulePaths = ModuleResolver::getInstance()->getModulesPath($verbosePath);
    31          }
    32      }
    33  
    34      /**
    35       * Extracts module name from the path given
```

## 23. `vendor/magento/module-url-rewrite/Model/Exception/UrlAlreadyExistsException.php:32` (core)

[P-DI-02] The constructor of Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    26       * @param int $code
    27       * @param array $urls
    28       */
    29      public function __construct(?Phrase $phrase = null, ?\Exception $cause = null, $code = 0, array $urls = [])
    30      {
    31          $this->urls = $urls;
>   32          if ($phrase === null) {
    33              $phrase = __('URL key for specified store already exists');
    34          }
    35          parent::__construct($phrase, $cause, $code);
    36      }
    37  
    38      /**
```

## 24. `vendor/magento/module-theme/Model/Uploader/Service.php:78` (core)

[P-DI-02] The constructor of Magento\Theme\Model\Uploader\Service does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    72          \Magento\Framework\Filesystem $filesystem,
    73          \Magento\Framework\File\Size $fileSize,
    74          \Magento\Framework\Convert\DataSize $dataSize,
    75          \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
    76          array $uploadLimits = []
    77      ) {
>   78          $this->_tmpDirectory = $filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
    79          $this->_fileSize = $fileSize;
    80          $this->_uploaderFactory = $uploaderFactory;
    81          $this->dataSize = $dataSize;
    82          if (isset($uploadLimits['css'])) {
    83              $this->_cssUploadLimit = $uploadLimits['css'];
    84          }
```

## 25. `vendor/magento/framework/Config/Reader/Filesystem.php:118` (core)

[P-DI-02] The constructor of Magento\Framework\Config\Reader\Filesystem does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   112          $defaultScope = 'global',
   113          ?string $typeAttributeName = null,
   114      ) {
   115          $this->_fileResolver = $fileResolver;
   116          $this->_converter = $converter;
   117          $this->_fileName = $fileName;
>  118          $this->_idAttributes = array_replace($this->_idAttributes, $idAttributes);
   119          $this->validationState = $validationState;
   120          $this->_schemaFile = $schemaLocator->getSchema();
   121          $this->_perFileSchema = $schemaLocator->getPerFileSchema() && $validationState->isValidationRequired()
   122              ? $schemaLocator->getPerFileSchema() : null;
   123          $this->_domDocumentClass = $domDocumentClass;
   124          $this->_defaultScope = $defaultScope;
```

## 26. `vendor/magento/module-downloadable-import-export/Model/Import/Product/Type/Downloadable.php:290` (core)

[P-DI-02] The constructor of Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   284              $prodAttrColFac,
   285              $resource,
   286              $params,
   287              $metadataPool,
   288              $attributeOptionCollectionFactory
   289          );
>  290          $this->parameters = $this->_entityModel->getParameters();
   291          $this->_resource = $resource;
   292          $this->uploaderHelper = $uploaderHelper;
   293          $this->domainValidator = $domainValidator;
   294          $this->downloadableHelper = $downloadableHelper;
   295      }
   296  
```

## 27. `vendor/magento/module-offline-payments/Model/InstructionsConfigProvider.php:41` (core)

[P-DI-02] The constructor of Magento\OfflinePayments\Model\InstructionsConfigProvider does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    35       */
    36      public function __construct(
    37          PaymentHelper $paymentHelper,
    38          Escaper $escaper
    39      ) {
    40          $this->escaper = $escaper;
>   41          foreach ($this->methodCodes as $code) {
    42              $this->methods[$code] = $paymentHelper->getMethodInstance($code);
    43          }
    44      }
    45  
    46      /**
    47       * {@inheritdoc}
```

## 28. `vendor/magento/module-wishlist/Model/Config.php:54` (core)

[P-DI-02] The constructor of Magento\Wishlist\Model\Config does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    48       */
    49      public function __construct(
    50          \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    51          \Magento\Catalog\Model\Config $catalogConfig,
    52          \Magento\Catalog\Model\Attribute\Config $attributeConfig
    53      ) {
>   54          $emailLimitInConfig = (int)$scopeConfig->getValue(
    55              self::XML_PATH_SHARING_EMAIL_LIMIT,
    56              \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    57          );
    58          $textLimitInConfig = (int)$scopeConfig->getValue(
    59              self::XML_PATH_SHARING_TEXT_LIMIT,
    60              \Magento\Store\Model\ScopeInterface::SCOPE_STORE
```

## 29. `vendor/magento/module-catalog/Ui/DataProvider/Product/Form/Modifier/System.php:57` (core)

[P-DI-02] The constructor of Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\System does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    51          LocatorInterface $locator,
    52          UrlInterface $urlBuilder,
    53          array $productUrls = []
    54      ) {
    55          $this->locator = $locator;
    56          $this->urlBuilder = $urlBuilder;
>   57          $this->productUrls = array_replace_recursive($this->productUrls, $productUrls);
    58      }
    59  
    60      /**
    61       * {@inheritdoc}
    62       * @since 101.0.0
    63       */
```

## 30. `vendor/magento/module-rule/Model/Action/Collection.php:39` (core)

[P-DI-02] The constructor of Magento\Rule\Model\Action\Collection does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    33      ) {
    34          $this->_actionFactory = $actionFactory;
    35          $this->_layout = $layout;
    36  
    37          parent::__construct($assetRepo, $layout, $data);
    38  
>   39          $this->setActions([]);
    40          $this->setType(\Magento\Rule\Model\Action\Collection::class);
    41      }
    42  
    43      /**
    44       * Returns array containing actions in the collection
    45       *
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-catalog-url-rewrite/Model/Category/ChildrenUrlRewriteGenerator.php:54` | TP-low | The flagged if-isset block is only a BC fallback, but the constructor also calls mergeDataProviderFactory->create(), which merely instantiates a small MergeDataProvider. | re-check |  |
| 2 | `vendor/magento/module-catalog-graph-ql/Model/Config/FilterAttributeReader.php:64` | TP-low | array_merge of an injected DI array into a default property is cheap argument merging. | re-check |  |
| 3 | `vendor/magento/zend-log/library/Zend/Log/Writer/Stream.php:57` | FP | Zend_Log_Writer_Stream is a ZF1 library writer built with new and explicit arguments, not by the Magento object manager, and the flagged statement only defaults the mode argument. | re-check |  |
| 4 | `vendor/magento/module-catalog-rule/Model/Rule/Condition/Combine.php:31` | TP-low | setType() only stores a class name string in the data array of a rule condition object. |  |  |
| 5 | `vendor/magento/framework/Image/Adapter/AbstractAdapter.php:287` | TP-low | Filesystem::getDirectoryWrite() returns a cached directory object, so the cost per adapter creation is negligible. |  |  |
| 6 | `vendor/magento/framework-message-queue/Publisher/Config/PublisherConfigItem/Iterator.php:39` | TP-low | Reading already loaded config data and rewinding happen in an iterator that is created only to be iterated right away. |  |  |
| 7 | `vendor/magento/module-widget/Model/ResourceModel/Widget/Instance/Options/ThemeId.php:27` | TP-low | A theme collection is instantiated but not loaded in the constructor of an option source that exists only to call toOptionHash(). |  |  |
| 8 | `vendor/magento/framework/Mail/Template/TransportBuilder.php:165` | FP | The flagged statements are ?: BC fallbacks through the injected objectManager, which is dependency assignment like the allowed ObjectManager::getInstance() fallback. |  |  |
| 9 | `vendor/magento/module-paypal/Model/Report/Settlement.php:203` | TP-low | getDirectoryWrite(SYS_TMP) returns a cached directory object, and the rest is parent construction and an allowed serializer fallback. |  |  |
| 10 | `vendor/magento/framework/Jwt/Jwe/Jwe.php:53` | FP | Jwe is a value object built with new by JweFactory and the token Issuer, and array_values() on a parameter only normalizes input. |  |  |
| 11 | `vendor/magento/module-admin-adobe-ims/ViewModel/LinkViewModel.php:46` | TP | The constructor calls ImsConnection::auth(), which performs a cURL GET to Adobe IMS every time the view model is created. |  |  |
| 12 | `vendor/magento/module-customer-import-export/Model/Import/Address.php:311` | TP-low | Creating and filtering the attribute collection is real work, but this import entity is built only during a customer address import that needs the collection. |  |  |
| 13 | `vendor/magento/framework/View/Page/Config.php:196` | TP-low | getLocale() and setting the html lang attribute run once on the shared Page\Config, which is created only for page rendering where it is always used. |  |  |
| 14 | `vendor/magento/framework/DB/AbstractMapper.php:103` | TP-low | init() is a template method and the only core subclass found, GenericMapper, implements it as empty. |  |  |
| 15 | `vendor/magento/module-eav/Model/Attribute/Data/File.php:71` | TP-low | getDirectoryWrite(MEDIA) returns a cached directory object, a negligible cost per data model. |  |  |
| 16 | `vendor/magento/module-payment-services-paypal/Model/ConfigProvider.php:63` | FP | The try block only fetches the optional CspNonceProvider dependency through the object manager for 2.4.6 compatibility, which is dependency assignment (P-DI-06 territory), not work. |  |  |
| 17 | `vendor/magento/module-customer/Block/Address/Edit.php:99` | FP | The flagged statement is the allowed ?: ObjectManager::getInstance()->get() BC fallback, only assigned to a $data array element instead of a property or variable. |  |  |
| 18 | `vendor/magento/module-tax-import-export/Model/Rate/CsvImportHandler.php:68` | TP-low | setLoadDefault(false) is a setter on an unloaded store collection, negligible cost. |  |  |
| 19 | `vendor/magento/module-config/Model/Config/Structure/Element/Dependency/Field.php:45` | TP-low | Parsing the passed fieldData with explode and string concatenation is cheap and the dependency field object exists only to hold this input in admin config. |  |  |
| 20 | `vendor/magento/framework/Image.php:33` | TP-low | open() decodes the image file in the constructor, but Image\Factory creates the object only when the caller is about to process that file. |  |  |
| 21 | `vendor/magento/module-checkout/Controller/Cart/UpdatePost.php:49` | FP | The flagged statement is a ?: BC fallback through the inherited _objectManager, which is dependency assignment. |  |  |
| 22 | `vendor/magento/magento2-functional-testing-framework/src/Magento/FunctionalTestingFramework/Util/ModulePathExtractor.php:29` | FP | ModulePathExtractor belongs to the MFTF test framework and is never built by the Magento object manager at runtime. |  |  |
| 23 | `vendor/magento/module-url-rewrite/Model/Exception/UrlAlreadyExistsException.php:32` | FP | This is an exception constructed with new, and the flagged statement only defaults the phrase argument before calling the parent. |  |  |
| 24 | `vendor/magento/module-theme/Model/Uploader/Service.php:78` | TP-low | getDirectoryRead(SYS_TMP) returns a cached directory object and the isset blocks only copy injected upload limits. |  |  |
| 25 | `vendor/magento/framework/Config/Reader/Filesystem.php:118` | TP-low | array_replace of injected id attributes and getSchema() calls on the schema locator are cheap argument handling. |  |  |
| 26 | `vendor/magento/module-downloadable-import-export/Model/Import/Product/Type/Downloadable.php:290` | TP-low | getParameters() is a plain getter on the entity model passed in, negligible cost. |  |  |
| 27 | `vendor/magento/module-offline-payments/Model/InstructionsConfigProvider.php:41` | TP-low | getMethodInstance() for the offline payment codes is real work, but the config provider is built only for checkout config, where getConfig() uses all of these instances. |  |  |
| 28 | `vendor/magento/module-wishlist/Model/Config.php:54` | TP-low | Two scopeConfig->getValue() reads are in-memory lookups after config is loaded, so the cost per Wishlist Config creation is small. |  |  |
| 29 | `vendor/magento/module-catalog/Ui/DataProvider/Product/Form/Modifier/System.php:57` | TP-low | array_replace_recursive of an injected DI array into defaults is cheap argument merging. |  |  |
| 30 | `vendor/magento/module-rule/Model/Action/Collection.php:39` | TP-low | setActions([]) and setType() only set keys in the data array of a rule action collection. |  |  |
