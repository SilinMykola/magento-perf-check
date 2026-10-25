# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

A rule added to or removed from the default set, or a changed default severity, is always listed: it can change the result of your CI.

## [Unreleased]

## [0.1.0] - 2026-10-24

First release.

### Added

- `magento-perf-check` command: analyzes a Magento 2 or Mage-OS project by path (`--project`) without writing into it, merging XML and PHPStan findings in one report.
- Output formats: `table`, `json`, `sarif` (SARIF 2.1.0), `checkstyle` and `github` (GitHub Actions annotations).
- `--fail-on`, `--paths`, `--include-tests`, `--memory-limit`, `--config` and `--version` options; exit codes 0 (clean), 1 (findings), 2 (tool error).
- `magento-perf-check.neon` configuration file: paths, test code, per-rule `enabled` and `severity`, X-LAY-01 handle allowlist.
- Baseline: `--generate-baseline` and `--baseline`, matching findings by rule, file and message.
- PHPStan extension (`extension.neon`, `extension-experimental.neon`) for use inside an existing PHPStan setup; PHPStan 1.12 and 2.x are supported.
- Examples for GitHub Actions and GrumPHP.
- Rules in the default set, calibrated on Magento 2.4.9 core and 25 popular modules:
  - `error`: P-DI-03, P-FPC-06, P-FPC-09, P-FPC-10, P-LOOP-01, P-LOOP-02, X-DB-01, X-GQL-01, X-LAY-02, X-SEC-01;
  - `warning`: P-COL-01, P-DI-01, P-FPC-05, P-LOOP-07, X-EVT-01, X-EVT-02, X-EVT-03, X-IDX-01, X-MQ-01, X-SETUP-01;
  - `info`: P-DI-06, P-FPC-01, P-LOOP-03, T-01.
- Experimental rules, off by default: P-COL-02, P-DI-02, P-FPC-02, P-PLG-01, X-LAY-01.

[Unreleased]: https://github.com/SilinMykola/magento-perf-check/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/SilinMykola/magento-perf-check/releases/tag/v0.1.0
