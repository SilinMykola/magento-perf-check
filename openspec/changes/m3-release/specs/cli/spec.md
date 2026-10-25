# Spec Delta

## ADDED Requirements

### Requirement: Version option
The command SHALL print its name and version and exit with 0 when run with `--version`, without analyzing anything.

#### Scenario: Version printed
- **WHEN** the command runs with `--version`
- **THEN** stdout is `magento-perf-check <version>` followed by a newline and the exit code is 0
- **AND** no project path is checked

### Requirement: Report to a file
With `--output=<file>`, relative to the current directory, the command SHALL write the report in the chosen format to that file instead of stdout, print one summary line with the counts per severity and the file path, and keep the same exit code. It SHALL fail with exit code 2 before the analysis when the file cannot be written.

#### Scenario: Report written to a file
- **WHEN** the command runs with `--format=json --output=report.json`
- **THEN** `report.json` holds the same JSON report that stdout would have held
- **AND** stdout is one line `<n> finding(s): <e> error, <w> warning, <i> info. Report written to <path>.`

#### Scenario: Unwritable location
- **WHEN** the command runs with `--output=/nonexistent-dir/report.txt`
- **THEN** it exits with code 2 before running the analysis and names the file

#### Scenario: Not with baseline generation
- **WHEN** the command runs with both `--output` and `--generate-baseline`
- **THEN** it exits with code 2
