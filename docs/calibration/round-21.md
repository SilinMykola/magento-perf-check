# Calibration, round 21

This round decides on U-CLI-01, the upgrade rule for Symfony Console 7.4 in Magento 2.4.9, with the findings of [round 7](round-7.md) and [round 20](round-20.md) and those of the five private stores of [round 15](round-15.md), which no earlier round ran with `--target-version=2.4.9`. The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Projects:** the calibration corpus ([`corpus.json`](corpus.json)) with the Magento 2.4.9 core, run with every rule on, and the five private stores of round 15 with U-CLI-01 enabled and `--target-version=2.4.9`, with the same rules on what is published.
- **Code:** 0.12.0-dev at commit 6b096af, on 2026-11-15. The runs are not stored.
- **Grading:** the corpus gives the 13 findings already graded: 9 in round 7 (the 25 modules and the extended part) and 4 in round 20 (the `github` part), in other modules; no other finding. The private stores give 23 findings, each graded in [`graded/round-21/`](graded/round-21/U-CLI-01.md): its class, its registration in `di.xml` and whether its module is enabled were checked in the code. All 13 corpus findings were checked again the same way; one of them, MageMojo's `CronCommand`, is not in `CommandList` itself but replaces core's `Magento\Cron\Console\Command\CronCommand` through a `<preference>` in its `di.xml`, and Magento_Cron registers that command, so `bin/magento` creates it all the same. Each was graded once, by one grader.
- **Other rules:** they were not run on the private stores; their earlier results stand.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [U-CLI-01](../rules/U-CLI-01.md) | error | 36 | 36 | 36 | 0 | 0 | 0 | 100 % | 100 % | default set |

**U-CLI-01.** Every finding is a command registered in `di.xml` of an enabled module whose `execute()` has no return type, in a class that extends Symfony's `Command`. Symfony Console 7.4 declares `execute(): int`, so on 2.4.9 PHP refuses to declare the class, and `bin/magento` fails on every call, cron included. Of the 23 private findings, 20 are the stores' own code and 3 come from third parties; four of the five stores have at least one. 10 of the 36 come from one private store.

## Decision

U-CLI-01 joins the default set at `error`: 36 graded findings, all correct and all mattering. Round 20 counted only its own 4 findings and so found the data insufficient; the rounds together reach the 10 a decision needs, as round 15 combined X-LAY-01's findings. The rule runs only on a project on 2.4.9 or later, or with `--target-version=2.4.9` or later, so on a project already there it reports commands that fail today.
