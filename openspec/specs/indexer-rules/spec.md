# indexer-rules Specification

## Purpose
Rules on indexer configuration that catch custom indexers which cannot work in "Update by Schedule" mode.

## Requirements

### Requirement: X-IDX-01 indexer without mview view
X-IDX-01 SHALL report, as `warning`, an `<indexer>` in `indexer.xml` with a `view_id` for which the project index has no `<view id="...">` in any `mview.xml`.

#### Scenario: Missing view
- **WHEN** `indexer.xml` declares `<indexer id="acme_promo" view_id="acme_promo" .../>` and no `mview.xml` declares view `acme_promo`
- **THEN** X-IDX-01 reports a warning

#### Scenario: View in another module
- **WHEN** the view is declared in `mview.xml` of a different module
- **THEN** X-IDX-01 reports nothing

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
