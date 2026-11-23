## MODIFIED Requirements

### Requirement: P-FPC-05 no-cache headers on cacheable requests
P-FPC-05 SHALL report, as `warning`, one finding per statement that disables caching: `setNoCacheHeaders()`, `setPrivateHeaders()`, or `setHeader()` with a literal `Cache-Control` name (case-insensitive) and a value that contains `no-cache`, `no-store` or `private`, or is not a literal. A value built from literals, by concatenation, interpolation or as the format of `sprintf()`, counts by those literals: it SHALL be reported when they contain `no-cache`, `no-store` or `private`, and SHALL NOT be when they contain `public` or `max-age` without those and every other part is an `int` or a `sprintf()` conversion other than `%s`, as `sprintf('public, max-age=%d', $ttl)` asks for caching. A `setHeader()` on a block (`Magento\Framework\View\Element\BlockInterface`, such as what `getBlock()` returns) SHALL NOT count: it sets the block's `header` data. It applies in a class that serves cacheable requests (see "P-FPC-05 scope"). The message SHALL ask to confirm that the response is meant to be uncacheable. A call on `$this` SHALL NOT count: a controller's own helper is not the response.

#### Scenario: GET controller sets no-cache
- **WHEN** a frontend controller implementing `HttpGetActionInterface` calls `$this->getResponse()->setNoCacheHeaders()`
- **THEN** P-FPC-05 reports a warning

#### Scenario: POST-only controller
- **WHEN** the controller implements only `HttpPostActionInterface`
- **THEN** P-FPC-05 reports nothing

#### Scenario: Public pragma
- **WHEN** a GET controller calls `setHeader('Pragma', 'public', true)`
- **THEN** P-FPC-05 reports nothing

#### Scenario: No-cache pragma
- **WHEN** a GET controller calls `setHeader('Pragma', 'no-cache', true)`
- **THEN** P-FPC-05 reports nothing, because neither page cache reads `Pragma` from a response

#### Scenario: Two headers in one chain
- **WHEN** one statement calls `->setHeader('Pragma', 'no-cache')->setHeader('Cache-Control', 'no-store')`
- **THEN** P-FPC-05 reports one warning

#### Scenario: Own helper method
- **WHEN** a GET controller calls `$this->setNoCacheHeaders()`, its own private method, which calls `$this->response->setHeader('Cache-Control', 'no-store')`
- **THEN** P-FPC-05 reports one warning, on the `setHeader()` call

#### Scenario: Computed public value
- **WHEN** a GET controller calls `setHeader('Cache-Control', sprintf('public, max-age=%d', $ttl), true)`
- **THEN** P-FPC-05 reports nothing
- **WHEN** the format is `'private, max-age=%d'`, or the value is a variable
- **THEN** P-FPC-05 reports a warning

#### Scenario: Header data of a block
- **WHEN** a GET controller calls `setHeader('Cache-Control', 'no-cache')` on the result of `$page->getLayout()->getBlock('name')`
- **THEN** P-FPC-05 reports nothing
