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
By default the command SHALL skip test code below the analyzed paths: any directory named `Test` and any `dev/tests` directory, for both XML and PHP. `--include-tests` SHALL turn the exclusion off. A path given in `--paths` that itself lies inside test code SHALL be analyzed. Excluded test code SHALL NOT be a source of symbols either: a class declared there SHALL NOT replace a class of the same name for other code.

#### Scenario: Module tests skipped
- **WHEN** `app/code/Acme/Promo/Test/Integration/ExportTest.php` contains a P-LOOP-01 violation and the command runs without `--include-tests`
- **THEN** no finding for that file is reported

#### Scenario: Tests included on request
- **WHEN** the same project is analyzed with `--include-tests`
- **THEN** the P-LOOP-01 violation in the test file is reported

#### Scenario: Test directory given explicitly
- **WHEN** the command runs with `--paths=app/code/Acme/Promo/Test`
- **THEN** files under that directory are analyzed

#### Scenario: Stub of a core class in test code
- **WHEN** `app/code/Acme/Other/Test/Unit/_stubs/Magento/Customer/Model/Session.php` declares a plain `Magento\Customer\Model\Session` and another module injects that session without a Proxy
- **THEN** P-DI-01 reports the other module's class

#### Scenario: Test path next to its parent
- **WHEN** `--paths=app/code/Acme/Demo,app/code/Acme/Demo/Test/Unit` is given
- **THEN** the code in `Test/Unit` is analyzed

### Requirement: Merged report
The command SHALL run both the XML and the PHP analysis and report their findings together. Each finding SHALL carry rule ID, severity, file path relative to the project, line, message and documentation link. Code in a trait SHALL give one finding per rule and line, however many classes use the trait; a finding that only some of those classes give SHALL still be reported.

#### Scenario: Findings from both analyzers in one report
- **WHEN** the analyzed paths contain both an X-LAY-01 violation and a P-LOOP-01 violation
- **THEN** one report lists both findings with their rule IDs

#### Scenario: Only rule findings are reported
- **WHEN** PHPStan reports an error that does not come from one of this tool's rules (for example an unknown class)
- **THEN** that error is not listed as a finding
- **AND** the summary shows how many such analysis notices were dropped

#### Scenario: Trait used by several classes
- **WHEN** a trait with a `load()` in a loop is used by two classes
- **THEN** one P-LOOP-01 finding is reported at that line of the trait

### Requirement: Output formats
The command SHALL support these formats on stdout:
- `--format=table` (default).
- `--format=json`: one JSON document with the findings and a summary of counts per severity and run duration.
- `--format=sarif`: one SARIF 2.1.0 log.
- `--format=checkstyle`: one Checkstyle XML document.
- `--format=github`: GitHub Actions workflow commands, one line per finding.

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

#### Scenario: GitHub output
- **WHEN** the command runs with `--format=github`
- **THEN** each finding is one line `::error file=<path>,line=<line>,title=<RULE-ID>::<message>`, using `::warning` for warnings and `::notice` for info
- **AND** property values and the message are escaped as GitHub requires (`%`, carriage return and line feed in the message; additionally `:` and `,` in properties)

#### Scenario: Unknown format
- **WHEN** the command runs with `--format=xml`
- **THEN** it prints the supported formats and exits with code 2

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

#### Scenario: Notice of a disabled rule
- **WHEN** P-COL-02 is not enabled and the code calls `getFirstItem()` on a receiver of unknown type
- **THEN** the result contains no `untyped-receiver` notice

### Requirement: Runs do not share state
Two runs on one project at the same time SHALL each analyze their own paths. With PHP's posix extension, generated files SHALL live in a per-user directory that only that user can read, and the command SHALL exit with 2 if that directory belongs to someone else.

#### Scenario: Concurrent runs with different paths
- **WHEN** two runs on one project start at the same time with different `--paths`
- **THEN** each report lists findings from its own paths only
