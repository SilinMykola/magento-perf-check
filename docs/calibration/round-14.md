# Calibration, round 14

This round measures C-02, the first rule on store settings: Merge JavaScript Files (`dev/js/merge_files`) switched on in the `system` section of `app/etc/config.php`. The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Public corpus:** the calibration corpus ([`corpus.json`](corpus.json)) and a Composer project of Magento 2.4.9. On 2026-11-12 their `app/etc/config.php` files had no `system` section, so they give no finding.
- **Private projects:** five Magento stores whose code is not public, used read-only on 2026-11-12. Only counts and anonymous descriptions are published: no name, path or file content. Four have an `app/etc/config.php`, three of those have a `system` section. Their latest commits date from 2022 to 2026.
- **Code:** 0.6.0-dev at commit 303f081, C-02 only. The rule does not depend on `--paths`.
- **Grading:** every finding was graded. The verdicts are in [`graded/round-14/`](graded/round-14/). Each was graded once, by one grader. For each finding, `app/etc/config.php` and `app/etc/env.php` were checked for the same setting and for JavaScript bundling (`dev/js/enable_js_bundling`). The database and the environment variables of the deployments were not seen.
- **Other rules:** they were not run.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [C-02](../rules/C-02.md) | warning | 2 | 2 | 0 | 2 | 0 | 0 | 100 % | 0 % | experimental |

**C-02.** The public corpus gives 0 findings, the private projects 2, in 2 of the 5 stores, both for the default scope. The third store with a `system` section sets the flag to `0`.
- TP-low, 2: in both stores merging is on in `config.php` and not overridden in `env.php`. Neither file switches on JavaScript bundling, which Adobe warns merging can break. Merging touches the scripts of the page head: in core, the RequireJS module puts its configuration and mixins next to `require.js` there ([Block/Html/Head/Config.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/RequireJs/Block/Html/Head/Config.php#L76-L138)), and every static file asset can be merged ([GroupedCollection.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Asset/GroupedCollection.php#L97)). These are a few files.
  - One store has a theme based on Luma, so every storefront page has them.
  - The other store's main theme inherits from Hyvä's `Hyva/reset`, whose installed `default_head_blocks.xml` has the `requirejs/require.js` head script commented out, and no layout of the store's own theme adds it back (seen on 2026-11-12). Only its checkout theme, based on Luma, has those head scripts.
- No false positive.

## Decision

C-02 stays experimental at `warning`: 2 graded findings are fewer than the 10 a decision needs (insufficient data), so the rule keeps its severity. Both are correct, so precision is not the obstacle, but their impact share of 0 % is below the 50 % an experimental rule needs to enter the default set ([round 6](round-6.md)). If later rounds confirm a low impact share, the rule would report at `info`, as X-LAY-04 does after [round 10](round-10.md).
