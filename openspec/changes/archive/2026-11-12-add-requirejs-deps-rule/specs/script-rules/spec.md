## MODIFIED Requirements

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

## ADDED Requirements

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
