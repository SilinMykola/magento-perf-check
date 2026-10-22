# X-GQL-01: GraphQL `Query` field with `@cache` but no `cacheIdentity`

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | GraphQL schema (`etc/*.graphqls`) |

## What it catches

A field of type `Query` (declared, or added with `extend type Query`) that has a `@cache` directive with neither a `cacheIdentity` argument nor `cacheable: false`.

## Why it hurts performance

Magento caches GraphQL GET responses in Varnish and the built-in page cache, tagged by the identity classes of the fields in the query. When a field has `@cache` without `cacheIdentity`, core cannot collect its cache tags and marks the whole query as not cacheable. Every response that contains the field, together with every cacheable field next to it, then goes to PHP on each request.

## Example

Bad:

```graphql
extend type Query {
    promoBanners: [PromoBanner] @resolver(class: "Acme\\Promo\\Model\\Resolver\\Banners") @cache(cacheTag: "promo")
}
```

Good:

```graphql
extend type Query {
    promoBanners: [PromoBanner]
        @resolver(class: "Acme\\Promo\\Model\\Resolver\\Banners")
        @cache(cacheIdentity: "Acme\\Promo\\Model\\Resolver\\Banners\\Identity")
}
```

If the field must never be cached, say so with `@cache(cacheable: false)`.

## How to suppress

Disable or re-grade the rule in `magento-perf-rules.neon`, or keep the finding in the baseline. Schema files have no inline suppression comment yet.

## Known false positives

None known.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [CacheableQueryHandler.php#L59-L85](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/CacheableQueryHandler.php#L59-L85) | B | A field with `@cache` but without `cacheIdentity` marks the whole query as not cacheable. |
| [Query/Resolver plugin#L43-L82](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/Plugin/Query/Resolver.php#L43-L82) | B | Cache tags are collected only through the field's identity class. |
| [Adobe: Identity class](https://developer.adobe.com/commerce/webapi/graphql/develop/identity-class) | A | Cacheable queries need an identity class that returns the cache tags. |

The SPEC row also covered "storefront query without `@cache`". No A/B source tells storefront queries from private ones, so that part is the separate experimental rule X-GQL-04 (decision in change m1-mvp, design D11).

Verified 2026-10-21 (see [source audit](../source-audit.md)).
