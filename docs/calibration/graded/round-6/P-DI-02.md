# P-DI-02: calibration sample

30 of 352 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Dotdigitalgroup/Email/ViewModel/ProductNotificationView.php:100` (extended)

[P-DI-02] The constructor of Dotdigitalgroup\Email\ViewModel\ProductNotificationView does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    94          EndpointResolver $endpointResolver,
    95          Logger $logger,
    96          ConfigInterface $productTypesConfig,
    97          Visibility $productVisibilities,
    98          VariantDataEncoder $variantDataEncoder
    99      ) {
>  100          $this->_scopeConfig = $context->getScopeConfig();
   101          $this->_storeManager = $context->getStoreManager();
   102          $this->currentProduct = $currentProduct;
   103          $this->endpointResolver = $endpointResolver;
   104          $this->logger = $logger;
   105          $this->productTypesConfig = $productTypesConfig;
   106          $this->productVisibilities = $productVisibilities;
```

## 2. `app/code/FireGento/FastSimpleImport/Model/Import/Category.php:271` (extended)

[P-DI-02] The constructor of FireGento\FastSimpleImport\Model\Import\Category does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   265          $this->transactionManager = $transactionManager;
   266          $this->resourceFactory = $resourceFactory;
   267          $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
   268          $this->urlPersist = $urlPersist;
   269          $this->categoryRepository = $categoryRepository;
   270  
>  271          $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
   272  
   273          foreach ($this->messageTemplates as $errorCode => $message) {
   274              $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
   275          }
   276  
   277          $this->config                       = $config;
```

## 3. `app/code/Bazaarvoice/Connector/Model/BVSEOSDK/Base.php:35` (extended)

[P-DI-02] The constructor of Bazaarvoice\Connector\Model\BVSEOSDK\Base does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    29       */
    30      private $config;
    31  
    32      public function __construct($params = array())
    33      {
    34  
>   35          $this->validateParams($params);
    36  
    37          $this->config = $params;
    38  
    39          // setup bv (internal) defaults
    40          $this->bv_config['seo-domain']['staging'] = 'seo-stg.bazaarvoice.com';
    41          $this->bv_config['seo-domain']['production'] = 'seo.bazaarvoice.com';
```

## 4. `app/code/Clerk/Clerk/Model/Adapter/AbstractAdapter.php:86` (extended)

[P-DI-02] The constructor of Clerk\Clerk\Model\Adapter\AbstractAdapter does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    80    ) {
    81      $this->clerk_logger = $clerk_logger;
    82      $this->scopeConfig = $scopeConfig;
    83      $this->eventManager = $eventManager;
    84      $this->storeManager = $storeManager;
    85      $this->collectionFactory = $collectionFactory;
>   86      $this->addFieldHandlers();
    87    }
    88  
    89    /**
    90     * Add default fieldhandlers
    91     */
    92    abstract protected function addFieldHandlers();
```

## 5. `app/code/Buckaroo/Magento2/Plugin/QuoteCheck.php:66` (extended)

[P-DI-02] The constructor of Buckaroo\Magento2\Plugin\QuoteCheck does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    60       */
    61      public function __construct(
    62          Session $checkoutSession,
    63          PaymentGroupTransaction $groupTransaction,
    64          ManagerInterface $messageManager
    65      ) {
>   66          $this->quote = $checkoutSession->getQuote();
    67          $this->messageManager = $messageManager;
    68          $this->groupTransaction = $groupTransaction;
    69      }
    70  
    71      /**
    72       * Throw error if user already started a group transaction
```

## 6. `app/code/KiwiCommerce/CronScheduler/Ui/DataProvider/ScheduleProvider.php:46` (extended)

[P-DI-02] The constructor of KiwiCommerce\CronScheduler\Ui\DataProvider\ScheduleProvider does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    40          $requestFieldName,
    41          \KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory $collectionFactory,
    42          array $meta = [],
    43          array $data = []
    44      ) {
    45          parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
>   46          $this->collection = $collectionFactory->create();
    47      }
    48  }
