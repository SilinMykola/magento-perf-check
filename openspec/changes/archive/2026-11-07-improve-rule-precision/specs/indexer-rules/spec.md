## ADDED Requirements

### Requirement: P-IDX-01 direct reindex in request code
P-IDX-01 SHALL report, as `warning`, `reindexRow()` or `reindexList()` on `Magento\Framework\Indexer\IndexerInterface` in an observer, a controller or a class the project index lists as a plugin, when the same method calls no method whose name contains `isScheduled`.

#### Scenario: Observer reindexes a row
- **WHEN** an observer calls `$this->indexer->reindexRow($id)`
- **THEN** P-IDX-01 reports a warning

#### Scenario: Mode checked
- **WHEN** the call sits inside `if (!$this->indexer->isScheduled())`
- **THEN** P-IDX-01 reports nothing

#### Scenario: Cron job
- **WHEN** a class that is not an observer, controller or plugin makes the call
- **THEN** P-IDX-01 reports nothing

#### Scenario: Not an indexer
- **WHEN** the receiver has a `reindexRow()` method but is not an `IndexerInterface`
- **THEN** P-IDX-01 reports nothing
