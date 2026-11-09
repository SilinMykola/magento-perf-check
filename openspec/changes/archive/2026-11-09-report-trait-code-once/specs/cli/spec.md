## MODIFIED Requirements

### Requirement: Merged report
The command SHALL run both the XML and the PHP analysis and report their findings together. Each finding SHALL carry rule ID, severity, file path relative to the project, line, message and documentation link. Code in a trait SHALL give each finding once, however many classes use the trait. A finding whose message names the using class SHALL be reported for each such class, and a finding that only some of those classes give SHALL still be reported.

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
