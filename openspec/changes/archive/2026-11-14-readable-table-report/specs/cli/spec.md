## MODIFIED Requirements

### Requirement: Output formats
The command SHALL support these formats on stdout:
- `--format=table` (default): for people, one block per rule and severity (errors, then warnings, then info) with the severity, rule ID, rule title and number of findings, the documentation link, and one line `file:line, line` per file. Without `--verbose`, messages of single findings are left out and an info rule lists 5 files when more than 6 have findings; `--verbose` lists every file and each finding's line and message. With `--color=auto` (the default) colors and OSC 8 links to the rule pages are used only when stdout is a terminal, `NO_COLOR` is empty and `TERM` is not `dumb`; `--color=always` and `--color=never` force it; a report written with `--output` has none.
- `--format=json`: one JSON document with the findings and a summary of counts per severity and run duration.
- `--format=sarif`: one SARIF 2.1.0 log.
- `--format=checkstyle`: one Checkstyle XML document.
- `--format=github`: GitHub Actions workflow commands, one line per finding.

Machine formats SHALL contain nothing else on stdout.

#### Scenario: Table output
- **WHEN** the command runs without `--format` and finds three P-LOOP-01 errors in two files and one P-DI-06 warning
- **THEN** a P-LOOP-01 block comes first with "ERROR", the rule ID, its title and "3 findings", its documentation link and one line per file such as `app/code/Acme/A/Model/Two.php:37, 40`, then the P-DI-06 block, followed by the summary line `4 finding(s): 3 error, 1 warning, 0 info.`

#### Scenario: Table output in detail
- **WHEN** the command runs with `--verbose`
- **THEN** each file of each block is followed by the line and message of each of its findings, and no file is left out

#### Scenario: Colors only in a terminal
- **WHEN** `--color=auto` is in effect and stdout is not a terminal, `NO_COLOR` is not empty or `TERM` is `dumb`; or `--color=never` is given; or the report is written with `--output`
- **THEN** the table report contains no escape sequences

#### Scenario: Colors on request
- **WHEN** `--color=always` is given and the report goes to stdout
- **THEN** severities are colored labels and rule IDs are OSC 8 links to their pages, whatever `NO_COLOR` says

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

#### Scenario: GitHub output
- **WHEN** the command runs with `--format=github`
- **THEN** each finding is one line `::error file=<path>,line=<line>,title=<RULE-ID>::<message>`, using `::warning` for warnings and `::notice` for info
- **AND** property values and the message are escaped as GitHub requires (`%`, carriage return and line feed in the message; additionally `:` and `,` in properties)

#### Scenario: Unknown format
- **WHEN** the command runs with `--format=xml`
- **THEN** it prints the supported formats and exits with code 2
