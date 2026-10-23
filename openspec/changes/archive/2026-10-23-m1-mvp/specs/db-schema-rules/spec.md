# Spec Delta

## Purpose

Rules on `db_schema.xml` that catch table definitions which cause problems in replicated or clustered databases.

## ADDED Requirements

### Requirement: X-DB-01 table without primary key
X-DB-01 SHALL report, as `error`, a `<table>` in `db_schema.xml` when the project index has no primary key constraint (`<constraint xsi:type="primary">`) for that table name in any module. Tables marked `disabled="true"` SHALL NOT be reported.

#### Scenario: New table without key
- **WHEN** a module declares `<table name="acme_log">` with columns and no primary constraint, and no other module declares one for it
- **THEN** X-DB-01 reports an error

#### Scenario: Extending a core table
- **WHEN** a module adds a column to `sales_order` without repeating its primary key
- **THEN** X-DB-01 reports nothing, because `Magento_Sales` declares it
