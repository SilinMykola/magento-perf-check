# Tasks

## 1. Rename

- [x] 1.1 Rename the package to `silin/magento-perf-check` and the namespace to `Silin\MagentoPerfCheck\` in `composer.json`, `src/`, `tests/`, neon files and scripts; regenerate the autoloader. Verify: `git grep MagentoPerfRules` finds nothing outside archived changes; `composer test` passes.
- [x] 1.2 Rename the default configuration file to `magento-perf-check.neon` (constant, help text, fixture). Verify: the "Default file" scenario test passes with the new name and fails with the old one.
- [x] 1.3 Use the new name for the SARIF tool name, the code scanning category, temp directories and the calibration user agent. Verify: `git grep magento-perf-rules` finds nothing outside archived changes and recorded runs.
- [x] 1.4 Update README (title, clone path, status line), SPEC (project name, repository), ADRs and rule pages. Verify: README tests pass.

## 2. Final check

- [x] 2.1 Run `composer test`, `bin/test-phpstan1`, `composer analyse`, `openspec validate rename-magento-perf-check --strict` and `openspec validate --specs`. Verify: all exit 0.
