## ADDED Requirements

### Requirement: X-LAY-04 synchronous script in the layout head
X-LAY-04 SHALL report, as `info`, every `<script>` or `<link>` element with a `src` attribute that core renders as a script, inside `<head>` of a layout file of area `frontend` or `base`, when the element has neither an `async` nor a `defer` attribute. The message SHALL name the handle and the script, and say that `dev/js/defer_non_critical` makes core defer it. It SHALL start as experimental.

#### Scenario: Synchronous external script
- **WHEN** `catalog_product_view.xml` has `<head><script src="https://example.com/sdk.js" src_type="url"/></head>`
- **THEN** X-LAY-04 reports an `info` naming handle `catalog_product_view`

#### Scenario: Script added with link
- **WHEN** a frontend layout file has `<head><link src="Acme_Shop::js/widget.js"/></head>`
- **THEN** X-LAY-04 reports an `info`

#### Scenario: Base area
- **WHEN** `view/base/layout/default.xml` has `<head><script src="Acme_Shop::js/everywhere.js"/></head>`
- **THEN** X-LAY-04 reports an `info`

#### Scenario: Deferred script
- **WHEN** a frontend layout file has `<head><script defer="defer" src="Acme_Shop::js/widget.js"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Stylesheet added with link
- **WHEN** a frontend layout file has `<head><link src="Acme_Shop::css/widget.css"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Admin layout
- **WHEN** a layout file under `view/adminhtml` has `<head><script src="Acme_Shop::js/admin.js"/></head>`
- **THEN** X-LAY-04 reports nothing

### Requirement: X-LAY-04 what counts as a script
X-LAY-04 SHALL count an element as a script as core decides: with `src_type` `url` or `controller`, a `<script>`, or a `<link>` with `content_type="js"`; otherwise, an element whose `src`, as written, has the extension `js`.

#### Scenario: Remote link without a content type
- **WHEN** a frontend layout file has `<head><link src="https://example.com/widget.js" src_type="url"/></head>`
- **THEN** X-LAY-04 reports nothing, because core renders it as `<link href>`

#### Scenario: Local link with a query string
- **WHEN** a frontend layout file has `<head><link src="Acme_Shop::js/widget.js?v=2"/></head>`
- **THEN** X-LAY-04 reports nothing, because core takes `js?v=2` as the content type

### Requirement: X-LAY-04 scripts left out
X-LAY-04 SHALL NOT report a script that core never defers: the RequireJS loader, `requirejs-config.js`, `requirejs/mixins.js`, `requirejs/baseUrlResolver.js`, `requirejs-map.js`, `requirejs-min-resolver.js`, `static.js`, files in `js/bundle/`, `_cache/` or `merged/`, and `sri.js`. It SHALL NOT report an element with an `ie_condition` attribute.

#### Scenario: Script core never defers
- **WHEN** a frontend layout file has `<head><script src="requirejs/require.js"/></head>`
- **THEN** X-LAY-04 reports nothing

#### Scenario: Internet Explorer only
- **WHEN** a frontend layout file has `<head><script src="Acme_Shop::js/polyfill.js" ie_condition="lt IE 11"/></head>`
- **THEN** X-LAY-04 reports nothing
