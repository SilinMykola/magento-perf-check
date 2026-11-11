## ADDED Requirements

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