```

## 7. `app/code/PostcodeEu/AddressValidation/Api/Data/MagentoDebugInfo.php:25` (extended)

[P-DI-02] The constructor of PostcodeEu\AddressValidation\Api\Data\MagentoDebugInfo does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    19  
    20      /**
    21       * @param array $data
    22       */
    23      public function __construct(array $data)
    24      {
>   25          $this->moduleVersion = $data['moduleVersion'] ?? '';
    26          $this->magentoVersion = $data['magentoVersion'] ?? '';
    27          $this->client = $data['client'] ?? '';
    28          $this->session = $data['session'] ?? '';
    29          $this->configuration = new MagentoDebugInfo\Configuration($data['configuration'] ?? []);
    30          $this->modules = $data['modules'] ?? [];
    31      }
```

## 8. `app/code/Mageplaza/Blog/Model/ResourceModel/Post.php:142` (extended)

[P-DI-02] The constructor of Mageplaza\Blog\Model\ResourceModel\Post does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   136          $this->helperData     = $helperData;
   137          $this->_request       = $request;
   138          $this->_authorFactory = $authorFactory;
   139  
   140          parent::__construct($context);
   141  
>  142          $this->postTagTable      = $this->getTable('mageplaza_blog_post_tag');
   143          $this->postTopicTable    = $this->getTable('mageplaza_blog_post_topic');
   144          $this->postCategoryTable = $this->getTable('mageplaza_blog_post_category');
   145          $this->postProductTable  = $this->getTable('mageplaza_blog_post_product');
   146          $this->postTrafficTable  = $this->getTable('mageplaza_blog_post_traffic');
   147          $this->postAuthorTable   = $this->getTable('mageplaza_blog_author');
   148      }
```

## 9. `app/code/Dotdigitalgroup/Email/Controller/Adminhtml/Datafield/Save.php:47` (extended)

[P-DI-02] The constructor of Dotdigitalgroup\Email\Controller\Adminhtml\Datafield\Save does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    41       */
    42      public function __construct(
    43          DataField $datafieldHandler,
    44          Context $context
    45      ) {
    46          $this->datafieldHandler = $datafieldHandler;
>   47          $this->messageManager = $context->getMessageManager();
    48          $this->request = $context->getRequest();
    49  
    50          parent::__construct($context);
    51      }
    52  
    53      /**
```

## 10. `app/code/CtiDigital/Configurator/Component/CustomerAttributes.php:83` (extended)

