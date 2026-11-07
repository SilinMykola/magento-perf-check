# db-schema-rules Specification

## Purpose
Rules on `db_schema.xml` that catch table definitions which cause problems in replicated or clustered databases.

## Requirements

### Requirement: X-DB-01 table without primary key
X-DB-01 SHALL report, as `error`, a `<table>` in `db_schema.xml` when the project index has no primary key constraint (`<constraint xsi:type="primary">`) for that table name in any module. Tables marked `disabled="true"` SHALL NOT be reported.

#### Scenario: New table without key
- **WHEN** a module declares `<table name="acme_log">` with columns and no primary constraint, and no other module declares one for it
- **THEN** X-DB-01 reports an error

#### Scenario: Extending a core table
- **WHEN** a module adds a column to `sales_order` without repeating its primary key
- **THEN** X-DB-01 reports nothing, because `Magento_Sales` declares it

### Requirement: X-DB-02 redundant index
X-DB-02 SHALL report, as `info`, an `<index>` of a `<table>` in `db_schema.xml` with the same columns in the same order as the primary key, a unique constraint or an earlier index of that table in the same file. Only `btree` indexes and indexes without `indexType` SHALL be reported. Disabled tables, indexes and constraints SHALL be ignored.

#### Scenario: Index on the primary key
- **WHEN** a table has `<constraint xsi:type="primary">` on `id` and `<index indexType="btree">` on `id`
- **THEN** X-DB-02 reports an info finding on the index

#### Scenario: Same columns in another order
- **WHEN** two indexes cover `code, store_id` and `store_id, code`
- **THEN** X-DB-02 reports nothing

#### Scenario: Fulltext index
- **WHEN** a fulltext index covers the same column as the primary key
- **THEN** X-DB-02 reports nothing
