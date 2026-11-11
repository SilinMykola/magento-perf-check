# X-LAY-01: calibration sample

30 of 47 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_report_bestsellers.xml:7` (extended)

[X-LAY-01] Block "ddg.report.bestsellers.container" in handle "connector_report_bestsellers" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Bestsellers" name="ddg.report.bestsellers.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 2. `app/code/Mageplaza/Blog/view/frontend/layout/mpblog_post_history.xml:27` (extended)

[X-LAY-01] Block "mpblog.author.post" in handle "mpblog_post_history" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    21   */
    22  -->
    23  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    24      <update handle="mpblog_author"/>
    25      <body>
    26          <referenceContainer name="content">
>   27              <block class="Mageplaza\Blog\Block\Post\AuthorPost" name="mpblog.author.post" as="mpblog_post" template="Mageplaza_Blog::post/author_post.phtml" cacheable="false">
    28                  <block class="Mageplaza\Blog\Block\Post\ManagePost" name="mpblog.manage.post" as="mpblog_manage_post" template="Mageplaza_Blog::post/manage_post.phtml">
    29                      <block name="field_additional"/>
    30                  </block>
    31              </block>
    32          </referenceContainer>
    33      </body>
```

## 3. `app/design/frontend/Hyva/default/Magento_Search/layout/search_term_popular.xml:14` (extended)

[X-LAY-01] Block "seo.searchterm" in handle "search_term_popular" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     8  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
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

## 4. `app/code/Paynl/Payment/view/frontend/layout/paynl_checkout_fastcheckoutfallback.xml:20` (extended)

[X-LAY-01] Block "pay_finish" in handle "paynl_checkout_fastcheckoutfallback" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    14              <block class="Paynl\Payment\Block\Page\FastCheckoutFallback" name="pay_finish.button" template="Magento_Checkout::button.phtml"/>
    15              <action method="setPageTitle">
    16                  <argument translate="true" name="title" xsi:type="string">Fast Checkout Shipping</argument>
    17              </action>
    18          </referenceBlock>
    19           <referenceContainer name="content">
>   20              <block class="Paynl\Payment\Block\Page\FastCheckoutFallback" name="pay_finish" template="Paynl_Payment::pay_fast_checkout_fallback.phtml" cacheable="false">
    21                  <container name="order.success.additional.info" label="Order Success Additional Info"/>
    22              </block>
    23          </referenceContainer>
    24      </body>
    25  </page>
```

## 5. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_quoteproducts_related.xml:7` (extended)

[X-LAY-01] Block "ddg.quote.related.container" in handle "connector_quoteproducts_related" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Quoteproducts" name="ddg.quote.related.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 6. `app/code/Payone/Core/view/frontend/layout/payone_onepage_debit.xml:33` (extended)

[X-LAY-01] Block "payone_onepage_debit" in handle "payone_onepage_debit" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    27  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    28      <head>
    29          <title>SEPA debit note</title>
    30          <css src="Payone_Core::css/debit.css"/>
    31      </head>
    32      <referenceBlock name="content">
>   33          <block class="Payone\Core\Block\Onepage\Debit" name="payone_onepage_debit" template="onepage/debit.phtml" cacheable="false"/>
    34      </referenceBlock>
    35  </page>
```

## 7. `app/code/Paynl/Payment/view/frontend/layout/paynl_order_invoice.xml:20` (extended)

[X-LAY-01] Block "pay_finish" in handle "paynl_order_invoice" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    14              <block class="Paynl\Payment\Block\Page\Finish" name="pay_finish.button" template="Magento_Checkout::button.phtml"/>
    15              <action method="setPageTitle">
    16                  <argument translate="true" name="title" xsi:type="string">Thank you for your purchase! Your invoice will be sent to you via email.</argument>
    17              </action>
    18          </referenceBlock>
    19           <referenceContainer name="content">
>   20              <block class="Paynl\Payment\Block\Page\Finish" name="pay_finish" template="Paynl_Payment::pay_finish.phtml" cacheable="false">
    21                  <container name="order.success.additional.info" label="Order Success Additional Info"/>
    22              </block>
    23          </referenceContainer>
    24      </body>
    25  </page>
```

