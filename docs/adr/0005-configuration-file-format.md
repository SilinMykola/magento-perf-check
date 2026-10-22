# 5. Configuration in magento-perf-rules.neon, read with nette/neon

- Status: Accepted
- Date: 2026-10-22

## Context

Teams need to keep analysis settings in their repository: paths, test inclusion, disabled rules, severity overrides and the X-LAY-01 allowlist (docs/SPEC.md section 3.3). The settings must work the same way for XML and PHP rules, and in `--project` mode, where the tool is not installed in the project.

The SPEC also names a section in the project's `phpstan.neon` as an alternative. That file configures PHPStan only, so it cannot hold settings for the XML analyzer when PHPStan runs on its own.

## Decision

- **File:** one optional file, `magento-perf-rules.neon`, read from the project root, or from another location with `--config=<file>`. Reading it does not write anything into the project. The top-level `magentoPerf` section accepts:
  - `paths`, `includeTests`;
  - `rules.<ID>.enabled`, `rules.<ID>.severity`;
  - `layout.allowlistHandles`, `layout.replaceAllowlist`.
- **Format:** NEON, the format of PHPStan configuration that Magento developers already use. It is parsed with `nette/neon ^3.4`. PHPStan's phar bundles a NEON parser, but under a prefixed namespace that is not public API. `nette/neon` is not part of Magento 2.4.9, so it adds no version conflict there.
- **Validation:** strict. Unknown keys, unknown rule IDs and invalid values stop the run with exit code 2 and name the key.
- **Precedence:** `--paths` and `--include-tests` on the command line win over the file.
- **Where settings apply:** disabled rules and severity overrides are applied once, to the merged findings, before the baseline and before `--fail-on`. PHP rules still run when disabled; their findings are dropped afterwards.
- **No `phpstan.neon` section:** a section there is not supported. Users who run PHPStan directly use PHPStan's own `ignoreErrors` with the rule identifiers.

## Consequences

- One file controls both analyzers, and typos fail loudly instead of silently changing the analysis.
- A project that pins `nette/neon` 2.x cannot install the package. No supported Magento version does.
- Disabling a PHP rule does not save analysis time. If that matters for some rule later, the runner can leave the rule out of its generated PHPStan config.
