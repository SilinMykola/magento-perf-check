# P-FPC-02: calibration sample

30 of 55 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Adyen/Payment/Block/Form/Moto.php:175` (packagist)

[P-FPC-02] Adyen\Payment\Block\Form\Moto reads Magento\Backend\Model\Session\Quote::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   169       */
   170      public function getAmount()
   171      {
   172          try {
   173              $quoteData = $this->backendSession->getQuote()->getData();
   174              $grandTotal = $quoteData['grand_total'];
>  175              $currenyCode = $this->backendSession->getQuote()->getCurrency()->getQuoteCurrencyCode();
   176              $value = $this->adyenHelper->formatAmount($grandTotal, $currenyCode);
   177  
   178              $amount = array("value" => $value, "currency" => $currenyCode);
   179  
   180              return json_encode($amount);
   181          } catch (\Throwable $e) {
```

## 2. `app/code/Adyen/Payment/Block/Form/PosCloud.php:115` (packagist)

[P-FPC-02] Adyen\Payment\Block\Form\PosCloud reads Magento\Backend\Model\Session\Quote::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   109          $serialisedInstallments = $this->configHelper->getAdyenPosCloudConfigData('installments');
   110          $formattedInstallments = [];
   111  
   112          if (isset($serialisedInstallments)) {
   113              $installments = $this->serializer->unserialize($serialisedInstallments);
   114  
>  115              $amount = $this->backendSession->getQuote()->getGrandTotal();
   116              $currencyCode = $this->backendSession->getQuote()->getCurrency()->getQuoteCurrencyCode();
   117              $precision = $this->adyenHelper->decimalNumbers($currencyCode);
   118  
   119              $formattedInstallments = $this->posHelper->getFormattedInstallments(
   120                  $installments,
   121                  $amount,
```

## 3. `app/code/Adyen/Payment/Block/Form/Moto.php:197` (packagist)

[P-FPC-02] Adyen\Payment\Block\Form\Moto reads Magento\Backend\Model\Session\Quote::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   191       * Returns the country code required for checkout component
   192       *
   193       * @return string
   194       */
   195      public function getCountryId(): string
   196      {
>  197          return $this->backendSession->getQuote()->getBillingAddress()->getCountryId();
   198      }
   199  
   200      /**
   201       * @return array
   202       */
   203      public function getMotoMerchantAccounts() : array
```

## 4. `vendor/magento/module-checkout/Block/Onepage/Link.php:58` (core)

[P-FPC-02] Magento\Checkout\Block\Onepage\Link reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    52  
    53      /**
    54       * @return bool
    55       */
    56      public function isDisabled()
    57      {
>   58          return !$this->_checkoutSession->getQuote()->validateMinimumAmount();
    59      }
    60  
    61      /**
    62       * @return bool
    63       */
    64      public function isPossibleOnepageCheckout()
```

## 5. `vendor/magento/module-payment-services-paypal/Block/SmartButtons.php:165` (core)

[P-FPC-02] Magento\PaymentServicesPaypal\Block\SmartButtons reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   159              'placeOrderUrl' => $this->getUrl('paymentservicespaypal/smartbuttons/placeorder/'),
   160              'getOrderDetailsUrl' => $this->getUrl('paymentservicespaypal/order/getcurrentorder'),
   161              'threeDSMode' => $this->config->getGooglePayThreeDS() !== "0"
   162                  ? $this->config->getGooglePayThreeDS()
   163                  : false,
   164              'styles' => $this->getStyles(),
>  165              'isVirtual' => $this->session->getQuote()->isVirtual(),
   166              'googlePayMode' => $this->config->getGooglePayMode(),
   167              'pageType' => $this->pageType,
   168              'completeOrderUrl' => $baseUrl . 'rest/V1/payment-order/completeOrder',
   169              'appSwitchWhenAvailable' => $this->config->getAppSwitch($storeId)
   170          ];
   171      }
```

## 6. `vendor/magento/module-catalog/Block/Product/View.php:347` (core)

[P-FPC-02] Magento\Catalog\Block\Product\View reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   341       * Retrieve customer data object
   342       *
   343       * @return int
   344       */
   345      protected function getCustomerId()
   346      {
>  347          return $this->customerSession->getCustomerId();
   348      }
   349  }
```

## 7. `vendor/magento/module-multishipping/Block/Checkout/Addresses.php:166` (core)

[P-FPC-02] Magento\Multishipping\Block\Checkout\Addresses reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   160       * Retrieve active customer ID
   161       *
   162       * @return int|null
   163       */
   164      public function getCustomerId()
   165      {
>  166          return $this->getCheckout()->getCustomerSession()->getCustomerId();
   167      }
   168  
   169      /**
   170       * @param mixed $item
   171       * @return string
   172       */
```

## 8. `vendor/magento/module-payment-services-paypal/Block/SmartButtons.php:296` (core)

[P-FPC-02] Magento\PaymentServicesPaypal\Block\SmartButtons reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   290       *
   291       * @deprecated as It is no longer being used
   292       */
   293      public function doesQuoteExist(): bool
   294      {
   295          try {
>  296              return $this->checkoutSession->getQuote()->getId() != null;
   297          } catch (NoSuchEntityException $e) {
   298              return false;
   299          }
   300      }
   301  }
