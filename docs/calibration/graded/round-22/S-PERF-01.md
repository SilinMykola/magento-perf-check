# S-PERF-01: calibration grades, round 22

8 of 8 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Crealoz/EasyAudit/Ui/Component/Form/Field/Entries.php:55` | FP | `$entryLines` is reset in each iteration and merged once with that entry's lines: nothing grows across iterations. |
| 2 | `app/code/Crealoz/EasyAudit/Ui/Request/Form/AuditTypes.php:36` | TP-low | The result grows over the processor tree of an admin option source, a few dozen entries. |
| 3 | `app/code/Magefan/AutoRelatedProduct/Controller/Adminhtml/Rule/InlineEdit.php:60` | FP | Merges one loaded model's data with its posted row per item; no result grows. |
| 4 | `app/code/Magefan/AutoRelatedProduct/Model/ResourceModel/Rule.php:173` | FP | Builds each row from a small fixed array; the cost per item is constant. |
| 5 | `app/code/Magefan/DSUServer/Model/Dump/Database.php:180` | TP-low | The result grows over 4 fixed groups, once per dump. |
| 6 | `app/code/Magefan/ProductLabel/Controller/Adminhtml/Rule/InlineEdit.php:60` | FP | The same per-item merge of one model's data. |
| 7 | `app/code/Magefan/ProductLabel/Model/ResourceModel/Rule.php:197` | FP | The same per-row merge of small fixed arrays. |
| 8 | `app/code/Magepow/Flipbook/Controller/Adminhtml/Flip/InlineEdit.php:35` | FP | The same per-item merge of one model's data. |
