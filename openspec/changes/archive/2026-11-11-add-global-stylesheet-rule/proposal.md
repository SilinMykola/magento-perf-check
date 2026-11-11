# Proposal

## Why

SPEC milestone M6 plans storefront rules, Hyvä first, then Luma. A module can add its stylesheet to the head of every storefront page through `default.xml`, and nothing reports that today. The Luma source audit ranked it after X-LAY-04:

| Source | Level | What it says |
|---|---|---|
| [Adobe: CSS critical path](https://developer.adobe.com/commerce/frontend-core/guide/css/critical-path) ([source L12](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/guide/css/critical-path.md?plain=1#L12)) | A | "All CSS styles loaded from external files are considered as render-blocking. This means that a web page will not be displayed until these files are loaded." |
| [Adobe: Layout instructions](https://developer.adobe.com/commerce/frontend-core/guide/layouts/xml-instructions) ([source L21-L22](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/guide/layouts/xml-instructions.md?plain=1#L21-L22)) | A | "For example, layout changes added to `app/code/Vendor/Module/view/frontend/layout/default.xml` are loaded on all pages." |
| [Renderer.php#L397-L399](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L397-L399), [Generator/Head.php#L120-L128](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Generator/Head.php#L120-L128) | B | A `css` asset is rendered as `<link rel="stylesheet">`; a remote asset without a content type becomes a `link` with only the written attributes. |
| [AsyncCssPlugin.php#L58-L62](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/Controller/Result/AsyncCssPlugin.php#L58-L62), [Theme config.xml#L74](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/etc/config.xml#L74) | B | Core loads stylesheets without blocking only with `dev/css/use_css_critical_path` on; the default is `0`. |

Counted on 2026-11-11: 1 in the 25 modules, 15 in the extended part, 2 in the core.

## What Changes

- X-LAY-05: a `<head>` `<css src>`, or a `<link src>` that core renders as a stylesheet, in a module's `default.xml`, `default_head_blocks.xml` or their `hyva_` copies, area `frontend` or `base`. Whether an element is a stylesheet follows core: a local file by the extension of its `src` as written, a remote one by its content type, or by `rel="stylesheet"` on a `<link>` without one.
- Theme layouts are left out: a theme's own stylesheet belongs on every page. So are `media="print"`, which core itself uses to load a stylesheet without blocking, and `ie_condition`.
- It does not read `dev/css/use_css_critical_path`: the value is in the database unless a project dumps it to `app/etc/config.php`. The message names the setting.
- X-LAY-04 and X-LAY-05 share one reading of how core renders a head element. X-LAY-04 now reports a script whose `ie_condition` is empty, which core does not wrap in a conditional comment ([Renderer.php#L439-L442](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L439-L442)); the corpus has none.
- Calibrated on the core and both corpus parts before any decision on the default set. Result of calibration round 11: 18 findings, all correct, impact share 83 %; the rule joins the default set at `warning`, which the changelog lists because it can change a CI result.