```

## 9. `vendor/magento/module-paypal/Block/Iframe.php:107` (core)

[P-FPC-02] Magento\Paypal\Block\Iframe reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   101       *
   102       * @return void
   103       */
   104      protected function _construct()
   105      {
   106          parent::_construct();
>  107          $paymentCode = $this->_getCheckout()->getQuote()->getPayment()->getMethod();
   108          if (in_array($paymentCode, $this->_hssHelper->getHssMethods())) {
   109              $this->_paymentMethodCode = $paymentCode;
   110              $templatePath = $paymentCode !== null ? str_replace('_', '', $paymentCode) : '';
   111              $templateFile = "Magento_Paypal::{$templatePath}/iframe.phtml";
   112              $directory = $this->readFactory->create($this->reader->getModuleDir('', 'Magento_Paypal'));
   113              $file = $this->resolver->getTemplateFileName($templateFile, ['module' => 'Magento_Paypal']);
```

## 10. `vendor/magento/module-payment-services-paypal/Block/SmartButtonsCart.php:68` (core)

[P-FPC-02] Magento\PaymentServicesPaypal\Block\SmartButtonsCart reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    62      /**
    63       * @inheritDoc
    64       */
    65      public function isGooglePayLocationEnabled(string $location): bool
    66      {
    67          return parent::isGooglePayLocationEnabled($location)
>   68              && (bool)(float)$this->session->getQuote()->getGrandTotal();
    69      }
    70  }
```

## 11. `app/code/Algolia/AlgoliaSearch/Block/Cart/Recommend.php:38` (packagist)

[P-FPC-02] Algolia\AlgoliaSearch\Block\Cart\Recommend reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    32       *
    33       * @return array
    34       */
    35      public function getAllCartItems()
    36      {
    37          $cartItems = [];
>   38          $itemCollection = $this->checkoutSession->getQuote()->getAllVisibleItems();
    39          foreach ($itemCollection as $item) {
    40              $cartItems[] = $item->getProductId();
    41          }
    42  
    43          return array_unique($cartItems);
    44      }
```

## 12. `vendor/magento/module-paypal/Block/Express/InContext/Minicart/Button.php:110` (core)

