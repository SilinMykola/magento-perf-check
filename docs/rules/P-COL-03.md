# P-COL-03: `addAttributeToSelect('*')` on an EAV collection

| | |
|---|---|
| Severity | `info` |
| Status | experimental, off by default |
| Analyzer | PHPStan, identifier `magentoPerf.selectAllAttributes` |

## What it catches

`addAttributeToSelect('*')`, with the literal `'*'`, on a subclass of `Magento\Eav\Model\Entity\Collection\AbstractCollection`.

## Why it hurts performance

`'*'` adds every attribute of the entity to the selection, and loading then reads the values of all of them from their tables, whether the code uses them or not.

## Example

Bad:

```php
$collection->addAttributeToSelect('*');
```

Good:

```php
$collection->addAttributeToSelect(['name', 'price']);
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.selectAllAttributes (reason)
```

## Known false positives

- Code that passes its items on to code it does not control, which may read any attribute. Core itself does this in places.
- A collection that loads a few rows of an entity with few attributes: correct, but the cost is small.

No source measures the cost, which is why the severity is `info`.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [AbstractCollection.php#L583-L588](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L583-L588) | B | For `'*'`, every attribute of the entity is added to the selected attributes. |
| [AbstractCollection.php#L1266-L1297](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1266-L1297) | B | `_loadAttributes()` reads the values of all selected attributes. |

Verified 2026-11-07.