## 8. `app/code/Mageplaza/Blog/view/frontend/layout/mpblog_post_preview.xml:30` (extended)

[X-LAY-01] Block "mpblog.post.view" in handle "mpblog_post_preview" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    24      <update handle="mpblog_design"/>
    25      <body>
    26          <referenceBlock name="head.additional">
    27              <block class="Mageplaza\Blog\Block\Post\View" name="mpblog.post.head" template="Mageplaza_Blog::post/head.phtml"/>
    28          </referenceBlock>
    29          <referenceContainer name="content">
>   30              <block class="Mageplaza\Blog\Block\Post\View" name="mpblog.post.view" template="Mageplaza_Blog::post/view.phtml" cacheable="false">
    31                  <block class="Mageplaza\Blog\Block\Post\RelatedProduct" name="related.category.products" as="related_products" template="Mageplaza_Blog::product/list.phtml"/>
    32              </block>
    33          </referenceContainer>
    34      </body>
    35  </page>
```

## 9. `app/code/Payone/Core/view/frontend/layout/payone_onepage_review.xml:45` (extended)

[X-LAY-01] Block "additional.product.info" in handle "payone_onepage_review" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    39                      <block class="Magento\Framework\View\Element\RendererList" name="checkout.onepage.review.item.renderers" as="renderer.list"/>
    40                      <block class="Magento\Checkout\Block\Cart\Totals" name="payone_onepage_review_details_totals" as="totals" template="Payone_Core::onepage/review/totals.phtml"/>
    41                  </block>
    42                  <block class="Magento\CheckoutAgreements\Block\Agreements" name="payone_onepage_review_details_agreements" as="agreements" template="Magento_CheckoutAgreements::additional_agreements.phtml"/>
    43              </block>
    44          </referenceContainer>
>   45          <block class="Payone\Core\Block\Onepage\Totals" name="additional.product.info" template="Magento_Theme::template.phtml" cacheable="false"/>
    46      </body>
    47  </page>
```

## 10. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_quoteproducts_crosssell.xml:7` (extended)

[X-LAY-01] Block "ddg.quote.related.container" in handle "connector_quoteproducts_crosssell" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Quoteproducts" name="ddg.quote.related.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 11. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_email_basket.xml:7` (extended)

[X-LAY-01] Block "ddg.basket.container" in handle "connector_email_basket" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Basket" name="ddg.basket.container" template="Dotdigitalgroup_Email::email/basket.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 12. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_report_mostviewed.xml:7` (extended)

[X-LAY-01] Block "ddg.report.mostviewed.container" in handle "connector_report_mostviewed" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Mostviewed" name="ddg.report.mostviewed.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 13. `app/code/Payone/Core/view/frontend/layout/payone_amazon_loadreview.xml:44` (extended)

[X-LAY-01] Block "payone_onepage_review_details_totals" in handle "payone_amazon_loadreview" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    38                      <block class="Magento\Checkout\Block\Item\Price\Renderer" name="checkout.item.price.row" template="item/price/row.phtml"/>
    39                      <block class="Magento\Checkout\Block\Item\Price\Renderer" name="checkout.onepage.review.item.price.unit.excl" template="onepage/review/item/price/unit_excl_tax.phtml"/>
    40                      <block class="Magento\Checkout\Block\Item\Price\Renderer" name="checkout.onepage.review.item.price.unit.incl" template="onepage/review/item/price/unit_incl_tax.phtml"/>
    41                      <block class="Magento\Checkout\Block\Item\Price\Renderer" name="checkout.onepage.review.item.price.rowtotal.excl" template="onepage/review/item/price/row_excl_tax.phtml"/>
    42                      <block class="Magento\Checkout\Block\Item\Price\Renderer" name="checkout.onepage.review.item.price.rowtotal.incl" template="onepage/review/item/price/row_incl_tax.phtml"/>
    43                  </block>
>   44                  <block class="Payone\Core\Block\Onepage\Totals" name="payone_onepage_review_details_totals" as="totals" template="Payone_Core::onepage/review/totals.phtml" cacheable="false"/>
    45              </block>
    46          </block>
    47      </container>
    48  </layout>
