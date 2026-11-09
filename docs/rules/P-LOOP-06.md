# P-LOOP-06: `getAttributeText()` or the table source `getOptionText()` inside a loop

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default |
| Analyzer | PHPStan, identifier `magentoPerf.loopAttributeText` |

## What it catches

`getAttributeText()` on a product, or `getOptionText()` on `Magento\Eav\Model\Entity\Attribute\Source\Table`, inside a loop.

"Inside a loop" has the meaning given on the [P-LOOP-01 page](P-LOOP-01.md).

## Why it hurts performance

The table source model creates and loads an option collection on every `getOptionText()` call, and nothing caches the result. It is the default source of a select or multiselect attribute. `getAttributeText()` goes through it.

## Example

Bad:

```php
foreach ($products as $product) {
    $colors[] = $product->getAttributeText('color');
}
```

Good:

```php
$labels = [];
foreach ($attribute->getSource()->getAllOptions() as $option) {
    $labels[$option['value']] = $option['label'];
}
foreach ($products as $product) {
    $colors[] = $labels[$product->getData('color')] ?? null;
}
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.loopAttributeText (reason)
```

## Known false positives

- An attribute with another source model, such as `Boolean` or a custom one, may run no query. The rule cannot tell which attribute `getAttributeText()` gets.
- A small, capped set.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Source/Table.php#L106-L119](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Attribute/Source/Table.php#L106-L119) | B | `getSpecificOptions()` creates and loads an option collection on every call. |
| [Product.php#L1796-L1801](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/Product.php#L1796-L1801) | B | `getAttributeText()` calls `getOptionText()` of the attribute source. |
| [magento2#38934](https://github.com/magento/magento2/issues/38934) | B | Confirmed and open on 2026-11-07. It has no measurements. |

Verified 2026-11-07.
