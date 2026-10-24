# P-DI-01: calibration sample

30 of 643 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-two-factor-auth/Controller/Adminhtml/U2f/VerifyIdentityPost.php:49` (core)

[P-DI-01] Magento\TwoFactorAuth\Controller\Adminhtml\U2f\VerifyIdentityPost receives $session (Magento\Backend\Model\Auth\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Backend\Model\Auth\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    43       * @param TfaProviderSessionInterface $tfaProviderSession
    44       * @SuppressWarnings("PHPMD.ExcessiveParameterList")
    45       */
    46      public function __construct(
    47          Action\Context $context,
    48          private readonly Tfa $tfa,
>   49          private readonly Session $session,
    50          private readonly JsonFactory $jsonFactory,
    51          private readonly U2fKey $u2fKey,
    52          private readonly U2fSession $u2fSession,
    53          private readonly DataObjectFactory $dataObjectFactory,
    54          private readonly AlertInterface $alert,
    55          private readonly TfaProviderSessionInterface $tfaProviderSession
```

## 2. `vendor/magento/module-weee/Plugin/Checkout/CustomerData/Cart.php:41` (core)

[P-DI-01] Magento\Weee\Plugin\Checkout\CustomerData\Cart receives $checkoutSession (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    35       * @param \Magento\Checkout\Model\Session $checkoutSession
    36       * @param \Magento\Checkout\Helper\Data $checkoutHelper
    37       * @param \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer
    38       * @param \Magento\Weee\Block\Item\Price\Renderer $itemWeePriceRenderer
    39       */
    40      public function __construct(
>   41          \Magento\Checkout\Model\Session $checkoutSession,
    42          \Magento\Checkout\Helper\Data $checkoutHelper,
    43          \Magento\Tax\Block\Item\Price\Renderer $itemPriceRenderer,
    44          \Magento\Weee\Block\Item\Price\Renderer $itemWeePriceRenderer
    45      ) {
    46          parent::__construct($checkoutSession, $checkoutHelper, $itemPriceRenderer);
    47          $this->itemPriceRenderer = $itemWeePriceRenderer;
```

## 3. `vendor/magento/module-inventory-admin-ui/Ui/DataProvider/SourceDataProvider.php:90` (core)

[P-DI-01] Magento\InventoryAdminUi\Ui\DataProvider\SourceDataProvider receives $session (Magento\Backend\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Backend\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    84          ReportingInterface $reporting,
    85          SearchCriteriaBuilder $searchCriteriaBuilder,
    86          RequestInterface $request,
    87          FilterBuilder $filterBuilder,
    88          SourceRepositoryInterface $sourceRepository,
    89          SearchResultFactory $searchResultFactory,
>   90          Session $session,
    91          array $meta = [],
    92          array $data = [],
    93          ?PoolInterface $pool = null
    94      ) {
    95          parent::__construct(
    96              $name,
```

## 4. `vendor/magento/module-payment-services-paypal/Block/SmartButtons.php:97` (core)

[P-DI-01] Magento\PaymentServicesPaypal\Block\SmartButtons receives $session (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    91       * @SuppressWarnings(Magento.TypeDuplication)
    92       * @SuppressWarnings(PHPMD.ExcessiveParameterList)
    93       */
    94      public function __construct(
    95          Context $context,
    96          Config $config,
>   97          Session $session,
    98          string $pageType = 'minicart',
    99          array $componentConfig = [],
   100          array $data = [],
   101          ?Json $serializer = null,
   102          ?CompositeConfigProvider $compositeConfigProvider = null,
   103          ?CheckoutSession $checkoutSession = null,
```

## 5. `vendor/magento/framework/App/Request/DataPersistor.php:25` (core)

[P-DI-01] Magento\Framework\App\Request\DataPersistor receives $session (Magento\Framework\Session\SessionManagerInterface) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Framework\Session\SessionManagerInterface\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    19      protected $session;
    20  
    21      /**
    22       * @param SessionManagerInterface $session
    23       */
    24      public function __construct(
>   25          SessionManagerInterface $session
    26      ) {
    27          $this->session = $session;
    28      }
    29  
    30      /**
    31       * Store data by key
```

## 6. `vendor/magento/module-checkout/Block/Cart/Grid.php:62` (core)

[P-DI-01] Magento\Checkout\Block\Cart\Grid receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    56       * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory
    57       * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
    58       * @param array $data
    59       */
    60      public function __construct(
    61          \Magento\Framework\View\Element\Template\Context $context,
>   62          \Magento\Customer\Model\Session $customerSession,
    63          \Magento\Checkout\Model\Session $checkoutSession,
    64          \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
    65          \Magento\Checkout\Helper\Cart $cartHelper,
    66          \Magento\Framework\App\Http\Context $httpContext,
    67          \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
    68          \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
```

## 7. `vendor/magento/module-multishipping/Model/Checkout/Type/Multishipping/State.php:55` (core)

[P-DI-01] Magento\Multishipping\Model\Checkout\Type\Multishipping\State receives $checkoutSession (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    49      /**
    50       * Init model, steps
    51       *
    52       * @param Session $checkoutSession
    53       * @param Multishipping $multishipping
    54       */
>   55      public function __construct(Session $checkoutSession, Multishipping $multishipping)
    56      {
    57          $this->_checkoutSession = $checkoutSession;
    58          $this->_multishipping = $multishipping;
    59          parent::__construct();
    60          $this->_steps = [
    61              self::STEP_SELECT_ADDRESSES => new \Magento\Framework\DataObject(['label' => __('Select Addresses')]),
```

## 8. `app/code/Mageplaza/Smtp/Controller/AbandonedCart/Recover.php:92` (packagist)

[P-DI-01] Mageplaza\Smtp\Controller\AbandonedCart\Recover receives $checkoutSession (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    86      public function __construct(
    87          Context $context,
    88          Data $helperData,
    89          Collection $quoteCollection,
    90          StoreManagerInterface $storeManager,
    91          CustomerSession $customerSession,
>   92          CheckoutSession $checkoutSession
    93      ) {
    94          $this->helperData      = $helperData;
    95          $this->quoteCollection = $quoteCollection;
    96          $this->storeManager    = $storeManager;
    97          $this->checkoutSession = $checkoutSession;
    98          $this->customerSession = $customerSession;
```

## 9. `vendor/magento/module-wishlist/Observer/CustomerLogout.php:27` (core)

[P-DI-01] Magento\Wishlist\Observer\CustomerLogout receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    21       */
    22      protected $customerSession;
    23  
    24      /**
    25       * @param Session $customerSession
    26       */
>   27      public function __construct(Session $customerSession)
    28      {
    29          $this->customerSession = $customerSession;
    30      }
    31  
    32      /**
    33       * Customer logout processing
```

## 10. `vendor/magento/module-catalog/Controller/Category/View.php:136` (core)

[P-DI-01] Magento\Catalog\Controller\Category\View receives $catalogSession (Magento\Catalog\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Catalog\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
   130       * @param LoggerInterface|null $logger
   131       * @SuppressWarnings(PHPMD.ExcessiveParameterList)
   132       */
   133      public function __construct(
   134          Context $context,
   135          Design $catalogDesign,
>  136          Session $catalogSession,
   137          Registry $coreRegistry,
   138          StoreManagerInterface $storeManager,
   139          CategoryUrlPathGenerator $categoryUrlPathGenerator,
   140          PageFactory $resultPageFactory,
   141          ForwardFactory $resultForwardFactory,
   142          Resolver $layerResolver,
```

## 11. `vendor/magento/module-persistent/Observer/CheckExpirePersistentQuoteObserver.php:101` (core)

[P-DI-01] Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver receives $checkoutSession (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    95      public function __construct(
    96          \Magento\Persistent\Helper\Session $persistentSession,
    97          \Magento\Persistent\Helper\Data $persistentData,
    98          \Magento\Persistent\Model\QuoteManager $quoteManager,
    99          \Magento\Framework\Event\ManagerInterface $eventManager,
   100          \Magento\Customer\Model\Session $customerSession,
>  101          \Magento\Checkout\Model\Session $checkoutSession,
   102          \Magento\Framework\App\RequestInterface $request,
   103          CartRepositoryInterface $quoteRepository
   104      ) {
   105          $this->_persistentSession = $persistentSession;
   106          $this->quoteManager = $quoteManager;
   107          $this->_customerSession = $customerSession;
```

## 12. `vendor/magento/module-newsletter/Controller/Manage.php:28` (core)

[P-DI-01] Magento\Newsletter\Controller\Manage receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    22      /**
    23       * @param \Magento\Framework\App\Action\Context $context
    24       * @param \Magento\Customer\Model\Session $customerSession
    25       */
    26      public function __construct(
    27          \Magento\Framework\App\Action\Context $context,
>   28          \Magento\Customer\Model\Session $customerSession
    29      ) {
    30          parent::__construct($context);
    31          $this->_customerSession = $customerSession;
    32      }
    33  
    34      /**
```

## 13. `vendor/magento/module-checkout/Block/Cart.php:46` (core)

[P-DI-01] Magento\Checkout\Block\Cart receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    40       * @param \Magento\Framework\App\Http\Context $httpContext
    41       * @param array $data
    42       * @codeCoverageIgnore
    43       */
    44      public function __construct(
    45          \Magento\Framework\View\Element\Template\Context $context,
>   46          \Magento\Customer\Model\Session $customerSession,
    47          \Magento\Checkout\Model\Session $checkoutSession,
    48          \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
    49          \Magento\Checkout\Helper\Cart $cartHelper,
    50          \Magento\Framework\App\Http\Context $httpContext,
    51          array $data = []
    52      ) {
```

## 14. `vendor/magento/module-product-alert/Controller/Add/Stock.php:45` (core)

[P-DI-01] Magento\ProductAlert\Controller\Add\Stock receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    39       * @param CustomerSession $customerSession
    40       * @param ProductRepositoryInterface $productRepository
    41       * @param StoreManagerInterface|null $storeManager
    42       */
    43      public function __construct(
    44          Context $context,
>   45          CustomerSession $customerSession,
    46          ProductRepositoryInterface $productRepository,
    47          ?StoreManagerInterface $storeManager = null
    48      ) {
    49          $this->productRepository = $productRepository;
    50          parent::__construct($context, $customerSession);
    51          $this->storeManager = $storeManager ?: $this->_objectManager
```

## 15. `vendor/magento/module-checkout/Controller/Action.php:41` (core)

[P-DI-01] Magento\Checkout\Controller\Action receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    35       * @param CustomerRepositoryInterface $customerRepository
    36       * @param AccountManagementInterface $accountManagement
    37       * @codeCoverageIgnore
    38       */
    39      public function __construct(
    40          \Magento\Framework\App\Action\Context $context,
>   41          \Magento\Customer\Model\Session $customerSession,
    42          CustomerRepositoryInterface $customerRepository,
    43          AccountManagementInterface $accountManagement
    44      ) {
    45          $this->_customerSession = $customerSession;
    46          $this->customerRepository = $customerRepository;
    47          $this->accountManagement = $accountManagement;
```

## 16. `vendor/magento/module-two-factor-auth/Controller/Adminhtml/Tfa/VerifyIdentity.php:44` (core)

[P-DI-01] Magento\TwoFactorAuth\Controller\Adminhtml\Tfa\VerifyIdentity receives $session (Magento\Backend\Model\Auth\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Backend\Model\Auth\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    38       * @param Session $session
    39       * @param TfaProviderSessionInterface $tfaProviderSession
    40       * @param Helper $helper
    41       */
    42      public function __construct(
    43          Action\Context $context,
>   44          private readonly Session $session,
    45          private readonly TfaProviderSessionInterface $tfaProviderSession,
    46          private readonly Helper $helper
    47      ) {
    48          parent::__construct($context);
    49      }
    50  
```

## 17. `vendor/magento/module-catalog/Block/Product/View.php:89` (core)

[P-DI-01] Magento\Catalog\Block\Product\View receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    83          \Magento\Framework\Url\EncoderInterface $urlEncoder,
    84          \Magento\Framework\Json\EncoderInterface $jsonEncoder,
    85          \Magento\Framework\Stdlib\StringUtils $string,
    86          \Magento\Catalog\Helper\Product $productHelper,
    87          \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
    88          \Magento\Framework\Locale\FormatInterface $localeFormat,
>   89          \Magento\Customer\Model\Session $customerSession,
    90          ProductRepositoryInterface $productRepository,
    91          \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
    92          array $data = []
    93      ) {
    94          $this->_productHelper = $productHelper;
    95          $this->urlEncoder = $urlEncoder;
```

## 18. `app/code/Mollie/Payment/Service/Mollie/Order/SuccessPageRedirect.php:28` (packagist)

[P-DI-01] Mollie\Payment\Service\Mollie\Order\SuccessPageRedirect receives $checkoutSession (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    22  
    23  class SuccessPageRedirect
    24  {
    25      public function __construct(
    26          readonly private RequestInterface $request,
    27          readonly private ResponseInterface $response,
>   28          readonly private Session $checkoutSession,
    29          readonly private ManagerInterface $eventManager,
    30          readonly private TransactionToOrderRepositoryInterface $transactionToOrderRepository,
    31          readonly private SearchCriteriaBuilder $searchCriteriaBuilder,
    32          readonly private Config $config,
    33          readonly private UrlInterface $urlBuilder,
    34      ) {}
```

## 19. `vendor/magento/module-catalog/Helper/Data.php:213` (core)

[P-DI-01] Magento\Catalog\Helper\Data receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
   207          $templateFilterModel,
   208          \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyFactory,
   209          \Magento\Tax\Model\Config $taxConfig,
   210          \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsFactory,
   211          \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory,
   212          \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
>  213          CustomerSession $customerSession,
   214          PriceCurrencyInterface $priceCurrency,
   215          ProductRepositoryInterface $productRepository,
   216          CategoryRepositoryInterface $categoryRepository,
   217          \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
   218          \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
   219          \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory
```

## 20. `vendor/magento/module-tax/Block/Checkout/Shipping/Price.php:32` (core)

[P-DI-01] Magento\Tax\Block\Checkout\Shipping\Price receives $checkoutSession (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    26       * @param \Magento\Tax\Helper\Data $taxHelper
    27       * @param array $data
    28       */
    29      public function __construct(
    30          \Magento\Framework\View\Element\Template\Context $context,
    31          \Magento\Customer\Model\Session $customerSession,
>   32          \Magento\Checkout\Model\Session $checkoutSession,
    33          PriceCurrencyInterface $priceCurrency,
    34          \Magento\Tax\Helper\Data $taxHelper,
    35          array $data = []
    36      ) {
    37          $this->taxHelper = $taxHelper;
    38          parent::__construct(
```

## 21. `vendor/magento/module-reports/Observer/EventSaver.php:42` (core)

[P-DI-01] Magento\Reports\Observer\EventSaver receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    36       * @param \Magento\Customer\Model\Session $customerSession
    37       * @param \Magento\Customer\Model\Visitor $customerVisitor
    38       */
    39      public function __construct(
    40          \Magento\Store\Model\StoreManagerInterface $storeManager,
    41          \Magento\Reports\Model\EventFactory $event,
>   42          \Magento\Customer\Model\Session $customerSession,
    43          \Magento\Customer\Model\Visitor $customerVisitor
    44      ) {
    45          $this->_storeManager = $storeManager;
    46          $this->_eventFactory = $event;
    47          $this->_customerSession = $customerSession;
    48          $this->_customerVisitor = $customerVisitor;
```

## 22. `vendor/magento/module-captcha/Model/Customer/Plugin/AjaxLogin.php:53` (core)

[P-DI-01] Magento\Captcha\Model\Customer\Plugin\AjaxLogin receives $sessionManager (Magento\Framework\Session\SessionManagerInterface) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Framework\Session\SessionManagerInterface\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    47       * @param array $formIds
    48       * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
    49       * @throws \RuntimeException
    50       */
    51      public function __construct(
    52          CaptchaHelper $helper,
>   53          SessionManagerInterface $sessionManager,
    54          JsonFactory $resultJsonFactory,
    55          array $formIds,
    56          ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    57      ) {
    58          $this->helper = $helper;
    59          $this->sessionManager = $sessionManager;
```

## 23. `app/code/Ebizmarts/MailChimp/Model/Plugin/AccountManagement.php:44` (packagist)

[P-DI-01] Ebizmarts\MailChimp\Model\Plugin\AccountManagement receives $checkoutSession (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    38       * @param \Magento\Checkout\Model\Session $checkoutSession
    39       * @param \Magento\Quote\Model\QuoteFactory $quote
    40       * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    41       */
    42      public function __construct(
    43          \Ebizmarts\MailChimp\Helper\Data $helper,
>   44          \Magento\Checkout\Model\Session $checkoutSession,
    45          \Magento\Quote\Model\QuoteFactory $quote,
    46          \Magento\Store\Model\StoreManagerInterface $storeManager
    47      ) {
    48  
    49          $this->_helper  = $helper;
    50          $this->_quote   = $quote;
```

## 24. `vendor/magento/module-config/Observer/Config/Backend/Admin/AfterCustomUrlChangedObserver.php:52` (core)

[P-DI-01] Magento\Config\Observer\Config\Backend\Admin\AfterCustomUrlChangedObserver receives $authSession (Magento\Backend\Model\Auth\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Backend\Model\Auth\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    46       * @param \Magento\Backend\Model\Auth\Session $authSession
    47       * @param \Magento\Framework\App\ResponseInterface $response
    48       */
    49      public function __construct(
    50          \Magento\Backend\Helper\Data $backendData,
    51          \Magento\Framework\Registry $coreRegistry,
>   52          \Magento\Backend\Model\Auth\Session $authSession,
    53          \Magento\Framework\App\ResponseInterface $response
    54      ) {
    55          $this->_backendData = $backendData;
    56          $this->_coreRegistry = $coreRegistry;
    57          $this->_authSession = $authSession;
    58          $this->_response = $response;
```

## 25. `vendor/magento/module-sales/CustomerData/LastOrderedItems.php:101` (core)

[P-DI-01] Magento\Sales\CustomerData\LastOrderedItems receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    95       * @param LoggerInterface $logger
    96       * @param bool|null $skipDeletedProductLogging
    97       */
    98      public function __construct(
    99          CollectionFactoryInterface $orderCollectionFactory,
   100          Config $orderConfig,
>  101          Session $customerSession,
   102          StockRegistryInterface $stockRegistry,
   103          StoreManagerInterface $storeManager,
   104          ProductRepositoryInterface $productRepository,
   105          LoggerInterface $logger,
   106          ?bool $skipDeletedProductLogging = null
   107      ) {
```

## 26. `vendor/magento/module-product-alert/Controller/Add/Price.php:46` (core)

[P-DI-01] Magento\ProductAlert\Controller\Add\Price receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    40       * @param CustomerSession $customerSession
    41       * @param StoreManagerInterface $storeManager
    42       * @param ProductRepositoryInterface $productRepository
    43       */
    44      public function __construct(
    45          Context $context,
>   46          CustomerSession $customerSession,
    47          StoreManagerInterface $storeManager,
    48          ProductRepositoryInterface $productRepository
    49      ) {
    50          $this->storeManager = $storeManager;
    51          $this->productRepository = $productRepository;
    52          parent::__construct($context, $customerSession);
```

## 27. `vendor/magento/module-catalog/Plugin/Framework/App/Action/ContextPlugin.php:43` (core)

[P-DI-01] Magento\Catalog\Plugin\Framework\App\Action\ContextPlugin receives $catalogSession (Magento\Catalog\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Catalog\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    37       * @param ToolbarMemorizer $toolbarMemorizer
    38       * @param CatalogSession $catalogSession
    39       * @param HttpContext $httpContext
    40       */
    41      public function __construct(
    42          ToolbarMemorizer $toolbarMemorizer,
>   43          CatalogSession $catalogSession,
    44          HttpContext $httpContext
    45      ) {
    46          $this->toolbarMemorizer = $toolbarMemorizer;
    47          $this->catalogSession = $catalogSession;
    48          $this->httpContext = $httpContext;
    49      }
```

## 28. `vendor/magento/module-paypal/Controller/Express/AbstractExpress/ShippingOptionsCallback.php:39` (core)

[P-DI-01] Magento\Paypal\Controller\Express\AbstractExpress\ShippingOptionsCallback receives $checkoutSession (Magento\Checkout\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Checkout\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    33       * @param \Magento\Customer\Model\Url $customerUrl
    34       * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    35       */
    36      public function __construct(
    37          \Magento\Framework\App\Action\Context $context,
    38          \Magento\Customer\Model\Session $customerSession,
>   39          \Magento\Checkout\Model\Session $checkoutSession,
    40          \Magento\Sales\Model\OrderFactory $orderFactory,
    41          \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
    42          \Magento\Framework\Session\Generic $paypalSession,
    43          \Magento\Framework\Url\Helper\Data $urlHelper,
    44          \Magento\Customer\Model\Url $customerUrl,
    45          \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
```

## 29. `vendor/magento/module-payment-services-paypal/Block/SmartButtons/Review/Details.php:38` (core)

[P-DI-01] Magento\PaymentServicesPaypal\Block\SmartButtons\Review\Details receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    32       * @param Checkout $checkout
    33       * @param array $data
    34       * @param array $layoutProcessors
    35       */
    36      public function __construct(
    37          Context $context,
>   38          CustomerSession $customerSession,
    39          CheckoutSession $checkoutSession,
    40          ConfigInterface $salesConfig,
    41          Checkout $checkout,
    42          array $data = [],
    43          array $layoutProcessors = []
    44      ) {
```

## 30. `vendor/magento/module-two-factor-auth/Model/UserConfig/HtmlAreaTokenVerifier.php:71` (core)

[P-DI-01] Magento\TwoFactorAuth\Model\UserConfig\HtmlAreaTokenVerifier receives $sessionManager (Magento\Backend\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Backend\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.

```
    65      public function __construct(
    66          RequestInterface $request,
    67          UserConfigTokenManagerInterface $tokenManager,
    68          CookieManagerInterface $cookies,
    69          CookieMetadataFactory $cookieMetadataFactory,
    70          Session $session,
>   71          SessionManager $sessionManager
    72      ) {
    73          $this->request = $request;
    74          $this->tokenManager = $tokenManager;
    75          $this->cookies = $cookies;
    76          $this->cookieMetadataFactory = $cookieMetadataFactory;
    77          $this->session = $session;
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-two-factor-auth/Controller/Adminhtml/U2f/VerifyIdentityPost.php:49` | TP-low | No Proxy configured, but this adminhtml POST controller always reads the admin user from the auth session, which every admin request starts anyway. | re-check |  |
| 2 | `vendor/magento/module-weee/Plugin/Checkout/CustomerData/Cart.php:41` | TP-low | No Proxy configured, but the plugin only runs during customer section load, where the parent Tax cart plugin and the cart section already use the checkout session. | re-check |  |
| 3 | `vendor/magento/module-inventory-admin-ui/Ui/DataProvider/SourceDataProvider.php:90` | TP-low | No Proxy configured, but this admin source form data provider runs only in adminhtml, where the backend session is already started. | re-check |  |
| 4 | `vendor/magento/module-payment-services-paypal/Block/SmartButtons.php:97` | TP-low | No Proxy configured, but the block renders only in frontend actions, where Magento\Customer\Model\App\Action\ContextPlugin already starts the session on every dispatch. |  |  |
| 5 | `vendor/magento/framework/App/Request/DataPersistor.php:25` | TP-low | module-backend di.xml injects the non-proxy Magento\Backend\Model\Session, but DataPersistor is used by admin form controllers and frontend form controllers that already have a started session. |  |  |
| 6 | `vendor/magento/module-checkout/Block/Cart/Grid.php:62` | TP-low | No Proxy configured, but the cart grid block renders only on the non-cacheable cart page, where the session is in use anyway. |  |  |
| 7 | `vendor/magento/module-multishipping/Model/Checkout/Type/Multishipping/State.php:55` | TP-low | No Proxy configured, but the state object is used only by multishipping checkout controllers, which always need the checkout session. |  |  |
| 8 | `app/code/Mageplaza/Smtp/Controller/AbandonedCart/Recover.php:92` | TP-low | No Proxy configured, but this frontend controller sets the recovered quote on the checkout session, and the customer ContextPlugin starts the session on every frontend action. |  |  |
| 9 | `vendor/magento/module-wishlist/Observer/CustomerLogout.php:27` | TP-low | No Proxy configured, but the observer runs only on frontend customer_logout, where the session is necessarily active. |  |  |
| 10 | `vendor/magento/module-catalog/Controller/Category/View.php:136` | TP-low | No Proxy configured, but execute() always writes the visited category to the catalog session and the frontend customer ContextPlugin starts the session anyway. |  |  |
| 11 | `vendor/magento/module-persistent/Observer/CheckExpirePersistentQuoteObserver.php:101` | TP-low | No Proxy configured, but the observer is a frontend controller_action_predispatch observer that reads the checkout session in execute(), and the session is started on every frontend action. |  |  |
| 12 | `vendor/magento/module-newsletter/Controller/Manage.php:28` | TP-low | No Proxy configured, but dispatch() always calls customerSession->authenticate(), so the session is needed on every request to this controller. |  |  |
| 13 | `vendor/magento/module-checkout/Block/Cart.php:46` | TP-low | No Proxy configured, but the cart block renders only on the non-cacheable cart page, where the session is in use anyway. |  |  |
| 14 | `vendor/magento/module-product-alert/Controller/Add/Stock.php:45` | TP-low | No Proxy configured, but this controller always reads the customer id from the session in execute(). |  |  |
| 15 | `vendor/magento/module-checkout/Controller/Action.php:41` | TP-low | No Proxy configured, but this is the base class of checkout controllers, which always need the customer session. |  |  |
| 16 | `vendor/magento/module-two-factor-auth/Controller/Adminhtml/Tfa/VerifyIdentity.php:44` | TP-low | No Proxy configured, but this adminhtml controller reads the auth session in _isAllowed() and execute(), and the admin session is started on every admin request. |  |  |
| 17 | `vendor/magento/module-catalog/Block/Product/View.php:89` | TP-low | No Proxy configured, but the product view block renders only in frontend actions, where the customer ContextPlugin already starts the session on every dispatch. |  |  |
| 18 | `app/code/Mollie/Payment/Service/Mollie/Order/SuccessPageRedirect.php:28` | TP-low | No Proxy configured, but the service is called only from Mollie frontend checkout controllers, which always write the last order data to the checkout session. |  |  |
| 19 | `vendor/magento/module-catalog/Helper/Data.php:213` | TP | module-catalog di.xml proxies only catalogSession; customerSession is not proxied, is read only in getTaxPrice(), and the helper is also built in the graphql area, where nothing else starts a session. |  |  |
| 20 | `vendor/magento/module-tax/Block/Checkout/Shipping/Price.php:32` | TP-low | No Proxy configured, but the checkout shipping price block renders only on non-cacheable checkout and cart pages, where the session is in use anyway. |  |  |
| 21 | `vendor/magento/module-reports/Observer/EventSaver.php:42` | TP | No Proxy configured, and EventSaver is built by CheckoutCartAddProductObserver on sales_quote_item_save_before in webapi_rest and webapi_soap, even when the report is disabled. |  |  |
| 22 | `vendor/magento/module-captcha/Model/Customer/Plugin/AjaxLogin.php:53` | TP-low | No Proxy configured, but the plugin wraps only the customer ajax login controller, where the session is always needed. |  |  |
| 23 | `app/code/Ebizmarts/MailChimp/Model/Plugin/AccountManagement.php:44` | TP-low | No Proxy configured, but the plugin is only instantiated when isEmailAvailable() is called and then reads the quote id from the checkout session whenever email saving is enabled. |  |  |
| 24 | `vendor/magento/module-config/Observer/Config/Backend/Admin/AfterCustomUrlChangedObserver.php:52` | TP-low | No Proxy configured, but the observer runs only in adminhtml after an admin config save where the admin auth session is already started for the request. |  |  |
| 25 | `vendor/magento/module-sales/CustomerData/LastOrderedItems.php:101` | TP-low | No Proxy configured, but this customer data section is built only during section load, which always reads the customer id from the session. |  |  |
| 26 | `vendor/magento/module-product-alert/Controller/Add/Price.php:46` | TP-low | No Proxy configured, but this controller always reads the customer id from the session in execute(). |  |  |
| 27 | `vendor/magento/module-catalog/Plugin/Framework/App/Action/ContextPlugin.php:43` | TP-low | No Proxy configured for catalogSession of this frontend AbstractAction plugin, but the customer ContextPlugin already starts the session on the same dispatch, so only a namespace object is added. |  |  |
| 28 | `vendor/magento/module-paypal/Controller/Express/AbstractExpress/ShippingOptionsCallback.php:39` | TP-low | No Proxy configured; this server-to-server callback loads the quote by id and does not need the session, but as a frontend action the customer ContextPlugin starts the session anyway. |  |  |
| 29 | `vendor/magento/module-payment-services-paypal/Block/SmartButtons/Review/Details.php:38` | TP-low | No Proxy configured, but the review details block renders only on the non-cacheable PayPal review page, where the session is in use anyway. |  |  |
| 30 | `vendor/magento/module-two-factor-auth/Model/UserConfig/HtmlAreaTokenVerifier.php:71` | TP-low | No Proxy configured, but the verifier is used only by adminhtml 2FA controllers and the predispatch observer, where the backend session is already started. |  |  |
