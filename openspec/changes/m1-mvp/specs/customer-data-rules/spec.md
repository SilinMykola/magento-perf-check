# Spec Delta

## Purpose

Rules on `sections.xml` that catch customer-data invalidation which makes browsers reload all private content sections far more often than needed.

## ADDED Requirements

### Requirement: X-SEC-01 broad section invalidation
X-SEC-01 SHALL report, in `sections.xml`:
- as `error`, an `<action name="*">`, which invalidates sections on every POST request;
- as `warning`, an action that invalidates all sections, either with `<section name="*"/>` or with no `<section>` children.

The warning SHALL NOT be reported for these actions, which core itself configures that way: `customer/account/logout`, `customer/account/loginPost`, `customer/account/createPost` and `customer/account/editPost`.

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
