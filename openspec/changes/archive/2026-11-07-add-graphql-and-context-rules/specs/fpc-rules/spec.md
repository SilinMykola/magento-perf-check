## ADDED Requirements

### Requirement: P-FPC-03 user-specific value in the HTTP context
P-FPC-03 SHALL report, as `error`, a call of `Magento\Framework\App\Http\Context::setValue()` whose value is specific to a visitor or a moment: a customer id, an email, a session id, a quote id, the id of a customer, quote or session object, or the current time, directly or through a cast, concatenation, hash, array, ternary branch, or a variable or property with such a name. It is off by default.

#### Scenario: Customer id
- **WHEN** a plugin calls `$this->httpContext->setValue('customer', $this->session->getCustomerId(), 0)`
- **THEN** P-FPC-03 reports the call

#### Scenario: Hashed email
- **WHEN** the value is `md5($customer->getEmail())`
- **THEN** P-FPC-03 reports the call

#### Scenario: Customer group
- **WHEN** the value is `$this->session->getCustomerGroupId()`
- **THEN** P-FPC-03 reports nothing

#### Scenario: A flag derived from the id
- **WHEN** the value is `(bool) $session->getCustomerId()` or `$session->getCustomerId() ? 1 : 0`
- **THEN** P-FPC-03 reports nothing

#### Scenario: Another class
- **WHEN** `setValue()` is called on a class that is not `Http\Context`
- **THEN** P-FPC-03 reports nothing
