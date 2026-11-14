## MODIFIED Requirements

### Requirement: Published calibration report
`docs/calibration.md` SHALL state:
- the corpus (link to the manifest), the tool version and the date;
- for each rule of the default set at round 1, with its severity at that round: the number of findings, sample size, TP, TP-low, FP, unclear, precision, impact share, how many verdicts the owner re-checked, and the decision (kept, downgraded, experimental, insufficient data);
- the limitations of the method.

The README section "What our rules are based on" SHALL link it. The rule registry and the SPEC catalog SHALL reflect every decision that no later round's report replaces.

#### Scenario: Catalog matches the report
- **WHEN** the report says a rule moved to experimental
- **THEN** unless a later round's report moves it back, the rule's status in the registry is experimental, the SPEC catalog shows 🧪, and the README rule table no longer lists it in the default set

#### Scenario: Regenerated page is stable
- **WHEN** a later round adds a rule to the default set, moves one out of it or changes its severity, or a run is repeated, and `bin/calibration/report` runs again
- **THEN** the round 1 table of `docs/calibration.md` lists exactly the round 1 rules with their round 1 severities and decisions, and the runs table lists the runs round 1 used
