# X-LAY-04: script added to the page head by layout XML without `async` or `defer`

| | |
|---|---|
| Severity | `info` |
| Status | experimental, after [calibration round 10](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-10.md): 14 findings, all correct, 1 of them on pages that matter, so it reports at `info` |
| Analyzer | XML (layout files of area `frontend` or `base`) |

## What it catches

In `<head>` of a storefront layout file, a `<script src>`, or a `<link src>` that core renders as a script, without an `async` or a `defer` attribute. Whether an element is a script follows core: a local file (no `src_type`) by the extension of its `src` as written, so `<link src="js/widget.js?v=2"/>` is not one; a remote file (`src_type="url"` or `"controller"`) is a script when it is a `<script>`, or a `<link>` with `content_type="js"`. Layout files of modules (`view/frontend/layout`, `view/base/layout`) and of themes (`app/design/frontend`) count; admin layouts are left out. The message names the handle, so you can tell a script on every page (`default`) from one on a single page.

The rule leaves out:
- the scripts core never defers itself: the RequireJS loader (`require.js`), `requirejs-config.js`, `requirejs/mixins.js`, `requirejs/baseUrlResolver.js`, `requirejs-map.js`, `requirejs-min-resolver.js`, `static.js`, files in `js/bundle/`, `_cache/` or `merged/`, and `sri.js`;
- an element with a non-empty `ie_condition`: core wraps it in an Internet Explorer conditional comment, which other browsers read as an HTML comment.

[T-05](T-05.md) reports the same problem in templates.

## Why it hurts performance

A script without `async` or `defer` in the head blocks rendering: the browser downloads and runs it before it goes on with the page. Adobe's page on JavaScript resources says JavaScript is kept out of the page head "to address the problem of slow page loads", and that RequireJS "enables asynchronous JavaScript loading". The layout documentation shows `async` and `defer` on head scripts.

From Magento 2.4.9, the setting `dev/js/defer_non_critical` makes core add `defer` to every head script that is not on the list above. It is off by default and usually lives in the database, which the rule does not read. With the setting on, the findings do not apply.

## Example

Bad:

```xml
<head>
    <script src="https://sdk.example.com/widget.js" src_type="url"/>
</head>
```

Good:

```xml
<head>
    <script defer="defer" src="https://sdk.example.com/widget.js" src_type="url"/>
</head>
```

On a Luma theme you can also load the file as a RequireJS dependency of the component that needs it.

## How to suppress

- **Command line:** put `<!-- perf-ignore X-LAY-04: reason -->` directly before the element. A comment without a reason suppresses nothing and is reported as a notice.
- **PHPStan:** this is an XML rule, so `@phpstan-ignore` does not apply.

## Known false positives

- A script that must run before the rest of the page, such as a payment SDK whose documentation asks for a synchronous load. Suppress it with the reason.
- A project that turns on `dev/js/defer_non_critical`.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: JavaScript resources](https://developer.adobe.com/commerce/frontend-core/javascript/resources) ([source L14-L16](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/resources.md?plain=1#L14-L16)) | A | "To address the problem of slow page loads, we exclude JavaScript from the page headers and we added the ability to use the RequireJS library." and "RequireJS improves the perceived page load time because it allows JavaScript to load in the background; in particular, it enables asynchronous JavaScript loading." |
| [Adobe: Layout instructions, head](https://developer.adobe.com/commerce/frontend-core/guide/layouts/xml-manage) ([source L45-L47](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/guide/layouts/xml-manage.md?plain=1#L45-L47)) | A | "The application supports async or defer attribute in script tag" |
| [Adobe: Optimize resource files](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/optimize-css-js-files) ([source L25](https://github.com/AdobeDocs/commerce-operations.en/blob/974c6238c0e0008eaa36851449de97218bfca949/help/implementation-playbook/best-practices/development/optimize-css-js-files.md?plain=1#L25)) | A | "Consider delivering critical JS and CSS features inline and deferring all non-critical JS/CSS styles." |
| [Source.php#L199-L202](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Asset/Source.php#L199-L202), [Generator/Head.php#L120-L128](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Generator/Head.php#L120-L128) | B | A local asset's content type is the extension of its path; a remote asset without `content_type` becomes a `link`. |
| [Renderer.php#L28-L39](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L28-L39) | B | The scripts core never defers, which the rule leaves out. |
| [Renderer.php#L439-L442](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L439-L442) | B | An asset group with a non-empty `ie_condition` is wrapped in `<!--[if ...]>` and `<![endif]-->`. |
| [Renderer.php#L459-L470](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L459-L470), [Backend config.xml#L23](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Backend/etc/config.xml#L23) | B | Core adds `defer` to the other head scripts only when `dev/js/defer_non_critical` is on; the default is `0`. `Renderer.php` reads the setting at tag 2.4.9 and not at tag 2.4.8. |

Verified 2026-11-11.
