# Proposal

## Why

The Luma source audit of SPEC milestone M6 ranked, after `jquery/ui`, the modules that a `requirejs-config.js` lists in its top-level `deps`. Core merges every module's and theme's `requirejs-config.js` into one file that the `default` handle adds to every storefront page, and RequireJS loads the modules of a top-level `deps` as soon as it reads that configuration. A module needed on one page is then downloaded and run on all of them.

| Source | Level | What it says |
|---|---|---|
| [Adobe: RequireJS](https://developer.adobe.com/commerce/frontend-core/javascript/requirejs) ([source L133-L135](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/requirejs.md?plain=1#L133-L135)) | A | "Adding modules under an independent `deps` configuration will load the specified modules in all pages." |
| [Adobe: Custom JavaScript](https://developer.adobe.com/commerce/frontend-core/javascript/custom) ([source L103](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/custom.md?plain=1#L103)) | A | "Additional dependencies demand more web requests, which can slow rendering." |
| [Framework RequireJs/Config.php#L79-L85](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/RequireJs/Config.php#L79-L85), [#L154-L166](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/RequireJs/Config.php#L154-L166) | B | Every `requirejs-config.js` of the theme's modules is wrapped in `require.config(config)` and merged into one file. |
| [Theme frontend default.xml#L14](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/view/frontend/layout/default.xml#L14) | B | The merged configuration is a block of the `default` handle, so every storefront page loads it. |

Counted on 2026-11-12 with grep and a brace count: 1 in the 25 modules (Algolia), 1 in the extended part (Yireo Webp2); `deps` under `shim` in 3 more files of the extended part, which load nothing by themselves.

## What Changes

- The script scanner gives each string literal its property path: the keys of the object properties around it, outermost first ([ADR 10](../../../docs/adr/0010-property-paths-in-the-script-scanner.md)).
- J-DEP-02, experimental, at `info`: a module listed in the top-level `deps` of a storefront `requirejs-config.js`. `deps` under `shim` and in other files is left out.
- Calibrated on the core and both corpus parts as calibration round 13: 8 findings, all correct, 1 of them for a module that most pages do not need (impact share 13 %), so the rule stays experimental and reports at `info`.
