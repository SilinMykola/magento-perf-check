# S-TPL-01: `$this` or a helper in a template

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default: its only source is the magento-coding-standard sniff, and it is not calibrated yet. Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | phpcs of the project, sniff `Magento2.Templates.ThisInTemplate` of [magento/magento-coding-standard](https://github.com/magento/magento-coding-standard) ([ADR 7](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0007-run-magento-coding-standard-performance-sniffs.md)) |

## What it catches

Use of `$this` in a `.phtml` template, and calls of `$this->helper()`. A `$this->helper()` call gives two findings on its line: one for the helper and one for `$this`.

The command runs this sniff only when the rule is enabled and the project has phpcs (`vendor/squizlabs/php_codesniffer`) and `vendor/magento/magento-coding-standard`; otherwise a `coding-standard` notice says why it did not run. The result is the sniff's, in the version the project has installed.

## Why it hurts performance

The cost is small: `helper()` returns a shared instance. The rule is here because helpers pull logic and dependencies into rendering that a view model would declare, and `$this->helper()` resolves the class through the object manager on every call.

## Example

Bad:

```php
<?= $this->helper(\Magento\Catalog\Helper\Output::class)->productAttribute($product, $value, 'name') ?>
```

Good:

```php
<?php $output = $block->getData('output_view_model'); ?>
<?= $output->productAttribute($product, $value, 'name') ?>
```

## How to suppress

```php
// phpcs:ignore Magento2.Templates.ThisInTemplate -- reason
```

phpcs reads the comment; a baseline works too.

## Known false positives

- Most findings are about style rather than speed; calibration has not measured them yet.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [magento-coding-standard: ThisInTemplateSniff.php#L16-L17](https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Templates/ThisInTemplateSniff.php#L16-L17) | C | `$this` in templates is deprecated and helpers are discouraged: use `$block` and view models. |

Verified 2026-11-10.
