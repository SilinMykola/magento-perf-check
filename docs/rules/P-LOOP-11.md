# P-LOOP-11: ObjectManager `get()` or `create()` inside a loop

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.loopObjectManager` |

## What it catches

A call to `get()` or `create()` on `Magento\Framework\ObjectManagerInterface` inside a loop: through `ObjectManager::getInstance()`, or through an object manager injected into the class. Classes whose name ends in `Factory` or `\Proxy` are left out, as for [P-DI-06](P-DI-06.md): creating objects is their job.
"Inside a loop" has the meaning given on the [P-LOOP-01 page](P-LOOP-01.md).

## Why it hurts performance

`create()` builds a new object on every iteration and resolves its constructor arguments through the DI configuration each time. `get()` builds the shared instance on the first call and then returns it from the object manager's list, so in a loop it mostly costs a lookup. Both hide the dependency from DI configuration: it cannot be replaced by a Proxy or a preference that is visible in `di.xml`, and Adobe's usage rules forbid calling the object manager directly outside factories and proxies. The rule is in the default set at `error` by the maintainer's decision: a direct object manager call on a path that runs per item is treated as a defect, not a style issue.

## Example

Bad:

```php
foreach ($rows as $row) {
    $item = $this->objectManager->create(Item::class, ['data' => $row]);
}
```

Good:

```php
foreach ($rows as $row) {
    $item = $this->itemFactory->create(['data' => $row]);
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopObjectManager (reason)
```

## Known false positives

- A hand-written builder that is not named `*Factory` and creates objects of a type known only at run time. Rename it, or suppress with a reason.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Object Manager](https://developer.adobe.com/commerce/php/development/components/object-manager/) | A | Usage rules: do not call the object manager directly, except in factories, proxies, the constructor backward-compatibility fallback and `__wakeup`/`__sleep`. |
| [ObjectManager.php#L57-L60](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/ObjectManager/ObjectManager.php#L57-L60) | B | `create()` builds a new instance through the factory on every call. |
| [ObjectManager.php#L68-L76](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/ObjectManager/ObjectManager.php#L68-L76) | B | `get()` builds the shared instance on the first call and returns it from `_sharedInstances` afterwards. |

Verified 2026-11-09.
