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

#### Scenario: One file kills a worker
- **WHEN** a PHPStan worker dies, a single PHPStan process with `--debug` dies on one file, and an analysis of that file alone also dies
- **THEN** that file is left out with a `phpstan-crash` notice naming it, the rest is analyzed again and reported
- **AND** the report is not incomplete
- **AND** the next run leaves the file out from the start while its content is unchanged

#### Scenario: Single process dies on a file that is fine alone
- **WHEN** the single process dies on a file that an analysis of its own completes
- **THEN** nothing is left out and the report is incomplete

#### Scenario: Internal error
- **WHEN** PHPStan reports an internal error and no worker died
- **THEN** no single-process run is made and the report is incomplete
