## MODIFIED Requirements

### Requirement: Exit codes
The command SHALL exit with 0 when no finding is at or above the `--fail-on` severity (default `error`), and with 1 when at least one finding is. It SHALL exit with 2 when the tool itself fails, including when PHPStan cannot complete, when PHPStan reports an error outside any file, and when nothing was analyzed.

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

#### Scenario: Worker failure with valid output
- **WHEN** PHPStan exits normally but its JSON output has an entry in `errors`, for example a worker that hit the memory limit
- **THEN** the report is still written, in every format, though PHPStan may have dropped its findings
- **AND** the JSON report has `summary.incomplete` set to true
- **AND** the reason is printed on stderr and the exit code is 2
- **AND** `--generate-baseline` writes no file

#### Scenario: Nothing to analyze
- **WHEN** the project has neither `app/code` nor `app/design`, or the paths hold no PHP, template or XML file, or only a PHP file that does not parse
- **THEN** the command says that there is nothing to analyze and exits with code 2

## ADDED Requirements

### Requirement: Paths stay in the project
Paths from `--paths` or the configuration file SHALL be normalized and deduplicated. A path with a `..` segment SHALL be rejected with exit code 2.

#### Scenario: Same path written twice
- **WHEN** the command runs with `--paths=app/code,./app/code`
- **THEN** `app/code` is analyzed once

#### Scenario: Path outside the project
- **WHEN** the command runs with `--paths=../other`
- **THEN** it prints an error naming that path and exits with code 2

### Requirement: Notices reach the reader
The analysis notice summary SHALL go to stderr when the format is `sarif`, `checkstyle` or `github`, or when `--output` is used. A missing or empty `generated/code`, without bitexpert/phpstan-magento installed, SHALL be a notice with source `generated-code`.

#### Scenario: SARIF with an unparsable file
- **WHEN** the command runs with `--format=sarif` and a file does not parse
- **THEN** stderr has the notice summary

#### Scenario: No generated code
- **WHEN** the project has no `generated/code`
- **THEN** the report has one `generated-code` notice

### Requirement: Runs do not share state
Two runs on one project at the same time SHALL each analyze their own paths. With PHP's posix extension, generated files SHALL live in a per-user directory that only that user can read, and the command SHALL exit with 2 if that directory belongs to someone else.

#### Scenario: Concurrent runs with different paths
- **WHEN** two runs on one project start at the same time with different `--paths`
- **THEN** each report lists findings from its own paths only
