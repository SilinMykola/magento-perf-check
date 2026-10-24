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

### Requirement: Rules made experimental by calibration
X-LAY-01, P-COL-02, P-DI-02, P-FPC-02 and P-PLG-01 SHALL have status experimental, as decided in `docs/calibration.md`: their measured precision is below the threshold for their severity. The PHP rules among them SHALL be registered in `extension-experimental.neon`, not in `extension.neon`.

#### Scenario: Calibrated rule off by default
- **WHEN** a project without configuration has a layout with `<block cacheable="false">` in `default.xml`
- **THEN** X-LAY-01 reports nothing

#### Scenario: PHPStan with the main extension only
- **WHEN** a project includes only `extension.neon` and calls `getFirstItem()` on a collection without `setPageSize()`
- **THEN** P-COL-02 reports nothing
