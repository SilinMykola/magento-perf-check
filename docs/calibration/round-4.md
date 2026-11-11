# Calibration, round 4

This round measures the five rules that run sniffs of magento/magento-coding-standard ([ADR 7](../adr/0007-run-magento-coding-standard-performance-sniffs.md)). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** the calibration corpus of round 1 (`app/code`), with coding standard version 40 as installed there, and all five rules enabled. Run on 2026-11-10 with the code of 0.5.0-dev.
- **Grading:** every finding was graded, not a sample of 30, since there were only 71. Each was graded once, by one grader. `TP-low` means the claim holds but the cost is negligible.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [S-PERF-01](../rules/S-PERF-01.md) | warning | 33 | 33 | 2 | 26 | 5 | 0 | 85 % | 7 % | experimental |
| [S-TPL-01](../rules/S-TPL-01.md) | warning | 28 | 28 | 0 | 28 | 0 | 0 | 100 % | 0 % | experimental |
| [S-TPL-02](../rules/S-TPL-02.md) | warning | 0 | 0 | 0 | 0 | 0 | 0 | – | – | experimental |
| [S-DI-01](../rules/S-DI-01.md) | warning | 8 | 8 | 0 | 8 | 0 | 0 | 100 % | 0 % | experimental |
| [S-FN-01](../rules/S-FN-01.md) | warning | 2 | 2 | 1 | 1 | 0 | 0 | 100 % | 50 % | experimental |

**S-PERF-01.**
- The 2 findings that matter merge a result back into itself. One builds a category tree on frontend category pages, merging each child's subtree into the parent's array. The other collects attribute values over the children of a composite product during indexing.
- The other true findings are small or capped loops: fixed lists, stores, one page of posts, one cart, a cron batch of 100, or admin code.
- The 5 false positives call `array_merge()` once per item, with no result that grows across iterations. Four write into a new variable, a keyed slot or `$data[]`; the fifth merges into a variable that is reset in every iteration.

**S-TPL-01.**
- 8 `$this->helper()` calls give 16 findings, two each: one for the helper, one for `$this`.
- The other 12 are `$this` used where `$block` is meant. It is the same object, so there is no cost.
- `helper()` asks the object manager for the class on every call ([Php.php#L39-L41](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/TemplateEngine/Php.php#L39-L41), [#L134](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/TemplateEngine/Php.php#L134)). `get()` returns the shared instance after the first call ([ObjectManager.php#L68-L76](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/ObjectManager/ObjectManager.php#L68-L76)).

**S-DI-01.** All 8 name a Proxy in a constructor. A generated Proxy builds the real object only on its first use ([Proxy.php#L113-L120](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/ObjectManager/Code/Generator/Proxy.php#L113-L120)), so naming it costs nothing at run time. With fewer than 10 findings, the data is insufficient.

**S-FN-01.** One `sleep(1)` with up to 3 retries sits in an observer on order placement. A failing remote API then delays checkout by seconds. With 2 findings, the data is insufficient.

## Decisions

All five stay experimental:
- S-PERF-01, S-TPL-01 and S-FN-01 rest only on the sniffs, level C (SPEC section 2).
- S-TPL-02 and S-DI-01 also have a level A source, the Technical guidelines. It states a design rule, not a cost. S-DI-01 measured no cost, and S-TPL-02 had no finding to measure.
- S-PERF-01 passes the precision threshold for `warning`, but its impact share of 7 % would move it to `info`.

Reporting only a result merged back into itself, such as `$result = array_merge($result, ...)`, would keep both findings that matter. It would also drop 4 of the 5 false positives; the variable reset in every iteration would stay.
