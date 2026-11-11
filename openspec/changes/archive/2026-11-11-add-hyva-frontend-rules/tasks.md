# Tasks

- [x] Quote on each rule page the sentence of each source it rests on
- [x] Decide the allowlist of T-05 for scripts that must load synchronously, such as consent managers and payment SDKs, from the corpus findings: none is needed, the corpus has no such false positive; a script that must block the page goes in the baseline
- [x] Implement T-05 and T-06 as template rules with positive and negative fixtures, and mutation-test them
- [x] Measure both on the core and both corpus parts, grade the findings, and write the calibration round
- [x] Write the rule pages, update SPEC, rules.md and the changelog