[P-FPC-02] Magento\Paypal\Block\Express\InContext\Minicart\Button reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   104       * Check is Paypal In-Context Express Checkout button should render in cart/mini-cart
   105       *
   106       * @return bool
   107       */
   108      protected function shouldRender()
   109      {
>  110          return $this->payment->isAvailable($this->session->getQuote())
   111              && $this->isMiniCart
   112              && $this->isInContext()
   113              && $this->isVisibleOnCart();
   114      }
   115  
   116      /**
```

## 13. `vendor/magento/module-sales/Block/Order/History.php:99` (core)

[P-FPC-02] Magento\Sales\Block\Order\History reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    93       * Get customer orders
    94       *
    95       * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
    96       */
    97      public function getOrders()
    98      {
>   99          if (!($customerId = $this->_customerSession->getCustomerId())) {
   100              return false;
   101          }
   102          if (!$this->orders) {
   103              $this->orders = $this->getOrderCollectionFactory()->create($customerId)->addFieldToSelect(
   104                  '*'
   105              )->addFieldToFilter(
```

## 14. `vendor/magento/module-paypal/Block/Express/Review/Shipping.php:171` (core)

[P-FPC-02] Magento\Paypal\Block\Express\Review\Shipping reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   165       *
   166       * @return Quote
   167       */
   168      public function getQuote()
   169      {
   170          if (empty($this->quote)) {
>  171              $this->quote = $this->checkoutSession->getQuote();
   172          }
   173          return $this->quote;
   174      }
   175  
   176      /**
   177       * @return bool
```

## 15. `vendor/magento/module-payment/Block/Transparent/Form.php:86` (core)

[P-FPC-02] Magento\Payment\Block\Transparent\Form reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    80       * @return void
    81       */
    82      protected function initializeMethod()
    83      {
    84          $this->setData(
    85              'method',
>   86              $this->checkoutSession
    87                  ->getQuote()
    88                  ->getPayment()
    89                  ->getMethodInstance()
    90          );
    91      }
    92  
```

## 16. `vendor/magento/module-paypal/Block/Checkout/Onepage/Success/BillingAgreement.php:59` (core)

[P-FPC-02] Magento\Paypal\Block\Checkout\Onepage\Success\BillingAgreement reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    53       *
    54       * @return string
    55       */
    56      protected function _toHtml()
    57      {
    58          $agreementReferenceId = $this->_checkoutSession->getLastBillingAgreementReferenceId();
>   59          $customerId = $this->_customerSession->getCustomerId();
    60          if (!$agreementReferenceId || !$customerId) {
    61              return '';
    62          }
    63          $agreement = $this->_agreementFactory->create()->load($agreementReferenceId, 'reference_id');
    64          if ($agreement->getId() && $customerId == $agreement->getCustomerId()) {
    65              $this->addData(
```

## 17. `app/code/Mollie/Payment/Block/Applepay/Shortcut/Button.php:47` (packagist)

[P-FPC-02] Mollie\Payment\Block\Applepay\Shortcut\Button reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    41  
    42      /**
    43       * @return float|null
    44       */
    45      public function getBaseGrandTotal(): ?float
    46      {
>   47          return (float)$this->checkoutSession->getQuote()->getBaseGrandTotal();
    48      }
    49  
    50      /**
    51       * @throws NoSuchEntityException
    52       * @return string
    53       */
```

## 18. `vendor/magento/module-customer/Block/Address/Edit.php:151` (core)

[P-FPC-02] Magento\Customer\Block\Address\Edit reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   145      private function initAddressObject()
   146      {
   147          // Init address object
   148          if ($addressId = $this->getRequest()->getParam('id')) {
   149              try {
   150                  $this->_address = $this->_addressRepository->getById($addressId);
>  151                  if ($this->_address->getCustomerId() != $this->_customerSession->getCustomerId()) {
   152                      $this->_address = null;
   153                  }
   154              } catch (NoSuchEntityException $e) {
   155                  $this->_address = null;
   156              }
   157          }
```

## 19. `app/code/Adyen/Payment/Block/Form/PosCloud.php:116` (packagist)

[P-FPC-02] Adyen\Payment\Block\Form\PosCloud reads Magento\Backend\Model\Session\Quote::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   110          $formattedInstallments = [];
   111  
   112          if (isset($serialisedInstallments)) {
   113              $installments = $this->serializer->unserialize($serialisedInstallments);
   114  
   115              $amount = $this->backendSession->getQuote()->getGrandTotal();
>  116              $currencyCode = $this->backendSession->getQuote()->getCurrency()->getQuoteCurrencyCode();
   117              $precision = $this->adyenHelper->decimalNumbers($currencyCode);
   118  
   119              $formattedInstallments = $this->posHelper->getFormattedInstallments(
   120                  $installments,
   121                  $amount,
   122                  $currencyCode,
```

## 20. `vendor/magento/module-paypal/ViewModel/PaypalFundingSourceDataProvider.php:41` (core)

[P-FPC-02] Magento\Paypal\ViewModel\PaypalFundingSourceDataProvider reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    35       * Return paypal funding source
    36       *
    37       * @return string|null
    38       */
    39      public function getPaypalFundingSource()
    40      {
>   41          $quote = $this->checkoutSession->getQuote();
    42          if ($quote->getPayment()->getAdditionalInformation(Checkout::PAYMENT_INFO_FUNDING_SOURCE)) {
    43              return ucfirst($quote->getPayment()->getAdditionalInformation(
    44                  Checkout::PAYMENT_INFO_FUNDING_SOURCE
    45              ));
    46          }
    47          return null;
```

## 21. `vendor/magento/module-paypal/Block/Express/Review/Shipping.php:158` (core)

[P-FPC-02] Magento\Paypal\Block\Express\Review\Shipping reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   152       *
   153       * @return \Magento\Customer\Api\Data\CustomerInterface
   154       */
   155      protected function _getCustomer()
   156      {
   157          if (empty($this->customer)) {
>  158              $this->customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
   159          }
   160          return $this->customer;
   161      }
   162  
   163      /**
   164       * Retrieve sales quote model
```

## 22. `vendor/magento/module-payment/Block/Transparent/Form.php:68` (core)

[P-FPC-02] Magento\Payment\Block\Transparent\Form reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    62       * Checks whether block should be rendered basing on TransparentInterface presence in checkout session
    63       *
    64       * @return bool
    65       */
    66      protected function shouldRender()
    67      {
>   68          $quote = $this->checkoutSession->getQuote();
    69          if ($quote) {
    70              $payment = $quote->getPayment();
    71              return $payment && $payment->getMethodInstance() instanceof TransparentInterface;
    72          }
    73  
    74          return false;
```

## 23. `vendor/magento/module-customer/Block/Account/Dashboard.php:82` (core)

[P-FPC-02] Magento\Customer\Block\Account\Dashboard reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    76       * Return the Customer given the customer Id stored in the session.
    77       *
    78       * @return CustomerInterface
    79       */
    80      public function getCustomer()
    81      {
>   82          return $this->customerRepository->getById($this->customerSession->getCustomerId());
    83      }
    84  
    85      /**
    86       * Retrieve the Url for editing the customer's account.
    87       *
    88       * @return string
```

## 24. `vendor/magento/module-payment-services-paypal/Block/SmartButtonsCart.php:49` (core)

[P-FPC-02] Magento\PaymentServicesPaypal\Block\SmartButtonsCart reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    43  
    44      /**
    45       * @inheritDoc
    46       */
    47      public function isLocationEnabled(string $location): bool
    48      {
>   49          return parent::isEnabled() && (bool)(float)$this->session->getQuote()->getGrandTotal()
    50              && parent::isLocationEnabled($location);
    51      }
    52  
    53      /**
    54       * @inheritDoc
    55       */
```

## 25. `app/code/Adyen/Payment/Block/Form/PosCloud.php:141` (packagist)

[P-FPC-02] Adyen\Payment\Block\Form\PosCloud reads Magento\Backend\Model\Session\Quote::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   135      public function hasFundingSource(): bool
   136      {
   137          if ($this->backendSession->getQuote()->getBillingAddress() === null) {
   138              return false;
   139          }
   140  
>  141          $countryId = $this->backendSession->getQuote()->getBillingAddress()->getCountryId();
   142          $currencyCode = $this->backendSession->getQuote()->getQuoteCurrencyCode();
   143  
   144          $allowedCurrenciesByCountry = [
   145              'BR' => 'BRL',
   146              'MX' => 'MXN'
   147          ];
```

## 26. `vendor/magento/module-payment-services-paypal/Block/SmartButtonsCart.php:59` (core)

[P-FPC-02] Magento\PaymentServicesPaypal\Block\SmartButtonsCart reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    53      /**
    54       * @inheritDoc
    55       */
    56      public function isApplePayLocationEnabled(string $location): bool
    57      {
    58          return parent::isApplePayLocationEnabled($location)
>   59              && (bool)(float)$this->session->getQuote()->getGrandTotal();
    60      }
    61  
    62      /**
    63       * @inheritDoc
    64       */
    65      public function isGooglePayLocationEnabled(string $location): bool
```

## 27. `vendor/magento/module-paypal/Block/Billing/Agreements.php:96` (core)

[P-FPC-02] Magento\Paypal\Block\Billing\Agreements reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    90       */
    91      public function getBillingAgreements()
    92      {
    93          if ($this->_billingAgreements === null) {
    94              $this->_billingAgreements = $this->_agreementCollection->create()->addFieldToFilter(
    95                  'customer_id',
>   96                  $this->_customerSession->getCustomerId()
    97              )->setOrder(
    98                  'agreement_id',
    99                  'desc'
   100              );
   101          }
   102          return $this->_billingAgreements;
```

## 28. `vendor/magento/module-checkout/Block/Cart/AbstractCart.php:123` (core)

[P-FPC-02] Magento\Checkout\Block\Cart\AbstractCart reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   117       *
   118       * @return Quote
   119       */
   120      public function getQuote()
   121      {
   122          if (null === $this->_quote) {
>  123              $this->_quote = $this->_checkoutSession->getQuote();
   124  
   125              if ($this->_quote->getId() && $this->shouldRecollectTotals()) {
   126                  $existingItemsCount = $this->_quote->getItemsCount();
   127                  $existingItemsQty = $this->_quote->getItemsQty();
   128                  $existingVirtualItemsQty = $this->_quote->getData('virtual_items_qty');
   129                  $this->_quote->setData('totals_collected_flag', false);
```

## 29. `vendor/magento/module-paypal/Block/Express/InContext/Minicart/SmartButton.php:169` (core)

[P-FPC-02] Magento\Paypal\Block\Express\InContext\Minicart\SmartButton reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   163      {
   164          $config = [];
   165          $quoteId = $this->getQuoteId();
   166          if (!empty($quoteId)) {
   167              $clientConfig = [
   168                  'quoteId' => $quoteId,
>  169                  'customerId' => $this->session->getQuote()->getCustomerId(),
   170                  'button' => 1,
   171                  'getTokenUrl' => $this->urlBuilder->getUrl(
   172                      'paypal/express/getTokenData',
   173                      ['_secure' => $this->getRequest()->isSecure()]
   174                  ),
   175                  'onAuthorizeUrl' => $this->urlBuilder->getUrl(
```

## 30. `app/code/Adyen/Payment/Block/Form/PosCloud.php:142` (packagist)

[P-FPC-02] Adyen\Payment\Block\Form\PosCloud reads Magento\Backend\Model\Session\Quote::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   136      {
   137          if ($this->backendSession->getQuote()->getBillingAddress() === null) {
   138              return false;
   139          }
   140  
   141          $countryId = $this->backendSession->getQuote()->getBillingAddress()->getCountryId();
>  142          $currencyCode = $this->backendSession->getQuote()->getQuoteCurrencyCode();
   143  
   144          $allowedCurrenciesByCountry = [
   145              'BR' => 'BRL',
   146              'MX' => 'MXN'
   147          ];
   148  
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Adyen/Payment/Block/Form/Moto.php:175` |  |  | re-check |  |
| 2 | `app/code/Adyen/Payment/Block/Form/PosCloud.php:115` |  |  | re-check |  |
| 3 | `app/code/Adyen/Payment/Block/Form/Moto.php:197` |  |  | re-check |  |
| 4 | `vendor/magento/module-checkout/Block/Onepage/Link.php:58` |  |  |  |  |
| 5 | `vendor/magento/module-payment-services-paypal/Block/SmartButtons.php:165` |  |  |  |  |
| 6 | `vendor/magento/module-catalog/Block/Product/View.php:347` |  |  |  |  |
| 7 | `vendor/magento/module-multishipping/Block/Checkout/Addresses.php:166` |  |  |  |  |
| 8 | `vendor/magento/module-payment-services-paypal/Block/SmartButtons.php:296` |  |  |  |  |
| 9 | `vendor/magento/module-paypal/Block/Iframe.php:107` |  |  |  |  |
| 10 | `vendor/magento/module-payment-services-paypal/Block/SmartButtonsCart.php:68` |  |  |  |  |
| 11 | `app/code/Algolia/AlgoliaSearch/Block/Cart/Recommend.php:38` |  |  |  |  |
| 12 | `vendor/magento/module-paypal/Block/Express/InContext/Minicart/Button.php:110` |  |  |  |  |
| 13 | `vendor/magento/module-sales/Block/Order/History.php:99` |  |  |  |  |
| 14 | `vendor/magento/module-paypal/Block/Express/Review/Shipping.php:171` |  |  |  |  |
| 15 | `vendor/magento/module-payment/Block/Transparent/Form.php:86` |  |  |  |  |
| 16 | `vendor/magento/module-paypal/Block/Checkout/Onepage/Success/BillingAgreement.php:59` |  |  |  |  |
| 17 | `app/code/Mollie/Payment/Block/Applepay/Shortcut/Button.php:47` |  |  |  |  |
| 18 | `vendor/magento/module-customer/Block/Address/Edit.php:151` |  |  |  |  |
| 19 | `app/code/Adyen/Payment/Block/Form/PosCloud.php:116` |  |  |  |  |
| 20 | `vendor/magento/module-paypal/ViewModel/PaypalFundingSourceDataProvider.php:41` |  |  |  |  |
| 21 | `vendor/magento/module-paypal/Block/Express/Review/Shipping.php:158` |  |  |  |  |
| 22 | `vendor/magento/module-payment/Block/Transparent/Form.php:68` |  |  |  |  |
| 23 | `vendor/magento/module-customer/Block/Account/Dashboard.php:82` |  |  |  |  |
| 24 | `vendor/magento/module-payment-services-paypal/Block/SmartButtonsCart.php:49` |  |  |  |  |
| 25 | `app/code/Adyen/Payment/Block/Form/PosCloud.php:141` |  |  |  |  |
| 26 | `vendor/magento/module-payment-services-paypal/Block/SmartButtonsCart.php:59` |  |  |  |  |
| 27 | `vendor/magento/module-paypal/Block/Billing/Agreements.php:96` |  |  |  |  |
| 28 | `vendor/magento/module-checkout/Block/Cart/AbstractCart.php:123` |  |  |  |  |
| 29 | `vendor/magento/module-paypal/Block/Express/InContext/Minicart/SmartButton.php:169` |  |  |  |  |
| 30 | `app/code/Adyen/Payment/Block/Form/PosCloud.php:142` |  |  |  |  |
