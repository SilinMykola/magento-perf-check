# template-rules Specification

## Purpose
Rules on `.phtml` templates that catch logic which belongs in blocks, view models or layout rather than in the template.

## Requirements

### Requirement: T-01 createBlock in a template
T-01 SHALL report, as `info`, a call chain `->getLayout()->createBlock(...)` in a `.phtml` file, on any receiver. The message SHALL say that the Technical guidelines forbid it and that the block should be declared in layout XML instead. Calibration lowered it from `warning`: most correct findings create one block per render of an uncacheable form.

#### Scenario: Block created in template
- **WHEN** a template contains `$block->getLayout()->createBlock(\Acme\Promo\Block\Badge::class)->toHtml()`
- **THEN** T-01 reports an info finding

#### Scenario: Child block from layout
- **WHEN** a template calls `$block->getChildHtml('badge')`
- **THEN** T-01 reports nothing

#### Scenario: createBlock in a PHP class
- **WHEN** a block class calls `$this->getLayout()->createBlock(...)`
- **THEN** T-01 reports nothing

### Requirement: T-03 JavaScript function declared once per item
T-03, experimental and off by default, SHALL report as `warning` a named JavaScript function, `function name(`, declared inside a `<script>` in a part of a template rendered once per item: inside a PHP `foreach`, `for`, `while` or `do` loop of the template, or anywhere in `templates/product/list/item.phtml`. A name built with PHP output and an anonymous function SHALL NOT be reported, nor SHALL text outside a `<script>`.

#### Scenario: Function in the Hyvä product list item
- **WHEN** `Magento_Catalog/templates/product/list/item.phtml` declares `function initWishlist()` in a `<script>`
- **THEN** T-03 reports it

#### Scenario: Function once per page
- **WHEN** a template declares `function initOncePerPage()` in a `<script>` outside any loop
- **THEN** T-03 reports nothing

### Requirement: T-04 view model required and never used
T-04, experimental and off by default, SHALL report as `warning` an assignment in a template of `$viewModels->require(...)`, of a call chain that starts with it, or of a call chain on a variable holding such a view model, to a variable the template never reads. A template that reads variables by name, through `compact()`, `extract()`, `get_defined_vars()` or `$$name`, SHALL NOT be reported, nor SHALL the inner assignment of `$a = $b = ...`.

#### Scenario: Navigation built and dropped
- **WHEN** a template runs `$menuItems = $viewModels->require(Navigation::class)->getNavigation(4);` and never reads `$menuItems`
- **THEN** T-04 reports the assignment

#### Scenario: Call on a held view model
- **WHEN** a template runs `$navigation = $viewModels->require(Navigation::class);` and `$menuItems = $navigation->getNavigation(4);` and never reads `$menuItems`
- **THEN** T-04 reports the second assignment

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
T-06 SHALL report, as `info`, an `<img>` element in a storefront template that does not have both a `width` and a `height` attribute, written or bound with `:` or `x-bind:`. A written `width` or `height` SHALL count only when its value is a number or printed by PHP. It SHALL NOT report an image inside a `<template>` element, an HTML comment or the body of a script, or a tag in which PHP prints something outside the quoted attribute values. It SHALL NOT report an image whose box CSS fixes: the rules of the template's `<style>` elements for all media whose selector ends in `img` or in a compound of classes the image has, applied in order and outside at-rules after its `width` and `height` attributes, then its `style` attribute, set a height in a length unit together with a width or an `aspect-ratio`, or a width (a length or a percentage) with an `aspect-ratio`. Selectors with a pseudo-class or an attribute do not count. It SHALL start as experimental.

#### Scenario: Image without dimensions
- **WHEN** a frontend template contains `<img src="banner.jpg" alt="">`
- **THEN** T-06 reports it

#### Scenario: Image with dimensions
- **WHEN** a frontend template contains `<img src="banner.jpg" width="1200" height="400" alt="">`
- **THEN** T-06 reports nothing

#### Scenario: Bound dimensions
- **WHEN** a frontend template contains `<img :src="image.url" :width="image.width" :height="image.height">`
- **THEN** T-06 reports nothing

#### Scenario: Box fixed by CSS in the template
- **WHEN** a template has `<style>.faq img.group-image { width: 6.25rem; height: 6.25rem; }</style>` and `<img class="group-image" src="a.png" alt="">`, or an image with `style="width: 100px; height: 100px"`
- **THEN** T-06 reports nothing
- **WHEN** the rule sets only a width, sets `height: auto` or `height: 100%`, needs a class the image does not have, has a pseudo-class, is inside `@media`, or a later rule resets the height
- **THEN** T-06 reports the image
