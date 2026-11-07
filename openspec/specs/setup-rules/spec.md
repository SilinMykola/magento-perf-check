# setup-rules Specification

## Purpose
Rules on setup scripts that catch heavy work in scripts which run on every `setup:upgrade`.

## Requirements

### Requirement: X-SETUP-01 heavy work in recurring setup
X-SETUP-01 SHALL report, as `warning`, heavy work in the `install()` method of a class named `Recurring` or `RecurringData` in a `Setup` namespace. Heavy work means any of:
- `save()` on a model, a resource model or a `*RepositoryInterface`;
- `reindexAll()`, `reindexList()` or `reindexRow()` on `Magento\Framework\Indexer\IndexerInterface`.

A loop SHALL count only through such calls inside it: a loop over a list of table names or in-memory configuration is not heavy work. A call inside a branch of an `if` SHALL NOT be reported, because it does not run every time. Reads SHALL NOT be reported. Each heavy call SHALL give one finding.

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
- **WHEN** `install()` loops over models and calls `save()` on each
- **THEN** X-SETUP-01 reports a warning on the `save()` call

#### Scenario: Reads
- **WHEN** `install()` calls `getItems()` or `getSize()` on a collection
- **THEN** X-SETUP-01 reports nothing

#### Scenario: Guarded reindex
- **WHEN** `install()` calls `reindexAll()` inside `if ($this->needsReindex())`
- **THEN** X-SETUP-01 reports nothing
