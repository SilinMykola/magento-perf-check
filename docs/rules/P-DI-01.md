# P-DI-01: session or cookie manager injected without a Proxy

| | |
|---|---|
| Severity | `warning` for sessions (`magentoPerf.sessionWithoutProxy`), `info` for the cookie manager (`magentoPerf.cookieManagerWithoutProxy`); one step lower than before calibration, because most findings are correct but low impact ([calibration](../calibration.md)) |
| Status | default set |
| Analyzer | PHPStan, with the project index of `di.xml` |

## What it catches

A constructor parameter whose type is a session (`Magento\Framework\Session\SessionManagerInterface` and its subtypes, such as `Magento\Customer\Model\Session` or `Magento\Checkout\Model\Session`) or the cookie manager (`Magento\Framework\Stdlib\CookieManagerInterface`). It is reported when no `di.xml` in the project configures a `\Proxy` for that argument, neither for the class nor for one of its parent classes, in any area.

The rule does not report:
- generated classes (`\Proxy`, `\Interceptor`, `Factory`);
- session classes themselves;
- parameters already type-hinted as `\Proxy` (magento-coding-standard reports those).

When the project has no Magento configuration at all, the rule reports nothing.

## Why it hurts performance

The object manager creates constructor dependencies together with the object. `SessionManager::__construct()` calls `start()`, which calls `session_start()`. A block, view model or plugin that receives the customer session therefore starts a session (session storage read, locking) every time it is built, even when the code path never uses it. The Technical guidelines forbid injecting Request, Session and Cookie objects into constructors at all.

How much this costs depends on where the object is built:
- **Frontend actions.** On a page cache miss, the core `Magento\Customer\Model\App\Action\ContextPlugin` (a `beforeExecute` plugin on every frontend `ActionInterface`, declared in `Magento_Customer`'s `etc/frontend/di.xml`) reads the customer session anyway, so one more session dependency in a block or controller adds little.
- **Before action dispatch, and outside the frontend.** Objects built on the page cache hit path (for example plugins on the front controller), and in GraphQL, REST, cron or CLI, start a session that nothing else would start. There it is real cost.

Calibration on Magento core and popular modules found mostly the first case, so most findings have low impact (see [calibration](../calibration.md)).

## Example

Bad:

```php
class Banner extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        Template\Context $context,
        private readonly \Magento\Customer\Model\Session $customerSession,
    ) {
        parent::__construct($context);
    }
}
```

Good: keep the type hint and let `di.xml` inject a Proxy, which starts the session only when a method is called:

```xml
<type name="Acme\Promo\Block\Banner">
    <arguments>
        <argument name="customerSession" xsi:type="object">Magento\Customer\Model\Session\Proxy</argument>
    </arguments>
</type>
```

Better still, for cacheable pages: do not read customer data on the server at all, and load it through customer data sections (see P-FPC-02).

## How to suppress

```php
// @phpstan-ignore magentoPerf.sessionWithoutProxy (controller for a POST-only action that always needs the session)
```

## Known false positives

- A Proxy configured through a virtual type is recognized for the class the virtual type is based on, but a `<preference>` that swaps in another class with its own `di.xml` arguments is not followed.
- Classes that run only in contexts where a session is needed anyway, such as POST controllers of the customer account, may be fine without a Proxy.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Technical guidelines](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) | A | Section 9.10: "The Request, Session, and Cookie objects MUST NOT be injected in an object constructor." |
| [SessionManager.php#L100-L125](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Session/SessionManager.php#L100-L125) | B | The constructor calls `start()`. |
| [SessionManager.php#L162-L208](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Session/SessionManager.php#L162-L208) | B | `start()` calls `session_start()` (not under CLI). |
| [Atwix: session depersonalization](https://www.atwix.com/magento-2/what-is-session-depersonalization/) | C | Background on sessions and cacheable pages. |

The SPEC's earlier claim that the session "sends Set-Cookie even on cacheable pages" is not used: Varnish and the built-in cache strip Set-Cookie from cacheable responses (see the [source audit](../source-audit.md)).

Verified 2026-10-21 (see [source audit](../source-audit.md)).
