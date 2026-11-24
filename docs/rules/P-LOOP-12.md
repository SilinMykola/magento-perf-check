# P-LOOP-12: method that runs a query, called inside a loop

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default: [calibration round 6](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-6.md) found it precise (93 %) but mostly negligible (impact share 11 %). Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | PHPStan, identifier `magentoPerf.loopCallQuery`, across files |

## What it catches

A method call inside a loop when the called method runs a query on every call. The query is one that P-LOOP-01, P-LOOP-09 or P-LOOP-10 would report if it were written in the loop itself:
- `load()` on a model;
- `get()` or `getById()` on a repository;
- `getList()` with search criteria on a repository, only when the loop runs once per item (`foreach`, `array_map()`), as for P-LOOP-09: a `while` or `for` around it is usually paging;
- `load()` on a resource model.

"Inside a loop" has the meaning given on the [P-LOOP-01 page](P-LOOP-01.md). The call can be `->`, `?->` or `::`. The called method may be in the same class, a parent class, a trait or an injected class, also in another module. The message names the query and its line, in the file of the class or trait that holds it.

The rule does not report:
- a query whose result the method keeps for later calls, unless the loop may call a new object each time. The kept query is the value stored in an element of a property (`$this->products[$id] = ...`, `self::$names[$id] ??= ...`) or with `??=` on a property, a query in the body of an `if` that tests a property and assigns it, or the value assigned to a property after an `if` on it that returns early (lazy getters). A new object is the foreach value or something reached from it (`$item->getOrderItem()`), `new` or a `create()` result: there, a kept result does not help, and the call is reported. An append, `$this->log[] = ...`, keeps nothing; other queries of the same method still count. An object held in a variable assigned inside the loop counts as the same object, so a kept result on it is missed;
- a query inside a closure that the method returns or stores, since calling the method does not run it. A closure passed to `array_map()` and the like does count;
- a query in the fallback of `$a ?: $b` or `$a ?? $b`, such as `return $attribute ?: $this->attributeFactory->create()->load($id);`: it runs only when the value before it is empty. A query on the left of `?:` or `??`, or in a branch of a full ternary, still counts. So does a fallback after a property, or an element of one, that the class declares `private` and never fills, as in `return $this->names[$id] ?? $this->repository->getById($id)->getName();` when the class gives `$names` no default other than `[]` or `null`, assigns it nothing else and assigns no element of it: the fallback runs on every call. The property may be written as `$this->names`, `self::$names`, `static::$names` or with the class's own name; a static property of another class, such as `Registry::$names`, is not one of the class. `++` and `--` on the property count as filling it. A property of a trait, or one that is `protected` or `public`, counts as filled, since another class may fill it, and so does every property of a class that uses a trait, since a method of the trait may fill it;
- a method that queries only through another method: the rule looks one call deep;
- a call through an interface or to an abstract method, because there is no body to look at;
- a call whose receiver may be one of several classes;
- a method outside the analyzed paths, such as core code;
- a call that is a query itself: P-LOOP-01, P-LOOP-09 and P-LOOP-10 report those.

## Why it hurts performance

The query runs once per item, as if it were written in the loop: 100 items, 100 queries where one collection query or one `getList()` would do. Moving the query into a helper method hides it from a reader of the loop, and from the rules that look at the loop body.

## Example

Bad:

```php
foreach ($items as $item) {
    $names[] = $this->productName((int) $item->getProductId());
}

private function productName(int $id): string
{
    return (string) $this->productRepository->getById($id)->getName();
}
```

Good: fetch all products once and look them up by id.

```php
$criteria = $this->criteriaBuilder->addFilter('entity_id', $ids, 'in')->create();
$products = [];
foreach ($this->productRepository->getList($criteria)->getItems() as $product) {
    $products[$product->getId()] = $product;
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopCallQuery (the loop runs over at most 3 stores)
```

Put it on the line of the call in the loop.

## Known false positives

- The query can sit in an `if` branch that runs only for some items, or only once, for example when the loop returns on the first match. This includes a type guard such as `if (!$shipment instanceof Shipment) { $shipment = $this->shipment->load($shipment); }` when the caller passes a Shipment, and a load under `if ($id)` when the caller passes no id: the rule does not follow the caller's arguments into the branch.
- An inline `@var` that names a model with a querying method, when the collection really holds another class whose method does not query: the rule trusts the `@var`, as PHPStan does.
- Some core repositories cache by id: `QuoteRepository::get()` keeps loaded carts, and `GroupRepository::getById()` reads through a registry. Calling them for the same id again costs no query.
- The caller can keep results itself, for example behind an `isset()` on a map it filled before the loop.
- A fallback after a private property that the class fills only in a way the rule does not see as an assignment, such as `array_push($this->names, ...)`, a reference (`$names = &$this->names`), `foreach (... as $this->names[...])`, destructuring (`[$this->names] = ...`), a dynamic name (`$this->$name = ...`) or a write through another instance (`$other->names[$id] = ...`), is reported.
- Loops with a small, fixed bound cost little, as for P-LOOP-01.

Suppress with a reason.

## Known false negatives

- A query in the fallback of `?:` or `??` after a parameter, a local variable or a method result, as in `return $product ?: $this->repository->getById($id);`, is not counted, even when every caller in the loop passes no value and the fallback runs each time. The rule does not follow what the caller passes.
- A fallback after a property is not counted when the class assigns the property anywhere, even if that assignment never runs before the loop, or stores a value that does not cover the ids the loop asks for. A property that is `protected` or `public`, or declared in a trait, counts as filled even when no class fills it, since the rule cannot see a child class or the class that uses the trait; so does every property of a class that uses a trait, even when the trait fills none of them.
- `$x ??= $this->repository->getById($id)` on a local variable is still counted as a query on every call.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Code review](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review) | A | "Are queries executed in a loop? This loop can be outside of the edited files." |
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "Running SQL queries in a loop often results in a performance bottleneck." |
| [AbstractModel.php#L564-L568](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L564-L568) | B | `load()` delegates to the resource model: one database round trip per call. |
| [AbstractDb.php#L323-L333](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/ResourceModel/Db/AbstractDb.php#L323-L333) | B | The resource model's `load()` runs `fetchRow()` on every call. |

Verified 2026-11-10.
