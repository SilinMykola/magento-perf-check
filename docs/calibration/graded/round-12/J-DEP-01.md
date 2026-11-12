# J-DEP-01: calibration grades, round 12

All 5 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-12 by one grader; see [round 12](../../round-12.md).

| # | location | part | verdict | reason |
|---|---|---|---|---|
| 1 | `app/code/Algolia/AlgoliaSearch/view/frontend/web/js/navigation/range-slider-widget.js:5` | packagist | TP | The price range slider of the category and search pages loads every jQuery UI widget for one slider. |
| 2 | `app/code/Clerk/Clerk/view/frontend/templates/livesearch.phtml:25` | extended | TP | The live search script, which `default.xml` adds to every page when live search is enabled and a search form selector is set, requires `jquery/ui` and uses none of its widgets. |
| 3 | `app/code/Mageplaza/Reports/view/base/web/js/lib/gridstack.jQueryUI.js:10` | extended | TP-low | Grid library of the reports dashboard; on the storefront only its own dashboard page uses it. |
| 4 | `app/code/Taxjar/SalesTax/view/base/web/js/modal.js:19` | extended | TP-low | Address suggestion modal of the address validation, used in the admin and, through a mixin of `Magento_Customer/js/addressValidation`, on the customer address form. |
| 5 | `app/code/Tweakwise/Magento2Tweakwise/view/frontend/web/js/navigation-slider-compat.js:10` | extended | FP | A fallback that the module loads only on Magento older than 2.3.3 (`Model/NavigationConfig.php`, `getSliderReference()`); from 2.3.3 it loads a slider that depends on `jquery-ui-modules/slider`. |
