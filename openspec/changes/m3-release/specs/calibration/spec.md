# Spec Delta

## MODIFIED Requirements

### Requirement: Published calibration report
`docs/calibration.md` SHALL state:
- the corpus (link to the manifest), the tool version and the date;
- for each default rule: the number of findings, sample size, TP, TP-low, FP, unclear, precision, impact share, how many verdicts the owner re-checked, and the decision (kept, downgraded, experimental, insufficient data);
- the limitations of the method.

The README section "What our rules are based on" SHALL link it. The rule registry and the SPEC catalog SHALL reflect every decision.

#### Scenario: Catalog matches the report
- **WHEN** the report says a rule moved to experimental
- **THEN** the rule's status in the registry is experimental, the SPEC catalog shows 🧪, and the rule table in `docs/rules.md` no longer lists it in the default set