```

## 14. `app/code/Payone/Core/view/frontend/layout/payone_onepage_review.xml:34` (extended)

[X-LAY-01] Block "payone_onepage_review" in handle "payone_onepage_review" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    28      <head>
    29          <title>Review Order</title>
    30      </head>
    31      <update handle="checkout_onepage_review_item_renderers"/>
    32      <body>
    33          <referenceContainer name="content">
>   34              <block class="Payone\Core\Block\Onepage\Review" name="payone_onepage_review" template="onepage/review.phtml" cacheable="false">
    35                  <block class="Magento\Framework\View\Element\Text\ListText" name="paypal.additional.actions">
    36                      <block class="Magento\Checkout\Block\Cart\Coupon" name="paypal.cart.coupon" as="coupon" template="Magento_Checkout::cart/coupon.phtml"/>
    37                  </block>
    38                  <block class="Payone\Core\Block\Onepage\Review\Details" name="payone_onepage_review_details" as="details" template="onepage/review/details.phtml">
    39                      <block class="Magento\Framework\View\Element\RendererList" name="checkout.onepage.review.item.renderers" as="renderer.list"/>
    40                      <block class="Magento\Checkout\Block\Cart\Totals" name="payone_onepage_review_details_totals" as="totals" template="Payone_Core::onepage/review/totals.phtml"/>
```

## 15. `app/code/Paynl/Payment/view/frontend/layout/paynl_order_finish.xml:20` (extended)

[X-LAY-01] Block "pay_finish" in handle "paynl_order_finish" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    14              <block class="Paynl\Payment\Block\Page\Finish" name="pay_finish.button" template="Magento_Checkout::button.phtml"/>
    15              <action method="setPageTitle">
    16                  <argument translate="true" name="title" xsi:type="string">Thank you for your purchase!</argument>
    17              </action>
    18          </referenceBlock>
    19           <referenceContainer name="content">
>   20              <block class="Paynl\Payment\Block\Page\Finish" name="pay_finish" template="Paynl_Payment::pay_finish.phtml" cacheable="false">
    21                  <container name="order.success.additional.info" label="Order Success Additional Info"/>
    22              </block>
    23              <block class="Magento\Checkout\Block\Registration" name="checkout.registration" template="Magento_Checkout::registration.phtml" cacheable="false"/>
    24          </referenceContainer>
    25      </body>
    26  </page>
```

## 16. `app/design/frontend/Hyva/default/Magento_Contact/layout/contact_index_index.xml:15` (extended)

[X-LAY-01] Block "contactForm" in handle "contact_index_index" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     9  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    10      <head>
    11          <title>Contact Us</title>
    12      </head>
    13      <body>
    14          <referenceContainer name="content">
>   15              <block class="Magento\Contact\Block\ContactForm" name="contactForm" template="Magento_Contact::form.phtml" cacheable="false">
    16                  <container name="form.additional.info" label="Form Additional Info"/>
    17                  <container name="form.additional.after" label="Form Additional After Container"/>
    18                  <block name="contactDetails" as="contact.details" template="Magento_Contact::form/contact-details.phtml" />
    19              </block>
    20          </referenceContainer>
    21      </body>
```

## 17. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_wishlist_related.xml:7` (extended)

[X-LAY-01] Block "ddg.wishlist.related.container" in handle "connector_wishlist_related" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Wishlistproducts" name="ddg.wishlist.related.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 18. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_wishlist_upsell.xml:7` (extended)

[X-LAY-01] Block "ddg.wishlist.upsell.container" in handle "connector_wishlist_upsell" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Wishlistproducts" name="ddg.wishlist.upsell.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 19. `app/code/Smile/StoreLocator/view/frontend/layout/smile_store_locator_store_contact.xml:7` (extended)

[X-LAY-01] Block "smile_store_locator_contact_form" in handle "smile_store_locator_store_contact" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
     3      <body>
     4          <referenceContainer name="content">
     5              <block class="Smile\StoreLocator\Block\ContactForm" name="smile_store_locator_contact_form"
     6                     template="Smile_StoreLocator::contact-form.phtml"
>    7                     cacheable="false">
     8                  <container name="form.additional.info" label="Form Additional Info"/>
     9              </block>
    10          </referenceContainer>
    11      </body>
    12  </page>
```

