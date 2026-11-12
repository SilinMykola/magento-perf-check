# J-DEP-01: storefront JavaScript that depends on the whole jQuery UI library

| | |
|---|---|
| Severity | `warning` |
| Status | default set, after [calibration round 15](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-15.md): 35 graded findings on the corpus and on three of five private stores, 86 % correct, 80 % of those with a real cost |
| Analyzer | XML analyzer, storefront scripts ([ADR 9](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0009-storefront-javascript-scanned-by-the-xml-analyzer.md)) |

## What it catches

The module ID `jquery/ui` as a string in storefront JavaScript: a dependency in `define([...])` or `require([...])`, a `deps` list in `requirejs-config.js`, or a `require('jquery/ui')` call. It reads `.js` files and the JavaScript `<script>` elements of `.phtml` templates under a module's `view/frontend` or `view/base`, and under `app/design/frontend`. Admin code is left out.

An object key is left out: core's own map entry `'jquery/ui': 'jquery/compat'` names the module but does not load it. So are comments and code in HTML comments of a template.

Not read: `<script type="text/x-magento-init">` blocks and `data-mage-init` attributes, which name components as object keys; scripts that PHP builds as a string, for example for `$secureRenderer->renderTag()`; minified `x.min.js` files next to their `x.js`; `node_modules`; themes outside `app/design`. The storefront scripts are read only while J-DEP-01 or J-DEP-02 is enabled; on the Magento 2.4.9 core that took about 0.5 s with a warm PHPStan cache ([round 15](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-15.md#timing)).

## Why it hurts performance

Since Magento 2.3.3, jQuery UI is split into one module per widget under `jquery-ui-modules/`. On the storefront, core maps `jquery/ui` to `jquery/compat`, which imports every widget; its own comment says "Import every plugin under the sun. Bad for performance". Adobe's page on custom JavaScript says: "Using individual jQuery UI components instead of the monolithic jQuery UI library improves storefront performance." In magento/magento2#22995, jQuery UI was measured at 227 KB of the shared bundle, unminified, of which 11 % was used on the home page.

## Example

Bad:

```javascript
define(['jquery', 'jquery/ui'], function ($) {
    $('.range').slider();
});
```

Good:

```javascript
define(['jquery', 'jquery-ui-modules/slider'], function ($) {
    $('.range').slider();
});
```

## How to suppress

- **Command line:** put `// perf-ignore J-DEP-01: reason` on the line before the string, or a `/* perf-ignore J-DEP-01: reason */` comment that ends on its line or on the line before. A comment without a reason suppresses nothing and is reported as a notice.
- **PHPStan:** this is not a PHP rule, so `@phpstan-ignore` does not apply.

## Known false positives

- A script in `view/base` that only the admin loads: the admin keeps the whole library on purpose, as the Adobe page says.
- A project on Magento older than 2.3.3, which has no `jquery-ui-modules`, and a fallback file that a module loads only on such versions.
- A script that nothing loads: a theme override for a module that the project does not have installed, or a `shim` entry of a library that nothing asks for. The rule does not follow where a script is used.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Custom JavaScript](https://developer.adobe.com/commerce/frontend-core/javascript/custom) ([source L103-L108](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/custom.md?plain=1#L103-L108)) | A | "When using custom JS, try to keep dependencies to a minimum. Additional dependencies demand more web requests, which can slow rendering." and "Using individual jQuery UI components instead of the monolithic jQuery UI library improves storefront performance." |
| [Adobe: Custom JavaScript](https://developer.adobe.com/commerce/frontend-core/javascript/custom) ([source L84-L86](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/custom.md?plain=1#L84-L86)) | A | Individual components for the frontend and base areas, `jquery/ui` "if your widget is for adminhtml area". |
| [Adobe: Release notes 2.4.3](https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/magento-open-source/2-4-3) ([source L1792](https://github.com/AdobeDocs/commerce-operations.en/blob/974c6238c0e0008eaa36851449de97218bfca949/help/release/release-notes/open-source/2-4-3.md?plain=1#L1792)) | A | Core moved its own `dropdownDialog` to the chunks it needs: "Previously, it loaded large amounts of unnecessary code, which inflated load time and reduced performance." |
| [compat.js#L1-L4](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/web/jquery/compat.js#L1-L4), [compat.js#L42-L47](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/web/jquery/compat.js#L42-L47) | B | `jquery/compat` imports every widget, "Bad for performance", and warns in the console that the store "is missing a dependency for a jQueryUI widget". |
| [Theme frontend requirejs-config.js#L30](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/view/frontend/requirejs-config.js#L30) | B | On the storefront, `jquery/ui` is mapped to `jquery/compat`. |
| [Theme base requirejs-config.js#L42-L47](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/view/base/requirejs-config.js#L42-L47) | B | The module IDs the message suggests, such as `jquery-ui-modules/slider` and `jquery-ui-modules/widget`. |
| [magento/magento2#22995](https://github.com/magento/magento2/issues/22995) | B | A measurement: "jQueryUI is `227kb` of the shared bundle (unminified), even though only 11% of that is used on the homepage." |
| `lib/web/jquery/compat.js` at tags [2.3.2](https://github.com/magento/magento2/tree/2.3.2/lib/web/jquery) and [2.3.3](https://github.com/magento/magento2/blob/2.3.3/lib/web/jquery/compat.js) | B | The file is missing at 2.3.2 and present at 2.3.3. |

Verified 2026-11-12.
