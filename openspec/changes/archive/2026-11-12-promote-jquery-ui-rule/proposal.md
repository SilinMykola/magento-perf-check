# Proposal

## Why

J-DEP-01 stayed experimental after [round 12](../../../docs/calibration/round-12.md): the corpus gave 5 findings, fewer than the 10 a decision needs. Round 14 showed that private stores hold the settings and the custom storefront code the public corpus lacks, and the owner allowed anonymous counts from them on 2026-11-12. Run on the five private stores on 2026-11-12 with every experimental rule enabled, J-DEP-01 gives 45 findings, X-LAY-01 10 and X-GQL-02 7. The other experimental rules either stay under the 10 graded findings a decision needs or already have them; round 15 lists them.

Graded with the method of round 1, J-DEP-01 (45 findings in three of the five stores) reaches 35 graded findings with round 12: 24 TP, 10 TP-low, 1 FP, a precision of 97 % and an impact share of 71 %, counting scripts that Magento does not load now as correct findings, as the owner ruled. That is above the 75 % a `warning` rule needs and the 50 % impact share an experimental rule needs to enter the default set (round 6).

Reading the storefront scripts for the rule adds about 0.5 s to a run on the Magento 2.4.9 core (`--paths=vendor/magento`) with a warm PHPStan cache: 2.7 s without it, 3.2 s with it, 964 findings in both, measured on 2026-11-12. Round 15 gives the command and both runs.

## What Changes

- J-DEP-01 moves to the default set at `warning`, so storefront scripts are read by default. Disabling J-DEP-01 and J-DEP-02 still skips them.
- Calibration may use private projects that the owner allows, published as anonymous counts and descriptions only.
- Round 15 publishes the grades of J-DEP-01, X-LAY-01 and X-GQL-02 on the private stores. X-LAY-01 and X-GQL-02 stay experimental.
- The false-positive patterns found go to `docs/calibration/follow-ups.md`.

**This can change a CI result:** a project with storefront JavaScript that depends on `jquery/ui` gets `warning` findings, which fail a run with `--fail-on=warning`.
