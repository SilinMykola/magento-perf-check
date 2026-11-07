## ADDED Requirements

### Requirement: X-PLG-02 plugin on a method that runs per rendered block
X-PLG-02 SHALL report, as `info`, a public `before`, `after` or `around` method for `toHtml()` or `getChildHtml()` of a class configured as a plugin on `AbstractBlock` or `Template`, for `fetchView()` on `Template`, and for `renderElement()` on `Layout` or `LayoutInterface`. A plugin that any module disables, by target and name, or that is configured only in the `adminhtml` area SHALL not count. It is off by default.

#### Scenario: After plugin on the base block
- **WHEN** a class configured in `etc/frontend/di.xml` as a plugin on `AbstractBlock` has `afterToHtml()`
- **THEN** X-PLG-02 reports that method

#### Scenario: Another method of the plugin
- **WHEN** a plugin on `Template` has `aroundGetTemplate()`
- **THEN** X-PLG-02 reports nothing

#### Scenario: A plugin on a specific block
- **WHEN** a plugin on `Magento\Catalog\Block\Product\View` has `afterToHtml()`
- **THEN** X-PLG-02 reports nothing

#### Scenario: Admin area
- **WHEN** the plugin is configured only in `etc/adminhtml/di.xml`
- **THEN** X-PLG-02 reports nothing

#### Scenario: Disabled plugin
- **WHEN** the plugin has `disabled="true"`
- **THEN** X-PLG-02 reports nothing

#### Scenario: Disabled by another module
- **WHEN** another module has `<plugin name="same_name" disabled="true"/>` on the same class
- **THEN** X-PLG-02 reports nothing
