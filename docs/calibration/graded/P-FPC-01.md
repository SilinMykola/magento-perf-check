# P-FPC-01: calibration sample

30 of 70 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-gift-message/Block/Cart/GiftOptions.php:63` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    57          CompositeConfigProvider $configProvider,
    58          array $layoutProcessors = [],
    59          array $data = []
    60      ) {
    61          parent::__construct($context, $data);
    62          $this->jsonEncoder = $jsonEncoder;
>   63          $this->_isScopePrivate = true;
    64          $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
    65          $this->configProvider = $configProvider;
    66          $this->layoutProcessors = $layoutProcessors;
    67      }
    68  
    69      /**
```

## 2. `vendor/magento/module-sales/Block/Order/Recent.php:67` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    61          array $data = [],
    62          ?StoreManagerInterface $storeManager = null
    63      ) {
    64          $this->_orderCollectionFactory = $orderCollectionFactory;
    65          $this->_customerSession = $customerSession;
    66          $this->_orderConfig = $orderConfig;
>   67          $this->_isScopePrivate = true;
    68          $this->storeManager = $storeManager ?: ObjectManager::getInstance()
    69              ->get(StoreManagerInterface::class);
    70          parent::__construct($context, $data);
    71      }
    72  
    73      /**
```

## 3. `vendor/magento/module-persistent/Block/Header/Additional.php:70` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    64      ) {
    65          $this->_isScopePrivate = true;
    66          $this->_customerViewHelper = $customerViewHelper;
    67          $this->_persistentSessionHelper = $persistentSessionHelper;
    68          $this->customerRepository = $customerRepository;
    69          parent::__construct($context, $data);
>   70          $this->_isScopePrivate = true;
    71          $this->jsonSerializer = $jsonSerializer ?: ObjectManager::getInstance()->get(Json::class);
    72          $this->persistentHelper = $persistentHelper ?: ObjectManager::getInstance()->get(Data::class);
    73      }
    74  
    75      /**
    76       * Retrieve unset cookie link
```

## 4. `vendor/magento/module-gift-message/Block/Cart/Item/Renderer/Actions/GiftOptions.php:21` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    15   */
    16  class GiftOptions extends Generic
    17  {
    18      /**
    19       * @var bool
    20       */
>   21      protected $_isScopePrivate = false;
    22  
    23      /**
    24       * @var array
    25       */
    26      protected $jsLayout;
    27  
```

## 5. `vendor/magento/module-paypal/Block/Billing/Agreements.php:63` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    57          array $data = []
    58      ) {
    59          $this->_helper = $helper;
    60          $this->_customerSession = $customerSession;
    61          $this->_agreementCollection = $agreementCollection;
    62          parent::__construct($context, $data);
>   63          $this->_isScopePrivate = true;
    64      }
    65  
    66      /**
    67       * Set Billing Agreement instance
    68       *
    69       * @return $this
```

## 6. `vendor/magento/module-weee/Block/Item/Price/Renderer.php:48` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    42          \Magento\Weee\Helper\Data $weeeHelper,
    43          array $data = []
    44      ) {
    45          $this->weeeHelper = $weeeHelper;
    46          $data['weeeHelper'] = $this->weeeHelper;
    47          parent::__construct($context, $taxHelper, $priceCurrency, $data);
>   48          $this->_isScopePrivate = true;
    49      }
    50  
    51      /**
    52       * Whether to display weee details together with price
    53       *
    54       * @return bool
```

## 7. `vendor/magento/module-sales/Block/Order/Info/Buttons.php:52` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    46          \Magento\Framework\App\Http\Context $httpContext,
    47          array $data = []
    48      ) {
    49          $this->_coreRegistry = $registry;
    50          $this->httpContext = $httpContext;
    51          parent::__construct($context, $data);
>   52          $this->_isScopePrivate = true;
    53      }
    54  
    55      /**
    56       * Retrieve current order model instance
    57       *
    58       * @return \Magento\Sales\Model\Order
```

## 8. `vendor/magento/module-checkout/Block/Cart/Totals.php:65` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    59          ConfigInterface $salesConfig,
    60          array $layoutProcessors = [],
    61          array $data = []
    62      ) {
    63          $this->_salesConfig = $salesConfig;
    64          parent::__construct($context, $customerSession, $checkoutSession, $data);
>   65          $this->_isScopePrivate = true;
    66          $this->layoutProcessors = $layoutProcessors;
    67      }
    68  
    69      /**
    70       * Retrieve encoded js layout.
    71       *
```

## 9. `vendor/magento/module-multishipping/Block/Checkout/Shipping.php:63` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    57      ) {
    58          $this->priceCurrency = $priceCurrency;
    59          $this->_taxHelper = $taxHelper;
    60          $this->_filterGridFactory = $filterGridFactory;
    61          $this->_multishipping = $multishipping;
    62          parent::__construct($context, $data);
>   63          $this->_isScopePrivate = true;
    64      }
    65  
    66      /**
    67       * Get multishipping checkout model
    68       *
    69       * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
```

## 10. `vendor/magento/module-gift-message/Block/Cart/GiftOptions.php:25` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    19   */
    20  class GiftOptions extends Template
    21  {
    22      /**
    23       * @var bool
    24       */
>   25      protected $_isScopePrivate = false;
    26  
    27      /**
    28       * @var array
    29       */
    30      protected $jsLayout;
    31  
```

## 11. `vendor/magento/module-paypal/Block/Billing/Agreement/View.php:96` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    90          $this->_orderCollectionFactory = $orderCollectionFactory;
    91          $this->_customerSession = $customerSession;
    92          $this->_orderConfig = $orderConfig;
    93          $this->_coreRegistry = $registry;
    94          $this->_agreementResource = $agreementResource;
    95          parent::__construct($context, $data);
>   96          $this->_isScopePrivate = true;
    97      }
    98  
    99      /**
   100       * Retrieve related orders collection
   101       *
   102       * @return \Magento\Sales\Model\ResourceModel\Order\Collection
```

## 12. `vendor/magento/module-checkout/Block/Cart/Crosssell.php:113` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
   107          $this->_itemRelationsList = $itemRelationsList;
   108          $this->stockHelper = $stockHelper;
   109          parent::__construct(
   110              $context,
   111              $data
   112          );
>  113          $this->_isScopePrivate = true;
   114          $this->productCollectionFactory = $productCollectionFactory
   115              ?? ObjectManager::getInstance()->get(CollectionFactory::class);
   116          $this->productRepository = $productRepository
   117              ?? ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
   118      }
   119  
