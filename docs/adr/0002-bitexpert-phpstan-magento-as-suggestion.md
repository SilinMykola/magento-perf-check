# 2. bitExpert/phpstan-magento is a suggestion, not a dependency

- Status: Accepted
- Date: 2026-10-21

## Context

[bitexpert/phpstan-magento](https://github.com/bitExpert/phpstan-magento) adds PHPStan type extensions for Magento factories, proxies and magic methods. Without it, PHPStan can resolve `$this->collectionFactory->create()` only when the project's `generated/code` directory contains the generated factory class.

Its PHPStan constraints on Packagist, as of 2026-10-21:

| Version | Released | `phpstan/phpstan` |
|---|---|---|
| 0.44.0 | 2026-09-14 | `^2.0` |
| 0.43.0 | 2026-06-28 | `^2.0` |
| 0.32.0 | 2024-08-31 | `~1.12.0` |

No single version supports both PHPStan majors. A hard dependency would therefore pin the extension to one major, which contradicts [ADR 1](0001-support-phpstan-1-12-and-2.md).

## Decision

- `bitexpert/phpstan-magento` is listed under `suggest`, not under `require`.
- The PHP rules must work without it. When a receiver type cannot be resolved, a rule reports nothing: a false negative, never a false positive.
- `magento-perf-check` includes bitExpert's `extension.neon` when the package is installed next to the tool. It also adds the project's `generated/code` to the scanned directories when that directory exists.
- Projects that use PHPStan directly and already have bitExpert installed get its type resolution through `phpstan/extension-installer` with no further action.

## Consequences

- No Composer conflict on either PHPStan major.
- Projects without `generated/code` and without bitExpert miss some findings. Typical cases are collections created through factories (P-COL-02) and repositories injected through generated proxies. The M0 core run counts how many `getFirstItem()` receivers stay untyped, to size the problem (see `docs/runs/m0-core.md`).
- If bitExpert later supports both majors in one release, this decision can be revisited.

## Update, 2026-11-07

On 56 modules, 680 of 1205 factory parameters named a factory that the checked project had not generated, so findings depended on whether `setup:di:compile` had run. `create()` of a missing factory is now typed by this package's own `FactoryCreateTypeExtension`, on both PHPStan majors. The results on those modules with generated factories and without `generated/code` are now the same 383 findings; without the extension, the run without `generated/code` gives 327. bitExpert stays a suggestion for proxies and extension attributes.
