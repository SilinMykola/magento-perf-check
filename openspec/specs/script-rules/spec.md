# script-rules Specification

## Purpose
Rules on storefront JavaScript (`J-*`): which files the XML analyzer reads as scripts, how its scanner reads them, how a finding in them is suppressed, and the rules themselves.

## Requirements

### Requirement: Storefront scripts are read
The XML analyzer SHALL read, as storefront scripts, the `.js` files and `.phtml` templates under a module's `view/frontend` or `view/base` and under `app/design/frontend`, with the same skips as other files: test code, disabled modules and core packages unless a path points inside them. It SHALL skip `node_modules` folders and an `x.min.js` next to its `x.js`, and it SHALL read scripts only when a script rule is enabled. In a template it SHALL read only the bodies of `<script>` elements outside HTML comments whose `type` is missing, names JavaScript or is printed by PHP, with a `<?=` tag read as a value and other PHP as white space. A theme file is told apart by its `app/design/<area>/` path before any `view/<area>/` folder.

#### Scenario: Admin script
- **WHEN** a module's `view/adminhtml/web/js/admin.js` has `define(['jquery/ui'], ...)`
- **THEN** no script rule reports it

#### Scenario: Theme script
- **WHEN** `app/design/frontend/Acme/shop/web/js/theme.js` has `require(['jquery/ui'], ...)`
- **THEN** J-DEP-01 reports it

#### Scenario: Minified copy, node_modules and a disabled rule
- **WHEN** a module has `view/frontend/web/js/slider.js` and `slider.min.js`, both with `jquery/ui`, and a `node_modules` folder with the same
- **THEN** J-DEP-01, when enabled, reports only `slider.js`, and when not enabled the scripts are not read

#### Scenario: Template script types
- **WHEN** a storefront template has `jquery/ui` in a `<script type="text/x-magento-init">` block and in PHP code
- **THEN** J-DEP-01 reports neither, and it reports the same string in a `<script>` without a JavaScript-excluding type

### Requirement: Script scanner
The scanner SHALL give every string literal of the source with its value, unescaped, its line, whether it is an object key, and its property path. A literal is an object key when it follows `{` or `,` and the next token is `:`. Its property path SHALL list, outermost first, the key of the property each enclosing object literal is giving a value to; arrays, parentheses and other braces add nothing, and a key's own name is not in its path. A key SHALL be a quoted literal or an identifier that follows `{` or `,` and is followed by `:`, with white space or comments between them; a `,` ends the property of the object it belongs to. Escape sequences SHALL be decoded, and the text of a template literal SHALL yield strings while its `${...}` substitutions are scanned as code. Comments SHALL NOT yield string literals. A `/` after a value SHALL be read as a division, otherwise as the start of a regular expression literal, whose content yields no string.

#### Scenario: Ternary is not a key
- **WHEN** the source is `t = q ? 'jquery/ui' : ''`
- **THEN** the string `jquery/ui` is not an object key

#### Scenario: Quote in a regular expression
- **WHEN** the source is `x = /'"/.test(y) ? 'a' : 0`
- **THEN** the only strings are `a` and none comes from the regular expression

#### Scenario: Property paths
- **WHEN** the source is `var config = {deps: ['a'], shim: {'b': {deps: ['c']}}, map: {'*': {d: 'e'}}}`
- **THEN** `a` has the path `deps`, `c` the path `shim`, `b`, `deps`, `e` the path `map`, `*`, `d`, and the key `b` the path `shim`

### Requirement: Script suppression
A script finding SHALL be suppressed by a JavaScript comment `perf-ignore RULE-ID: reason` that ends on the line of the finding or on the line before it. A comment without a reason SHALL NOT suppress, and SHALL add a `suppression` notice.

#### Scenario: Comment on the line before
- **WHEN** `// perf-ignore J-DEP-01: needed here` is on the line before `require(['jquery/ui'])`
- **THEN** J-DEP-01 does not report it

#### Scenario: No reason
- **WHEN** `// perf-ignore J-DEP-01` is on the line before `require(['jquery/ui'])`
- **THEN** J-DEP-01 reports it and a `suppression` notice names the comment line

### Requirement: J-DEP-01 whole jQuery UI library
J-DEP-01 SHALL report, as `warning`, every string literal `jquery/ui` in a storefront script that is not an object key. The message SHALL say that core maps it to `jquery/compat` and name `jquery-ui-modules/*` IDs to use instead. It SHALL start as experimental.

#### Scenario: Dependency in define
- **WHEN** a module's `view/frontend/web/js/widget.js` has `define(['jquery', 'jquery/ui'], ...)`
- **THEN** J-DEP-01 reports a `warning`

#### Scenario: Shim dependency and map key
- **WHEN** a module's `view/frontend/requirejs-config.js` has `map: {'*': {'jquery/ui': 'jquery/compat'}}` and `shim: {x: {deps: ['jquery/ui']}}`
- **THEN** J-DEP-01 reports the shim dependency and not the map key

#### Scenario: Commented out
- **WHEN** a script has `// define(['jquery/ui'], ...)`
- **THEN** J-DEP-01 reports nothing

### Requirement: J-DEP-02 module loaded on every page by requirejs-config.js
J-DEP-02 SHALL report, as `info`, every string literal whose property path is exactly `deps`, other than an object key, in a storefront `requirejs-config.js` that core merges into every page: directly in a module's `view/frontend` or `view/base`, or in a frontend theme at its root or in a `<Vendor_Module>` folder. The message SHALL say that RequireJS loads the module on every storefront page and name `x-magento-init` or `data-mage-init` in the templates that need it as the alternative. It SHALL start as experimental.

#### Scenario: Top-level deps
- **WHEN** a module's `view/frontend/requirejs-config.js` has `var config = {deps: ['Acme_Rules/js/everywhere']}`
- **THEN** J-DEP-02 reports an `info` on that line

#### Scenario: Shim deps and other files
- **WHEN** a `requirejs-config.js` has `shim: {x: {deps: ['jquery']}}`, or a storefront `.js` file that is not `requirejs-config.js` has `{deps: ['jquery']}`
- **THEN** J-DEP-02 reports nothing

#### Scenario: Theme configuration
- **WHEN** `app/design/frontend/Acme/shop/requirejs-config.js` has a top-level `deps: ['js/theme']`
- **THEN** J-DEP-02 reports it