```

## 13. `vendor/magento/module-checkout/Block/Cart/Sidebar.php:64` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    58              $this->jsLayout = array_merge_recursive($jsLayoutDataProvider->getData(), $data['jsLayout']);
    59              unset($data['jsLayout']);
    60          } else {
    61              $this->jsLayout = $jsLayoutDataProvider->getData();
    62          }
    63          parent::__construct($context, $customerSession, $checkoutSession, $data);
>   64          $this->_isScopePrivate = false;
    65          $this->imageHelper = $imageHelper;
    66          $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
    67              ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    68      }
    69  
    70      /**
```

## 14. `vendor/magento/module-customer/Block/Widget/Gender.php:51` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    45          \Magento\Customer\Model\Session $customerSession,
    46          array $data = []
    47      ) {
    48          $this->_customerSession = $customerSession;
    49          $this->customerRepository = $customerRepository;
    50          parent::__construct($context, $addressHelper, $customerMetadata, $data);
>   51          $this->_isScopePrivate = true;
    52      }
    53  
    54      /**
    55       * Initialize block
    56       *
    57       * @return void
```

## 15. `vendor/magento/module-captcha/Block/Captcha.php:39` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    33          \Magento\Framework\View\Element\Template\Context $context,
    34          \Magento\Captcha\Helper\Data $captchaData,
    35          array $data = []
    36      ) {
    37          $this->_captchaData = $captchaData;
    38          parent::__construct($context, $data);
>   39          $this->_isScopePrivate = true;
    40      }
    41  
    42      /**
    43       * Renders captcha HTML (if required)
    44       *
    45       * @return string
```

## 16. `vendor/magento/module-checkout/Block/Cart.php:56` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    50          \Magento\Framework\App\Http\Context $httpContext,
    51          array $data = []
    52      ) {
    53          $this->_cartHelper = $cartHelper;
    54          $this->_catalogUrlBuilder = $catalogUrlBuilder;
    55          parent::__construct($context, $customerSession, $checkoutSession, $data);
>   56          $this->_isScopePrivate = true;
    57          $this->httpContext = $httpContext;
    58      }
    59  
    60      /**
    61       * Prepare Quote Item Product URLs
    62       *
```

## 17. `vendor/magento/module-multishipping/Block/Checkout/Link.php:35` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    29          \Magento\Framework\View\Element\Template\Context $context,
    30          \Magento\Multishipping\Helper\Data $helper,
    31          array $data = []
    32      ) {
    33          $this->helper = $helper;
    34          parent::__construct($context, $data);
>   35          $this->_isScopePrivate = true;
    36      }
    37  
    38      /**
    39       * @return string
    40       */
    41      public function getCheckoutUrl()
```

## 18. `vendor/magento/module-checkout/Block/Onepage/Link.php:42` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    36          \Magento\Checkout\Helper\Data $checkoutHelper,
    37          array $data = []
    38      ) {
    39          $this->_checkoutHelper = $checkoutHelper;
    40          $this->_checkoutSession = $checkoutSession;
    41          parent::__construct($context, $data);
>   42          $this->_isScopePrivate = true;
    43      }
    44  
    45      /**
    46       * @return string
    47       */
    48      public function getCheckoutUrl()
```

