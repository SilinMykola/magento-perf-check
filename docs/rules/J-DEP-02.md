# J-DEP-02: module in the top-level `deps` of a storefront `requirejs-config.js`

| | |
|---|---|
| Severity | `info` |
| Status | experimental, after [calibration round 13](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-13.md): 8 findings, all correct, 1 of them for a module that most pages do not need, so it reports at `info` |
| Analyzer | XML analyzer, storefront scripts ([ADR 9](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0009-storefront-javascript-scanned-by-the-xml-analyzer.md), [ADR 10](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0010-property-paths-in-the-script-scanner.md)) |

## What it catches

A module ID in the top-level `deps` list of a `requirejs-config.js` that core merges into every storefront page: the one directly in a module's `view/frontend` or `view/base`, and a theme's under `app/design/frontend`, at its root or in a `<Vendor_Module>` folder. A file of that name anywhere else, such as under `web/`, is not merged and is left out, and so is admin code.

`deps` under `shim` is left out: it only orders the loading of a module that something else asks for. So is a `deps` key anywhere else, such as inside `map` or `config`.

Not seen: modules that the file adds in code, such as `config.deps.push('x')`.

## Why it hurts performance

Core merges the `requirejs-config.js` of every module and of the theme into one configuration, and the `default` handle adds it to every storefront page. Adobe's page on RequireJS says: "Adding modules under an independent `deps` configuration will load the specified modules in all pages." Every such module is downloaded and run on every page, whether the page uses it or not, and Adobe's page on custom JavaScript notes that "Additional dependencies demand more web requests, which can slow rendering."

A module that every page needs, such as core's own `mage/bootstrap`, belongs there. For one that only some pages need, start it from the templates of those pages.

## Example

Bad, in `view/frontend/requirejs-config.js`:

```javascript
var config = {
    deps: ['Vendor_Slider/js/slider-init']
};
```

Good, in the template that renders the slider:

```html
<script type="text/x-magento-init">
    {"*": {"Vendor_Slider/js/slider-init": {}}}
</script>
```

## How to suppress

- **Command line:** put `// perf-ignore J-DEP-02: reason` on the line before the module ID, or a `/* perf-ignore J-DEP-02: reason */` comment that ends on its line or on the line before. A comment without a reason suppresses nothing and is reported as a notice.
- **PHPStan:** this is not a PHP rule, so `@phpstan-ignore` does not apply.

## Known false positives

- A module that every page needs, such as tracking, a site-wide widget or a polyfill. The finding is correct, and the module belongs there: suppress it with the reason.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: RequireJS](https://developer.adobe.com/commerce/frontend-core/javascript/requirejs) ([source L133-L135](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/requirejs.md?plain=1#L133-L135)) | A | "Adding modules under an independent `deps` configuration will load the specified modules in all pages." |
| [Adobe: Custom JavaScript](https://developer.adobe.com/commerce/frontend-core/javascript/custom) ([source L103](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/custom.md?plain=1#L103)) | A | "Additional dependencies demand more web requests, which can slow rendering." |
| [RequireJs/Config.php#L79-L85](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/RequireJs/Config.php#L79-L85), [#L154-L166](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/RequireJs/Config.php#L154-L166) | B | Each `requirejs-config.js` of the theme's modules is wrapped in `require.config(config)` and merged into one file. |
| [RequireJs etc/di.xml#L9-L35](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/RequireJs/etc/di.xml#L9-L35) | B | The files that are merged: those the collectors `Base` (a module's `view/<area>/`), `ThemeModular` (a theme's `<Vendor_Module>` folders) and `Theme` (a theme's root) find. |
| [Theme frontend default.xml#L14](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/view/frontend/layout/default.xml#L14) | B | The merged configuration is the `requirejs-config` block of the `default` handle, so every storefront page loads it. |

Verified 2026-11-12.