## 20. `app/code/Buckaroo/Magento2/view/frontend/layout/buckaroo_mrcash_pay.xml:27` (extended)

[X-LAY-01] Block "buckaroo_magento2.checkout.mrcash_pay" in handle "buckaroo_mrcash_pay" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    21      <head>
    22          <css src="Buckaroo_Magento2::css/styles.css" />
    23      </head>
    24  
    25      <body>
    26          <referenceContainer name="content">
>   27              <block class="Buckaroo\Magento2\Block\Checkout\Mrcash\Pay" name="buckaroo_magento2.checkout.mrcash_pay" template="Buckaroo_Magento2::checkout/mrcash/pay.phtml" cacheable="false" />
    28          </referenceContainer>
    29      </body>
    30  </page>
```

## 21. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_email_review.xml:7` (extended)

[X-LAY-01] Block "ddg.review.container" in handle "connector_email_review" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Review" name="ddg.review.container" template="Dotdigitalgroup_Email::email/review.phtml" cacheable="false"/>
     8      </container>
     9  </layout>
```

## 22. `app/code/Paynl/Payment/view/frontend/layout/paynl_order_finish.xml:23` (extended)

[X-LAY-01] Block "checkout.registration" in handle "paynl_order_finish" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    17              </action>
    18          </referenceBlock>
    19           <referenceContainer name="content">
    20              <block class="Paynl\Payment\Block\Page\Finish" name="pay_finish" template="Paynl_Payment::pay_finish.phtml" cacheable="false">
    21                  <container name="order.success.additional.info" label="Order Success Additional Info"/>
    22              </block>
>   23              <block class="Magento\Checkout\Block\Registration" name="checkout.registration" template="Magento_Checkout::registration.phtml" cacheable="false"/>
    24          </referenceContainer>
    25      </body>
    26  </page>
```

## 23. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_email_coupon.xml:6` (extended)

[X-LAY-01] Block "ddg.coupon.container" in handle "connector_email_coupon" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <container name="root">
>    6          <block class="Dotdigitalgroup\Email\Block\Coupon" name="ddg.coupon.container" template="Dotdigitalgroup_Email::email/coupon.phtml" cacheable="false"/>
     7      </container>
     8  </layout>
```

## 24. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_product_crosssell.xml:7` (extended)

[X-LAY-01] Block "ddg.crosssell.container" in handle "connector_product_crosssell" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Product" name="ddg.crosssell.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 25. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_report_recentlyviewed.xml:7` (extended)

[X-LAY-01] Block "ddg.report.recentlyviewed.container" in handle "connector_report_recentlyviewed" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Recentlyviewed" name="ddg.report.recentlyviewed.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 26. `app/code/Paynl/Payment/view/frontend/layout/paynl_order_cancel.xml:21` (extended)

[X-LAY-01] Block "pay_cancel" in handle "paynl_order_cancel" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    15              <action method="setPageTitle">
    16                  <argument translate="true" name="title" xsi:type="string">Payment has been cancelled</argument>
    17              </action>
    18          </referenceBlock>
    19  
    20           <referenceContainer name="content">
>   21              <block class="Paynl\Payment\Block\Page\Finish" name="pay_cancel" template="Paynl_Payment::pay_cancel.phtml" cacheable="false"></block>
    22          </referenceContainer>
    23      </body>
    24  </page>
```

## 27. `app/code/Paynl/Payment/view/frontend/layout/paynl_order_confirm.xml:20` (extended)

[X-LAY-01] Block "pay_finish" in handle "paynl_order_confirm" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
    14              <block class="Paynl\Payment\Block\Page\Finish" name="pay_finish.button" template="Magento_Checkout::button.phtml"/>
    15              <action method="setPageTitle">
    16                  <argument translate="true" name="title" xsi:type="string">Thank you for your purchase, as soon as the payment is completed you will receive an email.</argument>
    17              </action>
    18          </referenceBlock>
    19           <referenceContainer name="content">