## 19. `vendor/magento/module-reports/Block/Product/AbstractProduct.php:57` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    51          parent::__construct(
    52              $context,
    53              $data
    54          );
    55          $this->_productVisibility = $productVisibility;
    56          $this->_indexFactory = $indexFactory;
>   57          $this->_isScopePrivate = true;
    58      }
    59  
    60      /**
    61       * Retrieve page size
    62       *
    63       * @return int
```

## 20. `vendor/magento/module-sales/Block/Order/View.php:59` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    53          array $data = []
    54      ) {
    55          $this->_paymentHelper = $paymentHelper;
    56          $this->_coreRegistry = $registry;
    57          $this->httpContext = $httpContext;
    58          parent::__construct($context, $data);
>   59          $this->_isScopePrivate = true;
    60      }
    61  
    62      /**
    63       * @return void
    64       */
    65      protected function _prepareLayout()
```

## 21. `vendor/magento/module-checkout/Block/Cart/Shipping.php:71` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    65          ?Json $serializer = null,
    66          ?JsonHexTag $jsonHexTagSerializer = null
    67      ) {
    68          $this->configProvider = $configProvider;
    69          $this->layoutProcessors = $layoutProcessors;
    70          parent::__construct($context, $customerSession, $checkoutSession, $data);
>   71          $this->_isScopePrivate = true;
    72          $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    73          $this->jsonHexTagSerializer = $jsonHexTagSerializer ?: ObjectManager::getInstance()->get(JsonHexTag::class);
    74      }
    75  
    76      /**
    77       * Retrieve checkout configuration
```

## 22. `vendor/magento/module-send-friend/Block/Send.php:74` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    68      ) {
    69          $this->_customerSession = $customerSession;
    70          $this->_coreRegistry = $registry;
    71          $this->_sendfriendData = $sendfriendData;
    72          $this->sendfriend = $sendfriend;
    73          parent::__construct($context, $data);
>   74          $this->_isScopePrivate = true;
    75          $this->httpContext = $httpContext;
    76          $this->_customerViewHelper = $customerViewHelper;
    77      }
    78  
    79      /**
    80       * Retrieve username for form field
```

## 23. `vendor/magento/module-customer/Block/Widget/Name.php:54` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    48          AddressMetadataInterface $addressMetadata,
    49          array $data = []
    50      ) {
    51          $this->options = $options;
    52          parent::__construct($context, $addressHelper, $customerMetadata, $data);
    53          $this->addressMetadata = $addressMetadata;
>   54          $this->_isScopePrivate = true;
    55      }
    56  
    57      /**
    58       * @inheritdoc
    59       */
    60      public function _construct()
```

## 24. `vendor/magento/module-multishipping/Block/Checkout/Billing/Items.php:43` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    37          \Magento\Checkout\Model\Session $checkoutSession,
    38          array $data = []
    39      ) {
    40          $this->_multishipping = $multishipping;
    41          $this->_checkoutSession = $checkoutSession;
    42          parent::__construct($context, $data);
>   43          $this->_isScopePrivate = true;
    44      }
    45  
    46      /**
    47       * Get multishipping checkout model
    48       *
    49       * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
```

## 25. `vendor/magento/module-customer/Block/Form/Register.php:90` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    84              $jsonEncoder,
    85              $configCacheType,
    86              $regionCollectionFactory,
    87              $countryCollectionFactory,
    88              $data
    89          );
>   90          $this->_isScopePrivate = false;
    91      }
    92  
    93      /**
    94       * Get config
    95       *
    96       * @param string $path
```

## 26. `vendor/magento/module-admin-notification/Block/Window.php:68` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    62          \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection\Critical $criticalCollection,
    63          array $data = []
    64      ) {
    65          parent::__construct($context, $data);
    66          $this->_authSession = $authSession;
    67          $this->_criticalCollection = $criticalCollection;
>   68          $this->_isScopePrivate = true;
    69      }
    70  
    71      /**
    72       * Render block
    73       *
    74       * @return string
```

## 27. `vendor/magento/module-customer/Block/Widget/Company.php:61` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    55          AddressMetadataInterface $addressMetadata,
    56          array $data = []
    57      ) {
    58          $this->options = $options;
    59          parent::__construct($context, $addressHelper, $customerMetadata, $data);
    60          $this->addressMetadata = $addressMetadata;
>   61          $this->_isScopePrivate = true;
    62      }
    63  
    64      /**
    65       * @return void
    66       */
    67      public function _construct()
