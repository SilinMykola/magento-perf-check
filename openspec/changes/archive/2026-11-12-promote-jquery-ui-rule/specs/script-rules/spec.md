## MODIFIED Requirements

### Requirement: J-DEP-01 whole jQuery UI library
J-DEP-01 SHALL report, as `warning`, every string literal `jquery/ui` in a storefront script that is not an object key. The message SHALL say that core maps it to `jquery/compat` and name `jquery-ui-modules/*` IDs to use instead. It SHALL be in the default set.

#### Scenario: Dependency in define
- **WHEN** a module's `view/frontend/web/js/widget.js` has `define(['jquery', 'jquery/ui'], ...)`
- **THEN** J-DEP-01 reports a `warning`

#### Scenario: Shim dependency and map key
- **WHEN** a module's `view/frontend/requirejs-config.js` has `map: {'*': {'jquery/ui': 'jquery/compat'}}` and `shim: {x: {deps: ['jquery/ui']}}`
- **THEN** J-DEP-01 reports the shim dependency and not the map key

#### Scenario: Commented out
- **WHEN** a script has `// define(['jquery/ui'], ...)`
- **THEN** J-DEP-01 reports nothing

#### Scenario: Default run
- **WHEN** a project without a configuration file has a storefront script with `define(['jquery/ui'], ...)`
- **THEN** J-DEP-01 reports it
