## ADDED Requirements

### Requirement: Private projects as anonymous data
A calibration round MAY grade findings from private Magento projects that the owner allows. The round SHALL publish only counts and anonymous descriptions: each project is named by a letter, and no project name, client name, vendor namespace of custom code, path outside `app/etc/` or file content is published. The run and the verdicts with real paths SHALL stay local. Its decisions SHALL use the same thresholds as the public corpus.

#### Scenario: Finding in custom code
- **WHEN** a graded finding is in a private store's own module
- **THEN** the round names the store by its letter and describes the code by what it does, such as "a product page widget", without its path
