# X-LAY-01: calibration sample

10 of 10 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-product-alert/view/frontend/layout/productalert_unsubscribe_email.xml:11` (core)

[X-LAY-01] Block "unsubscription_form" in handle "productalert_unsubscribe_email" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     5   * All Rights Reserved.
     6   */
     7  -->
     8  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
     9      <body>
    10          <referenceContainer name="content">
>   11              <block class="Magento\Framework\View\Element\Template" name="unsubscription_form" cacheable="false" template="Magento_ProductAlert::email/email.phtml" />
    12          </referenceContainer>
    13      </body>
    14  </page>
```

## 2. `app/code/Ebizmarts/MailChimp/view/frontend/layout/hyva_checkout_index_index.xml:21` (packagist)

[X-LAY-01] Block "mailchimp.pixel.checkout.hyva" in handle "hyva_checkout_index_index" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    15   * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
    16   */
    17  -->
    18  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    19      <body>
    20          <referenceContainer name="before.body.end">
>   21              <block class="Ebizmarts\MailChimp\Block\Pixel\Checkout" name="mailchimp.pixel.checkout.hyva" template="Ebizmarts_MailChimp::pixel/checkout.phtml" ifconfig="mailchimp/pixel/enabled_for_store" cacheable="false"/>
    22          </referenceContainer>
    23      </body>
    24  </page>
```

## 3. `app/code/Mollie/Payment/view/frontend/layout/mollie_savedcards_index.xml:14` (packagist)

[X-LAY-01] Block "mollie.account.saved_cards" in handle "mollie_savedcards_index" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     8      <update handle="customer_account"/>
     9      <body>
    10          <referenceContainer name="content">
    11              <block class="Mollie\Payment\Block\Account\SavedCards"
    12                     name="mollie.account.saved_cards"
    13                     template="Mollie_Payment::account/saved_cards.phtml"
>   14                     cacheable="false"/>
    15          </referenceContainer>
    16      </body>
    17  </page>
```

## 4. `app/code/Magefan/Blog/view/frontend/layout/blog_non_cacheable.xml:13` (packagist)

[X-LAY-01] Block "blog_non_cacheable" in handle "blog_non_cacheable" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     7   * Glory to Ukraine! Glory to the heroes!
     8   */
     9  -->
    10  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    11      <body>
    12          <referenceBlock name="content">
>   13              <block class="Magento\Framework\View\Element\Text" name="blog_non_cacheable" cacheable="false" />
    14          </referenceBlock>
    15      </body>
    16  </page>
```

## 5. `vendor/magento/module-captcha/view/frontend/layout/contact_index_index.xml:11` (core)

[X-LAY-01] Block "captcha" in handle "contact_index_index" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     5   * All Rights Reserved.
     6   */
     7  -->
     8  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
     9      <body>
    10          <referenceContainer name="form.additional.info">
>   11              <block class="Magento\Captcha\Block\Captcha" name="captcha" after="-" cacheable="false">
    12                  <action method="setFormId">
    13                      <argument name="formId" xsi:type="string">contact_us</argument>
    14                  </action>
    15                  <action method="setImgWidth">
    16                      <argument name="width" xsi:type="string">230</argument>
    17                  </action>
```

## 6. `vendor/magento/module-send-friend/view/frontend/layout/sendfriend_product_send.xml:16` (core)

[X-LAY-01] Block "sendfriend.send" in handle "sendfriend_product_send" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    10          <referenceBlock name="page.main.title">
    11              <action method="setPageTitle">
    12                  <argument name="title" translate="true" xsi:type="string">Email to a Friend</argument>
    13              </action>
    14          </referenceBlock>
    15          <referenceContainer name="content">
>   16              <block class="Magento\SendFriend\Block\Send" name="sendfriend.send" cacheable="false" template="Magento_SendFriend::send.phtml">
    17                  <arguments>
    18                      <argument name="button_lock_manager" xsi:type="object">Magento\Framework\View\Element\ButtonLockManager</argument>
    19                  </arguments>
    20                  <container name="form.additional.info" as="form_additional_info"/>
    21              </block>
    22          </referenceContainer>
