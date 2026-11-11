# X-LAY-04: calibration grades, round 10

All 14 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-11 by one grader; see [round 10](../../round-10.md).

| # | location | part | verdict | reason |
|---|---|---|---|---|
| 1 | `vendor/magento/module-checkout/view/frontend/layout/checkout_cart_configure.xml:10` | core | TP-low | Small local script on the page that edits a cart item, which is not cached. |
| 2 | `vendor/magento/module-configurable-product/view/frontend/layout/checkout_cart_configure_type_configurable.xml:10` | core | TP-low | Small local script on the page that edits a configurable cart item. |
| 3 | `vendor/magento/module-swatches/view/frontend/layout/checkout_cart_configure_type_configurable.xml:10` | core | TP-low | Small local script on the page that edits a configurable cart item. |
| 4 | `app/code/Buckaroo/Magento2/view/frontend/layout/catalog_product_view.xml:25` | extended | TP | Apple Pay SDK from Apple's CDN on every product page, whether or not the visitor can pay with Apple Pay. |
| 5 | `app/code/Buckaroo/Magento2/view/frontend/layout/checkout_cart_index.xml:25` | extended | TP-low | The same SDK on the cart page only. |
| 6 | `app/code/Buckaroo/Magento2/view/frontend/layout/checkout_index_index.xml:27` | extended | TP-low | The same SDK on the checkout page only. |
| 7 | `app/code/Mageplaza/Blog/view/frontend/layout/hyva_mpblog_author.xml:29` | extended | TP-low | Editor script on the blog author's own pages (information and post history), which only authors open. |
| 8 | `app/code/Mageplaza/Blog/view/frontend/layout/hyva_mpblog_author.xml:31` | extended | TP-low | Tree select script on the blog author's own pages. |
| 9 | `app/code/Mageplaza/Blog/view/frontend/layout/hyva_mpblog_author_information.xml:27` | extended | TP-low | Editor script on the blog author's information page. |
| 10 | `app/code/Mageplaza/Blog/view/frontend/layout/hyva_mpblog_author_signup.xml:28` | extended | TP-low | Editor script on the blog author sign-up page. |
| 11 | `app/code/Mageplaza/Blog/view/frontend/layout/mpblog_post_editpost.xml:25` | extended | TP-low | Plugin script on the page where an author edits a post. |
| 12 | `app/code/Mageplaza/Blog/view/frontend/layout/mpblog_post_editpost.xml:26` | extended | TP-low | Plugin script on the page where an author edits a post. |
| 13 | `app/code/Mollie/HyvaCheckout/view/frontend/layout/hyva_checkout.xml:11` | extended | TP-low | Mollie Components script on the Hyvä checkout only. |
| 14 | `app/code/Mollie/HyvaCheckout/view/frontend/layout/hyva_checkout.xml:12` | extended | TP-low | Mollie Components script on the Hyvä checkout only. |
