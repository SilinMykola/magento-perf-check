# Proposal

## Why

U-CLI-01 stayed experimental after [round 7](../../../docs/calibration/round-7.md) with 9 findings, all correct, fewer than the 10 a decision needs. [Round 20](../../../docs/calibration/round-20.md) graded 4 more in other modules, all correct, but counted only its own findings. No round had run it on the five private stores with `--target-version=2.4.9`: they give 23 findings, all registered commands of enabled modules. Together that is 36 graded findings, all `TP`: a precision of 100 % and an impact share of 100 %, above what an `error` rule needs. [Round 21](../../../docs/calibration/round-21.md) records them.

## What Changes

- U-CLI-01 moves to the default set at `error`. It still runs only on a project on 2.4.9 or later, or with such a `--target-version`.
- Round 21 publishes the grades of the private findings, anonymously.

**This can change a CI result:** a project on 2.4.9 or with `--target-version=2.4.9` gets `error` findings for console commands whose `execute()` has no `int` return type.