```

## 7. `app/code/Ebizmarts/MailChimp/view/frontend/layout/mailchimp_cart_loadquote.xml:13` (packagist)

[X-LAY-01] Block "mailchimp.loadquote" in handle "mailchimp_cart_loadquote" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     7   * @author    info@ebizmarts.com
     8   */
     9  -->
    10  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    11      <body>
    12          <referenceContainer name="content">
>   13              <block class="Ebizmarts\MailChimp\Block\Loadquote" name="mailchimp.loadquote" ifconfig="mailchimp/general/active" cacheable="false"/>
    14          </referenceContainer>
    15      </body>
    16  </page>
```

## 8. `vendor/magento/module-shipping/view/frontend/layout/shipping_tracking_popup.xml:11` (core)

[X-LAY-01] Block "shipping.tracking.popup" in handle "shipping_tracking_popup" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     5   * All Rights Reserved.
     6   */
     7  -->
     8  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="empty" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
     9      <body>
    10          <referenceContainer name="content">
>   11              <block class="Magento\Shipping\Block\Tracking\Popup" name="shipping.tracking.popup" template="Magento_Shipping::tracking/popup.phtml" cacheable="false">
    12                  <arguments>
    13                      <argument name="delivery_date_title" xsi:type="object">Magento\Shipping\Block\DataProviders\Tracking\DeliveryDateTitle</argument>
    14                  </arguments>
    15              </block>
    16          </referenceContainer>
    17      </body>
```

## 9. `app/code/Ebizmarts/MailChimp/view/frontend/layout/hyva_checkout_success.xml:16` (packagist)

[X-LAY-01] Block "mailchimp.pixel.order.hyva" in handle "hyva_checkout_success" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    10   * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
    11   */
    12  -->
    13  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    14      <body>
    15          <referenceContainer name="before.body.end">
>   16              <block class="Ebizmarts\MailChimp\Block\Pixel\Order" name="mailchimp.pixel.order.hyva" template="Ebizmarts_MailChimp::pixel/order.phtml" ifconfig="mailchimp/pixel/enabled_for_store" cacheable="false"/>
    17          </referenceContainer>
    18      </body>
    19  </page>
```

## 10. `vendor/magento/module-search/view/frontend/layout/search_term_popular.xml:14` (core)

[X-LAY-01] Block "seo.searchterm" in handle "search_term_popular" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     8  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
     9      <head>
    10          <title>Popular Search Terms</title>
    11      </head>
    12      <body>
    13          <referenceContainer name="content">
>   14              <block class="Magento\Search\Block\Term" name="seo.searchterm" template="Magento_Search::term.phtml" cacheable="false"/>
    15          </referenceContainer>
    16      </body>
    17  </page>
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-product-alert/view/frontend/layout/productalert_unsubscribe_email.xml:11` |  |  | re-check |  |
| 2 | `app/code/Ebizmarts/MailChimp/view/frontend/layout/hyva_checkout_index_index.xml:21` |  |  | re-check |  |
| 3 | `app/code/Mollie/Payment/view/frontend/layout/mollie_savedcards_index.xml:14` |  |  | re-check |  |
| 4 | `app/code/Magefan/Blog/view/frontend/layout/blog_non_cacheable.xml:13` |  |  |  |  |
| 5 | `vendor/magento/module-captcha/view/frontend/layout/contact_index_index.xml:11` |  |  |  |  |
| 6 | `vendor/magento/module-send-friend/view/frontend/layout/sendfriend_product_send.xml:16` |  |  |  |  |
| 7 | `app/code/Ebizmarts/MailChimp/view/frontend/layout/mailchimp_cart_loadquote.xml:13` |  |  |  |  |
| 8 | `vendor/magento/module-shipping/view/frontend/layout/shipping_tracking_popup.xml:11` |  |  |  |  |
| 9 | `app/code/Ebizmarts/MailChimp/view/frontend/layout/hyva_checkout_success.xml:16` |  |  |  |  |
| 10 | `vendor/magento/module-search/view/frontend/layout/search_term_popular.xml:14` |  |  |  |  |