[P-DI-02] The constructor of CtiDigital\Configurator\Component\CustomerAttributes does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    77          Attribute $attributeResource,
    78          LoggerInterface $log,
    79          \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
    80          \Magento\Eav\Model\Config $eavConfig
    81      ) {
    82          parent::__construct($eavSetup, $attributeRepository, $log, $attrOptionCollectionFactory, $eavConfig);
>   83          $this->attributeConfigMap = array_merge($this->attributeConfigMap, $this->customerConfigMap);
    84          $this->customerSetup = $customerSetupFactory;
    85          $this->attributeResource = $attributeResource;
    86          $this->log = $log;
    87      }
    88  
    89      /**
```

## 11. `app/code/Dotdigitalgroup/Email/Model/Events/SetupIntegration/ProductsHandler.php:39` (extended)

[P-DI-02] The constructor of Dotdigitalgroup\Email\Model\Events\SetupIntegration\ProductsHandler does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    33       */
    34      public function __construct(
    35          Logger $logger,
    36          Context $context,
    37          IntegrationSetup $integrationSetup
    38      ) {
>   39          $this->request = $context->getRequest();
    40          $this->logger = $logger;
    41          $this->integrationSetup = $integrationSetup;
    42      }
    43  
    44      /**
    45       * Event Process
```

## 12. `app/code/ShipperHQ/Shipper/Helper/Rest.php:48` (extended)

[P-DI-02] The constructor of ShipperHQ\Shipper\Helper\Rest does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    42      public function __construct(
    43          \ShipperHQ\Lib\Helper\Rest $restHelper,
    44          ScopeConfigInterface $config
    45      ) {
    46          $this->restHelper = $restHelper;
    47          $this->config = $config;
>   48          $this->restHelper->setBaseUrl($this->getGatewayUrl());
    49          $this->restHelper->setBasePostorderUrl($this->getPostOrderGatewayUrl());
    50      }
    51  
    52      /**
    53       * Returns url to use - live if present, otherwise dev
    54       * @return string
```

## 13. `app/code/ShipperHQ/Shipper/Model/Order/Detail.php:80` (extended)

[P-DI-02] The constructor of ShipperHQ\Shipper\Model\Order\Detail does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    74              $extensionFactory,
    75              $customAttributeFactory,
    76              $resource,
    77              $resourceCollection,
    78              $data
    79          );
>   80          $this->orderDetailCollection = $orderDetailCollectionFactory->create();
    81      }
    82  
    83      /**
    84       * Return model from carrier group id and address id
    85       *
    86       * @param string $carrierGroupId
```

## 14. `app/code/Dotdigitalgroup/Email/Controller/Adminhtml/Rules/Selected.php:115` (extended)

[P-DI-02] The constructor of Dotdigitalgroup\Email\Controller\Adminhtml\Rules\Selected does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   109          $this->ruleType = $ruleType;
   110          $this->ruleCondition = $ruleCondition;
   111          $this->ruleValue = $ruleValue;
   112          $this->escaper = $escaper;
   113          $this->rulesResource = $rulesResource;
   114          $this->ruleValidator = $ruleValidator;
>  115          $this->request = $context->getRequest();
   116          $this->resultJsonFactory = $resultJsonFactory;
   117  
   118          parent::__construct($context);
   119      }
   120  
   121      /**
```

## 15. `app/code/MagestyApps/WebImages/Plugin/App/MediaPlugin.php:67` (extended)

[P-DI-02] The constructor of MagestyApps\WebImages\Plugin\App\MediaPlugin does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    61      ) {
    62          $this->imageHelper = $imageHelper;
    63          $this->imageConfig = $imageConfig;
    64          $this->request = $request;
    65          $this->logger = $logger;
    66  
>   67          $this->directoryPub = $filesystem->getDirectoryWrite(
    68              DirectoryList::PUB,
    69              Filesystem\DriverPool::FILE
    70          );
    71          $this->directoryMedia = $filesystem->getDirectoryWrite(
    72              DirectoryList::MEDIA,
    73              Filesystem\DriverPool::FILE
```

## 16. `app/code/ScandiPWA/PersistedQuery/Plugin/InvalidatePQCache.php:39` (extended)

[P-DI-02] The constructor of ScandiPWA\PersistedQuery\Plugin\InvalidatePQCache does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    33       */
    34      public function __construct(
    35          TypeListInterface $typeList,
    36          StateInterface $cacheState
    37      )
    38      {
>   39          $this->cacheState = $cacheState->isEnabled(strtolower(ResponseCache::CACHE_TAG));
    40          $this->typeList = $typeList;
    41      }
    42  
    43      /**
    44       * @param AbstractAction $subject
    45       * @param AbstractAction $result
```

## 17. `app/code/Bazaarvoice/Connector/Logger/Handler.php:53` (extended)

[P-DI-02] The constructor of Bazaarvoice\Connector\Logger\Handler does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    47       */
    48      public function __construct(
    49          DriverInterface $filesystem
    50      ) {
    51          $this->filesystem = $filesystem;
    52          parent::__construct($filesystem);
>   53          $this->setFormatter(new LineFormatter($this->_format, null, true));
    54      }
    55  }
