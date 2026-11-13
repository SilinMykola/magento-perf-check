## MODIFIED Requirements

### Requirement: Private projects as anonymous data
A calibration round MAY grade findings from private Magento projects that the owner allows. The round SHALL publish only counts and anonymous descriptions: each project is named by a letter, and no project name, client name, vendor namespace of custom code, path outside `app/etc/` or file content is published. The run and the verdicts with real paths SHALL stay local. A code excerpt received for a finding SHALL stay local, SHALL NOT be committed or published, and SHALL be deleted once the fixture it leads to is written or no change is needed; a committed fixture SHALL be written from scratch, without copying the excerpt. Its decisions SHALL use the same thresholds as the public corpus.

#### Scenario: Finding in custom code
- **WHEN** a graded finding is in a private store's own module
- **THEN** the round names the store by its letter and describes the code by what it does, such as "a product page widget", without its path

#### Scenario: Excerpt for a false positive
- **WHEN** a contributor sends a renamed excerpt of the code behind a false positive
- **THEN** the excerpt is not in the repository, and the fixture committed for the fix is written from scratch
