# Spec Delta

## Purpose

Rules on PHP code that breaks or bypasses Full Page Cache and other caches: per-user data in cached output, uncacheable responses, state changes on cacheable requests, and cache or index invalidation from request-time code.

## ADDED Requirements

### Requirement: P-FPC-01 $_isScopePrivate
P-FPC-01 SHALL report, as `error`, a declaration of or assignment to `$_isScopePrivate` in a subclass of `Magento\Framework\View\Element\AbstractBlock`.

#### Scenario: Property declared
- **WHEN** a block declares `protected $_isScopePrivate = true;`
- **THEN** P-FPC-01 reports an error

#### Scenario: Unrelated class
- **WHEN** a class that is not a block declares a property with that name
- **THEN** P-FPC-01 reports nothing

### Requirement: P-FPC-02 customer data from session in blocks
P-FPC-02 SHALL report, as `warning`, calls to `getCustomer`, `getCustomerId`, `getCustomerGroupId` or `getQuote` on a subtype of `Magento\Framework\Session\SessionManagerInterface`. The call must be made in a block (`AbstractBlock` subclass) or a view model (implements `Magento\Framework\View\Element\Block\ArgumentInterface`).

#### Scenario: Block reads customer
- **WHEN** a block method calls `$this->customerSession->getCustomerId()`
- **THEN** P-FPC-02 reports a warning

#### Scenario: Controller reads customer
- **WHEN** a controller calls the same method
- **THEN** P-FPC-02 reports nothing

### Requirement: P-FPC-05 no-cache headers on cacheable requests
P-FPC-05 SHALL report, as `warning`, a call to `setNoCacheHeaders()`, `setPrivateHeaders()`, or `setHeader()` with a literal `Cache-Control` or `Pragma` name (case-insensitive), in a class that serves cacheable requests (see "P-FPC-05 scope"). The message SHALL ask to confirm that the response is meant to be uncacheable.

#### Scenario: GET controller sets no-cache
- **WHEN** a frontend controller implementing `HttpGetActionInterface` calls `$this->getResponse()->setNoCacheHeaders()`
- **THEN** P-FPC-05 reports a warning

#### Scenario: POST-only controller
- **WHEN** the controller implements only `HttpPostActionInterface`
- **THEN** P-FPC-05 reports nothing

### Requirement: P-FPC-05 scope
P-FPC-05 SHALL consider only:
- frontend controllers that can serve GET requests: not under `Controller\Adminhtml`, and implementing `HttpGetActionInterface` or no `Http*ActionInterface` at all;
- GraphQL resolvers: classes implementing `ResolverInterface` or `BatchResolverInterface`.

#### Scenario: Admin controller
- **WHEN** a controller under `Controller\Adminhtml` sets no-cache headers
- **THEN** P-FPC-05 reports nothing

#### Scenario: GraphQL resolver
- **WHEN** a resolver calls `setHeader('Cache-Control', 'no-store')` on the response
- **THEN** P-FPC-05 reports a warning

### Requirement: P-FPC-06 state change in a GET controller
P-FPC-06 SHALL report, as `error`, a controller that implements `HttpGetActionInterface` and not `HttpPostActionInterface`, whose `execute()` calls any of:
- `save()`, `delete()` or `deleteById()` on a model, resource model or `*RepositoryInterface`;
- `addProduct()` on `Magento\Checkout\Model\Cart` or `Magento\Quote\Model\Quote`.

#### Scenario: Add to cart on GET
- **WHEN** a GET-only controller's `execute()` calls `$this->cart->addProduct($product, $params)`
- **THEN** P-FPC-06 reports an error

#### Scenario: POST controller
- **WHEN** the same code is in a controller implementing `HttpPostActionInterface`
- **THEN** P-FPC-06 reports nothing

### Requirement: P-FPC-09 cache cleaning in code
P-FPC-09 SHALL report, as `error`, a call that cleans more cache than needed:
- `cleanType('full_page')` on `Magento\Framework\App\Cache\TypeListInterface`, with a literal argument or the `Type::TYPE_IDENTIFIER` constant;
- `clean()` with no tags or an empty array, on `Magento\Framework\App\CacheInterface`.

#### Scenario: Full page cache cleaned
- **WHEN** an observer calls `$this->typeList->cleanType('full_page')`
- **THEN** P-FPC-09 reports an error

#### Scenario: Tag-based clean
- **WHEN** code calls `$this->cache->clean(['catalog_product_42'])`
- **THEN** P-FPC-09 reports nothing

### Requirement: P-FPC-10 full reindex from request-time code
P-FPC-10 SHALL report, as `error`, `reindexAll()` on `Magento\Framework\Indexer\IndexerInterface` in one of:
- an observer (implements `ObserverInterface`);
- a controller (implements `Magento\Framework\App\ActionInterface`);
- a class the project index lists as a plugin.

`reindexList()` and `reindexRow()` SHALL NOT be reported.

#### Scenario: Observer rebuilds index
- **WHEN** an observer calls `$this->indexer->reindexAll()`
- **THEN** P-FPC-10 reports an error

#### Scenario: Partial reindex
- **WHEN** a plugin calls `$this->indexer->reindexList($ids)`
- **THEN** P-FPC-10 reports nothing
