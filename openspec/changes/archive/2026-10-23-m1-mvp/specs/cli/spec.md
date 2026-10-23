# Spec Delta

## MODIFIED Requirements

### Requirement: Output formats
The command SHALL support these formats on stdout:
- `--format=table` (default).
- `--format=json`: one JSON document with the findings and a summary of counts per severity and run duration.
- `--format=sarif`: one SARIF 2.1.0 log.
- `--format=checkstyle`: one Checkstyle XML document.

Machine formats SHALL contain nothing else on stdout.

#### Scenario: Table output
- **WHEN** the command runs without `--format`
- **THEN** findings are printed grouped by file with line, severity, rule ID and message, followed by a summary

#### Scenario: JSON output
- **WHEN** the command runs with `--format=json`
- **THEN** stdout parses as JSON with `findings` and `summary` keys and contains no other text

#### Scenario: SARIF output
- **WHEN** the command runs with `--format=sarif`
- **THEN** stdout is a SARIF 2.1.0 log with one run whose `tool.driver.rules` lists every reported rule ID with its documentation link as `helpUri`
- **AND** each finding is a result with `ruleId`, `level` (`error`, `warning` or `note` for info), message, and a location with the file path relative to the project and the line

#### Scenario: Checkstyle output
- **WHEN** the command runs with `--format=checkstyle`
- **THEN** stdout is a `<checkstyle>` document with one `<file>` per file and one `<error>` per finding carrying `line`, `severity`, `message` and `source` set to the rule ID

#### Scenario: Unknown format
- **WHEN** the command runs with `--format=xml`
- **THEN** it prints the supported formats and exits with code 2
