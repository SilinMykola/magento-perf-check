# Calibration, round 6

This round grades the experimental rules on the extended part of the calibration corpus ([change extend-calibration-corpus](../../openspec/changes/archive/2026-11-10-extend-calibration-corpus/proposal.md), SPEC milestone M8). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules of the default set are not graded here and keep their earlier result.

## Run

- **Corpus:** the extended part, `var/corpus/extended` ([`corpus.json`](corpus.json), section `extended`): the next 100 Packagist modules by downloads after the 25 of round 1, and Hyvä Theme 1.5.3. Paths `app/code` and `app/design`, all experimental rules enabled. Run on 2026-11-10 with the code of 0.5.0-dev, including the P-LOOP-12 and P-DI-02 changes of this round ([`runs/extended/`](runs/extended/)).
- **Sample:** up to 30 findings per rule, seed 20261110, with `bin/calibration/sample extended`; the graded files are in [`graded/round-6/`](graded/round-6/).
- **Grading:** each finding was graded once, by one grader, from the code of the corpus. `TP-low` means the claim holds but the cost is negligible.
- **During the round:** P-DI-02 reported `return parent::__construct(...)`, which its spec allows. That was fixed, the part was run again and P-DI-02 and P-LOOP-12 were sampled again with the same seed; verdicts of findings still in the sample were kept. P-LOOP-12 changed in the same run after a code review; its numbers below are for the final code.
- **No findings:** X-GQL-02, P-API-01, T-03 and S-TPL-02.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [P-COL-02](../rules/P-COL-02.md) | error | 44 | 30 | 0 | 4 | 25 | 1 | 14 % | 0 % | experimental |
| [P-COL-03](../rules/P-COL-03.md) | info | 13 | 13 | 4 | 9 | 0 | 0 | 100 % | 31 % | experimental |
| [P-COL-04](../rules/P-COL-04.md) | warning | 1 | 1 | 0 | 1 | 0 | 0 | 100 % | 0 % | experimental |
| [P-DI-02](../rules/P-DI-02.md) | warning | 352 | 30 | 0 | 24 | 6 | 0 | 80 % | 0 % | experimental |
| [P-DI-04](../rules/P-DI-04.md) | info | 74 | 30 | 1 | 28 | 0 | 1 | 100 % | 3 % | experimental |
| [P-FPC-02](../rules/P-FPC-02.md) | warning | 20 | 20 | 2 | 0 | 18 | 0 | 10 % | 100 % | experimental |
| [P-FPC-03](../rules/P-FPC-03.md) | error | 1 | 1 | 1 | 0 | 0 | 0 | 100 % | 100 % | experimental |
| [P-LOOP-04](../rules/P-LOOP-04.md) | warning | 4 | 4 | 0 | 2 | 2 | 0 | 50 % | 0 % | experimental |
| [P-LOOP-06](../rules/P-LOOP-06.md) | warning | 1 | 1 | 1 | 0 | 0 | 0 | 100 % | 100 % | experimental |
| [P-LOOP-12](../rules/P-LOOP-12.md) | warning | 52 | 30 | 3 | 24 | 2 | 1 | 93 % | 11 % | experimental |
| [S-DI-01](../rules/S-DI-01.md) | warning | 2 | 2 | 0 | 1 | 1 | 0 | 50 % | 0 % | experimental |
| [S-FN-01](../rules/S-FN-01.md) | warning | 6 | 6 | 1 | 1 | 4 | 0 | 33 % | 50 % | experimental |
| [S-PERF-01](../rules/S-PERF-01.md) | warning | 27 | 27 | 0 | 13 | 14 | 0 | 48 % | 0 % | experimental |
| [S-TPL-01](../rules/S-TPL-01.md) | warning | 247 | 30 | 0 | 30 | 0 | 0 | 100 % | 0 % | experimental |
| [T-04](../rules/T-04.md) | warning | 25 | 25 | 0 | 25 | 0 | 0 | 100 % | 0 % | experimental |
| [X-LAY-01](../rules/X-LAY-01.md) | warning | 47 | 30 | 1 | 5 | 24 | 0 | 20 % | 17 % | experimental |
| [X-PLG-02](../rules/X-PLG-02.md) | info | 7 | 7 | 0 | 7 | 0 | 0 | 100 % | 0 % | experimental |

## Decisions

All rules stay experimental. The thresholds leave three groups:

- **Below the precision threshold:** P-COL-02 (14 %), P-FPC-02 (10 %), X-LAY-01 (20 %) and S-PERF-01 (48 %). Their false-positive patterns are listed in [follow-ups](follow-ups.md).
- **Fewer than 10 graded findings:** P-COL-04, P-FPC-03, P-LOOP-04, P-LOOP-06, S-DI-01, S-FN-01 and X-PLG-02, insufficient data.
- **Precise but mostly negligible:** P-COL-03 (100 %, impact share 31 %), P-DI-02 (80 %, 0 %), P-DI-04 (100 %, 3 %), P-LOOP-12 (93 %, 11 %), S-TPL-01 (100 %, 0 %) and T-04 (100 %, 0 %). An experimental rule moves to the default set only when it would keep its severity there, that is with an impact share of at least 50 %. Below that, the impact share lowers a `warning` to `info`, and an `info` rule with mostly negligible findings adds work to every report for little gain. Round 4 kept S-PERF-01 experimental for the same reason. S-TPL-01 and T-04 also rest on level C sources only (SPEC section 2).

**P-LOOP-12.** The query in the called method is real in 27 of 29 decided findings. Most loops are small: the items of one order, cart or shipment, a few stores, or a one-off configurator command. The three that matter are a blog post page that loads one customer per comment without paging, a tax rate import that loads one rate per imported row, and a category import with one repository call per category and store. The two false positives are a product that the same iteration had just loaded into the repository's cache, and a loop that returns on the first order found.

**T-04.** All 25 findings are real and none costs anything: the view models that Hyvä's `require()` returns are shared instances that do no work when created.

**P-DI-02.** No finding has a cost that matters; most are getters on the context object, setters or in-memory configuration reads. The false positives are value and SDK objects that code creates with `new`, not the object manager, and one `return $this;`.
