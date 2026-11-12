# Proposal

## Why

SPEC milestone M6 plans rules for storefront code that runs in the browser. The Luma source audit ranked first a dependency on `jquery/ui`, the whole jQuery UI library, which on the storefront maps to `jquery/compat`. Neither analyzer reads JavaScript, so the rule needs a way to read storefront scripts first.

| Source | Level | What it says |
|---|---|---|
| [Adobe: Custom JavaScript](https://developer.adobe.com/commerce/frontend-core/javascript/custom) ([source L107-L108](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/custom.md?plain=1#L107-L108)) | A | "Using individual jQuery UI components instead of the monolithic jQuery UI library improves storefront performance." |
| [compat.js#L1-L4](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/web/jquery/compat.js#L1-L4) | B | "Import every plugin under the sun. Bad for performance," |
| [Theme frontend requirejs-config.js#L30](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/view/frontend/requirejs-config.js#L30) | B | `'jquery/ui': 'jquery/compat'` on the storefront. |
| [magento/magento2#22995](https://github.com/magento/magento2/issues/22995) | B | "jQueryUI is `227kb` of the shared bundle (unminified), even though only 11% of that is used on the homepage." |

Counted on 2026-11-12 with a prototype scanner: 1 in the 25 modules, 4 in the extended part, none in the core storefront code.

## What Changes

- The XML analyzer reads storefront scripts: `.js` files and the JavaScript `<script>` elements of `.phtml` templates under `view/frontend`, `view/base` and `design/frontend`. A scanner gives their string literals and comments ([ADR 9](../../../docs/adr/0009-storefront-javascript-scanned-by-the-xml-analyzer.md)).
- A JavaScript comment `perf-ignore RULE-ID: reason` on the line of a finding or the line before suppresses it.
- J-DEP-01, experimental: the string `jquery/ui` in storefront JavaScript, other than an object key.
- Calibrated on the core and both corpus parts. Result of calibration round 12: 5 findings, 4 correct (precision 80 %), impact share 50 %; too few for a decision, so the rule stays experimental at `warning`.
