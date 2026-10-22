# cli Specification

## Purpose
A single command that analyzes a Magento 2 / Mage-OS project for performance and Full Page Cache anti-patterns across XML and PHP and reports all findings in one place.

## Requirements

### Requirement: Analyze a project by path without installation
The command SHALL accept `--project=<path>` and analyze that Magento project without writing any file inside it. When `--project` is omitted, the current working directory SHALL be used.

#### Scenario: External project is analyzed read-only
- **WHEN** the command runs with `--project` pointing at a Magento installation
- **THEN** it reports findings for that project
- **AND** no file under the project path is created, modified or deleted

#### Scenario: Path is not a Magento project
- **WHEN** `--project` points at a directory without `app/etc` and `vendor/autoload.php`
- **THEN** the command prints an error naming the missing files and exits with code 2

### Requirement: Default and explicit analysis paths
By default the command SHALL analyze `app/code` and `app/design` of the project. `--paths=<p1,p2,...>`, relative to the project, SHALL replace the defaults and SHALL be analyzed even when the paths lie under `vendor`.

#### Scenario: Default paths
- **WHEN** the command runs without `--paths`
- **THEN** only files under `app/code` and `app/design` appear in findings

#### Scenario: Core code analyzed explicitly
- **WHEN** the command runs with `--paths=vendor/magento`
- **THEN** XML and PHP files under `vendor/magento` are analyzed

#### Scenario: Missing path
- **WHEN** a path given in `--paths` does not exist
- **THEN** the command prints an error naming that path and exits with code 2

### Requirement: Test code excluded by default
By default the command SHALL skip test code below the analyzed paths: any directory named `Test` and any `dev/tests` directory, for both XML and PHP. `--include-tests` SHALL turn the exclusion off. A path given in `--paths` that itself lies inside test code SHALL be analyzed.

#### Scenario: Module tests skipped
- **WHEN** `app/code/Acme/Promo/Test/Integration/ExportTest.php` contains a P-LOOP-01 violation and the command runs without `--include-tests`
- **THEN** no finding for that file is reported

#### Scenario: Tests included on request
- **WHEN** the same project is analyzed with `--include-tests`
- **THEN** the P-LOOP-01 violation in the test file is reported

#### Scenario: Test directory given explicitly
- **WHEN** the command runs with `--paths=app/code/Acme/Promo/Test`
- **THEN** files under that directory are analyzed

### Requirement: Merged report
The command SHALL run both the XML and the PHP analysis and report their findings together. Each finding SHALL carry rule ID, severity, file path relative to the project, line, message and documentation link.

#### Scenario: Findings from both analyzers in one report
- **WHEN** the analyzed paths contain both an X-LAY-01 violation and a P-LOOP-01 violation
- **THEN** one report lists both findings with their rule IDs

#### Scenario: Only rule findings are reported
- **WHEN** PHPStan reports an error that does not come from one of this tool's rules (for example an unknown class)
- **THEN** that error is not listed as a finding
- **AND** the summary shows how many such analysis notices were dropped

### Requirement: Output formats
The command SHALL support `--format=table` (default) and `--format=json`. JSON output SHALL be a single valid JSON document on stdout containing the list of findings and a summary with counts per severity and run duration.

#### Scenario: Table output
- **WHEN** the command runs without `--format`
- **THEN** findings are printed grouped by file with line, severity, rule ID and message, followed by a summary

#### Scenario: JSON output
- **WHEN** the command runs with `--format=json`
- **THEN** stdout parses as JSON with `findings` and `summary` keys and contains no other text

#### Scenario: Unknown format
- **WHEN** the command runs with `--format=xml`
- **THEN** it prints the supported formats and exits with code 2

### Requirement: Exit codes
The command SHALL exit with 0 when no finding is at or above the `--fail-on` severity (default `error`), and with 1 when at least one finding is. It SHALL exit with 2 when the tool itself fails, including when PHPStan cannot complete.

#### Scenario: Clean run
- **WHEN** there are no findings, or only `warning` findings with the default `--fail-on`
- **THEN** the exit code is 0

#### Scenario: Error finding
- **WHEN** at least one finding has severity `error`
- **THEN** the exit code is 1

#### Scenario: Lower threshold
- **WHEN** the command runs with `--fail-on=warning` and there is a `warning` finding
- **THEN** the exit code is 1

#### Scenario: Tool failure
- **WHEN** the PHPStan process crashes or runs out of memory
- **THEN** the command reports the failure and exits with code 2 rather than reporting a clean run
