# Spec Delta

## ADDED Requirements

### Requirement: Suppression comments in XML
An XML finding SHALL NOT be reported when the element that triggered it is directly preceded, ignoring whitespace, by a comment `<!-- perf-ignore <RULE-ID>: <reason> -->` naming that rule. The reason SHALL be required: a comment without a reason suppresses nothing and SHALL be reported as an analysis notice naming the file and line.

#### Scenario: Suppressed with reason
- **WHEN** `<!-- perf-ignore X-LAY-01: payment return page must not be cached -->` directly precedes a `<block cacheable="false">`
- **THEN** X-LAY-01 reports nothing for that block

#### Scenario: Comment for another rule
- **WHEN** the preceding comment is `<!-- perf-ignore X-LAY-02: ... -->`
- **THEN** the X-LAY-01 finding is still reported

#### Scenario: Missing reason
- **WHEN** the preceding comment is `<!-- perf-ignore X-LAY-01 -->`
- **THEN** the finding is reported
- **AND** the summary counts one analysis notice for the comment without a reason

#### Scenario: Comment not adjacent
- **WHEN** another element stands between the comment and the violating element
- **THEN** the finding is reported

### Requirement: Additional configuration files
The XML analyzer SHALL also discover these module files under `etc/` and assign them area and module like other config files:
- `sections.xml`, `communication.xml`, `indexer.xml`, `mview.xml`, `db_schema.xml`.
- `.graphqls` files under `etc/` (area `graphql`).

#### Scenario: GraphQL schema
- **WHEN** `app/code/Acme/Promo/etc/schema.graphqls` exists
- **THEN** it is analyzed by the GraphQL rules with module `Acme_Promo`

#### Scenario: Frontend sections
- **WHEN** `app/code/Acme/Promo/etc/frontend/sections.xml` exists
- **THEN** it is analyzed with area `frontend`
