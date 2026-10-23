# Spec Delta

## Purpose

Rules on indexer configuration that catch custom indexers which cannot work in "Update by Schedule" mode.

## ADDED Requirements

### Requirement: X-IDX-01 indexer without mview view
X-IDX-01 SHALL report, as `warning`, an `<indexer>` in `indexer.xml` with a `view_id` for which the project index has no `<view id="...">` in any `mview.xml`.

#### Scenario: Missing view
- **WHEN** `indexer.xml` declares `<indexer id="acme_promo" view_id="acme_promo" .../>` and no `mview.xml` declares view `acme_promo`
- **THEN** X-IDX-01 reports a warning

#### Scenario: View in another module
- **WHEN** the view is declared in `mview.xml` of a different module
- **THEN** X-IDX-01 reports nothing
