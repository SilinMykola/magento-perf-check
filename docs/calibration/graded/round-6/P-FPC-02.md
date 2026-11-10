# P-FPC-02: calibration sample

20 of 20 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/TIG/PostNL/ViewModel/FillIn.php:138` (extended)

[P-FPC-02] TIG\PostNL\ViewModel\FillIn reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   132  
   133      /**
   134       * Get the shipping country from the checkout session
   135       */
   136      public function getShippingCountry(): ?string
   137      {
>  138          $shippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();
   139          if ($shippingAddress) {
   140              return $shippingAddress->getCountryId();
   141          }
   142  
   143          return null;
   144      }
```

## 2. `app/code/Mollie/HyvaCheckout/Block/Checkout/Payment/ApplepayAfter.php:48` (extended)

[P-FPC-02] Mollie\HyvaCheckout\Block\Checkout\Payment\ApplepayAfter reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    42  
    43          return (string)($cart->getBillingAddress()->getCountryId() ?: $this->directoryHelper->getDefaultCountry());
    44      }
    45  
    46      public function getCurrencyCode(): string
    47      {
>   48          $cart = $this->checkoutSession->getQuote();
    49  
    50          return (string)$cart->getQuoteCurrencyCode();
    51      }
    52  
    53      /**
    54       * @return list<string>
```

## 3. `app/code/Mageplaza/ShareCart/Block/Cart/Items.php:127` (extended)

[P-FPC-02] Mageplaza\ShareCart\Block\Cart\Items reads Magento\Customer\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   121       *
   122       * @return Quote|null
   123       */
   124      public function getQuote($quote = null)
   125      {
   126          try {
>  127              $quote = $quote ?: $this->checkoutSession->getQuote();
   128          } catch (NoSuchEntityException $e) {
   129              return null;
   130          } catch (LocalizedException $e) {
   131              return null;
   132          }
   133  
```

## 4. `app/code/Dotdigitalgroup/Email/ViewModel/Customer/AccountSubscriptions.php:144` (extended)

[P-FPC-02] Dotdigitalgroup\Email\ViewModel\Customer\AccountSubscriptions reads Magento\Customer\Model\Session::getCustomer() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   138       * Get customer from session.
   139       *
   140       * @return \Magento\Customer\Model\Customer
   141       */
   142      private function getCustomer()
   143      {
>  144          return $this->customerSession->getCustomer();
   145      }
   146  }
```

## 5. `app/code/Mollie/HyvaCheckout/Block/Checkout/Payment/Creditcard.php:67` (extended)

[P-FPC-02] Mollie\HyvaCheckout\Block\Checkout\Payment\Creditcard reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    61      {
    62          if (!$this->isSavedCardsEnabled() || !$this->isCustomerLoggedIn()) {
    63              return [];
    64          }
    65  
    66          /** @var list<array{mandate_id: string, card_label: string, card_number_last4: string, card_expiry_date: string|null, card_holder: string|null}> $mandates */
>   67          $mandates = $this->getCustomerMandates->execute((int)$this->customerSession->getCustomerId());
    68  
    69          return $mandates;
    70      }
    71  
    72      public function getCardLogoUrl(string $cardLabel): string
    73      {
```

## 6. `app/code/Mageplaza/ShareCart/Block/Cart/Button.php:117` (extended)

[P-FPC-02] Mageplaza\ShareCart\Block\Cart\Button reads Magento\Customer\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   111       *
   112       * @return AbstractCollection
   113       */
   114      public function getItems($quote = null)
   115      {
   116          try {
>  117              $quote = $quote ?: $this->checkoutSession->getQuote();
   118          } catch (NoSuchEntityException $e) {
   119              return null;
   120          } catch (LocalizedException $e) {
   121              return null;
   122          }
   123  
```

## 7. `app/code/Mageplaza/Productslider/Block/WishlistProducts.php:112` (extended)

[P-FPC-02] Mageplaza\Productslider\Block\WishlistProducts reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   106      public function getProductCollection()
   107      {
   108          $collection = [];
   109  
   110          if ($this->_customerSession->isLoggedIn()) {
   111              $wishlist = $this->_wishlistCollectionFactory->create()
>  112                  ->addCustomerIdFilter($this->_customerSession->getCustomerId());
   113  
   114              $mpProductIds = $this->getProductParentIds($wishlist);
   115  
   116              $collection = $this->_productCollectionFactory->create()->addIdFilter($mpProductIds);
   117              $collection = $this->_addProductAttributesAndPrices($collection)
   118                  ->addStoreFilter($this->getStoreId())
```

## 8. `app/code/Payone/Core/Block/Amazon/ButtonV2.php:150` (extended)

[P-FPC-02] Payone\Core\Block\Amazon\ButtonV2 reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   144  
   145      /**
   146       * @return mixed
   147       */
   148      public function getQuote()
   149      {
>  150          return $this->checkoutSession->getQuote();
   151      }
   152  
   153      /**
   154       * @return string
   155       */
   156      public function getPublicKeyId()
```

## 9. `app/code/Bold/OrderComment/ViewModel/Comment.php:51` (extended)

[P-FPC-02] Bold\OrderComment\ViewModel\Comment reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    45       * @throws \Magento\Framework\Exception\LocalizedException
    46       * @throws \Magento\Framework\Exception\NoSuchEntityException
    47       */
    48      public function getComment(): ?string
    49      {
    50          if ($this->checkoutSession->getQuoteId()) {
>   51              return $this->checkoutSession->getQuote()->getData(OrderComment::COMMENT_FIELD_NAME);
    52          }
    53          return null;
    54      }
    55  
    56      /**
    57       * Get Max Length validation classes if character restriction is enabled
```

## 10. `app/code/Buckaroo/Magento2/Block/Catalog/Product/View/PaypalExpress.php:236` (extended)

[P-FPC-02] Buckaroo\Magento2\Block\Catalog\Product\View\PaypalExpress reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   230      {
   231          if ($this->checkoutSession === null) {
   232              return null;
   233          }
   234  
   235          try {
>  236              return $this->checkoutSession->getQuote();
   237          } catch (\Exception $e) {
   238              return null;
   239          }
   240      }
   241  
   242      /**
```

## 11. `app/code/Hyva/Theme/ViewModel/CustomerSectionData.php:56` (extended)

[P-FPC-02] Hyva\Theme\ViewModel\CustomerSectionData reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    50       */
    51      public function getDefaultSectionData(): array
    52      {
    53          /*
    54           * Store current session state
    55           */
>   56          $customerId = $this->customerSession->getCustomerId();
    57          $customerGroupId = $this->customerSession->getCustomerGroupId();
    58  
    59          /*
    60           * Ensure no customer specific data is returned by $sectionPool->getSectionsData().
    61           */
    62          $this->customerSession->setCustomerId(null);
```

## 12. `app/code/Payone/Core/Block/RedirectReturn.php:90` (extended)

[P-FPC-02] Payone\Core\Block\RedirectReturn reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    84       * Returns quote object from session
    85       *
    86       * @return Quote
    87       */
    88      public function getQuote()
    89      {
>   90          return $this->checkoutSession->getQuote();
    91      }
    92  
    93      /**
    94       * Get shipping address
    95       *
    96       * @return Quote\Address|false
```

## 13. `app/code/Dotdigitalgroup/Email/ViewModel/Customer/Account/Newsletter.php:89` (extended)

[P-FPC-02] Dotdigitalgroup\Email\ViewModel\Customer\Account\Newsletter reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    83       * @return bool
    84       * @throws LocalizedException
    85       */
    86      public function isSubscribed()
    87      {
    88          $subscriber = $this->backportedSubscriberLoader->loadByCustomer(
>   89              $this->customerSession->getCustomerId(),
    90              $this->storeManager->getWebsite()->getId()
    91          );
    92          if ($subscriber->getId()) {
    93              return $subscriber->isSubscribed();
    94          }
    95  
```

## 14. `app/code/Hyva/Theme/ViewModel/Cart/Items.php:37` (extended)

[P-FPC-02] Hyva\Theme\ViewModel\Cart\Items reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    31  
    32      /**
    33       * return \Magento\Quote\Model\Quote\Item[]
    34       */
    35      public function getCartItems()
    36      {
>   37          return $this->checkoutSession->getQuote()->getAllVisibleItems();
    38      }
    39  
    40      public function getCartItemsSkus(): string
    41      {
    42          $items = $this->getCartItems();
    43          $skus = [];
```

## 15. `app/code/Payone/Core/Block/Onepage/Amazon.php:105` (extended)

[P-FPC-02] Payone\Core\Block\Onepage\Amazon reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    99       * Returns currency
   100       *
   101       * @return string
   102       */
   103      public function getCurrency()
   104      {
>  105          return $this->apiHelper->getCurrencyFromQuote($this->checkoutSession->getQuote());
   106      }
   107  
   108      /**
   109       * Get redirect url of the checkout controller
   110       *
   111       * @return string
```

## 16. `app/code/Mollie/HyvaCheckout/Block/Checkout/Payment/ApplepayAfter.php:41` (extended)

[P-FPC-02] Mollie\HyvaCheckout\Block\Checkout\Payment\ApplepayAfter reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    35      {
    36          return $this->config->applePayIntegrationType() == ApplePayIntegrationType::DIRECT;
    37      }
    38  
    39      public function getCountryId(): string
    40      {
>   41          $cart = $this->checkoutSession->getQuote();
    42  
    43          return (string)($cart->getBillingAddress()->getCountryId() ?: $this->directoryHelper->getDefaultCountry());
    44      }
    45  
    46      public function getCurrencyCode(): string
    47      {
```

## 17. `app/code/Payone/Core/Block/Payments/Management.php:84` (extended)

[P-FPC-02] Payone\Core\Block\Payments\Management reads Magento\Customer\Model\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    78       * Load all saved payment data of the current customer
    79       *
    80       * @return array
    81       */
    82      public function getSavedPaymentData()
    83      {
>   84          $iCustomerId = $this->customerSession->getCustomerId();
    85          return $this->savedPaymentData->getSavedPaymentData($iCustomerId, PayoneConfig::METHOD_CREDITCARD, false);
    86      }
    87  
    88      /**
    89       * Generate action url
    90       *
```

## 18. `app/code/Yireo/GoogleTagManager2/DataLayer/Event/AddShippingInfo.php:52` (extended)

[P-FPC-02] Yireo\GoogleTagManager2\DataLayer\Event\AddShippingInfo reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
    46      {
    47          if (false === $this->checkoutSession->hasQuote()) {
    48              return [];
    49          }
    50  
    51          try {
>   52              $quote = $this->checkoutSession->getQuote();
    53          } catch (NoSuchEntityException|LocalizedException $e) {
    54              return [];
    55          }
    56  
    57          $shippingMethod = $this->getShippingMethodFromQuote($quote);
    58          if (empty($shippingMethod)) {
```

## 19. `app/code/Mageplaza/ShareCart/Block/Cart/Button.php:185` (extended)

[P-FPC-02] Mageplaza\ShareCart\Block\Cart\Button reads Magento\Customer\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   179       *
   180       * @return Quote|null
   181       */
   182      public function getQuote($quote = null)
   183      {
   184          try {
>  185              $quote = $quote ?: $this->checkoutSession->getQuote();
   186          } catch (NoSuchEntityException $e) {
   187              return null;
   188          } catch (LocalizedException $e) {
   189              return null;
   190          }
   191  
```

## 20. `app/code/Payone/Core/Block/Onepage/Review.php:142` (extended)

[P-FPC-02] Payone\Core\Block\Onepage\Review reads Magento\Checkout\Model\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.

```
   136       *
   137       * @return \Magento\Quote\Model\Quote
   138       */
   139      protected function getQuote()
   140      {
   141          if (!$this->_quote) {
>  142              $this->_quote = $this->checkoutSession->getQuote();
   143          }
   144          return $this->_quote;
   145      }
   146  
   147      /**
   148       * Return quote billing address
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/TIG/PostNL/ViewModel/FillIn.php:138` | TP | On Luma the FillIn view model renders in minicart.addons from default.xml on every page, so on cacheable pages getShippingCountry() reads an emptied session and the cached button has no shipping country for anyone. |  |  |
| 2 | `app/code/Mollie/HyvaCheckout/Block/Checkout/Payment/ApplepayAfter.php:48` | FP | ApplepayAfter is placed only in the hyva_checkout layout of the checkout page, which is not page-cached. |  |  |
| 3 | `app/code/Mageplaza/ShareCart/Block/Cart/Items.php:127` | FP | Block\Cart\Items is not placed by any layout or code in the corpus; the download layout uses Block\Cart\Button with items.phtml and cacheable=false. |  |  |
| 4 | `app/code/Dotdigitalgroup/Email/ViewModel/Customer/AccountSubscriptions.php:144` | FP | AccountSubscriptions is used only on connector_customer_index, a customer account page whose block is cacheable=false. |  |  |
| 5 | `app/code/Mollie/HyvaCheckout/Block/Checkout/Payment/Creditcard.php:67` | FP | The Creditcard block is a Hyvä Checkout payment component, rendered only on the checkout page, which is not page-cached. |  |  |
| 6 | `app/code/Mageplaza/ShareCart/Block/Cart/Button.php:117` | FP | getItems() is called only from the cart button templates on the uncacheable cart page (Luma and Hyvä) and from the cacheable=false download layout; the Hyvä minicart template does not call it. |  |  |
| 7 | `app/code/Mageplaza/Productslider/Block/WishlistProducts.php:112` | TP | The AddBlock observer and the slider widget render WishlistProducts on cacheable pages such as allpage, category and CMS pages, where the depersonalized session is never logged in, so the wishlist slider is always empty. |  |  |
| 8 | `app/code/Payone/Core/Block/Amazon/ButtonV2.php:150` | FP | AddAmazonPayButton skips the product page containers, so ButtonV2 renders only in the cart page shortcut container and the minicart shortcut built during section load. |  |  |
| 9 | `app/code/Bold/OrderComment/ViewModel/Comment.php:51` | FP | The order comment view model is placed only on checkout_cart_index, which is uncacheable. |  |  |
| 10 | `app/code/Buckaroo/Magento2/Block/Catalog/Product/View/PaypalExpress.php:236` | FP | On the cacheable product page the template calls only getConfig(), which reads no quote; getCartConfig() and its getQuote() run only in the cart page shortcut container that AddExpressShortcuts fills. |  |  |
| 11 | `app/code/Hyva/Theme/ViewModel/CustomerSectionData.php:56` | FP | getDefaultSectionData() reads the customer ID only to clear it and restore it afterwards, so the default section data it renders carries no customer data. |  |  |
| 12 | `app/code/Payone/Core/Block/RedirectReturn.php:90` | FP | RedirectReturn is placed only on checkout_index_index, which is not page-cached. |  |  |
| 13 | `app/code/Dotdigitalgroup/Email/ViewModel/Customer/Account/Newsletter.php:89` | FP | The Newsletter view model is used only inside the cacheable=false block on connector_customer_index, a customer account page. |  |  |
| 14 | `app/code/Hyva/Theme/ViewModel/Cart/Items.php:37` | FP | getCartItems() is called only for the crosssell slider, which the Hyvä theme places only on the uncacheable cart page. |  |  |
| 15 | `app/code/Payone/Core/Block/Onepage/Amazon.php:105` | FP | Onepage\Amazon is placed only on payone_onepage_amazon with cacheable=false. |  |  |
| 16 | `app/code/Mollie/HyvaCheckout/Block/Checkout/Payment/ApplepayAfter.php:41` | FP | ApplepayAfter is placed only in the hyva_checkout layout of the checkout page, which is not page-cached. |  |  |
| 17 | `app/code/Payone/Core/Block/Payments/Management.php:84` | FP | Payments\Management is placed only on payone_payments_management, a customer account page with cacheable=false. |  |  |
| 18 | `app/code/Yireo/GoogleTagManager2/DataLayer/Event/AddShippingInfo.php:52` | FP | AddShippingInfo is used only by a webapi_rest plugin after shipping information is saved, never while a page renders. |  |  |
| 19 | `app/code/Mageplaza/ShareCart/Block/Cart/Button.php:185` | FP | getQuote() is called only through getItemsCount() in the cart button templates on the uncacheable cart page (Luma and Hyvä); the Hyvä minicart template does not call it. |  |  |
| 20 | `app/code/Payone/Core/Block/Onepage/Review.php:142` | FP | Onepage\Review is placed only on payone_onepage_review and payone_amazon_loadreview, both with cacheable=false. |  |  |
