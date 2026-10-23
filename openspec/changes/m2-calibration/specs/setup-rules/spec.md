# Spec Delta

## MODIFIED Requirements

### Requirement: X-SETUP-01 heavy work in recurring setup
X-SETUP-01 SHALL report, as `warning`, heavy work in the `install()` method of a class named `Recurring` or `RecurringData` in a `Setup` namespace. Heavy work means any of:
- a call to `load()`, `getItems()`, `getSize()` or `save()`;
- `reindexAll()`, `reindexList()` or `reindexRow()`.

A loop SHALL count only through such calls inside it: a loop over a list of table names or in-memory configuration is not heavy work. One finding per method SHALL point at the first heavy call.

#### Scenario: Reindex in recurring
- **WHEN** `Setup/Recurring.php` calls `$this->indexer->reindexAll()` in `install()`
- **THEN** X-SETUP-01 reports a warning

#### Scenario: Schema check only
- **WHEN** `install()` only checks whether a column exists and adds it
- **THEN** X-SETUP-01 reports nothing

#### Scenario: Data patch
- **WHEN** a data patch class in `Setup/Patch/Data` loops over records
- **THEN** X-SETUP-01 reports nothing

#### Scenario: Loop over table names
- **WHEN** `install()` loops over a literal list of table names and adds a foreign key for each
- **THEN** X-SETUP-01 reports nothing

#### Scenario: Save inside a loop
- **WHEN** `install()` loops over items and calls `save()` on each
- **THEN** X-SETUP-01 reports a warning on the `save()` call
