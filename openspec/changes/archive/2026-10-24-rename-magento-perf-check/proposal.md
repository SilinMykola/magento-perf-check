# Proposal

## Why

The project goes to GitHub as `SilinMykola/magento-perf-check` and to Packagist under the vendor `silin`. Its working name, `magento-perf-rules`, reads like a list of rules, while users run a checker: the command is already `magento-perf-check`. One name for the repository, the package, the command and the configuration file is easier to remember and to search for. The owner decided on 2026-10-24 to use `magento-perf-check` everywhere.

## What Changes

- **BREAKING** Composer package `magento-perf-rules/magento-perf-rules` becomes `silin/magento-perf-check`. Nothing is published yet, so no user is affected.
- **BREAKING** PHP namespace `MagentoPerfRules\` becomes `Silin\MagentoPerfCheck\` (tests: `Silin\MagentoPerfCheck\Tests\`).
- **BREAKING** The default configuration file `magento-perf-rules.neon` becomes `magento-perf-check.neon`.
- The SARIF tool name, the GitHub code scanning category, the temp directories and the HTTP user agent of the calibration scripts use the new name.
- README, SPEC, ADRs and rule pages use the new name. The README status line is brought up to date (M2 done).

Not changed: PHPStan identifiers (`magentoPerf.*`) and the `magentoPerf` parameter key, which do not contain the old name; archived changes and recorded runs, which are history.

## Capabilities

### New Capabilities

None.

### Modified Capabilities

- `configuration`: the default configuration file name.

## Impact

- **Code:** namespace in every PHP file, `composer.json` autoload, neon files.
- **Docs:** README, SPEC, ADRs 0003–0005, rule pages, CI example.
- **Users:** none yet; the package is not published.
