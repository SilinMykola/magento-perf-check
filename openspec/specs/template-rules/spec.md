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
T-04, experimental and off by default, SHALL report as `warning` an assignment in a template of `$viewModels->require(...)`, or of a call chain that starts with it, to a variable the template never reads.

#### Scenario: Navigation built and dropped
- **WHEN** a template runs `$menuItems = $viewModels->require(Navigation::class)->getNavigation(4);` and never reads `$menuItems`
- **THEN** T-04 reports the assignment
