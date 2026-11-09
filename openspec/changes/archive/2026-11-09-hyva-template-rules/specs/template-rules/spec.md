## ADDED Requirements

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
