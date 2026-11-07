## MODIFIED Requirements

### Requirement: P-FPC-05 no-cache headers on cacheable requests
P-FPC-05 SHALL report, as `warning`, one finding per statement that disables caching: `setNoCacheHeaders()`, `setPrivateHeaders()`, or `setHeader()` with a literal `Cache-Control` or `Pragma` name (case-insensitive) and a value that contains `no-cache`, `no-store` or `private`, or is not a literal. It applies in a class that serves cacheable requests (see "P-FPC-05 scope"). The message SHALL ask to confirm that the response is meant to be uncacheable. A call on `$this` SHALL NOT count: a controller's own helper is not the response.

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

#### Scenario: Own helper method
- **WHEN** a GET controller calls `$this->setNoCacheHeaders()`, its own private method, which calls `$this->response->setHeader('Pragma', 'no-cache')`
- **THEN** P-FPC-05 reports one warning, on the `setHeader()` call
