# Proposal

## Why

[ADR 7](../../../docs/adr/0007-run-magento-coding-standard-performance-sniffs.md) decides that the command runs the few performance sniffs of magento/magento-coding-standard (MCS) when the project has it, instead of copying them. Users who do not run MCS then get them in the same report, configuration and baseline as the other rules.

## What Changes

- **Five new rules**, all experimental, each backed by one MCS sniff:

  | ID | Sniff | Severity |
  |---|---|---|
  | S-PERF-01 | `Magento2.Performance.ForeachArrayMerge` | warning |
  | S-TPL-01 | `Magento2.Templates.ThisInTemplate` | warning |
  | S-TPL-02 | `Magento2.Templates.ObjectManager` | warning |
  | S-DI-01 | `Magento2.Classes.DiscouragedDependencies` | warning |
  | S-FN-01 | `Magento2.Functions.DiscouragedFunction`, only `sleep()`, `time_nanosleep()` and `session_*()` | warning |

  The IDs follow the existing pattern, so configuration, the baseline, `--fail-on` and the DDEV wrapper treat them like any other rule; the message names the sniff. ADR 7 said the sniff code would be the ID; that is changed in the ADR. They are experimental because their only sources are the MCS sniffs (level C, SPEC section 2) and they are not calibrated.
- **Running:** when at least one of them is enabled, the command runs the project's phpcs (`vendor/squizlabs/php_codesniffer/bin/phpcs`) with the project's MCS ruleset (`vendor/magento/magento-coding-standard/Magento2/ruleset.xml`) and only the enabled sniffs, on the analyzed paths. Test code and disabled modules are filtered by path afterwards, as the PHPStan runner leaves them out; phpcs's own `--ignore` reads patterns as regular expressions and ignores case, so it is not used. Nothing is written into the project. When none is enabled, phpcs does not run.
- **Notices:** when a rule of this group is enabled and the project has no phpcs or no MCS, or phpcs fails, one `coding-standard` notice says so; the run goes on. A sniff missing from the installed MCS version, which phpcs would skip without a word, and an internal phpcs error on a file are notices too, and so is a project path that matches the ruleset's exclude patterns for S-DI-01 or S-FN-01.
- **Subprocesses** get an empty stdin; phpcs would otherwise check the tool's stdin as a file.
- **Process:** the subprocess code of the PHPStan runner moves to a class both runners use.
- **Tests:** magento/magento-coding-standard becomes a development dependency, so the tests run the real phpcs.

## Impact

Nothing changes unless a user enables one of the five rules. The rule list grows to 53, 19 of them experimental.
