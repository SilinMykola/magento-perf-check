## ADDED Requirements

### Requirement: X-LAY-05 module stylesheet on every page
X-LAY-05 SHALL report, as `warning`, every `<css>` or `<link>` element with a `src` attribute that core renders as a stylesheet, inside `<head>` of a module layout file of area `frontend` or `base` whose handle is `default`, `default_head_blocks`, `hyva_default` or `hyva_default_head_blocks`. The message SHALL name the stylesheet and the handle, and say that `dev/css/use_css_critical_path` makes core load it without blocking. It SHALL be in the default set.

#### Scenario: Module stylesheet in default.xml
- **WHEN** a module's `view/frontend/layout/default.xml` has `<head><css src="Acme_Rules::css/slider.css"/></head>`
- **THEN** X-LAY-05 reports a `warning` naming handle `default`

#### Scenario: Head blocks, Hyvä copy and base area
- **WHEN** a module's `default_head_blocks.xml`, `hyva_default.xml`, `hyva_default_head_blocks.xml` or `view/base/layout/default.xml` has a `<css src>` in `<head>`
- **THEN** X-LAY-05 reports a `warning`

#### Scenario: Other handle
- **WHEN** a module's `catalog_product_view.xml` has `<head><css src="Acme_Rules::css/styles.css"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Theme layout
- **WHEN** a theme's `Magento_Theme/layout/default_head_blocks.xml` has `<head><css src="css/theme.css"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Admin layout
- **WHEN** a module's `view/adminhtml/layout/default.xml` has `<head><css src="Acme_Rules::css/admin.css"/></head>`
- **THEN** X-LAY-05 reports nothing

### Requirement: X-LAY-05 what counts as a stylesheet
X-LAY-05 SHALL count an element as a stylesheet as core decides. Its content type is, with `src_type` `url` or `controller`, `css` for a `<css>` and otherwise the written `content_type`, or `link` without one; for a local element, the extension of its `src` as written. An element is a stylesheet when its content type is `css`, or when its content type is neither `js` nor a font type (`eot`, `svg`, `ttf`, `woff`, `woff2`) and it has `rel="stylesheet"` in any case.

#### Scenario: Local link
- **WHEN** a module's `default.xml` has `<head><link src="Acme_Rules::css/linked.css"/></head>`
- **THEN** X-LAY-05 reports a `warning`

#### Scenario: Remote stylesheet
- **WHEN** a module's `default.xml` has `<head><css src="https://fonts.example.com/css?family=Sans" src_type="url"/></head>` or `<head><link rel="stylesheet" src="https://cdn.example.com/library.css" src_type="url"/></head>`
- **THEN** X-LAY-05 reports a `warning`

#### Scenario: Remote stylesheet by content type, controller or mixed case
- **WHEN** a module's `default.xml` has `<head><link src="https://cdn.example.com/typed.css" src_type="url" content_type="css"/></head>`, `<head><css src="acme/styles/dynamic" src_type="controller"/></head>` or `<head><link rel="Stylesheet" src="https://cdn.example.com/upper-rel.css" src_type="url"/></head>`
- **THEN** X-LAY-05 reports a `warning`

#### Scenario: Print in mixed case
- **WHEN** a module's `default.xml` has `<head><css src="Acme_Rules::css/print-upper.css" media=" Print"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Remote link without rel
- **WHEN** a module's `default.xml` has `<head><link src="https://cdn.example.com/no-rel.css" src_type="url"/></head>`
- **THEN** X-LAY-05 reports nothing, because core renders it as `<link href>` without `rel`

#### Scenario: Script or font with rel stylesheet
- **WHEN** a module's `default.xml` has `<head><link rel="stylesheet" src="Acme_Rules::fonts/icons.woff2"/></head>` or `<head><link rel="stylesheet" defer="defer" src="Acme_Rules::js/rel.js"/></head>`
- **THEN** X-LAY-05 reports nothing

### Requirement: X-LAY-05 stylesheets left out
X-LAY-05 SHALL NOT report an element whose `media` is `print` in any case, or that has a non-empty `ie_condition` attribute.

#### Scenario: Print stylesheet
- **WHEN** a module's `default.xml` has `<head><css src="Acme_Rules::css/print.css" media="print"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Internet Explorer only
- **WHEN** a module's `default.xml` has `<head><css src="Acme_Rules::css/old-ie.css" ie_condition="lt IE 9"/></head>`
- **THEN** X-LAY-05 reports nothing

#### Scenario: Empty Internet Explorer condition
- **WHEN** a module's `default.xml` has `<head><css src="Acme_Rules::css/empty-ie.css" ie_condition=""/></head>` or `ie_condition="0"`
- **THEN** X-LAY-05 reports a `warning`, because core does not wrap it

## MODIFIED Requirements

### Requirement: X-LAY-04 scripts left out
X-LAY-04 SHALL NOT report a script that core never defers: the RequireJS loader, `requirejs-config.js`, `requirejs/mixins.js`, `requirejs/baseUrlResolver.js`, `requirejs-map.js`, `requirejs-min-resolver.js`, `static.js`, files in `js/bundle/`, `_cache/` or `merged/`, and `sri.js`. It SHALL NOT report an element with a non-empty `ie_condition` attribute, which core wraps in an Internet Explorer conditional comment.

#### Scenario: Script core never defers
- **WHEN** a frontend layout file has `<head><script src="requirejs/require.js"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Internet Explorer only
- **WHEN** a frontend layout file has `<head><script src="Acme_Shop::js/polyfill.js" ie_condition="lt IE 11"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Empty Internet Explorer condition
- **WHEN** a frontend layout file has `<head><script src="Acme_Shop::js/empty-ie.js" ie_condition=""/></head>`
- **THEN** X-LAY-04 reports an `info`, because core does not wrap it
