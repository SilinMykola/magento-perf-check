# X-DB-01: calibration sample

1 of 1 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-message-queue/etc/db_schema.xml:24` (core)

[X-DB-01] Table "queue_poison_pill" has no primary key in any db_schema.xml of the project. Add <constraint xsi:type="primary">: replication and clusters need one.

```
    18              <column name="id"/>
    19          </constraint>
    20          <constraint xsi:type="unique" referenceId="QUEUE_LOCK_MESSAGE_CODE">
    21              <column name="message_code"/>
    22          </constraint>
    23      </table>
>   24      <table name="queue_poison_pill" resource="default" engine="innodb" comment="Sequence table for poison pill versions">
    25          <column xsi:type="varchar" name="version" length="255" nullable="false" comment="Poison Pill version."/>
    26      </table>
    27  </schema>
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-message-queue/etc/db_schema.xml:24` | TP | queue_poison_pill is declared only in module-message-queue db_schema.xml with a single version column and no primary constraint anywhere in the corpus. | re-check |  |
