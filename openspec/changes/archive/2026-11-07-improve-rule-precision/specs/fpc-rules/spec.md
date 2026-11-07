## MODIFIED Requirements

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
