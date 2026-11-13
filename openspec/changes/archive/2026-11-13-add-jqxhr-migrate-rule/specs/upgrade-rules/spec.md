## ADDED Requirements

### Requirement: U-JS-01 jqXHR methods that jquery-migrate added
U-JS-01 SHALL report a call of `success`, `error` or `complete` in a call chain that starts with `$.ajax`, `$.get`, `$.post`, `$.getJSON` or `$.getScript`, also written with `jQuery`, or with `get`, `post`, `put` or `delete` on an object named `storage` that is not itself a property, and goes on only through `done`, `fail`, `always` or `progress`, which return the same jqXHR. It SHALL read storefront and admin scripts. Its version SHALL be 2.4.6, and it SHALL start as experimental, at `error`.

#### Scenario: Old copy of a core script
- **WHEN** a theme's `Magento_Review/web/js/process-reviews.js` has `$.ajax({...}).done(...).complete(...)`
- **THEN** U-JS-01 reports the `complete` call

#### Scenario: mage/storage
- **WHEN** a script has `storage.post(url, payload).success(...)`
- **THEN** U-JS-01 reports it

#### Scenario: Admin script
- **WHEN** a module's `view/adminhtml/web/js/form.js` has `$.post(url, data).success(...)`
- **THEN** U-JS-01 reports it

#### Scenario: Call inside the arguments of another
- **WHEN** a script has `$.ajax({success: function () { $.get(url).complete(fn); }})`
- **THEN** U-JS-01 reports the `complete` call

#### Scenario: Not a jqXHR
- **WHEN** a script has `this.city().clear().error(false)`, `this.storage.get(key).success(...)`, `$('.form').on(...).error(...)`, `window.jQuery.ajax(url).success(...)`, `$j.ajax(url).success(...)`, `$.ajax(url)?.success(...)`, or the same code in a comment or a string
- **THEN** U-JS-01 reports nothing

#### Scenario: jQuery 3 methods
- **WHEN** a chain on `$.get(url)` uses only `done`, `fail`, `always` or `then`, or calls `success` after `then`
- **THEN** U-JS-01 reports nothing
