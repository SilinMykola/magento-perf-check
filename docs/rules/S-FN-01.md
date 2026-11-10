# S-FN-01: `sleep()`, `time_nanosleep()` or `session_*()` call

| | |
|---|---|
| Severity | `warning` |
| Status | experimental, off by default: its only source is the magento-coding-standard sniff, and it is not calibrated yet. Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | phpcs of the project, sniff `Magento2.Functions.DiscouragedFunction` of [magento/magento-coding-standard](https://github.com/magento/magento-coding-standard) ([ADR 7](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0007-run-magento-coding-standard-performance-sniffs.md)) |

## What it catches

A call of `sleep()`, `time_nanosleep()` or a `session_*()` function. The sniff reports many more functions; the others are left out here, since they are not about performance.

The command runs this sniff only when the rule is enabled and the project has phpcs (`vendor/squizlabs/php_codesniffer`) and `vendor/magento/magento-coding-standard`; otherwise a `coding-standard` notice says why it did not run. The result is the sniff's, in the version the project has installed. The coding standard's ruleset also leaves this sniff out of `_files`, `Fixtures`, `lib`, `Test` and `tests` directories and `*Test.php` files, even with `--include-tests`. phpcs compares those patterns with the absolute path, ignoring case, so a project that itself lies in such a directory (for example under `/var/lib/`) gets no finding of this rule; a `coding-standard` notice says so.

## Why it hurts performance

`sleep()` holds a PHP worker that serves nothing in the meantime. `session_*()` calls bypass Magento's session manager, so a session can start, or stay locked, where the framework would not start it.

## Example

Bad:

```php
while (!$this->lock->acquire($name)) {
    sleep(1);
}
```

Good:

```php
if (!$this->lock->acquire($name, 10)) {
    throw new LocalizedException(__('Busy, try again.'));
}
```

## How to suppress

```php
// phpcs:ignore Magento2.Functions.DiscouragedFunction -- reason
```

phpcs reads the comment; a baseline works too.

## Known false positives

- A retry pause in a CLI command or a cron job blocks no visitor. Suppress with a reason.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [magento-coding-standard: DiscouragedFunctionSniff.php#L125-L150](https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Functions/DiscouragedFunctionSniff.php#L125-L150) | C | `session_*`, `sleep` and `time_nanosleep` are in the list of discouraged functions. |

Verified 2026-11-10.
