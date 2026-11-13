## MODIFIED Requirements

### Requirement: U-LOG-01 version and status
U-LOG-01 SHALL have the version 2.4.4, and SHALL be experimental, at `error`, after its calibration round: its impact share is below the 50 % an experimental rule needs to enter the default set.

#### Scenario: Version gate
- **WHEN** the project runs Magento 2.4.3-p3 without `--target-version`, with U-LOG-01 enabled
- **THEN** U-LOG-01 does not run, and with `--target-version=2.4.4` it runs

#### Scenario: Off by default
- **WHEN** the configuration does not enable U-LOG-01
- **THEN** U-LOG-01 does not run, whatever the version