```

## 18. `app/code/Dotdigitalgroup/Email/Model/Events/Response/StreamedResponse.php:42` (extended)

[P-DI-02] The constructor of Dotdigitalgroup\Email\Model\Events\Response\StreamedResponse does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    36       * @param callable $stream
    37       * @param int $status
    38       * @param array $responseHeaders
    39       */
    40      public function __construct(callable $stream, $status = 200, $responseHeaders = [])
    41      {
>   42          $this->setStream($stream);
    43          $this->statusCode = $status;
    44          $this->responseHeaders = $responseHeaders;
    45      }
    46  
    47      /**
    48       * Get the response as stream
```

## 19. `app/code/Buckaroo/Magento2/Model/Voucher/ApplyVoucherRequest.php:150` (extended)

[P-DI-02] The constructor of Buckaroo\Magento2\Model\Voucher\ApplyVoucherRequest does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   144          $this->cartRepository = $cartRepository;
   145          $this->idinVerificationGuard = $idinVerificationGuard;
   146          $this->scopeConfig = $scopeConfig;
   147          $this->configProviderAccount = $configProviderAccount;
   148          $this->urlBuilder = $urlBuilder;
   149          $this->formKey = $formKey;
>  150          $this->store = $storeManager->getStore();
   151          $this->transferFactory = $transferFactory;
   152          $this->clientInterface = $clientInterface;
   153          $this->httpRequest = $httpRequest;
   154          $this->groupTransaction = $groupTransaction;
   155      }
   156  
```

## 20. `app/code/Taxjar/SalesTax/Model/Import/Rate.php:113` (extended)

[P-DI-02] The constructor of Taxjar\SalesTax\Model\Import\Rate does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   107          $this->rateService = $rateService;
   108          $this->regionFactory = $regionFactory;
   109          $this->searchCriteriaBuilder = $searchCriteriaBuilder;
   110          $this->filterBuilder = $filterBuilder;
   111          $this->rule = $rule;
   112  
>  113          return $this;
   114      }
   115  
   116      /**
   117       * Attempt to create a new rate from data.
   118       * Should return a tuple containing rate model's ID and shipping rate's ID.
   119       *
```

## 21. `app/code/GhostUnicorns/WebapiLogs/Ui/DataProvider/LogListingDataProvider.php:114` (extended)

[P-DI-02] The constructor of GhostUnicorns\WebapiLogs\Ui\DataProvider\LogListingDataProvider does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
   108          $this->primaryFieldName = $primaryFieldName;
   109          $this->requestFieldName = $requestFieldName;
   110          $this->reporting = $reporting;
   111          $this->searchCriteriaBuilder = $searchCriteriaBuilder;
   112          $this->meta = $meta;
   113          $this->data = $data;
>  114          $this->prepareUpdateUrl();
   115      }
   116  
   117      /**
   118       * Get Data Provider name
   119       *
   120       * @return string
```

## 22. `app/code/PostcodeEu/AddressValidation/Api/Data/Autocomplete/AutocompleteMatch.php:34` (extended)

[P-DI-02] The constructor of PostcodeEu\AddressValidation\Api\Data\Autocomplete\AutocompleteMatch does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    28       * @access public
    29       * @param array $match
    30       * @return void
    31       */
    32      public function __construct(array $match)
    33      {
>   34          $this->value = $match['value'];
    35          $this->label = $match['label'];
    36          $this->description = $match['description'];
    37          $this->precision = $match['precision'];
    38          $this->context = $match['context'];
    39          $this->highlights = $match['highlights'];
    40      }
```

## 23. `app/code/Taxjar/SalesTax/Observer/ImportData.php:97` (extended)

[P-DI-02] The constructor of Taxjar\SalesTax\Observer\ImportData does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    91          $this->resourceConfig = $resourceConfig;
    92          $this->clientFactory = $clientFactory;
    93          $this->configFactory = $configFactory;
    94          $this->reinitableConfig = $reinitableConfig;
    95          $this->taxjarConfig = $taxjarConfig;
    96          $this->backupRateOriginAddress = $backupRateOriginAddress;
>   97          $this->apiKey = $this->taxjarConfig->getApiKey();
    98      }
    99  
   100      /**
   101       * @param  Observer $observer
   102       * @return $this
   103       * @throws LocalizedException
```

