## MODIFIED Requirements

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
