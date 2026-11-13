# calibration Specification

## Purpose
Measures the precision of every default rule on a fixed, reproducible corpus of real Magento code. Publishes the results, and applies the thresholds that decide whether a rule stays in the default set and at which severity.

## Requirements

### Requirement: Fixed corpus
The calibration corpus SHALL consist of two parts, each pinned to a version:
- Magento 2.4.9 core;
- the 25 most-downloaded Packagist packages of type `magento2-module` that install on Magento 2.4.9.

`docs/calibration/corpus.json` SHALL list each part with its name, version and, for downloaded archives, the SHA-256 of the archive. This lets anyone rebuild the same corpus. It SHALL also record that Mage-OS, which SPEC section 7 names, is not part of this calibration, and why.

#### Scenario: Corpus manifest
- **WHEN** the corpus is built
- **THEN** `docs/calibration/corpus.json` names every package with its version and checksum

#### Scenario: Mage-OS left out
- **WHEN** the calibration report is generated
- **THEN** it states that Mage-OS is not in the corpus and gives the reason recorded in the manifest

#### Scenario: Skipped package
- **WHEN** a top-downloaded package does not install on Magento 2.4.9, or is a metapackage without code
- **THEN** the manifest records it as skipped with the reason, and the next package by downloads takes its place

### Requirement: Corpus is built without touching the test project
The corpus SHALL be built under `var/corpus/` in this repository, a git-ignored directory. The Magento 2.4.9 part is a copy of the project named by the `CORPUS_SOURCE_PROJECT` environment variable, and that project SHALL NOT be written to.

#### Scenario: Source project unchanged
- **WHEN** the corpus build finishes
- **THEN** no file under `CORPUS_SOURCE_PROJECT` is newer than the start of the build

### Requirement: Sampling
For each default rule, up to 30 findings SHALL be sampled from the corpus run with a fixed, recorded seed, after test code and sample-data modules are excluded. If a rule has 30 findings or fewer, all of them are graded. The sample SHALL be stored in `docs/calibration/`.

#### Scenario: Reproducible sample
- **WHEN** the sampling script runs twice on the same corpus run with the same seed
- **THEN** it selects the same findings

### Requirement: Grading
Every sampled finding SHALL be graded by reading the code:
- `TP`: the rule's claim holds;
- `TP-low`: it holds, but the impact is negligible;
- `FP`: the claim does not hold;
- `unclear`.

Each verdict has a one-line reason. The owner MAY re-check graded findings; each re-checked finding is marked as re-checked, and every changed verdict is recorded with the original one. The report SHALL state how many verdicts were re-checked per rule, and that unreviewed verdicts had a single grader.

#### Scenario: Owner correction
- **WHEN** the owner changes a verdict from TP to FP
- **THEN** the table shows the final verdict, the original verdict and that the owner changed it
- **AND** precision is computed from the final verdicts

### Requirement: Precision and thresholds
Precision SHALL be (TP + TP-low) / (TP + TP-low + FP); `unclear` findings are excluded and counted. Thresholds:
- `error` rules SHALL stay `error` at ≥ 90 %, become `warning` at 75–90 %, and move to experimental below 75 %;
- `warning` rules SHALL stay `warning` at ≥ 75 % and move to experimental below;
- `info` rules have no threshold;
- a rule with fewer than 10 graded findings keeps its severity and is marked "insufficient data".

#### Scenario: Error rule at 80 %
- **WHEN** an `error` rule reaches 80 % after its fixes
- **THEN** its severity becomes `warning`

#### Scenario: Warning rule at 60 %
- **WHEN** a `warning` rule reaches 60 % after its fixes
- **THEN** it moves to experimental and is off by default

### Requirement: Impact share
Impact share SHALL be TP / (TP + TP-low). For a rule that stays in the default set with at least 10 graded findings, an impact share below 50 % SHALL lower the severity from the precision thresholds by one step: `error` to `warning`, `warning` to `info`. The report SHALL show the impact share next to precision.

#### Scenario: Correct but mostly negligible
- **WHEN** an `error` rule has 30 graded findings, 2 TP, 28 TP-low and no FP
- **THEN** precision is 100 %, impact share is 7 %, and the rule becomes `warning`

#### Scenario: Mostly significant
- **WHEN** a `warning` rule has 20 TP, 5 TP-low and 2 FP
- **THEN** precision is 93 %, impact share is 80 %, and the rule stays `warning`

