# fpc-rules Specification

## Purpose
Rules on PHP code that breaks or bypasses Full Page Cache and other caches: per-user data in cached output, uncacheable responses, state changes on cacheable requests, and cache or index invalidation from request-time code.

## Requirements

### Requirement: P-FPC-01 $_isScopePrivate
P-FPC-01 SHALL report, as `info`, a declaration of `$_isScopePrivate` with the default value `true`, or an assignment of `true` to it, in a subclass of `Magento\Framework\View\Element\AbstractBlock`. Calibration lowered it from `error`: precision 87 %, and most correct findings are blocks on pages that are not cached anyway.

#### Scenario: Property declared
- **WHEN** a block declares `protected $_isScopePrivate = true;`
- **THEN** P-FPC-01 reports an info finding

#### Scenario: Unrelated class
- **WHEN** a class that is not a block declares a property with that name
- **THEN** P-FPC-01 reports nothing

#### Scenario: False is the default
- **WHEN** a block declares `protected $_isScopePrivate = false;` or assigns `false` to it
- **THEN** P-FPC-01 reports nothing

### Requirement: P-FPC-02 customer data from session in blocks
P-FPC-02 SHALL report, as `warning`, calls to `getCustomer`, `getCustomerId` or `getQuote` on a subtype of `Magento\Framework\Session\SessionManagerInterface`. The call must be made in a frontend block (an `AbstractBlock` subclass not under a `Block\Adminhtml` namespace) or a view model (implements `Magento\Framework\View\Element\Block\ArgumentInterface` and is not a collection). `getCustomerGroupId()` SHALL NOT be reported: page cache depersonalization keeps the customer group, and it is part of the cache key.

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

### Requirement: P-FPC-05 no-cache headers on cacheable requests
P-FPC-05 SHALL report, as `warning`, one finding per statement that disables caching: `setNoCacheHeaders()`, `setPrivateHeaders()`, or `setHeader()` with a literal `Cache-Control` or `Pragma` name (case-insensitive) and a value that contains `no-cache`, `no-store` or `private`, or is not a literal. It applies in a class that serves cacheable requests (see "P-FPC-05 scope"). The message SHALL ask to confirm that the response is meant to be uncacheable.

#### Scenario: GET controller sets no-cache
- **WHEN** a frontend controller implementing `HttpGetActionInterface` calls `$this->getResponse()->setNoCacheHeaders()`
- **THEN** P-FPC-05 reports a warning

#### Scenario: POST-only controller
- **WHEN** the controller implements only `HttpPostActionInterface`
- **THEN** P-FPC-05 reports nothing

#### Scenario: Public pragma
- **WHEN** a GET controller calls `setHeader('Pragma', 'public', true)`
- **THEN** P-FPC-05 reports nothing

#### Scenario: Two headers in one chain
- **WHEN** one statement calls `->setHeader('Pragma', 'no-cache')->setHeader('Cache-Control', 'no-store')`
- **THEN** P-FPC-05 reports one warning

### Requirement: P-FPC-05 scope
P-FPC-05 SHALL consider only:
- frontend controllers that can serve GET requests: not under `Controller\Adminhtml`, and implementing `HttpGetActionInterface` or no `Http*ActionInterface` at all;
- GraphQL resolvers: classes implementing `ResolverInterface` or `BatchResolverInterface`.

Controllers of core endpoints that are private by design SHALL NOT be reported: `Magento\Customer\Controller\Section\Load`, `Magento\PageCache\Controller\Block\Render` and the controllers under `Magento\Downloadable\Controller\Download`.

#### Scenario: Admin controller
- **WHEN** a controller under `Controller\Adminhtml` sets no-cache headers
- **THEN** P-FPC-05 reports nothing

#### Scenario: GraphQL resolver
- **WHEN** a resolver calls `setHeader('Cache-Control', 'no-store')` on the response
- **THEN** P-FPC-05 reports a warning

#### Scenario: Customer section endpoint
- **WHEN** `Magento\Customer\Controller\Section\Load` sets no-cache headers
- **THEN** P-FPC-05 reports nothing

### Requirement: P-FPC-06 state change in a GET controller
P-FPC-06 SHALL report, as `error`, a frontend controller (not under `Controller\Adminhtml`) that implements `HttpGetActionInterface` and not `HttpPostActionInterface`, whose `execute()` calls any of:
- `save()`, `delete()` or `deleteById()` on a model, resource model or `*RepositoryInterface`;
- `addProduct()` on `Magento\Checkout\Model\Cart` or `Magento\Quote\Model\Quote`.

It SHALL NOT report a controller whose `execute()` returns only redirect, forward, JSON or raw results. Varnish and the built-in page cache store only 200 and 404 responses, and `FrontController` sets no-cache headers before every action, which only a page result replaces with public ones.

#### Scenario: Add to cart on GET
- **WHEN** a GET-only controller's `execute()` calls `$this->cart->addProduct($product, $params)` and returns a page
- **THEN** P-FPC-06 reports an error

#### Scenario: POST controller
- **WHEN** the same code is in a controller implementing `HttpPostActionInterface`
- **THEN** P-FPC-06 reports nothing

#### Scenario: Admin controller
- **WHEN** an Adminhtml GET controller saves a model
- **THEN** P-FPC-06 reports nothing

#### Scenario: Redirect only
- **WHEN** a GET-only controller saves a model and every return statement returns a redirect result
- **THEN** P-FPC-06 reports nothing

#### Scenario: Redirect from the result factory
- **WHEN** a GET-only controller saves a model, creates its result with `$this->resultFactory->create(ResultFactory::TYPE_REDIRECT)` and returns only that result
- **THEN** P-FPC-06 reports nothing

#### Scenario: JSON result
- **WHEN** a GET-only controller saves a model and returns only a result from `$this->resultFactory->create(ResultFactory::TYPE_JSON)` or from a `$resultJsonFactory`
- **THEN** P-FPC-06 reports nothing

### Requirement: P-FPC-09 cache cleaning in code
P-FPC-09 SHALL report, as `error`, a call that cleans more cache than needed, in code that runs during requests:
- `cleanType('full_page')` on `Magento\Framework\App\Cache\TypeListInterface`, with a literal argument or the `Type::TYPE_IDENTIFIER` constant;
- `clean()` with no tags or an empty array, on `Magento\Framework\App\CacheInterface`.

It SHALL NOT report CLI commands (subclasses of `Symfony\Component\Console\Command\Command`), setup code (classes in a `Setup` namespace or under `setup/`), deployment configuration importers (implementations of `Magento\Framework\App\DeploymentConfig\ImporterInterface`), or configuration backend models (subclasses of `Magento\Framework\App\Config\Value`). In those places a full flush is the intended action.

#### Scenario: Full page cache cleaned
- **WHEN** an observer calls `$this->typeList->cleanType('full_page')`
- **THEN** P-FPC-09 reports an error

#### Scenario: Tag-based clean
- **WHEN** code calls `$this->cache->clean(['catalog_product_42'])`
- **THEN** P-FPC-09 reports nothing

#### Scenario: CLI command
- **WHEN** a console command calls `$this->typeList->cleanType('full_page')`
- **THEN** P-FPC-09 reports nothing

#### Scenario: Config backend model
- **WHEN** an `afterSave()` of a `Magento\Framework\App\Config\Value` subclass calls `cleanType('full_page')`
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
