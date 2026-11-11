## ADDED Requirements

### Requirement: Default severities of the coding-standard rules
S-PERF-01, S-TPL-01 and S-DI-01 SHALL default to `info`, because calibration rounds 4 and 6 found their correct findings mostly negligible. S-TPL-02 and S-FN-01 SHALL default to `warning`. A severity set in the configuration file SHALL override these defaults.

#### Scenario: Recommendation by default
- **WHEN** S-PERF-01 is enabled without a severity and reports `array_merge()` in a loop
- **THEN** the finding has severity `info`

#### Scenario: Severity raised in configuration
- **WHEN** S-PERF-01 is enabled with `severity: warning`
- **THEN** its findings have severity `warning`
