# Proposal

## Why

A user ran `vendor/bin/magento-perf-check` in a Magento project and the command stopped at once: PHPStan said "Invalid configuration: Unexpected item 'parameters › extdn'" and "'parameters › magento › checkResourceModelsUsedDirectly'". When `bitexpert/phpstan-magento` is installed, the command includes its `extension.neon` and sets four of its parameters to switch its own rules off. `bitexpert/phpstan-magento` 0.30.0 sets two of those parameters itself but does not declare them in its `parametersSchema`, so PHPStan rejects every configuration that includes it; versions from 0.30.1 on declare all four. The command assumed every installed version has them.

## What Changes

- The command includes `bitexpert/phpstan-magento` only when its `extension.neon` declares, in `parametersSchema`, every parameter the command sets. Otherwise it runs without it, as when it is not installed, and a `generated-code` notice names the version, the missing parameter and the fix: update to 0.30.1 or later.
- `docs/configuration.md` names the oldest version that is used.
