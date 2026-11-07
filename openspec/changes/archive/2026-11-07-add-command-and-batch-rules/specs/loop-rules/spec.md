## ADDED Requirements

### Requirement: Product interface counts as a product
P-LOOP-04 and P-LOOP-06 SHALL treat a receiver typed `Magento\Catalog\Api\Data\ProductInterface` as a product, because a repository returns that type and the model behind it runs the same queries. P-LOOP-05 SHALL NOT, because `ProductRepository::getList()` returns that type after calling `addCategoryIds()`.

#### Scenario: Product URL on the interface
- **WHEN** a loop calls `getProductUrl()` on a variable typed `ProductInterface`
- **THEN** P-LOOP-04 reports that call

#### Scenario: Category IDs on the interface
- **WHEN** a loop calls `getCategoryIds()` on a variable typed `ProductInterface`
- **THEN** P-LOOP-05 reports nothing