## 24. `app/code/Smile/ElasticsuiteCms/Block/Page/Result.php:64` (extended)

[P-DI-02] The constructor of Smile\ElasticsuiteCms\Block\Page\Result does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    58          \Magento\Cms\Helper\Page $cmsPage,
    59          array $data = []
    60      ) {
    61          parent::__construct($context, $data);
    62  
    63          $this->queryFactory   = $queryFactory;
>   64          $this->pageCollection = $this->initPageCollection($pageCollectionFactory);
    65          $this->cmsPage        = $cmsPage;
    66      }
    67  
    68      /**
    69       * Prepare layout
    70       *
```

## 25. `app/code/Bazaarvoice/Connector/Model/BVSEOSDK/Reviews.php:29` (extended)

[P-DI-02] The constructor of Bazaarvoice\Connector\Model\BVSEOSDK\Reviews does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    23          parent::__construct($params);
    24  
    25          // since we are in the reviews class
    26          // we need to set the content_type config
    27          // to reviews so we get reviews in our
    28          // SEO request
>   29          $this->config['content_type'] = 'reviews';
    30  
    31          // for reviews subject type will always
    32          // need to be product
    33          $this->config['subject_type'] = 'product';
    34      }
    35  
```

## 26. `app/code/Magenest/Core/Block/Adminhtml/System/Config/Form/Button/SendFeedback.php:46` (extended)

[P-DI-02] The constructor of Magenest\Core\Block\Adminhtml\System\Config\Form\Button\SendFeedback does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    40      public function __construct(
    41          ProductMetadataInterface $productMetadata,
    42          Context $context,
    43          Data $helper,
    44          array $data = []
    45      ) {
>   46          $this->_version = $productMetadata->getVersion();
    47          $this->_edition = $productMetadata->getEdition();
    48          parent::__construct($context, $data);
    49          $this->helper = $helper;
    50      }
    51  
    52      /**
```

## 27. `app/code/Emico/AttributeLanding/Block/LandingPage/Content.php:52` (extended)

[P-DI-02] The constructor of Emico\AttributeLanding\Block\LandingPage\Content does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    46          FilterProvider $filterProvider,
    47          LoggerInterface $logger,
    48          private readonly ImageUploader $imageUploader
    49      ) {
    50          parent::__construct($context);
    51          $this->landingPageContext = $landingPageContext;
>   52          $this->pageFilter = $filterProvider->getPageFilter();
    53          $this->logger = $logger;
    54      }
    55  
    56      /**
    57       * @return string|null
    58       */
```

## 28. `app/code/Swissup/Marketplace/Model/Channel/Composer.php:85` (extended)

[P-DI-02] The constructor of Swissup\Marketplace\Model\Channel\Composer does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    79      ) {
    80          $this->channelManager = $channelManager;
    81          $this->cache = $cache;
    82          $this->scopeConfig = $scopeConfig;
    83          $this->jsonSerializer = $jsonSerializer;
    84          $this->httpClientFactory = $httpClientFactory;
>   85          $this->data = array_merge(
    86              $this->getDefaultData(),
    87              $data,
    88              [
    89                  'url' => $url,
    90                  'title' => $title,
    91                  'identifier' => $identifier,
```

## 29. `app/code/Dotdigitalgroup/Email/Block/Adminhtml/Config/Rules/Customdatafields.php:68` (extended)

