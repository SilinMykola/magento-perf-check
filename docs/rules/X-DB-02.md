# X-DB-02: index that duplicates a key or another index

| | |
|---|---|
| Severity | `info` |
| Status | default set |
| Analyzer | XML (`db_schema.xml`) |

## What it catches

An `<index>` (btree, or without `indexType`) with the same columns in the same order as the primary key, a unique constraint or an earlier index of that table in the same file. Fulltext and hash indexes, and disabled ones, are not reported.

## Why it hurts performance

The duplicate serves no query the first key does not serve, and every index adds work to writes. How much depends on the table: an index on the primary key column changes only on INSERT, DELETE or a change of the key, so on small tables written from the admin the cost is small. This is why the rule is `info`.

## Example

Bad:

```xml
<constraint xsi:type="primary" referenceId="PRIMARY">
    <column name="swatch_id"/>
</constraint>
<index referenceId="ACME_SWATCH_ID" indexType="btree">
    <column name="swatch_id"/>
</index>
```

Good:

Keep only the constraint.

## How to suppress

`<!-- perf-ignore X-DB-02: reason -->` directly before the element.

## Known false positives

- A key of the same table declared in another module is not compared, so that duplicate goes unreported.
- If another module disables the first key, the index is not a duplicate any more.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | Indexes "are regenerated during every table INSERT or UPDATE. That can hit performance; only add indexes when necessary." |

Verified 2026-11-07.