>   20              <block class="Paynl\Payment\Block\Page\Finish" name="pay_finish" template="Paynl_Payment::pay_finish.phtml" cacheable="false">
    21                  <container name="order.success.additional.info" label="Order Success Additional Info"/>
    22              </block>
    23              <block class="Magento\Checkout\Block\Registration" name="checkout.registration" template="Magento_Checkout::registration.phtml" cacheable="false"/>
    24          </referenceContainer>
    25      </body>
    26  </page>
```

## 28. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_email_wishlist.xml:7` (extended)

[X-LAY-01] Block "ddg.wishlist.container" in handle "connector_email_wishlist" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Wishlist" name="ddg.wishlist.container" template="Dotdigitalgroup_Email::email/wishlist.phtml" cacheable="false"/>
     8      </container>
     9  </layout>
```

## 29. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_quoteproducts_upsell.xml:7` (extended)

[X-LAY-01] Block "ddg.quote.related.container" in handle "connector_quoteproducts_upsell" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     1  <?xml version="1.0"?>
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
>    7          <block class="Dotdigitalgroup\Email\Block\Recommended\Quoteproducts" name="ddg.quote.related.container" template="Dotdigitalgroup_Email::product/list.phtml" cacheable="false">
     8              <block class="Magento\Framework\Pricing\Render" name="ddg.product.price.render.default">
     9                  <arguments>
    10                      <argument name="price_render_handle" xsi:type="string">catalog_product_prices</argument>
    11                      <argument name="use_link_for_as_low_as" xsi:type="boolean">true</argument>
    12                  </arguments>
    13              </block>
```

## 30. `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_feefo_score.xml:8` (extended)

[X-LAY-01] Block "ddg.feefo.service.score" in handle "connector_feefo_score" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.

```
     2  
     3  <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     4          xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/layout_generic.xsd">
     5      <update handle="formkey"/>
     6      <container name="root">
     7          <block class="Dotdigitalgroup\Email\Block\Feefo"
