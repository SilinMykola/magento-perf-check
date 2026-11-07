# X-GQL-02: database query in a resolver of a field on a type returned inside a list

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default |
| Analyzer | PHPStan and the project's `schema.graphqls` files, identifier `magentoPerf.resolverQuery` |

## What it catches

A query made directly in a class that implements `ResolverInterface` and is the `@resolver` of a field on a type reachable from `Query` or `Mutation` through a list. The schemas of all modules count, so a field that a module adds to core's `ProductInterface` is covered.

A query is `load()` on a model or a flat or EAV resource model, `get()` or `getById()` on a repository, `getList()` with search criteria on a repository, running a database collection, including with `foreach`, and a fetch on a database adapter. Store, website and store group repositories read cached configuration and are not reported.

A resolver that only reads `$value` or formats it is not reported: most resolvers on list types are like that, and they cost nothing. A query in a constructor or in a closure is not reported either, because it runs once per request or later for all elements. A closure that is a callback of `array_map()` or `array_walk()` runs per element, so it is reported.

## Why it hurts performance

GraphQL calls a `ResolverInterface` resolver once per element of the list. A field on `ProductInterface` that loads something runs one query per product of every page: 20 products, 20 queries, for each such field.

## Example

Bad:

```php
class Brand implements ResolverInterface
{
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return $this->brandRepository->getById($value['brand_id'])->getName();
    }
}
```

Good: implement `BatchResolverInterface`, which receives all requests of the field at once, and load everything with one query. Or add the ids to a shared collector and return a deferred value:

```php
$this->brandCollector->add($value['brand_id']);

return $this->valueFactory->create(fn () => $this->brandCollector->getName($value['brand_id']));
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.resolverQuery (reason)
```

## Known false positives

- A type reachable through a list that in practice is returned one at a time. `StoreConfig` is an example: `availableStores` returns a list of it, but `storeConfig` is the usual query.
- A list that is always short. The rule does not know the size.
- A list that one module adds to a type another module uses for one object. On a project with both ScandiPWA's quote module and Mollie, `[Order]` from the first makes the `Order` resolvers of the second count, although Mollie returns one order.
- A query memoized in a property, such as `$this->rates ??= $this->collection->getItems()`, runs once but is reported. A query memoized per id runs per distinct id, so that finding is correct when ids repeat rarely.
- A private method that a deferred closure calls is treated as an ordinary method, so its queries are reported.
- A resolver on an interface field that a type overrides with its own resolver, when only that type is returned in a list.

Not seen, so unreported: queries in another class that the resolver calls, a `resolve()` inherited from a parent class the schema does not name, and a resolver replaced through a `preference`.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: GraphQL resolvers](https://developer.adobe.com/commerce/webapi/graphql/develop/resolvers) | A | "We recommend using batch resolvers for queries because they improve performance"; loading related products per product "would be expensive performance-wise". |

Verified 2026-11-07.
