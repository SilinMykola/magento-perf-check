# Spec Delta

## Purpose

Makes every finding traceable to a verifiable primary source: each rule documents what it catches, why, and which level A/B/C sources back it. Rules without an A/B source never reach the default set.

## ADDED Requirements

### Requirement: Rule metadata
Every rule SHALL have one metadata record that both analyzers use. The record holds the rule ID, default severity (`error`, `warning` or `info`), status (`default`, `after-calibration` or `experimental`), the documentation path and at least one source with its level (A, B or C).

#### Scenario: Severity comes from metadata
- **WHEN** a rule reports a finding without a context-specific severity
- **THEN** the finding's severity equals the rule's default severity in its metadata

#### Scenario: Default set needs an A/B source
- **WHEN** a rule's metadata lists only level C sources
- **THEN** its status is `experimental` and it is not enabled by default

### Requirement: Rule documentation page
Every implemented rule SHALL have `docs/rules/<ID>.md` with these sections:
- What it catches.
- Why it hurts performance.
- Bad and good examples.
- How to suppress it.
- Known false positives.
- **Basis**: each source with its level and a sentence on what it confirms.

Links to Magento core code SHALL point to a specific commit, never to a branch.

#### Scenario: Page exists for every implemented rule
- **WHEN** the test suite runs
- **THEN** it fails if a rule in the metadata has no `docs/rules/<ID>.md` or that page has no `Basis` section

#### Scenario: Branch links rejected
- **WHEN** a rule page links to `github.com/magento/magento2/blob/2.4-develop/...`
- **THEN** the documentation check fails and names the page and the link

### Requirement: Documentation link in findings
Every finding SHALL include a link to its rule's documentation page.

#### Scenario: Link in table and JSON
- **WHEN** a finding of rule X-LAY-01 is reported in any format
- **THEN** it contains a link ending in `docs/rules/X-LAY-01.md`

### Requirement: Source audit
`docs/source-audit.md` SHALL list every rule from the SPEC catalog marked ✅ or ⏳. For each rule it gives the verified sources with their levels, what each source confirms and the date of verification. A rule left without a verified A/B source SHALL be marked 🧪 in both the audit and the SPEC catalog.

#### Scenario: Rule without A/B source is demoted
- **WHEN** the audit finds no level A or B source for a ✅ or ⏳ rule
- **THEN** that rule is 🧪 in `docs/source-audit.md` and in `docs/SPEC.md`
- **AND** the catalog totals in `docs/SPEC.md` match the new counts

#### Scenario: Audit covers the catalog
- **WHEN** the audit is complete
- **THEN** every ✅ and ⏳ rule ID of the SPEC catalog appears in `docs/source-audit.md`
