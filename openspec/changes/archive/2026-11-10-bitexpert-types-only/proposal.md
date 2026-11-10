# Proposal

## Why

When `bitexpert/phpstan-magento` is installed next to the tool, the command includes its `extension.neon` for the types of generated proxies, factories and extension attributes ([ADR 2](../../../docs/adr/0002-bitexpert-phpstan-magento-as-suggestion.md)). The same file also turns on bitExpert's own rules, such as "Use service contracts to persist entities". They are not performance rules, and their errors show as `phpstan` notices. On the calibration corpus that was 198 notices next to 221 findings.

bitExpert switches those rules with parameters (`magento.checkCollectionViaFactory`, `magento.checkServiceContracts`, `magento.checkResourceModelsUsedDirectly`, `extdn.setTemplateDisallowedForBlockClasses`). All four exist in every version that installs next to PHPStan 1.12 or 2.x: 0.32.0 requires `~1.12.0`, 0.40.0 and later `^2.0`, and 0.31.0 and earlier an older PHPStan (read in their `composer.json` and `extension.neon`, November 2026).

## What Changes

- **Generated configuration:** with bitExpert included, the command sets the four parameters to `false`.
- bitExpert's type extensions stay as they are. Its one rule without a switch, on collection mocks in tests, runs only on test code, which the command leaves out by default.

## Impact

With bitExpert installed next to the tool, the report has no notices from bitExpert's rules. Findings do not change. Without bitExpert nothing changes.
