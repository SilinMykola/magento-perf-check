# Proposal

## Why

Issue #6: in a project with `phpstan/extension-installer`, `magento-perf-check` stops before the analysis with exit code 2. PHPStan reports that the package's `extension.neon` and `bitexpert/phpstan-magento`'s `extension.neon` are included multiple times. The installer adds every installed extension's `extra.phpstan.includes` to the configuration on its own, and the configuration the command generates includes the same files again. PHPStan treats a file included twice as an error.

PHPStan takes the installer's list from the first Composer autoloader that knows `PHPStan\ExtensionInstaller\GeneratedConfig`. It loads the autoloader of its working directory, which is the analyzed project, before its own. This was read in `bin/phpstan` of PHPStan 1.12.34 and 2.3.1 in November 2026.

## What Changes

- **Generated configuration:** before it includes this package's `extension.neon` or `bitexpert/phpstan-magento`'s `extension.neon`, the command reads the installer's list that PHPStan will use. A file of a package the installer already includes is left out of the generated configuration. `extension-experimental.neon`, which the installer does not include, stays.
- **`generated-code` notice:** it is not given when the installer loads `bitexpert/phpstan-magento`, as it is not given when the package is installed next to the tool.

## Impact

The command runs in projects that use the extension installer. Projects without it see no change.
