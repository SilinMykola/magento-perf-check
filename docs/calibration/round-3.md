# Calibration, round 3

Rounds 1 ([`docs/calibration.md`](../calibration.md)) and [2](round-2.md) set severities from measured precision and impact. This round records two decisions by the maintainer about direct object manager use, made on 2026-11-09 after a full review of the findings on one more project. A decision here replaces the earlier ones. Rules not listed keep their earlier result.

## Why

The maintainer treats a direct object manager call as a defect, not as a matter of impact: it hides the dependency from DI configuration, so it cannot be replaced by a Proxy or a preference, and Adobe's usage rules forbid it outside factories, proxies, the constructor fallback and `__wakeup`/`__sleep`. Rounds 1 and 2 lowered P-DI-06 to `info` because most findings had low impact. That stays true, and the severity is raised anyway.

## Runs

- **Project:** a Magento 2.3.3 project, not public, with 21 module vendors in `app/code` and one theme in `app/design`. All 177 findings of the default set were graded on 2026-11-09, with the code of version 0.3.1. The project is not named; its verdicts cannot be checked by others.
- **Corpus:** the calibration corpus of round 1 (`app/code`), run on 2026-11-09 with the code of this change.

Each finding was graded once, by one grader, with the verdicts of round 1.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [P-DI-06](../rules/P-DI-06.md) | info | 65 | 65 | 3 | 60 | 2 | 0 | 97 % | 5 % | raised to warning |
| [P-LOOP-11](../rules/P-LOOP-11.md) | error | 16 | 12 | 2 | 10 | 0 | 0 | 100 % | 17 % | kept |

- **P-DI-06** on the project. The 2 false positives were a lazy getter that keeps the object in the block's data; the rule leaves that form out since this round. The 3 findings that matter create a helper per rendered price box, or per deal in a list.
- **P-LOOP-11** is new. It reported 10 findings in the corpus and 6 on the project, 4 of them in the same blog module in both, so 12 distinct findings were graded. The 2 that matter: a product `create()->load()` per id in a batch export, and a product type `create()` per cart item in a frontend observer. The others are admin actions, an import and a self-test; two of them, admin mass actions, also hide a `create()->load()` per id. P-LOOP-01 does not see such loads, because a model created by class name through the object manager is untyped. In the Magento core the rule gives 79 findings, most of them `get()` of a class named in a configured list (pools and composites); they were not graded.
