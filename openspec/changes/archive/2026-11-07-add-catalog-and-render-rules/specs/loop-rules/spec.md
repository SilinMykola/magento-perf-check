## ADDED Requirements

### Requirement: P-LOOP-04 product URL inside a loop
P-LOOP-04 SHALL report, as `warning`, `getProductUrl()` or `getUrlInStore()` on a product, or `getProductUrl()`, `getUrl()` or `getUrlInStore()` on `Magento\Catalog\Model\Product\Url`, inside a loop, unless `addUrlRewrite()` was called earlier in the same function. It is off by default.

#### Scenario: Loop over a product collection
- **WHEN** a function iterates a product collection and calls `$product->getProductUrl()`
- **THEN** P-LOOP-04 reports the call

#### Scenario: Rewrites added
- **WHEN** the function called `$collection->addUrlRewrite()` before the loop
- **THEN** P-LOOP-04 reports nothing

#### Scenario: Outside a loop
- **WHEN** `getProductUrl()` is called on one product outside a loop
- **THEN** P-LOOP-04 reports nothing

#### Scenario: Another class
- **WHEN** a block's own `getProductUrl($product)` is called in a loop
- **THEN** P-LOOP-04 reports nothing

### Requirement: P-LOOP-06 attribute option text inside a loop
P-LOOP-06 SHALL report, as `warning`, `getAttributeText()` on a product, or `getOptionText()` on `Magento\Eav\Model\Entity\Attribute\Source\Table`, inside a loop. It is off by default.

#### Scenario: Attribute text per product
- **WHEN** a loop over products calls `$product->getAttributeText('color')`
- **THEN** P-LOOP-06 reports the call

#### Scenario: Option text on the table source
- **WHEN** a loop calls `getOptionText()` on a `Source\Table`
- **THEN** P-LOOP-06 reports the call

#### Scenario: Other source model
- **WHEN** a loop calls `getOptionText()` on another source model
- **THEN** P-LOOP-06 reports nothing

#### Scenario: Outside a loop
- **WHEN** `getAttributeText()` is called outside a loop
- **THEN** P-LOOP-06 reports nothing

### Requirement: P-LOOP-02 paging
P-LOOP-02 SHALL not report a collection created in a `while`, `do` or `for` loop outside a loop over items, when `setPageSize()`, `setPage()`, `limit()` or `limitPage()` sets a row count other than the literal 1 on it before it loads: one query per page is what paging is for. A `foreach` and a `for` with `count()` or `sizeof()` in its condition are loops over items. A limit in a loop over items, a limit of one row, and `setCurPage()` alone SHALL still be reported.

#### Scenario: Pages in a while loop
- **WHEN** a `while` loop creates a collection, calls `getSelect()->limit(500)` and loads it
- **THEN** P-LOOP-02 reports nothing

#### Scenario: One row per item
- **WHEN** a `foreach` over ids creates a collection, calls `setPageSize(1)` and `getFirstItem()`
- **THEN** P-LOOP-02 reports the call

#### Scenario: Written in one chain
- **WHEN** a `do` loop has `$c = $factory->create(); $c->setPageSize(100)->setCurPage($p)->load();`
- **THEN** P-LOOP-02 reports nothing

#### Scenario: One row in a while loop
- **WHEN** a `while` loop creates a collection and calls `setPageSize(1)` and `load()`
- **THEN** P-LOOP-02 reports the call

#### Scenario: Loop over a count
- **WHEN** a `for` with `count($ids)` in its condition creates a collection and calls `setPageSize(100)` and `load()`
- **THEN** P-LOOP-02 reports the call

