# customer-data-rules Specification

## Purpose
Rules on `sections.xml` that catch customer-data invalidation which makes browsers reload all private content sections far more often than needed.

## Requirements

### Requirement: X-SEC-01 broad section invalidation
X-SEC-01 SHALL report, in `sections.xml`:
- as `error`, an `<action name="*">`, which invalidates sections on every POST request, unless its only section is `messages` (how core shows flash messages after a POST);
- as `warning`, an action that invalidates all sections, either with `<section name="*"/>` or with no `<section>` children.

The warning SHALL NOT be reported for actions that change the customer, store or currency, which core itself configures that way: `customer/account/logout`, `customer/account/loginPost`, `customer/account/createPost`, `customer/account/editPost`, `stores/store/switch`, `stores/store/switchrequest` and `directory/currency/switch`.

#### Scenario: Wildcard action
- **WHEN** `sections.xml` contains `<action name="*"><section name="cart"/></action>`
- **THEN** X-SEC-01 reports an error

#### Scenario: All sections on custom action
- **WHEN** `sections.xml` contains `<action name="acme/wishlist/share"><section name="*"/></action>`
- **THEN** X-SEC-01 reports a warning

#### Scenario: Action without sections
- **WHEN** `sections.xml` contains `<action name="acme/cart/sync"/>`
- **THEN** X-SEC-01 reports a warning

#### Scenario: Login
- **WHEN** a module repeats `<action name="customer/account/loginPost"><section name="*"/></action>`
- **THEN** X-SEC-01 reports nothing

#### Scenario: Listed sections
- **WHEN** an action lists `<section name="cart"/>` and `<section name="checkout-data"/>`
- **THEN** X-SEC-01 reports nothing

#### Scenario: Messages on every POST
- **WHEN** `sections.xml` contains `<action name="*"><section name="messages"/></action>`
- **THEN** X-SEC-01 reports nothing

#### Scenario: Currency switch
- **WHEN** a module declares `<action name="directory/currency/switch"><section name="*"/></action>`
- **THEN** X-SEC-01 reports nothing
