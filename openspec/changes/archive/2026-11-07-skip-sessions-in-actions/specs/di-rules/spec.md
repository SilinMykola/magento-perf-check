## MODIFIED Requirements

### Requirement: P-DI-01 exclusions
P-DI-01 SHALL NOT report generated classes (names ending in `\Proxy`, `\Interceptor` or `Factory`) or classes that are themselves session managers. It SHALL NOT report a session parameter of a class that implements `Magento\Framework\App\ActionInterface`, because every action starts a session anyway; a cookie manager parameter there is still reported.

#### Scenario: Session subclass
- **WHEN** `Acme\Promo\Model\Session extends SessionManager` passes its own session dependencies to `parent::__construct`
- **THEN** P-DI-01 reports nothing for that class

#### Scenario: Session in a controller action
- **WHEN** a class implementing `ActionInterface` takes `Magento\Customer\Model\Session $customerSession` and `CookieManagerInterface $cookieManager` without a Proxy
- **THEN** P-DI-01 reports only the cookie manager
