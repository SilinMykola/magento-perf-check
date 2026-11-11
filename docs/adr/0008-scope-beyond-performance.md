# 8. Design and compatibility rules are in scope

- Status: Accepted
- Date: 2026-11-10

## Context

[SPEC section 1](../SPEC.md) limits the tool to performance and Full Page Cache anti-patterns. Two kinds of checks have come up that do not fit that wording but rest on official Adobe sources:

- **Design rules.** S-DI-01 and S-TPL-02 ([calibration round 4](../calibration/round-4.md)) are backed by the Technical guidelines, sections 2.5 and 6.2.6, which state design rules with no performance cost.
- **Compatibility after a Magento patch.** Adobe Security Bulletin APSB25-88 changed how REST requests build data objects. The page [Web API services](https://developer.adobe.com/commerce/php/development/components/web-api/services) says that only simple types and `*\Api\Data\*Interface` types are accepted as constructor parameters of such objects. In the core, `ServiceInputProcessor::getConstructorData()` takes the preference of the data object's type, and a constructor parameter gets its value from the request only when its type is simple or matches `\Vendor\Module\Api\Data\` ([ServiceInputProcessor.php#L229-L241](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L229-L241), [#L290-L296](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L290-L296)); otherwise the value is set through a setter, if there is one. Custom REST services can break after the upgrade without any error at build time.

magento/magento-coding-standard covers neither. It changes rarely, while every Magento release can bring a change like the one above.

## Decision

- The tool also takes rules that keep Magento code working and maintainable, not only fast, when an A or B source states the rule. The source policy (SPEC section 2) and calibration (SPEC section 7) apply to them unchanged.
- With each Magento release, its release notes and security bulletins are read for changes that custom code must follow, and a rule is proposed for each one that static analysis can check.
- A rule says what it protects in its page: speed, cacheability, design or compatibility. A rule without a performance cost does not claim one.

## Consequences

- SPEC section 1 names the wider goal; the package name stays, since it is published.
- The rule list grows with Magento releases, so each new rule still goes through an OpenSpec change, fixtures and calibration before it enters the default set.
- The first rule of this kind checks the constructor parameters of REST data objects (APSB25-88).
