# Proposal

## Why

Since 0.4.0 the command runs in projects with `phpstan/extension-installer` by leaving to the installer the files it includes ([#6](https://github.com/SilinMykola/magento-perf-check/issues/6)). The installer then also adds every other PHPStan extension the project has installed. Their rules run in the analysis and their errors show as `phpstan` notices. On one module of the calibration corpus, with `phpstan/phpstan-strict-rules` and `phpstan/phpstan-deprecation-rules` installed, that was 634 notices next to 27 findings. The installer also makes PHPStan check those extensions' PHPStan version constraint, which can stop a run from the tool's own checkout.

PHPStan asks the installer for its list through the class `PHPStan\ExtensionInstaller\GeneratedConfig`, after it has loaded the file given with `--autoload-file`, and it passes `--autoload-file` on to its parallel workers. This was read in `src/Command/CommandHelper.php` and `src/Process/ProcessHelper.php` of PHPStan 1.12.34 and 2.3.1 in November 2026.

## What Changes

- **Analysis:** the command gives PHPStan an autoload file, kept in its temp directory, that declares `GeneratedConfig` with no extensions. The installer then adds nothing. The generated configuration includes this package's files, and `bitexpert/phpstan-magento`'s when it is installed next to the tool, as before 0.4.0.
- The reading of the installer's list added in 0.4.0 is removed: nothing is left to the installer any more.
- **`generated-code` notice:** given again unless `bitexpert/phpstan-magento` is installed next to the tool, since a copy in the project is no longer loaded.

## Impact

In projects with the installer, only this package's rules run. Projects without it see no change.
