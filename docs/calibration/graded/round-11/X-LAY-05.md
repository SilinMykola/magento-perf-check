# X-LAY-05: calibration grades, round 11

All 18 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-11 by one grader; see [round 11](../../round-11.md).

| # | location | part | verdict | reason |
|---|---|---|---|---|
| 1 | `vendor/magento/module-catalog/view/base/layout/default.xml:10` | core | TP | `uppy-custom.css` styles the file uploader of UI forms, which the admin uses; the `base` area puts it on every storefront page too, where few pages show an uploader. |
| 2 | `vendor/magento/module-theme/view/frontend/layout/default_head_blocks.xml:11` | core | TP | `mage/calendar.css` (6 KB) styles the date picker, which only a few forms show. |
| 3 | `app/code/Magewirephp/Magewire/view/base/layout/default.xml:6` | packagist | TP-low | Styles for Magewire's loading and offline states; the same file puts Magewire's root block, notifier and loading indicator on every page. |
| 4 | `app/code/Clerk/Clerk/view/frontend/layout/default.xml:5` | extended | TP | Styles of the popup shown after a product is added to the cart. |
| 5 | `app/code/MageWorx/SearchSuiteAutocomplete/view/frontend/layout/default.xml:5` | extended | TP-low | Styles of the search box autocomplete in the header, which is on every page. |
| 6 | `app/code/Mageplaza/BannerSlider/view/frontend/layout/default.xml:25` | extended | TP | Owl Carousel styles, used only on pages with a banner slider. |
| 7 | `app/code/Mageplaza/BannerSlider/view/frontend/layout/default.xml:26` | extended | TP | Owl Carousel theme styles, used only on pages with a banner slider. |
| 8 | `app/code/Mageplaza/BannerSlider/view/frontend/layout/default.xml:27` | extended | TP | `animate.min.css` (58 KB) of slide animations, used only on pages with a banner slider. |
| 9 | `app/code/Mageplaza/BannerSlider/view/frontend/layout/default.xml:28` | extended | TP | The slider's own styles, used only on pages with a banner slider. |
| 10 | `app/code/Mageplaza/BannerSlider/view/frontend/layout/hyva_default.xml:29` | extended | TP | The slider's Hyvä styles, on every Hyvä page. |
| 11 | `app/code/Mageplaza/Blog/view/frontend/layout/default_head_blocks.xml:25` | extended | TP | Icon styles of the blog post information (date, author, comments), used only on blog pages. |
| 12 | `app/code/Mageplaza/Productslider/view/frontend/layout/default.xml:25` | extended | TP | Owl Carousel styles, used only on pages with a product slider. |
| 13 | `app/code/Mageplaza/Productslider/view/frontend/layout/default.xml:26` | extended | TP | Owl Carousel theme styles, used only on pages with a product slider. |
| 14 | `app/code/Mageplaza/ShareCart/view/frontend/layout/default.xml:25` | extended | TP-low | Styles of the share cart button, which the same file adds to the mini cart on every page. |
| 15 | `app/code/PayU/PaymentGateway/view/frontend/layout/default.xml:4` | extended | TP | Styles of the payment form and saved cards, on the checkout and the account pages. |
| 16 | `app/code/Payone/Core/view/frontend/layout/default.xml:10` | extended | TP | Styles of Payone's checkout messages. |
| 17 | `app/code/Trustpilot/Reviews/view/frontend/layout/default.xml:4` | extended | TP | Styles of a TrustBox message box and its animation; no frontend template of the module uses these classes, and TrustBoxes load only on the pages configured for them. |
| 18 | `app/code/Tweakwise/Magento2Tweakwise/view/frontend/layout/default.xml:12` | extended | TP | Mostly styles of the layered navigation sliders and trees, used only on category and search pages; a small part styles the header search autocomplete. |
