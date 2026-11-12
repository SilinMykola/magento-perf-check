# Proposal

## Why

Three of the rules that run sniffs of magento/magento-coding-standard report findings that are nearly always correct and nearly never matter. Measured in two calibration rounds:

| Rule | Round 4 (25 modules) | Round 6 (extended part) |
|---|---|---|
| S-PERF-01 | 85 % precision, impact share 7 % | 48 % precision, impact share 0 % |
| S-TPL-01 | 100 % precision, impact share 0 % | 100 % precision, impact share 0 % |
| S-DI-01 | 100 % precision, impact share 0 % | 50 % precision, impact share 0 % |

For a rule in the default set, the calibration procedure ([`docs/calibration.md`](../../../docs/calibration.md), threshold 6) lowers a `warning` whose impact share is below 50 % to `info`, the level the catalog calls a recommendation (SPEC section 4). It does not cover experimental rules. This change applies the same step to these three, as a decision of the maintainer, who asked that the findings of magento-coding-standard be shown as recommendations rather than as problems: a user who enables them should see the level the data supports. S-DI-01 has fewer than 10 graded findings in each round, but 10 across both: none matters, 9 are correct and 1 is a false positive.

Other experimental rules with a low impact share (T-04, P-DI-02, P-LOOP-12 in round 6) are not part of this change.

S-FN-01 (impact share 50 % in both rounds: a `sleep()` in an order placement observer) and S-TPL-02 (no finding in either round) keep `warning`.

## What Changes

- S-PERF-01, S-TPL-01 and S-DI-01 default to `info`.
- They stay experimental: their sources are the sniffs (level C), except S-DI-01, whose level A source states a design rule, not a cost.
- A user can still set `severity: warning` for them in `magento-perf-check.neon`.
