# Proposal

## Why

PHPStan analyses the code of a trait once for each class that uses it. A finding in a trait used by nine classes was reported nine times at the same line, and P-DI-06 named one of the using classes, not the trait. Seen on a Magento 2.3.3 project with a third-party blog module: one line of a sidebar trait gave nine P-DI-06 findings.

## What Changes

- **Report:** a finding of a rule at one line of a trait is reported once, from the first class that uses the trait. A finding that only some of the using classes give is still reported.
- **P-DI-06:** the message names the trait for code in a trait.

## Impact

Fewer findings, never new ones, for code in traits. A baseline that holds trait findings with the name of a using class lists them as new until it is generated again: the message changes.