[P-DI-02] The constructor of Dotdigitalgroup\Email\Block\Adminhtml\Config\Rules\Customdatafields does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    62      ) {
    63          $this->condition = $condition;
    64          $this->value = $value;
    65          $this->_addAfter = false;
    66          $this->className = 'ddg-rules-conditions';
    67  
>   68          $this->_addButtonLabel = __('Add New Condition');
    69          parent::__construct($context, $data);
    70      }
    71  
    72      /**
    73       * Prepare to render.
    74       *
```

## 30. `app/code/Mageplaza/Blog/Model/ResourceModel/Category.php:87` (extended)

[P-DI-02] The constructor of Mageplaza\Blog\Model\ResourceModel\Category does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.

```
    81          $this->helperData   = $helperData;
    82          $this->date         = $date;
    83          $this->eventManager = $eventManager;
    84  
    85          parent::__construct($context);
    86  
>   87          $this->categoryPostTable = $this->getTable('mageplaza_blog_post_category');
    88      }
    89  
    90      /**
    91       * Initialize resource model
    92       *
    93       * @return void
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Dotdigitalgroup/Email/ViewModel/ProductNotificationView.php:100` | TP-low | getScopeConfig() and getStoreManager() are plain getters on the template context, negligible cost. |  |  |
| 2 | `app/code/FireGento/FastSimpleImport/Model/Import/Category.php:271` | TP-low | initCategories() and the other init calls load the whole category tree, websites, stores and attributes, but this import entity is built only when a catalog_category import runs and needs all of it. |  |  |
| 3 | `app/code/Bazaarvoice/Connector/Model/BVSEOSDK/Base.php:35` | FP | BVSEOSDK\Base is SDK code built with new by BV, not by the object manager, and its constructor only validates and normalizes the params passed in. |  |  |
| 4 | `app/code/Clerk/Clerk/Model/Adapter/AbstractAdapter.php:86` | TP-low | The only subclass, Clerk Adapter\Product, implements addFieldHandlers() by registering closures in an array, which runs no query and costs almost nothing. |  |  |
| 5 | `app/code/Buckaroo/Magento2/Plugin/QuoteCheck.php:66` | TP-low | getQuote() loads the quote and $this->quote is never read, but the plugin is built only when a wrapped Cart method runs, and those methods load the same session-cached quote anyway. |  |  |
| 6 | `app/code/KiwiCommerce/CronScheduler/Ui/DataProvider/ScheduleProvider.php:46` | TP-low | The collection is created but not loaded in an admin cron schedule grid data provider, which exists only to load it. |  |  |
| 7 | `app/code/PostcodeEu/AddressValidation/Api/Data/MagentoDebugInfo.php:25` | FP | MagentoDebugInfo is a value object built with new from an API response in Autocomplete, not by the object manager, and the constructor only copies input fields. |  |  |
| 8 | `app/code/Mageplaza/Blog/Model/ResourceModel/Post.php:142` | TP-low | getTable() resolves table names from an in-memory cache in ResourceConnection, a negligible cost on a shared resource model. |  |  |
| 9 | `app/code/Dotdigitalgroup/Email/Controller/Adminhtml/Datafield/Save.php:47` | TP-low | getMessageManager() and getRequest() are plain getters on the action context, negligible cost. |  |  |
| 10 | `app/code/CtiDigital/Configurator/Component/CustomerAttributes.php:83` | TP-low | array_merge() of two default property arrays is cheap, and the configurator component runs only from the CLI. |  |  |
| 11 | `app/code/Dotdigitalgroup/Email/Model/Events/SetupIntegration/ProductsHandler.php:39` | TP-low | $context->getRequest() is a plain getter on the context, negligible cost. |  |  |
| 12 | `app/code/ShipperHQ/Shipper/Helper/Rest.php:48` | TP-low | getGatewayUrl() and getPostOrderGatewayUrl() are in-memory scopeConfig reads plus string trimming, a small cost per helper creation. |  |  |
| 13 | `app/code/ShipperHQ/Shipper/Model/Order/Detail.php:80` | TP-low | Every Detail model, including each row the detail collection loads, builds an unloaded collection in its constructor, but an order has only a few carrier-group detail rows. |  |  |
| 14 | `app/code/Dotdigitalgroup/Email/Controller/Adminhtml/Rules/Selected.php:115` | TP-low | $context->getRequest() is a plain getter on the action context, negligible cost. |  |  |
| 15 | `app/code/MagestyApps/WebImages/Plugin/App/MediaPlugin.php:67` | TP-low | Filesystem::getDirectoryWrite() returns a cached directory object, so the two calls cost almost nothing. |  |  |
| 16 | `app/code/ScandiPWA/PersistedQuery/Plugin/InvalidatePQCache.php:39` | TP-low | StateInterface::isEnabled() is an in-memory lookup, and the plugin wraps only the full category product indexer. |  |  |
| 17 | `app/code/Bazaarvoice/Connector/Logger/Handler.php:53` | TP-low | setFormatter(new LineFormatter(...)) only creates one small formatter object for a logger handler. |  |  |
| 18 | `app/code/Dotdigitalgroup/Email/Model/Events/Response/StreamedResponse.php:42` | FP | StreamedResponse is built with new in Helper\ServerSentEvents, not by the object manager, and setStream() only stores the passed callable. |  |  |
| 19 | `app/code/Buckaroo/Magento2/Model/Voucher/ApplyVoucherRequest.php:150` | TP-low | StoreManager::getStore() returns the already initialized current store from memory, negligible cost. |  |  |
| 20 | `app/code/Taxjar/SalesTax/Model/Import/Rate.php:113` | FP | The flagged statement is return $this, which does no work, and every other statement assigns a dependency. |  |  |
| 21 | `app/code/GhostUnicorns/WebapiLogs/Ui/DataProvider/LogListingDataProvider.php:114` | TP-low | prepareUpdateUrl() only loops over the data config and adds filters from request params, cheap work in an admin grid data provider that needs them. |  |  |
| 22 | `app/code/PostcodeEu/AddressValidation/Api/Data/Autocomplete/AutocompleteMatch.php:34` | FP | AutocompleteMatch is a value object built with new in Autocomplete for each match, not by the object manager, and the constructor only copies array fields. |  |  |
| 23 | `app/code/Taxjar/SalesTax/Observer/ImportData.php:97` | TP-low | getApiKey() is an in-memory scopeConfig read in an adminhtml observer, a small cost. |  |  |
| 24 | `app/code/Smile/ElasticsuiteCms/Block/Page/Result.php:64` | TP-low | initPageCollection() creates an unloaded collection and QueryFactory::get() loads the search query once and caches it, on the CMS search result page that always uses both. |  |  |
| 25 | `app/code/Bazaarvoice/Connector/Model/BVSEOSDK/Reviews.php:29` | FP | BVSEOSDK\Reviews is SDK code built with new by BV, not by the object manager, and the constructor only sets two literal config keys. |  |  |
| 26 | `app/code/Magenest/Core/Block/Adminhtml/System/Config/Form/Button/SendFeedback.php:46` | TP-low | ProductMetadata::getVersion() reads a cached value and getEdition() returns a constant, in an admin config button block. |  |  |
| 27 | `app/code/Emico/AttributeLanding/Block/LandingPage/Content.php:52` | TP-low | FilterProvider::getPageFilter() returns a shared filter instance that this landing page content block uses to render anyway. |  |  |
| 28 | `app/code/Swissup/Marketplace/Model/Channel/Composer.php:85` | TP-low | array_merge() of literal defaults and constructor arguments is cheap argument handling. |  |  |
| 29 | `app/code/Dotdigitalgroup/Email/Block/Adminhtml/Config/Rules/Customdatafields.php:68` | TP-low | __('Add New Condition') only creates a Phrase object in an admin config block, negligible cost. |  |  |
| 30 | `app/code/Mageplaza/Blog/Model/ResourceModel/Category.php:87` | TP-low | getTable() resolves the table name from an in-memory cache in ResourceConnection, a negligible cost on a shared resource model. |  |  |
