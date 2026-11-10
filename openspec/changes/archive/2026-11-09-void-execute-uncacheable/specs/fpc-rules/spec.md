## MODIFIED Requirements

### Requirement: P-FPC-06 state change in a GET controller
P-FPC-06 SHALL report, as `error`, a frontend controller (not under `Controller\Adminhtml`) that implements `HttpGetActionInterface` and not `HttpPostActionInterface`, whose `execute()` calls any of:
- `save()`, `delete()` or `deleteById()` on a model, resource model or `*RepositoryInterface`;
- `addProduct()` on `Magento\Checkout\Model\Cart` or `Magento\Quote\Model\Quote`.

It SHALL NOT report a controller whose `execute()` returns only redirect, forward, JSON or raw results. Nor SHALL it report one whose `execute()` returns no value, by `return;`, no return at all or reaching its end, unless it may render a layout: a call whose name contains `layout` or `render`, a call on a page, layout or view object, or a call of its own class other than `getRequest()`, `getResponse()` and `_redirect()`. Such an action sends its own response, which keeps the no-cache headers; a rendered layout sets public ones. An action that sets public headers itself, by `setPublicHeaders()` or `setHeader('Cache-Control', ...)`, SHALL be reported whatever it returns. Varnish and the built-in page cache store only 200 and 404 responses, and `FrontController` sets no-cache headers before every action, which only a page result replaces with public ones.

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

#### Scenario: Action that returns nothing
- **WHEN** a GET-only controller saves the quote in an `execute()` with no return value and renders no layout
- **THEN** P-FPC-06 reports nothing
- **WHEN** the same `execute()` calls `$this->_view->loadLayout()` or a method of its own class
- **THEN** P-FPC-06 reports the save
