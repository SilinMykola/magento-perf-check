# Proposal

## Why

A user ran `vendor/bin/magento-perf-check` in a Magento project and the command stopped at once: PHPStan said "Invalid configuration: Unexpected item 'parameters › extdn'" and "'parameters › magento › checkResourceModelsUsedDirectly'". When `bitexpert/phpstan-magento` is installed, the command includes its `extension.neon` and sets four of its parameters to switch its own rules off. `bitexpert/phpstan-magento` 0.30.0 and older do not declare all four in their `parametersSchema` (0.30.0 even sets two of them itself), so PHPStan rejects the configuration; versions from 0.30.1 on declare all four. The command assumed every installed version declares them. Versions 0.11.0 to 0.21.0 set only a lower bound on PHPStan 1.x (`^1.1.0` to `^1.6.2`), so a project gets one of them next to PHPStan 1.12 when another package pins it: the user's project has a module that requires `bitexpert/phpstan-magento ^0.11`.

## What Changes

- The command includes `bitexpert/phpstan-magento` only when its `extension.neon` declares, in `parametersSchema`, every parameter the command sets. Otherwise it runs without it, as when it is not installed, and a `generated-code` notice names the version, the missing parameter and the fix: update to 0.32.0 or later with PHPStan 1.12, or 0.40.0 or later with PHPStan 2.
- `docs/configuration.md` names the oldest version that is used.
