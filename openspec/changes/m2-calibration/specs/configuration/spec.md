# Spec Delta

## ADDED Requirements

### Requirement: Experimental rules are off by default
Rules with status experimental (🧪) in the rule registry SHALL produce no findings unless the configuration enables them with `rules: {<ID>: {enabled: true}}`. Rules with status default (✅) SHALL be on unless disabled. The same holds for findings from the PHPStan extension inside `magento-perf-check`.

#### Scenario: Experimental rule silent by default
- **WHEN** a rule was moved to experimental by calibration and the code violates it
- **THEN** no finding of that rule is reported

#### Scenario: Experimental rule enabled
- **WHEN** the configuration sets `enabled: true` for that rule
- **THEN** its findings are reported
