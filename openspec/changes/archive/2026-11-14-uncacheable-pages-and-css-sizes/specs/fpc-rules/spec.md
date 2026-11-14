## MODIFIED Requirements

### Requirement: P-FPC-02 customer data from session in blocks
P-FPC-02 SHALL report, as `warning`, calls to `getCustomer`, `getCustomerId` or `getQuote` on a subtype of `Magento\Framework\Session\SessionManagerInterface`. The call must be made in a frontend block (an `AbstractBlock` subclass not under a `Block\Adminhtml` namespace) or a view model (implements `Magento\Framework\View\Element\Block\ArgumentInterface` and is not a collection). `getCustomerGroupId()` SHALL NOT be reported: page cache depersonalization keeps the customer group, and it is part of the cache key. A class that the project's frontend layouts name (`<block class>`, `<referenceBlock class>`, an `<argument>` or `<item>` of type `object`) only on handles no cacheable page uses SHALL NOT be reported, unless a subclass of it is named on a handle a cacheable page may use. Handles no cacheable page uses are those whose layout files hold a `cacheable="false"` block that neither it nor an element around it has an `ifconfig` and that no layout file of the handle removes, except shop-wide handles X-LAY-03 reports (`default`, `catalog_*`, `cms_*` outside its allowlist); account pages that include `customer_account`; and handles only such handles include, unless the handle is a page of its own (its name starts with a storefront route).

#### Scenario: Block reads customer
- **WHEN** a block method calls `$this->customerSession->getCustomerId()`
- **THEN** P-FPC-02 reports a warning

#### Scenario: Controller reads customer
- **WHEN** a controller calls the same method
- **THEN** P-FPC-02 reports nothing

#### Scenario: Customer group
- **WHEN** a block calls `$this->customerSession->getCustomerGroupId()`
- **THEN** P-FPC-02 reports nothing

#### Scenario: Admin block
- **WHEN** a block under `Block\Adminhtml` calls `$this->backendQuoteSession->getQuote()`
- **THEN** P-FPC-02 reports nothing

#### Scenario: Block only on uncacheable pages
- **WHEN** a block that reads `getCustomerId()` is placed only on a handle whose layout holds a `cacheable="false"` block, as Magento's `checkout_cart_index.xml` does, or that includes `customer_account`
- **THEN** P-FPC-02 reports nothing
- **WHEN** the same block is also placed in `catalog_product_view.xml`, or only in `default.xml` although a `default.xml` holds a `cacheable="false"` block, or the `cacheable="false"` block has an `ifconfig` or is removed, or a subclass of it is placed in `catalog_product_view.xml`, or no layout places it
- **THEN** P-FPC-02 reports the call
