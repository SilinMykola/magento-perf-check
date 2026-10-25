# Spec Delta

## MODIFIED Requirements

### Requirement: Documentation link in findings
Every finding SHALL include a link to its rule's documentation page. The link SHALL be an absolute `https://github.com/SilinMykola/magento-perf-check/blob/v<version>/docs/rules/<ID>.md` URL for the running version, or the `blob/main/...` URL for a `-dev` version, so it works outside the machine that ran the analysis.

#### Scenario: Link in table and JSON
- **WHEN** a finding of rule X-LAY-01 is reported in any format
- **THEN** it contains a link ending in `docs/rules/X-LAY-01.md`

#### Scenario: Link is a versioned URL
- **WHEN** a finding of rule X-LAY-01 is reported by version 0.1.0
- **THEN** its link is `https://github.com/SilinMykola/magento-perf-check/blob/v0.1.0/docs/rules/X-LAY-01.md`
- **AND** the SARIF `helpUri` for X-LAY-01 is the same URL

#### Scenario: Development build links to main
- **WHEN** a finding of rule X-LAY-01 is reported by version `0.2.0-dev`
- **THEN** its link is `https://github.com/SilinMykola/magento-perf-check/blob/main/docs/rules/X-LAY-01.md`
