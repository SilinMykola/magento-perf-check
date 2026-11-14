# Calibration, round 20

This round grades the experimental rules on the `github` part of the calibration corpus: 50 modules from the most-starred GitHub repositories with the topic `magento2-module` ([`corpus.json`](corpus.json), section `github`; the [third held-out check](held-out-3.md) graded the default set on the same modules). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)): an experimental rule enters the default set with at least 10 graded findings, the precision its severity needs and an impact share of at least 50 % ([round 6](round-6.md)).

## Run

- **Corpus:** the 50 modules of the `github` part, analyzed alone, with every experimental rule enabled, on 2026-11-14 with the code of commit a368540 ([`runs/github/round-20.json`](runs/github/round-20.meta.json)). 141 findings come from 13 experimental rules; the other experimental rules report nothing here.
- **Sample:** up to 12 findings per rule, seed 20261114. Sampling: the findings of each rule sorted by file and line; for a rule with more than 12, Python's `random.Random(20261114).sample(findings, 12)`, one generator drawing for the rules in alphabetical order, and the drawn findings sorted again by file and line. The graded files are in [`graded/round-20/`](graded/round-20/).
- **Grading:** each finding was graded once, by one grader, from the code of the corpus; the false positives were checked again against the code. `TP-low` means the claim holds but the cost is negligible.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [P-COL-02](../rules/P-COL-02.md) | error | 1 | 1 | 0 | 0 | 1 | 0 | 0 % | – | experimental |
| [P-COL-03](../rules/P-COL-03.md) | info | 1 | 1 | 0 | 0 | 1 | 0 | 0 % | – | experimental |
| [P-DI-02](../rules/P-DI-02.md) | warning | 53 | 12 | 0 | 11 | 1 | 0 | 92 % | 0 % | experimental |
| [P-DI-04](../rules/P-DI-04.md) | info | 10 | 10 | 0 | 10 | 0 | 0 | 100 % | 0 % | experimental |
| [P-FPC-02](../rules/P-FPC-02.md) | warning | 12 | 12 | 0 | 0 | 12 | 0 | 0 % | – | experimental |
| [P-LOOP-12](../rules/P-LOOP-12.md) | warning | 5 | 5 | 0 | 2 | 3 | 0 | 40 % | 0 % | experimental |
| [S-PERF-01](../rules/S-PERF-01.md) | info | 17 | 12 | 0 | 8 | 4 | 0 | 67 % | 0 % | experimental |
| [S-TPL-01](../rules/S-TPL-01.md) | info | 29 | 12 | 0 | 12 | 0 | 0 | 100 % | 0 % | experimental |
| [T-06](../rules/T-06.md) | info | 4 | 4 | 0 | 0 | 4 | 0 | 0 % | – | experimental |
| [U-CLI-01](../rules/U-CLI-01.md) | error | 4 | 4 | 4 | 0 | 0 | 0 | 100 % | 100 % | experimental |
| [U-LOG-01](../rules/U-LOG-01.md) | error | 2 | 2 | 1 | 1 | 0 | 0 | 100 % | 50 % | experimental |
| [X-LAY-01](../rules/X-LAY-01.md) | warning | 1 | 1 | 0 | 0 | 1 | 0 | 0 % | – | experimental |
| [X-PLG-02](../rules/X-PLG-02.md) | info | 2 | 2 | 0 | 0 | 2 | 0 | 0 % | – | experimental |

- **P-DI-02, P-DI-04, S-TPL-01:** almost every finding is correct and none matters: constructors that read a context getter or an in-memory config, commands whose dependencies only store their arguments, `$this` in templates. Impact share 0 %.
- **P-DI-02's false positive** was the constructor fallback written as a full ternary, `$this->config = $config !== null ? $config : ObjectManager::getInstance()->get(DeploymentConfig::class)`. P-DI-06 already takes it as the documented fallback; P-DI-02 now does too.
- **S-PERF-01:** the 4 false positives are `array_merge()` calls in a loop that grow nothing: a merge into an array built anew in each iteration, defaults merged into each item, a write into a new key. That is the known false positive of the sniff; the rule runs magento-coding-standard and is not changed here.
- **P-FPC-02:** all 12 findings are blocks and view models placed only on pages that are never cached: the cart, the checkout of core, Hyvä and two checkout modules, and a customer account page. That is the false positive [round 6](round-6.md) found; the rule does not look at the pages a block is placed on.
- **T-06:** three images get their size from CSS, the known false positive on the rule page: two from the module's LESS, one from a `<style>` block in the same template. The fourth is a loading image inside a `position: fixed` wrapper, out of the page flow (graded false, unsure).
- **P-LOOP-12:** 3 of 5 findings are loads that only run when a caller passes an optional argument, and no caller does; the other 2 are per-store queries in a cron, TP-low.
- **X-LAY-01:** the one finding was the checkout page of Hyvä React Checkout, `hyvareactcheckout_reactcheckout_index`. Like `checkout_` and `onestepcheckout_`, it shows the visitor's own quote and is never cached, so `hyvareactcheckout_reactcheckout_` joins the personal pages of known modules on the rule page.
- **X-PLG-02, P-COL-02, P-COL-03:** a debug toolbar that returns at once unless it is collecting, a lazy-load plugin that returns after a short check for the blocks it does not process, a `getFirstItem()` after a filter on an external id that matches one row, and `addAttributeToSelect('*')` in a repository that returns one full category: the known false positives of these rules.
- **U-CLI-01, U-LOG-01:** 5 of 6 findings break on Magento 2.4.9 on a path users hit: four console commands registered in `CommandList`, so every `bin/magento` call fails, and a Monolog 1 `addInfo()` on every push to a module's REST endpoint. The sixth is a log call on a validation error, TP-low.

## Decision

No rule enters the default set. P-DI-02, P-DI-04 and S-TPL-01 have the data and the precision, but an impact share of 0 %; S-PERF-01 and P-FPC-02 have neither the impact nor the precision of 75 % that a `warning` needs, which round 6 also applied to S-PERF-01; the other rules have fewer than 10 graded findings, of which the upgrade rules U-CLI-01 and U-LOG-01 are the closest: 5 of 6 correct findings matter.

Two rules changed after grading, without a change of status:
- P-DI-02 takes the full ternary fallback in a constructor as allowed. The fallback it allows is now exactly one `get()` or `create()` call on the object manager with plain arguments: a further call on its result, or a call in an argument, is work.
- X-LAY-01 leaves out `hyvareactcheckout_reactcheckout_` handles.

## Limitations

- 50 modules picked by stars and one grading of each finding.
- Impact was judged from the code, not measured.
- Rules with fewer than 10 findings here may have more data in earlier rounds; this round decides only on its own data, as round 6 did.
