# S-TPL-02: object manager in a template

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default: its sources state a design rule, and [calibration round 4](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-4.md) found no case to measure. Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | phpcs of the project, sniff `Magento2.Templates.ObjectManager` of [magento/magento-coding-standard](https://github.com/magento/magento-coding-standard) ([ADR 7](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0007-run-magento-coding-standard-performance-sniffs.md)) |

## What it catches

A static access on a class named `ObjectManager`, or a static call of `getInstance()`, in a `.phtml` template.

The command runs this sniff only when the rule is enabled and the project has phpcs (`vendor/squizlabs/php_codesniffer`) and `vendor/magento/magento-coding-standard`; otherwise a `coding-standard` notice says why it did not run. The result is the sniff's, in the version the project has installed.

## Why it hurts performance

A template that builds its own objects runs that code on every render, out of sight of layout and DI configuration: it cannot get a Proxy, a preference or a cached view model instead.

## Example

Bad:

```php
<?php $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Acme\Promo\Helper\Data::class); ?>
```

Good:

```php
<?php $promo = $block->getData('promo_view_model'); ?>
```

## How to suppress

```php
// phpcs:ignore Magento2.Templates.ObjectManager -- reason
```

phpcs reads the comment; a baseline works too.

## Known false positives

- The sniff looks at the names only: `ObjectManager::class`, or `getInstance()` of any other class, is reported too.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Technical guidelines 6.2.6](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines#presentation-layer) | A | "Templates MUST NOT instantiate objects. All objects MUST be passed from the Block objects." A design rule; it says nothing about speed. |
| [magento-coding-standard: ObjectManagerSniff.php#L23-L29](https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Templates/ObjectManagerSniff.php#L23-L29) | C | Templates must not instantiate objects; all objects are passed from the block. |

Verified 2026-11-10.
