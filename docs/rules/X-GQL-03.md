# X-GQL-03: GraphQL cache id factor

| | |
|---|---|
| Severity | `info` |
| Status | default set |
| Analyzer | XML (`di.xml`) |

## What it catches

Every `<item>` of the `idFactorProviders` argument of `Magento\GraphQlCache\Model\CacheId\CacheIdCalculator` in a `di.xml` outside the `Magento_*` modules. The core factors are needed for correct content, so nothing can be done about them.

## Why it hurts performance

Each factor goes into the hash of `X-Magento-Cache-Id`, so each of its values gets its own cache entries. The rule cannot see how many values a provider returns: a store code is fine, a customer id is not. It is `info` for that reason: it asks you to check.

## Example

Bad:

```xml
<type name="Magento\GraphQlCache\Model\CacheId\CacheIdCalculator">
    <arguments>
        <argument name="idFactorProviders" xsi:type="array">
            <item name="customer_id" xsi:type="object">Acme\Graph\CustomerIdProvider</item>
        </argument>
    </arguments>
</type>
```

Good:

Use a factor with few possible values, such as a customer segment, or none.

## How to suppress

`<!-- perf-ignore X-GQL-03: reason -->` directly before the element.

## Known false positives

- A factor with few values is reported too. Suppress with a reason.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: GraphQL caching](https://developer.adobe.com/commerce/webapi/graphql/usage/caching/) | A | "Adding factors could generate too many unique cache keys, thereby reducing the number of caching hits and affecting performance." |
| [CacheIdCalculator.php#L89-L104](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/CacheId/CacheIdCalculator.php#L89-L104) | B | Every provider value goes into the hash. |

Verified 2026-11-07.
