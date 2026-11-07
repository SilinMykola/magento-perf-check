# P-LOOP-09: repository `getList()` with search criteria once per item of a loop

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.loopRepositoryList` |

## What it catches

A call to `getList()` whose first parameter is a `SearchCriteriaInterface`, on a type that is, or implements, an interface whose name ends with `RepositoryInterface`, in the body of a `foreach` or of a closure passed straight to `array_map()`, `array_walk()` or a collection's `walk()`.

Not reported: `while`, `do` and `for` loops, which usually page through one result; a `foreach` over `array_chunk()`, which is one query per batch; and a `getList()` with another first parameter, such as one SKU or an entity type, which no filter can replace. This also leaves out the `Magento\Store\Api` repositories, which take no parameters and read cached configuration.

## Why it hurts performance

`getList()` runs a search query, and usually a count query too. P-LOOP-01 tells people to use `getList()` instead of `getById()`, but it helps only when it replaces the loop.

## Example

Bad:

```php
foreach ($orders as $order) {
    $criteria = $this->criteriaBuilder->addFilter('order_id', $order->getId())->create();
    $shipments = $this->shipmentRepository->getList($criteria)->getItems();
}
```

Good:

```php
$criteria = $this->criteriaBuilder->addFilter('order_id', $orderIds, 'in')->create();
$shipments = $this->shipmentRepository->getList($criteria)->getItems();
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopRepositoryList (reason)
```

## Known false positives

- Loops with a small, fixed bound cost little. Suppress with a reason.
- The rule cannot see whether a repository implementation reads the database.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "Running SQL queries in a loop often results in a performance bottleneck." |
| [Adobe: Code review best practices](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review) | A | "Are queries executed in a loop? This loop can be outside of the edited files." |

Verified 2026-11-07.
