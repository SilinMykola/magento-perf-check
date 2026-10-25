# Spec Delta

## ADDED Requirements

### Requirement: Version option
The command SHALL print its name and version and exit with 0 when run with `--version`, without analyzing anything.

#### Scenario: Version printed
- **WHEN** the command runs with `--version`
- **THEN** stdout is `magento-perf-check <version>` followed by a newline and the exit code is 0
- **AND** no project path is checked
