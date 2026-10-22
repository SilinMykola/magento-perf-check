# 1. Support PHPStan 1.12 and 2.x from one code base

- Status: Accepted
- Date: 2026-10-21

## Context

The PHP rules ship as a PHPStan extension. Users install it next to the PHPStan version their project already has.

- A clean Magento 2.4.9 project declares `"phpstan/phpstan": "^1.9"` in `require-dev`, and its `composer.lock` pins 1.12.33. An extension that requires PHPStan 2.x does not install into that project unless the user edits the constraint.
- PHPStan 2.x is the current major: 2.3.1 is the latest release on Packagist as of 2026-10-21; the latest 1.x release is 1.12.34.
- Magento 2.4.4–2.4.8 and Mage-OS have not been checked yet. If any of them pins PHPStan below 1.12, this ADR is revisited.

## Decision

- `composer.json` requires `phpstan/phpstan: ^1.12 || ^2.0`.
- Rule code uses only API available in both majors:
  - `Rule`.
  - `RuleErrorBuilder` with `->identifier()`, which is mandatory in 2.x and already supported in 1.12.
  - `Scope` and type checks through `ObjectType::isSuperTypeOf()`.
  - php-parser node classes that are the same in parser 4 (bundled with PHPStan 1.12) and parser 5 (bundled with PHPStan 2.x).
- Where the two majors diverge, a small adapter class isolates the difference. No adapter is needed for the M0 rules.
- The development install uses PHPStan 2.x. `bin/test-phpstan1` copies the repository into a temporary directory, installs `phpstan/phpstan:~1.12.0` there and runs the same test suite.

## Consequences

- The extension installs into stock Magento 2.4.9 and into projects on PHPStan 2.x.
- Every rule test must pass on both majors; `bin/test-phpstan1` is part of the definition of done.
- Features available only in 2.x cannot be used in rules without an adapter. For example, the `parent` node attribute is not set by default in 2.x, so loop context uses a dedicated node attribute instead (see change m0-skeleton, design D4).
- Verified on 2026-10-21: PHPStan 1.12.34 and 2.3.1 both install and run the test suite on host PHP 8.5.11. Rule-level compatibility is verified by the rule tests themselves.
- Dropping 1.12 support later is a breaking change and needs a major version.
