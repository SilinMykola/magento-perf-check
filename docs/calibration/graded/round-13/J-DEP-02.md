# J-DEP-02: calibration grades, round 13

All 8 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-12 by one grader; see [round 13](../../round-13.md).

| # | location | part | verdict | reason |
|---|---|---|---|---|
| 1 | `vendor/magento/module-page-cache/view/frontend/requirejs-config.js:12` | core | TP-low | The form key provider. The `deps` entry only preloads it: PageCache's `default.xml` already starts it on every page through `x-magento-init` when full page cache is on. |
| 2 | `vendor/magento/module-theme/view/frontend/requirejs-config.js:35` | core | TP-low | `mage/common`, shared behavior of every storefront page. |
| 3 | `vendor/magento/module-theme/view/frontend/requirejs-config.js:36` | core | TP-low | `mage/dataPost`, which the POST links of every page use. |
| 4 | `vendor/magento/module-theme/view/frontend/requirejs-config.js:37` | core | TP-low | `mage/bootstrap`, which starts the components of every page. |
| 5 | `vendor/magento/module-translation/view/base/requirejs-config.js:13` | core | TP-low | The translation dictionary, used by the scripts of every page. |
| 6 | `vendor/magento/module-ui/view/frontend/requirejs-config.js:8` | core | TP-low | `Magento_Ui/js/core/app` and the Knockout bootstrap it depends on, which the UI components of every page use. |
| 7 | `app/code/Algolia/AlgoliaSearch/view/frontend/requirejs-config.js:61` | packagist | TP | Algolia's click and conversion tracking: `insights.js` and its 12.7 KB analytics library are loaded on every page, while click analytics and personalization are off by default (`etc/config.xml`), so by default the module does nothing with them. |
| 8 | `app/code/Yireo/Webp2/view/frontend/requirejs-config.js:13` | extended | TP-low | Adds a class to `body` that tells the theme's CSS whether the browser supports WebP, which every page needs. |
