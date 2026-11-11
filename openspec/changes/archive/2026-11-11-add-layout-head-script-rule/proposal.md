# Proposal

## Why

SPEC milestone M6 plans storefront rules, Hyvä first, then Luma. T-05 reports a synchronous `<script src>` in a storefront template, but a module or theme can add the same script through layout XML, in `<head>`, and nothing reports that today. A source audit of the Adobe frontend documentation for the Luma half of M6 found this the candidate with the best sources and the most corpus findings:

| Source | Level | What it says |
|---|---|---|
| [Adobe: JavaScript resources](https://developer.adobe.com/commerce/frontend-core/javascript/resources) ([source L14-L16](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/javascript/resources.md?plain=1#L14-L16)) | A | "To address the problem of slow page loads, we exclude JavaScript from the page headers and we added the ability to use the RequireJS library." |
| [Adobe: Layout instructions, head](https://developer.adobe.com/commerce/frontend-core/guide/layouts/xml-manage) ([source L45-L47](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/guide/layouts/xml-manage.md?plain=1#L45-L47)) | A | "The application supports async or defer attribute in script tag" |
| [Adobe: Optimize resource files](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/optimize-css-js-files) ([source L25](https://github.com/AdobeDocs/commerce-operations.en/blob/974c6238c0e0008eaa36851449de97218bfca949/help/implementation-playbook/best-practices/development/optimize-css-js-files.md?plain=1#L25)) | A | "Consider delivering critical JS and CSS features inline and deferring all non-critical JS/CSS styles." |
| [Renderer.php#L28-L39](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L28-L39) | B | The scripts core never defers: the RequireJS loader and its configuration, static and bundle files, `sri.js`. |
| [Renderer.php#L459-L470](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L459-L470), [Backend config.xml#L23](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Backend/etc/config.xml#L23) | B | With `dev/js/defer_non_critical` on, core adds `defer` to every other head script; the setting is off by default. It is in 2.4.9 and not in 2.4.8 (`Renderer.php` at the tags, checked on 2026-11-11). |

Counted on 2026-11-11 with the critical scripts left out: 0 in the 25 modules, 12 in 4 modules of the extended part (one of them an IE 11 polyfill behind `ie_condition`), 3 in the core.

## What Changes

- X-LAY-04, experimental: a `<head>` `<script src>`, or a `<link src>` that core renders as a script, without `async` or `defer` in a storefront layout file. Whether an element is a script follows core: a local file by the extension of its `src` as written ([Source.php#L199-L202](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Asset/Source.php#L199-L202)), a remote one by its content type, which `<script>` always has and a `<link>` only when written ([Generator/Head.php#L120-L128](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Generator/Head.php#L120-L128)).
- It leaves out the scripts core never defers and scripts behind `ie_condition`, which core wraps in an Internet Explorer conditional comment.
- It does not read `dev/js/defer_non_critical`: the value is in the database unless a project dumps it to `app/etc/config.php`, and no corpus or private project does. The message names the setting.
- Calibrated on the core and both corpus parts before any decision on the default set. Result of calibration round 10: 14 findings, all correct, impact share 7 %; the rule stays experimental and, as round 6 applies the impact share, reports at `info`.
