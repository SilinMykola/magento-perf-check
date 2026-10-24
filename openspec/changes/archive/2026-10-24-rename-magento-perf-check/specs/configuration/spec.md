# Spec Delta

## MODIFIED Requirements

### Requirement: Configuration file location
The command SHALL read `magento-perf-check.neon` from the project root when it exists. `--config=<path>` SHALL select another file. A missing file given with `--config` SHALL stop the command with exit code 2. A missing default file SHALL mean default settings.

#### Scenario: Default file
- **WHEN** the project root contains `magento-perf-check.neon`
- **THEN** its settings apply to the run

#### Scenario: Explicit file
- **WHEN** the command runs with `--config=ci/perf.neon` and that file exists
- **THEN** its settings apply and the project root file is ignored

#### Scenario: Explicit file missing
- **WHEN** `--config` points to a file that does not exist
- **THEN** the command names the file and exits with code 2
