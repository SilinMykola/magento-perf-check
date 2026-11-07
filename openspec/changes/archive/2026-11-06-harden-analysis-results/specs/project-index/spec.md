## ADDED Requirements

### Requirement: Cached results follow the index
When an XML file behind the project index changes, PHP findings that depend on the index SHALL reflect the change on the next run, even if no PHP file changed.

#### Scenario: Proxy added and removed
- **WHEN** a class reports P-DI-01, then `di.xml` gets a Proxy for that argument, then the Proxy is removed again
- **THEN** the second run reports nothing and the third reports P-DI-01
