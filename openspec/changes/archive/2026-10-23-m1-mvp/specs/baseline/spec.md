# Spec Delta

## Purpose

Lets a legacy project adopt the tool without fixing every existing finding first: known findings are recorded once, and later runs report only what is new.

## ADDED Requirements

### Requirement: Generate a baseline
`--generate-baseline=<file>` SHALL write all findings of the run to `<file>` as JSON and exit with code 0. A finding is identified by rule ID, file path relative to the project, and message; line numbers are not part of the identity. The same identity occurring several times SHALL be stored with a count.

#### Scenario: Baseline written
- **WHEN** the command runs with `--generate-baseline=perf-baseline.json` and finds 12 issues
- **THEN** `perf-baseline.json` holds entries covering those 12 findings and the exit code is 0

#### Scenario: File path relative to the working directory
- **WHEN** `--generate-baseline=perf-baseline.json` is given with `--project=/srv/shop`
- **THEN** the file is written to the current working directory, not into `/srv/shop`

### Requirement: Filter with a baseline
`--baseline=<file>` SHALL hide findings that match baseline entries, up to each entry's count, and report only the rest. The summary SHALL state how many findings the baseline hid. A missing or invalid baseline file SHALL stop the command with exit code 2.

#### Scenario: Only new findings
- **WHEN** a baseline was generated, one new violation is added in another file, and the command runs with `--baseline`
- **THEN** only the new finding is reported and the summary shows the number hidden by the baseline

#### Scenario: Line moved
- **WHEN** a baselined violation moves to another line in the same file without other changes
- **THEN** it stays hidden

#### Scenario: Second occurrence
- **WHEN** the baseline holds one occurrence of a finding and the file now has two identical ones
- **THEN** one of them is reported

### Requirement: Stale baseline entries
The summary SHALL count baseline entries that matched no finding in the run, so that a team can regenerate the baseline after fixing issues.

#### Scenario: Fixed issue
- **WHEN** a baselined violation was fixed
- **THEN** the summary reports one unmatched baseline entry
