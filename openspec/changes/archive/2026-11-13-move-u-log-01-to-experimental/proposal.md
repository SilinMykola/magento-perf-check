# Proposal

## Why

Round 19 put U-LOG-01 in the default set with an impact share of exactly 50 %, the least an experimental rule needs to enter the default set, and said the decision rested on one verdict. A second look at the code behind the store with most findings, after 0.9.0 was released, changed five verdicts from `TP` to `TP-low`:
- three calls are in a plugin on an admin controller, not on a storefront request as graded;
- two calls are in `catch` blocks of a newsletter observer around services that catch the exceptions of their remote calls themselves: one block is never reached, the other only on a database error.

Three of the five are in the sample, which now gives 12 `TP` and 18 `TP-low`, an impact share of 40 %; all 38 findings give 18 `TP` and 20 `TP-low` (47 %). Precision stays 100 %. Under the rule applied since round 6, an experimental rule enters the default set only with an impact share of at least 50 %. The re-check redoes round 19's decision on entry, so the drop to `warning` for a rule that stays in the default set does not apply.

## What Changes

- U-LOG-01 leaves the default set and becomes experimental, still at `error`. Nothing else about the rule changes.
- Round 19 and its grading file record the corrected verdicts and the new decision.

**This can change a CI result:** a project that relied on the default set no longer gets U-LOG-01 findings; enable the rule in `magento-perf-check.neon` to keep them.
