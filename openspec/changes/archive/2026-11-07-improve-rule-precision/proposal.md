# Proposal

## Why

Calibration left several default rules weak on a small sample (P-COL-01 1 of 5, X-SETUP-01 1 of 4, P-LOOP-02 4 of 6), and it listed recurring false-positive patterns in `docs/calibration/follow-ups.md` that a rule change can remove. Meanwhile the most damaging Full Page Cache mistake, `cacheable="false"` in `default.xml`, is not reported by default, because it shares the experimental rule X-LAY-01 with handles that are mostly personal pages. The research for new rules also found well-sourced checks with a low false-positive risk.

## What Changes

Precision:
- **P-FPC-01:** only `true` counts; `false` is the default value and marks nothing private.
- **P-FPC-06:** actions that return only JSON or raw results are out of scope, as redirects already are: `FrontController` sets no-cache headers before the action, and nothing but a page sets public ones.
- **P-LOOP-02:** one finding per collection, its first loading call.
- **Loop context:** the body of a `foreach` over `array_chunk()` is not a loop body; one query per batch is the fix.
- **P-COL-01:** a collection on `$this`, as a property or from the object's own getter, is out of scope: it is usually memoized and its rows are read elsewhere.
- **P-COL-02:** a guard on the size being one, `setPage()` and `setIdFilter()` count.
- **X-SETUP-01:** only writes and reindexing count, on a matching receiver type, and calls inside an `if` branch are out of scope.
- **P-PLG-01:** a local variable assigned before `$proceed` and read after it means around is needed. With this change it moves back to the default set: 11 of 11 graded findings are correct.
- **X-LAY-01:** the default allowlist grows, and layouts that include the `customer_account` handle are out of scope.

New default rules: X-LAY-03 (split from X-LAY-01: the `default`, `catalog_*` and `cms_*` handles), P-LOOP-05, P-LOOP-09, P-LOOP-10, P-IDX-01, X-DB-02, X-AREA-01, X-GQL-03.

## Impact

- X-LAY-03 is on by default at `error`, so a project with `cacheable="false"` on those handles starts failing CI. This is the point of the rule, and it goes into `CHANGELOG.md`.
- `perf-ignore X-LAY-01` comments and baseline entries on those handles stop matching and need the new id. Only projects that opted in to X-LAY-01 have them.
- A new calibration round is published next to the first one. The first report cannot be regenerated: it records the severities before the decisions were applied.