>    8                 name="ddg.feefo.service.score" template="Dotdigitalgroup_Email::feefo/score.phtml" cacheable="false"/>
     9      </container>
    10  </layout>
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_report_bestsellers.xml:7` | TP-low | Bestsellers list is the same for every caller, but this is a passcode- and IP-checked external dynamic content endpoint fetched by Dotdigital, not a shopper page. |  |  |
| 2 | `app/code/Mageplaza/Blog/view/frontend/layout/mpblog_post_history.xml:27` | FP | History page lists the logged-in customer's own blog author posts and redirects guests to customer/account, personal by nature. |  |  |
| 3 | `app/design/frontend/Hyva/default/Magento_Search/layout/search_term_popular.xml:14` | TP | The Hyvä override keeps core's cacheable=false on the popular search terms page, a public page with the same terms for everyone that now renders in PHP on every request. |  |  |
| 4 | `app/code/Paynl/Payment/view/frontend/layout/paynl_checkout_fastcheckoutfallback.xml:20` | FP | Fast checkout fallback lists shipping methods cached for the current quote ID, a per-cart checkout step. |  |  |
| 5 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_quoteproducts_related.xml:7` | FP | Quote related products endpoint renders products of the quote_id in the request for one customer's email, personal by nature. |  |  |
| 6 | `app/code/Payone/Core/view/frontend/layout/payone_onepage_debit.xml:33` | FP | Payone debit page shows the SEPA mandate for the current checkout quote, a checkout step. |  |  |
| 7 | `app/code/Paynl/Payment/view/frontend/layout/paynl_order_invoice.xml:20` | FP | Invoice return page prints the last order ID from the checkout session, a personal payment return page. |  |  |
| 8 | `app/code/Mageplaza/Blog/view/frontend/layout/mpblog_post_preview.xml:30` | FP | Preview controller shows an unpublished post version only to its own author and redirects everyone else to noroute; it must not be cached. |  |  |
| 9 | `app/code/Payone/Core/view/frontend/layout/payone_onepage_review.xml:45` | FP | payone_onepage_review is the order review step of the current quote, a checkout page. |  |  |
| 10 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_quoteproducts_crosssell.xml:7` | FP | Quote cross-sell endpoint renders products of the quote_id in the request for one customer's email, personal by nature. |  |  |
| 11 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_email_basket.xml:7` | FP | Basket endpoint renders the cart of the quote in the request for one customer's abandoned-cart email, personal by nature. |  |  |
| 12 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_report_mostviewed.xml:7` | TP-low | Most viewed list depends only on category parameters, but the endpoint is passcode- and IP-checked and fetched by Dotdigital, not by shoppers. |  |  |
| 13 | `app/code/Payone/Core/view/frontend/layout/payone_amazon_loadreview.xml:44` | FP | Amazon loadreview is an AJAX layout that renders the current quote's review and totals during checkout. |  |  |
| 14 | `app/code/Payone/Core/view/frontend/layout/payone_onepage_review.xml:34` | FP | payone_onepage_review is the order review step of the current quote, a checkout page. |  |  |
| 15 | `app/code/Paynl/Payment/view/frontend/layout/paynl_order_finish.xml:20` | FP | Order finish page redirects to the cart without a last order in the checkout session and shows that order, a personal success page. |  |  |
| 16 | `app/design/frontend/Hyva/default/Magento_Contact/layout/contact_index_index.xml:15` | TP-low | The public contact page is excluded from FPC because the Hyvä form prefills the customer's name and email from the session; the claim holds but the page has low traffic. |  |  |
| 17 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_wishlist_related.xml:7` | FP | Wishlist related endpoint renders products for the customer_id in the request for one customer's email, personal by nature. |  |  |
| 18 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_wishlist_upsell.xml:7` | FP | Wishlist upsell endpoint renders products for the customer_id in the request for one customer's email, personal by nature. |  |  |
| 19 | `app/code/Smile/StoreLocator/view/frontend/layout/smile_store_locator_store_contact.xml:7` | TP-low | Per-store contact page is public but excluded from FPC because the form prefills the customer's name and email; the claim holds but the page has low traffic. |  |  |
| 20 | `app/code/Buckaroo/Magento2/view/frontend/layout/buckaroo_mrcash_pay.xml:27` | FP | Bancontact pay page renders the transaction key from the request for one payment in progress, personal by nature. |  |  |
| 21 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_email_review.xml:7` | FP | Review endpoint renders the items of the order_id in the request, filtered by that customer's reviews, personal by nature. |  |  |
| 22 | `app/code/Paynl/Payment/view/frontend/layout/paynl_order_finish.xml:23` | FP | Order finish page shows the last order from the checkout session; the registration block offers an account for that order, personal by nature. |  |  |
| 23 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_email_coupon.xml:6` | FP | Coupon endpoint generates a coupon code per request and also sends no-store headers; caching it would hand one code to every recipient. |  |  |
| 24 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_product_crosssell.xml:7` | FP | Product cross-sell endpoint renders products of the order_id in the request for one customer's email, personal by nature. |  |  |
| 25 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_report_recentlyviewed.xml:7` | FP | Recently viewed endpoint renders products viewed by the customer_id in the request, personal by nature. |  |  |
| 26 | `app/code/Paynl/Payment/view/frontend/layout/paynl_order_cancel.xml:21` | FP | Cancel page is the payment return page after a cancelled or denied payment, reached only from a customer's checkout. |  |  |
| 27 | `app/code/Paynl/Payment/view/frontend/layout/paynl_order_confirm.xml:20` | FP | Confirm page redirects to the cart without a last order in the checkout session and shows that order, a personal success page. |  |  |
| 28 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_email_wishlist.xml:7` | FP | Wishlist endpoint renders the wishlist of the customer_id in the request, personal by nature. |  |  |
| 29 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_quoteproducts_upsell.xml:7` | FP | Quote upsell endpoint renders products of the quote_id in the request for one customer's email, personal by nature. |  |  |
| 30 | `app/code/Dotdigitalgroup/Email/view/frontend/layout/connector_feefo_score.xml:8` | TP-low | Feefo service score is the same for every caller, but the endpoint is passcode- and IP-checked and fetched by Dotdigital, not by shoppers. |  |  |
