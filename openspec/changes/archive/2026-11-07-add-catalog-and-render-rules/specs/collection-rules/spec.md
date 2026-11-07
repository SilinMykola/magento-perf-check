## ADDED Requirements

### Requirement: P-COL-03 all attributes selected
P-COL-03 SHALL report, as `info`, `addAttributeToSelect('*')` on an EAV collection. It is off by default.

#### Scenario: Literal star
- **WHEN** code calls `$productCollection->addAttributeToSelect('*')`
- **THEN** P-COL-03 reports the call

#### Scenario: Named attributes
- **WHEN** code calls `addAttributeToSelect('name')` or `addAttributeToSelect(['name', 'price'])`
- **THEN** P-COL-03 reports nothing

#### Scenario: Another class
- **WHEN** a class that is not an EAV collection has `addAttributeToSelect('*')`
- **THEN** P-COL-03 reports nothing