```

## 28. `vendor/magento/module-paypal/Block/Express/Form.php:78` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    72          array $data = []
    73      ) {
    74          $this->_paypalData = $paypalData;
    75          $this->_paypalConfigFactory = $paypalConfigFactory;
    76          $this->_localeResolver = $localeResolver;
    77          $this->_config = null;
>   78          $this->_isScopePrivate = true;
    79          $this->currentCustomer = $currentCustomer;
    80          parent::__construct($context, $data);
    81      }
    82  
    83      /**
    84       * Set template and redirect message
```

## 29. `vendor/magento/module-checkout/Block/Cart/Item/Renderer.php:136` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
   130          $this->imageBuilder = $imageBuilder;
   131          $this->_urlHelper = $urlHelper;
   132          $this->_productConfig = $productConfig;
   133          $this->_checkoutSession = $checkoutSession;
   134          $this->messageManager = $messageManager;
   135          parent::__construct($context, $data);
>  136          $this->_isScopePrivate = true;
   137          $this->moduleManager = $moduleManager;
   138          $this->messageInterpretationStrategy = $messageInterpretationStrategy;
   139          $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
   140      }
   141  
   142      /**
```

## 30. `vendor/magento/module-multishipping/Block/Checkout/Address/Select.php:34` (core)

[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.

```
    28       */
    29      protected $addressMapper;
    30  
    31      /**
    32       * @var bool
    33       */
>   34      protected $_isScopePrivate = true;
    35  
    36      /**
    37       * @var \Magento\Framework\Api\FilterBuilder
    38       */
    39      protected $filterBuilder;
    40  
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-gift-message/Block/Cart/GiftOptions.php:63` |  |  | re-check |  |
| 2 | `vendor/magento/module-sales/Block/Order/Recent.php:67` |  |  | re-check |  |
| 3 | `vendor/magento/module-persistent/Block/Header/Additional.php:70` |  |  | re-check |  |
| 4 | `vendor/magento/module-gift-message/Block/Cart/Item/Renderer/Actions/GiftOptions.php:21` |  |  |  |  |
| 5 | `vendor/magento/module-paypal/Block/Billing/Agreements.php:63` |  |  |  |  |
| 6 | `vendor/magento/module-weee/Block/Item/Price/Renderer.php:48` |  |  |  |  |
| 7 | `vendor/magento/module-sales/Block/Order/Info/Buttons.php:52` |  |  |  |  |
| 8 | `vendor/magento/module-checkout/Block/Cart/Totals.php:65` |  |  |  |  |
| 9 | `vendor/magento/module-multishipping/Block/Checkout/Shipping.php:63` |  |  |  |  |
| 10 | `vendor/magento/module-gift-message/Block/Cart/GiftOptions.php:25` |  |  |  |  |
| 11 | `vendor/magento/module-paypal/Block/Billing/Agreement/View.php:96` |  |  |  |  |
| 12 | `vendor/magento/module-checkout/Block/Cart/Crosssell.php:113` |  |  |  |  |
| 13 | `vendor/magento/module-checkout/Block/Cart/Sidebar.php:64` |  |  |  |  |
| 14 | `vendor/magento/module-customer/Block/Widget/Gender.php:51` |  |  |  |  |
| 15 | `vendor/magento/module-captcha/Block/Captcha.php:39` |  |  |  |  |
| 16 | `vendor/magento/module-checkout/Block/Cart.php:56` |  |  |  |  |
| 17 | `vendor/magento/module-multishipping/Block/Checkout/Link.php:35` |  |  |  |  |
| 18 | `vendor/magento/module-checkout/Block/Onepage/Link.php:42` |  |  |  |  |
| 19 | `vendor/magento/module-reports/Block/Product/AbstractProduct.php:57` |  |  |  |  |
| 20 | `vendor/magento/module-sales/Block/Order/View.php:59` |  |  |  |  |
| 21 | `vendor/magento/module-checkout/Block/Cart/Shipping.php:71` |  |  |  |  |
| 22 | `vendor/magento/module-send-friend/Block/Send.php:74` |  |  |  |  |
| 23 | `vendor/magento/module-customer/Block/Widget/Name.php:54` |  |  |  |  |
| 24 | `vendor/magento/module-multishipping/Block/Checkout/Billing/Items.php:43` |  |  |  |  |
| 25 | `vendor/magento/module-customer/Block/Form/Register.php:90` |  |  |  |  |
| 26 | `vendor/magento/module-admin-notification/Block/Window.php:68` |  |  |  |  |
| 27 | `vendor/magento/module-customer/Block/Widget/Company.php:61` |  |  |  |  |
| 28 | `vendor/magento/module-paypal/Block/Express/Form.php:78` |  |  |  |  |
| 29 | `vendor/magento/module-checkout/Block/Cart/Item/Renderer.php:136` |  |  |  |  |
| 30 | `vendor/magento/module-multishipping/Block/Checkout/Address/Select.php:34` |  |  |  |  |
