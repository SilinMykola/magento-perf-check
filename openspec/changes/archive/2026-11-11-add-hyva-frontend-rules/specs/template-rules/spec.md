## ADDED Requirements

### Requirement: T-05 synchronous external script in a template
T-05 SHALL report, as `warning`, a `<script>` element with a `src` attribute and without `async`, `defer` or `type="module"` in a storefront template: a `.phtml` file under `view/frontend`, `view/base` or `design/frontend`. It SHALL NOT report a script whose type is not a JavaScript MIME type or is bound or printed, a tag inside a `<template>` element, an HTML comment or the body of another script, or a tag in which PHP prints something outside the quoted attribute values. It is in the default set after calibration round 9.

#### Scenario: Synchronous script
- **WHEN** a frontend template contains `<script src="https://example.com/widget.js"></script>`
- **THEN** T-05 reports it

#### Scenario: Deferred script
- **WHEN** a frontend template contains `<script defer src="https://example.com/widget.js"></script>`
- **THEN** T-05 reports nothing

#### Scenario: Attributes printed by PHP
- **WHEN** a frontend template contains `<script <?= $attributes ?> src="https://example.com/widget.js"></script>`
- **THEN** T-05 reports nothing

#### Scenario: Admin template
- **WHEN** a template under `view/adminhtml` contains `<script src="https://example.com/admin.js"></script>`
- **THEN** T-05 reports nothing

### Requirement: T-06 image without dimensions in a template
T-06 SHALL report, as `info`, an `<img>` element in a storefront template that does not have both a `width` and a `height` attribute, written or bound with `:` or `x-bind:`. A written `width` or `height` SHALL count only when its value is a number or printed by PHP. It SHALL NOT report an image inside a `<template>` element, an HTML comment or the body of a script, or a tag in which PHP prints something outside the quoted attribute values. It SHALL start as experimental.

#### Scenario: Image without dimensions
- **WHEN** a frontend template contains `<img src="banner.jpg" alt="">`
- **THEN** T-06 reports it

#### Scenario: Image with dimensions
- **WHEN** a frontend template contains `<img src="banner.jpg" width="1200" height="400" alt="">`
- **THEN** T-06 reports nothing

#### Scenario: Bound dimensions
- **WHEN** a frontend template contains `<img :src="image.url" :width="image.width" :height="image.height">`
- **THEN** T-06 reports nothing
