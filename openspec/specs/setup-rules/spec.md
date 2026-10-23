# setup-rules Specification

## Purpose
Rules on setup scripts that catch heavy work in scripts which run on every `setup:upgrade`.

## Requirements

### Requirement: X-SETUP-01 heavy work in recurring setup
X-SETUP-01 SHALL report, as `warning`, heavy work in the `install()` method of a class named `Recurring` or `RecurringData` in a `Setup` namespace. Heavy work means any of:
- a loop;
- a call to `load()`, `getItems()`, `getSize()` or `save()`;
- `reindexAll()`, `reindexList()` or `reindexRow()`.

One finding per method SHALL point at the first such statement.

#### Scenario: Reindex in recurring
- **WHEN** `Setup/Recurring.php` calls `$this->indexer->reindexAll()` in `install()`
- **THEN** X-SETUP-01 reports a warning

#### Scenario: Schema check only
- **WHEN** `install()` only checks whether a column exists and adds it
- **THEN** X-SETUP-01 reports nothing

#### Scenario: Data patch
- **WHEN** a data patch class in `Setup/Patch/Data` loops over records
- **THEN** X-SETUP-01 reports nothing
