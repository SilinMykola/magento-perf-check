# X-DB-01: table without a primary key

| | |
|---|---|
| Severity | `error` |
| Status | default set |
| Analyzer | XML (`db_schema.xml`), with the project index of all `db_schema.xml` files |

## What it catches

A `<table>` in `db_schema.xml` (not `disabled="true"`) when no module in the project, core included, declares a `<constraint xsi:type="primary">` for that table name. A module that only adds columns to a core table such as `sales_order` is therefore not reported. Without any Magento configuration in the project, the rule reports nothing.

## Why it hurts performance

Row-based replication and clustered MySQL setups (Galera, Group Replication) need a primary key on every table. Without one, replicas apply changes with full table scans, and some cluster setups reject writes to such tables. Adobe requires a primary key on every table.

## Example

Bad:

```xml
<table name="acme_log" resource="default">
    <column xsi:type="int" name="log_id" nullable="false"/>
    <column xsi:type="text" name="message"/>
</table>
```

Good:

```xml
<table name="acme_log" resource="default">
    <column xsi:type="int" name="log_id" unsigned="true" nullable="false" identity="true"/>
    <column xsi:type="text" name="message"/>
    <constraint xsi:type="primary" referenceId="PRIMARY">
        <column name="log_id"/>
    </constraint>
</table>
```

## How to suppress

`<!-- perf-ignore X-DB-01: reason -->` directly before the `<table>`.

## Known false positives

- Tables whose primary key is created outside `db_schema.xml` (old install scripts) are not in the index.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | Tables must have a primary key. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
