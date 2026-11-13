# Proposal

## Why

A verdict of `FP` or `unclear` from a private project says that a rule is wrong or doubtful, not why. Fixing the rule needs the shape of the code it reported, so the calibration guide now asks contributors for a small, renamed code excerpt for those findings, sent privately and only where their agreement allows it. The calibration spec must say what happens to such code.

## What Changes

- Code excerpts received for a finding stay local, are never committed or published, and are deleted once the fixture they lead to is written, or once no change is needed. Committed fixtures are written from scratch, without copying the excerpt.
