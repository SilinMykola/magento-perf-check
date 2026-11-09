# P-LOOP-01: model `load()` or repository `get()`/`getById()` inside a loop

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.loopModelLoad` |

## What it catches

Inside a loop, either:
- a call to `load()` on a subtype of `Magento\Framework\Model\AbstractModel`;
- a call to `get()` or `getById()` on a type that is, or implements, an interface whose name ends with `RepositoryInterface`.

Not reported, because no query runs: `Magento\Directory\Model\Currency::load()`, which only sets the currency code, and the `Magento\Store\Api` store, website and group repositories, which read cached configuration.

"Inside a loop" means:
- the body of `foreach`, `for`, `while` or `do`;
- the body of a closure passed straight to `array_map()`, `array_walk()` or a collection's `walk()`.

The body of a `foreach` over `array_chunk()` does not count: it runs once per batch. A loop inside it counts again.

A call after which the loop is left in the same iteration does not count either: it runs at most once. The call is a statement of its own, or a `return`, and what follows it reaches `return`, `throw`, or a `break` out of the only loop around it, with no `continue`, `break` or `goto` on the way. A `break` that leaves a `switch` is passed through: what follows the `switch` decides. Inside `try`, in a closure, or in the condition of an `if`, the call still counts.

## Why it hurts performance

Each `load()` is at least one database round trip, and for EAV entities such as products it is several. A repository `getById()` calls `load()` for every id that is not cached yet. A loop over 100 ids therefore runs hundreds of queries where one collection or `getList()` query would do. Adobe's extension guidance names "loading models in a loop" as a typical bottleneck.

## Example

Bad:

```php
foreach ($orderItems as $item) {
    $product = $this->productRepository->getById($item->getProductId());
    $names[] = $product->getName();
}
```

Good: one query for all products.

```php
$ids = array_map(static fn ($item) => $item->getProductId(), $orderItems);
$collection = $this->productCollectionFactory->create()
    ->addIdFilter($ids)
    ->addAttributeToSelect('name');
foreach ($collection as $product) {
    $names[] = $product->getName();
}
```

`getList()` with a `SearchCriteria` filter on `entity_id` `in` `$ids` works the same way when a service contract is preferred.

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopModelLoad (loop is capped at 3 ids by the caller)
$product = $this->productRepository->getById($id);
```

## Known false positives

- `ProductRepository` caches products by id, so repeated calls with the same id within one request are cheap. The rule cannot see whether the ids repeat.
- Loops with a small, fixed bound (for example two or three iterations) cost little. Suppress with a reason.
- The rule does not follow calls into other methods: a loop that calls a method which loads a model is not reported (cross-method analysis is planned for M4).

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "Running SQL queries in a loop often results in a performance bottleneck." It recommends data collections instead of loading models in a loop. |
| [Adobe: Code review best practices](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review) | A | Performance checklist: "Are queries executed in a loop? This loop can be outside of the edited files." |
| [AbstractModel.php#L564-L568](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L564-L568) | B | `load()` delegates to the resource model: one database round trip per call. |
| [ProductRepository.php#L308-L328](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ProductRepository.php#L308-L328) | B | `getById()` loads the product unless that id is already cached. |

Adobe does not forbid repositories inside loops as such. The same code-review page recommends factories or repositories to create models within a loop, to keep customer data isolated. The rule targets per-iteration **loading**, which is what the sources call a bottleneck.

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).
