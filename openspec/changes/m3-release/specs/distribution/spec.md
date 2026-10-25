# Spec Delta

## Purpose

Makes the tool installable with Composer into a Magento project and keeps what each release ships, its version and its changelog consistent.

## ADDED Requirements

### Requirement: Single version
The package SHALL declare its version in exactly one place in the code. The `--version` output, the documentation links and the changelog SHALL use that version.

#### Scenario: Changelog matches the version
- **WHEN** the test suite runs
- **THEN** it fails unless the newest released entry in `CHANGELOG.md` has the same version as the code

#### Scenario: Development version
- **WHEN** the code version ends in `-dev`
- **THEN** the test passes only if that version without the suffix is higher than the newest released entry in `CHANGELOG.md`

### Requirement: Changelog
The repository SHALL have `CHANGELOG.md` in Keep a Changelog format. Each release lists its changes; a rule added to or removed from the default set, or a changed default severity, SHALL be listed because it can change a user's CI result.

#### Scenario: First release entry
- **WHEN** version 0.1.0 is tagged
- **THEN** `CHANGELOG.md` has a `0.1.0` entry with its date that lists the default and experimental rules shipped

### Requirement: Distribution archive contents
The Composer dist archive SHALL contain the code, `extension.neon`, `extension-experimental.neon`, the command, the license, the README, `CHANGELOG.md`, `docs/` pages users read, and the coding-agent skill. It SHALL NOT contain tests, OpenSpec files, calibration data or development scripts.

#### Scenario: Archive excludes development files
- **WHEN** `git archive HEAD` is listed
- **THEN** it contains `src/`, `bin/magento-perf-check` and `docs/rules/`
- **AND** it contains no path under `tests/`, `openspec/`, `docs/calibration/` or `bin/calibration/`

### Requirement: Install into a Magento project
The package SHALL install with `composer require --dev silin/magento-perf-check` into a Magento 2.4.9 project with its locked PHPStan 1.12, without changing any other locked package, and `vendor/bin/magento-perf-check` SHALL then analyze that project with no options.

#### Scenario: Install and first run within two minutes
- **WHEN** the package is installed into a copy of the test Magento 2.4.9 project and run there without options
- **THEN** install plus first run finish within 2 minutes of wall-clock time
- **AND** the run exits with 0 or 1, not 2
- **AND** the time, versions and output are recorded in `docs/runs/m3-install.md`
