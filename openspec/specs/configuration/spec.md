# configuration Specification

## Purpose
Lets a team keep its analysis settings in the repository: which paths to check, which rules to turn off or re-grade, and project-specific allowlists, so every developer and CI run uses the same setup.

## Requirements

### Requirement: Configuration file location
The command SHALL read `magento-perf-rules.neon` from the project root when it exists. `--config=<path>` SHALL select another file. A missing file given with `--config` SHALL stop the command with exit code 2. A missing default file SHALL mean default settings.

#### Scenario: Default file
- **WHEN** the project root contains `magento-perf-rules.neon`
- **THEN** its settings apply to the run

#### Scenario: Explicit file
- **WHEN** the command runs with `--config=ci/perf.neon` and that file exists
- **THEN** its settings apply and the project root file is ignored

#### Scenario: Explicit file missing
- **WHEN** `--config` points to a file that does not exist
- **THEN** the command names the file and exits with code 2

### Requirement: Configuration keys
The file SHALL be NEON with a top-level `magentoPerf` section. It accepts:
- `paths`: a list of paths, relative to the project.
- `includeTests`: a boolean.
- `rules`: a map from rule ID to `enabled` (boolean) and `severity` (`error`, `warning` or `info`).
- `layout.allowlistHandles`: a list of handle prefixes added to the X-LAY-01 allowlist.
- `layout.replaceAllowlist`: a boolean; when true, the list replaces the default.

#### Scenario: Rule settings read
- **WHEN** the file sets `rules: {P-LOOP-03: {severity: info}}`
- **THEN** P-LOOP-03 findings are reported as `info`

### Requirement: Invalid configuration rejected
Unknown keys, unknown rule IDs and invalid values SHALL stop the command with exit code 2 and a message naming the key.

#### Scenario: Unknown key
- **WHEN** the file contains `magentoPerf: {path: [app/code]}`
- **THEN** the command reports the unknown key `path` and exits with code 2

#### Scenario: Unknown rule ID
- **WHEN** the file sets `rules: {X-LAY-99: {enabled: false}}`
- **THEN** the command reports that `X-LAY-99` is not a known rule and exits with code 2

#### Scenario: Invalid severity
- **WHEN** the file sets `severity: fatal` for a rule
- **THEN** the command exits with code 2 naming the rule and the allowed severities

### Requirement: Rule settings apply to every analyzer
A rule disabled in configuration SHALL produce no findings, whether it is an XML or a PHP rule. A severity override SHALL replace the rule's severity in every output format and in the `--fail-on` decision.

#### Scenario: Disabled PHP rule
- **WHEN** configuration sets `P-LOOP-01: {enabled: false}` and the code has a P-LOOP-01 violation
- **THEN** no P-LOOP-01 finding is reported

#### Scenario: Downgraded rule does not fail the run
- **WHEN** configuration sets `X-LAY-01: {severity: warning}`, the only finding is an X-LAY-01 error in `default.xml`, and `--fail-on` is the default
- **THEN** the finding is reported as `warning` and the exit code is 0

### Requirement: Command line overrides configuration
Options given on the command line SHALL take precedence over the same settings in the file: `--paths` over `paths`, and `--include-tests` over `includeTests`.

#### Scenario: Paths on the command line
- **WHEN** the file sets `paths: [app/code]` and the command runs with `--paths=app/code/Acme/Promo`
- **THEN** only `app/code/Acme/Promo` is analyzed

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
