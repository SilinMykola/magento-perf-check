# S-PERF-01: `array_merge()` inside a loop

| | |
|---|---|
| Severity | `info` (a recommendation: calibration rounds 4 and 6 found its correct findings mostly negligible) |
| Status | experimental, off by default: its only source is the sniff, and in [calibration round 4](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-4.md) only 2 of 33 findings mattered. Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | phpcs of the project, sniff `Magento2.Performance.ForeachArrayMerge` of [magento/magento-coding-standard](https://github.com/magento/magento-coding-standard) ([ADR 7](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0007-run-magento-coding-standard-performance-sniffs.md)) |

## What it catches

A call of `array_merge()` inside a `foreach` or `for` loop.

The command runs this sniff only when the rule is enabled and the project has phpcs (`vendor/squizlabs/php_codesniffer`) and `vendor/magento/magento-coding-standard`; otherwise a `coding-standard` notice says why it did not run. The result is the sniff's, in the version the project has installed.

## Why it hurts performance

`array_merge()` copies both arrays into a new one. Called once per iteration to grow a result, it copies everything collected so far each time, so the work grows with the square of the number of items.

## Example

Bad:

```php
$result = [];
foreach ($groups as $group) {
    $result = array_merge($result, $group->getItems());
}
```

Good:

```php
$items = [];
foreach ($groups as $group) {
    $items[] = $group->getItems();
}
$result = array_merge([], ...$items);
```

## How to suppress

```php
// phpcs:ignore Magento2.Performance.ForeachArrayMerge -- reason
```

phpcs reads the comment; a baseline works too.

## Known false positives

- A loop with a small fixed number of iterations costs little. Suppress with a reason.
- The sniff reports any `array_merge()` in the loop body, also one that does not grow a result, such as `array_merge($defaults, $item)` per item. That one costs the same in every iteration and is not quadratic.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [magento-coding-standard: ForeachArrayMergeSniff.php#L21](https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Performance/ForeachArrayMergeSniff.php#L21) | C | "array_merge(...) is used in a loop and is a resources greedy construction." |

Verified 2026-11-10.
