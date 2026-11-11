# coding-standard-rules Specification

## Purpose
Experimental rules that run sniffs of the project's magento/magento-coding-standard through its phpcs, instead of copying them (ADR 7), and report them with the other findings.

## Requirements

### Requirement: Coding-standard sniffs as experimental rules
The command SHALL offer S-PERF-01, S-TPL-01, S-TPL-02, S-DI-01 and S-FN-01 as experimental rules, each reporting what one sniff of the project's magento/magento-coding-standard reports; S-FN-01 SHALL keep only `sleep()`, `time_nanosleep()` and `session_*()` calls. Each finding SHALL keep the sniff's file and line, and its message SHALL start with the rule ID and name the sniff.

#### Scenario: array_merge in a loop
- **WHEN** S-PERF-01 is enabled and a module calls `array_merge()` inside a `foreach`
- **THEN** the report has an S-PERF-01 finding at that line

#### Scenario: Other discouraged functions
- **WHEN** S-FN-01 is enabled and the code calls `is_null()` and `sleep()`
- **THEN** the report has one S-FN-01 finding, for `sleep()`

### Requirement: phpcs runs only for enabled sniffs
The command SHALL run the project's phpcs (`vendor/squizlabs/php_codesniffer/bin/phpcs`) only when at least one of these rules is enabled, with the project's MCS ruleset, only the enabled sniffs and the analyzed paths. Findings in test code and in disabled modules SHALL be left out as for the other rules, by their path relative to the analyzed path they came from, also through a symlinked module. It SHALL NOT write into the project.

#### Scenario: None enabled
- **WHEN** none of the five rules is enabled
- **THEN** phpcs does not run

#### Scenario: Test code
- **WHEN** S-PERF-01 is enabled and `array_merge()` in a loop is only in a `Test` directory
- **THEN** the report has no S-PERF-01 finding unless `--include-tests` is given or the `Test` directory is an analyzed path

#### Scenario: Symlinked module
- **WHEN** `app/code/Ext/Mod` links to a directory outside `app/code`
- **THEN** its findings are reported under `app/code/Ext/Mod`

### Requirement: Missing coding standard is a notice
When one of these rules is enabled and the project has no phpcs or no `vendor/magento/magento-coding-standard/Magento2/ruleset.xml`, or phpcs does not return a JSON report, the command SHALL give one notice with source `coding-standard` and go on with the other rules.

#### Scenario: No coding standard installed
- **WHEN** S-PERF-01 is enabled in a project without magento/magento-coding-standard
- **THEN** the report has one `coding-standard` notice and the other findings

#### Scenario: Sniff missing from the installed version
- **WHEN** S-TPL-02 is enabled and the installed coding standard has no `Magento2/Sniffs/Templates/ObjectManagerSniff.php`
- **THEN** the report has a `coding-standard` notice naming the sniff

### Requirement: What phpcs cannot report is a notice
An enabled rule whose sniff the installed version does not have, an internal phpcs error on a file, and a project path that matches an exclude pattern of the ruleset for an enabled sniff SHALL each give a `coding-standard` notice.

#### Scenario: Project under a lib directory
- **WHEN** S-FN-01 is enabled and the project lies under `/var/lib/`
- **THEN** the report has a `coding-standard` notice that S-FN-01 reports nothing there

### Requirement: Default severities of the coding-standard rules
S-PERF-01, S-TPL-01 and S-DI-01 SHALL default to `info`, because calibration rounds 4 and 6 found their correct findings mostly negligible. S-TPL-02 and S-FN-01 SHALL default to `warning`. A severity set in the configuration file SHALL override these defaults.

#### Scenario: Recommendation by default
- **WHEN** S-PERF-01 is enabled without a severity and reports `array_merge()` in a loop
- **THEN** the finding has severity `info`

#### Scenario: Severity raised in configuration
- **WHEN** S-PERF-01 is enabled with `severity: warning`
- **THEN** its findings have severity `warning`
