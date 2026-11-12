# X-LAY-05: stylesheet added to every storefront page by a module layout

| | |
|---|---|
| Severity | `warning` |
| Status | default set, after [calibration round 11](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-11.md): 18 findings, all correct, 15 of them for styles most pages do not use |
| Analyzer | XML (module layout files of area `frontend` or `base`) |

## What it catches

In `<head>` of a module's `default.xml` or `default_head_blocks.xml`, or of their Hyvä copies `hyva_default.xml` and `hyva_default_head_blocks.xml`, a `<css src>` or a `<link src>` that core renders as a stylesheet. These handles apply to every storefront page, so the stylesheet is loaded on every page. Whether an element is a stylesheet follows core: a local file (no `src_type`) by the extension of its `src` as written; a remote file (`src_type="url"` or `"controller"`) when it is a `<css>` or a `<link>` with `content_type="css"`. Any other `<link>` that core renders neither as a script nor as a font counts when it has `rel="stylesheet"`, because core passes the written attributes to the tag. The message names the stylesheet and the handle.

The rule looks only at module layouts (`view/frontend/layout`, `view/base/layout`). A theme's layouts are left out: a theme adds its own stylesheet to every page by design. Admin layouts are left out too.

The rule also leaves out:
- an element with `media="print"` (in any case), which the browser loads without blocking the page; core uses the same attribute to load stylesheets without blocking;
- an element with a non-empty `ie_condition`: core wraps it in an Internet Explorer conditional comment, which other browsers read as an HTML comment.

## Why it hurts performance

Adobe's page on the CSS critical path says: "All CSS styles loaded from external files are considered as render-blocking. This means that a web page will not be displayed until these files are loaded." A module that adds its stylesheet in `default.xml` makes every page wait for it, also the pages that do not use its styles, such as a banner slider's styles on a product page or a payment form's styles on the home page. Adobe's layout documentation says changes in a module's `default.xml` "are loaded on all pages".

With the setting `dev/css/use_css_critical_path` on, core moves every stylesheet to the end of the head and loads it without blocking. The setting is off by default and usually lives in the database, which the rule does not read. With the setting on, the findings do not apply.

## Example

Bad, in `view/frontend/layout/default.xml`:

```xml
<head>
    <css src="Vendor_Slider::css/slider.css"/>
</head>
```

Good: add the stylesheet only on the handles of the pages that show a slider, for example `cms_index_index.xml`. On a Luma theme you can also move the styles into the module's `view/frontend/web/css/source/_module.less`, which core compiles into the theme's own stylesheet, so no extra file is loaded.

## How to suppress

- **Command line:** put `<!-- perf-ignore X-LAY-05: reason -->` directly before the element. A comment without a reason suppresses nothing and is reported as a notice.
- **PHPStan:** this is an XML rule, so `@phpstan-ignore` does not apply.

## Known false positives

- Styles for an element that is on every page, such as a header search box or a site-wide widget. The finding is correct, but moving the styles into the theme's stylesheet only saves one request.
- A project that turns on `dev/css/use_css_critical_path`.
- A Hyvä storefront with Hyvä's base layout reset: seen in November 2026 in its `GenericBaseLayoutFileReset`, which removes `<css>` and `<link>` from the base layout files of the modules it resets, so the stylesheet is not loaded there.
- A theme that removes the stylesheet with `<remove src="..."/>`: the rule does not read theme layouts.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: CSS critical path](https://developer.adobe.com/commerce/frontend-core/guide/css/critical-path) ([source L12](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/guide/css/critical-path.md?plain=1#L12)) | A | "All CSS styles loaded from external files are considered as render-blocking. This means that a web page will not be displayed until these files are loaded." |
| [Adobe: Layout instructions](https://developer.adobe.com/commerce/frontend-core/guide/layouts/xml-instructions) ([source L21-L22](https://github.com/AdobeDocs/commerce-frontend-core/blob/1b823a3758868223e8b06d3479dfc4196ba3be57/src/pages/guide/layouts/xml-instructions.md?plain=1#L21-L22)) | A | "To make layout changes available on every page, modify the `default.xml` file. For example, layout changes added to `app/code/Vendor/Module/view/frontend/layout/default.xml` are loaded on all pages." |
| [Hyvä: The hyva_ layout handles](https://docs.hyva.io/hyva-themes/writing-code/layout-and-templates/the-hyva_-layout-handles.html) | A | "On store views using a Hyvä theme, for every layout update handle that is applied on a page, a new handle with an hyva_ prefix is automatically applied, too." |
| [Reader/Head.php#L50-L63](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Reader/Head.php#L50-L63), [Generator/Head.php#L120-L128](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Generator/Head.php#L120-L128), [Source.php#L199-L202](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Asset/Source.php#L199-L202) | B | `<css>` gets the content type `css`; a remote asset without a content type becomes a `link`; a local asset's content type is the extension of its path. |
| [Generator/Head.php#L144-L157](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Generator/Head.php#L144-L157), [Renderer.php#L397-L399](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L397-L399), [Renderer.php#L422-L425](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L422-L425) | B | Attributes written in the layout, such as `rel` and `media`, are passed to the tag; a `css` asset is rendered as `<link rel="stylesheet">`, any other asset except scripts and fonts as `<link href>` with only the written attributes. |
| [AsyncCssPlugin.php#L58-L62](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/Controller/Result/AsyncCssPlugin.php#L58-L62), [AsyncCssPlugin.php#L118-L122](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/Controller/Result/AsyncCssPlugin.php#L118-L122), [Theme config.xml#L74](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Theme/etc/config.xml#L74) | B | Only with `dev/css/use_css_critical_path` on does core rewrite each stylesheet to `media="print"` with an `onload` that restores its media, which loads it without blocking; the default is `0`. |
| [Blank theme styles-m.less#L23](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/design/frontend/Magento/blank/web/css/styles-m.less#L23) | B | The theme's stylesheet imports the `source/_module.less` of every module, so styles placed there need no file of their own. |
| [Renderer.php#L439-L442](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L439-L442) | B | An asset group with a non-empty `ie_condition` is wrapped in `<!--[if ...]>` and `<![endif]-->`. |

Verified 2026-11-11.