### Requirement: Fix before demotion
For a rule whose M1 spot checks showed a recurring false-positive pattern, the fix SHALL be implemented and the rule measured after the fix. Demotion SHALL be decided on the measurement after the fix.

#### Scenario: Fixed rule passes
- **WHEN** a rule's fix removes its false-positive pattern and the measured precision meets its threshold
- **THEN** the rule keeps its severity

### Requirement: Published calibration report
`docs/calibration.md` SHALL state:
- the corpus (link to the manifest), the tool version and the date;
- for each default rule: the number of findings, sample size, TP, TP-low, FP, unclear, precision, impact share, how many verdicts the owner re-checked, and the decision (kept, downgraded, experimental, insufficient data);
- the limitations of the method.

The README section "What our rules are based on" SHALL link it. The rule registry and the SPEC catalog SHALL reflect every decision.

#### Scenario: Catalog matches the report
- **WHEN** the report says a rule moved to experimental
- **THEN** the rule's status in the registry is experimental, the SPEC catalog shows 🧪, and the README rule table no longer lists it in the default set

### Requirement: Follow-ups from grading
Recurring false-positive patterns found during grading that need a rule change beyond this change's specs SHALL be listed in `docs/calibration/follow-ups.md`, per rule, with the graded findings that show them. `docs/calibration.md` SHALL link that list.

#### Scenario: Pattern needs a spec change
- **WHEN** grading finds that X-LAY-01 reports Hyvä Checkout layouts that are private by design
- **THEN** `docs/calibration/follow-ups.md` lists the pattern under X-LAY-01 with the findings that show it

### Requirement: Extended corpus part
The calibration corpus SHALL have a second part, `extended`, built under `var/corpus/extended` as a copy of the first part without its `app/code`. It SHALL hold up to 100 Packagist modules chosen by downloads after the 25 of the first part, and Hyvä Theme at pinned tags. The first part SHALL stay unchanged. `docs/calibration/corpus.json` SHALL list every package of the part with its version and the SHA-256 of its archive, and every skipped package with the reason.

#### Scenario: First part untouched
- **WHEN** the extended part is built
- **THEN** the modules and versions of the first part in the manifest are the same as before

#### Scenario: Hyvä present
- **WHEN** the extended part is built
- **THEN** it contains the Hyvä default theme under `app/design/frontend/Hyva/default` and the Hyvä theme module under `app/code/Hyva/Theme`, and the manifest names their tags and checksums

### Requirement: Only standard registration code
A package SHALL be left out of the extended part when its `registration.php` holds anything other than a single standard `ComponentRegistrar::register()` call, because the analysis loads the project's autoloader. The manifest SHALL record it as skipped with that reason. A `cli_commands.php` at the root of a copied module, which the autoloader also runs, SHALL be removed from the copy and listed in the manifest.

#### Scenario: Extra code in registration.php
- **WHEN** a package's `registration.php` also reads a `.env` file
- **THEN** the package is not copied and the manifest records it as skipped

### Requirement: Experimental rules graded on the extended part
Every experimental rule SHALL be run on the extended part, and up to 30 of its findings SHALL be graded with the method of the first round, in `docs/calibration/round-6.md`. The precision thresholds SHALL apply to these results.

#### Scenario: Enough data
- **WHEN** an experimental `warning` rule has 12 graded findings with a precision of 90 % and an impact share of 60 %
- **THEN** round 6 may move it to the default set at `warning`, and the move is listed in `CHANGELOG.md`

### Requirement: Private projects as anonymous data
A calibration round MAY grade findings from private Magento projects that the owner allows. The round SHALL publish only counts and anonymous descriptions: each project is named by a letter, and no project name, client name, vendor namespace of custom code, path outside `app/etc/` or file content is published. The run and the verdicts with real paths SHALL stay local. A code excerpt received for a finding SHALL stay local, SHALL NOT be committed or published, and SHALL be deleted once the fixture it leads to is written or no change is needed; a committed fixture SHALL be written from scratch, without copying the excerpt. Its decisions SHALL use the same thresholds as the public corpus.

#### Scenario: Finding in custom code
- **WHEN** a graded finding is in a private store's own module
- **THEN** the round names the store by its letter and describes the code by what it does, such as "a product page widget", without its path

#### Scenario: Excerpt for a false positive
- **WHEN** a contributor sends a renamed excerpt of the code behind a false positive
- **THEN** the excerpt is not in the repository, and the fixture committed for the fix is written from scratch
