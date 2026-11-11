# Proposal

## Why

A developer who plans to move a project to a newer Magento wants to know, before running `composer update`, which of the project's own code will break. Adobe's Upgrade Compatibility Tool answers part of that question. But it is documented as "available for Adobe Commerce instances only", it needs Commerce access keys, and it checks mostly whether core symbols still exist. The Magento PhpStorm plugin has upgrade inspections too. They are off by default, and their bundled data stops at 2.4.4-beta4. PHPStan reports signature breaks only once the new vendor is installed. So an Open Source project still on 2.4.8 has nothing that tells it what 2.4.9 will break. P-API-01 already covers one such break (APSB25-88). SPEC milestone M7 plans the rest.

## What Changes

- **Upgrade rules.** A new rule group with the `U-` prefix. Each rule names the Magento version that introduced the change it checks, and has a level A or B source for that change.
- **`--target-version=<version>`.** The command runs every upgrade rule whose version is not newer than the target. A rule runs when it applies to the installed Magento version or to the option, so rules for versions the project already has keep running: the code they find is broken now. A rule can name a first version per release line, for changes that also shipped in patch releases.
- **First rule, U-CLI-01.** A console command that overrides `execute()` without the `: int` return type that Symfony Console 7.4 declares. Since 2.4.9 such a class cannot be loaded. The corpus has 9 such commands (1 in the first part, 8 in the extended part, counted by grep on 2026-11-10).
- **The audit** of 2.4.8 and 2.4.9 is in `design.md`. It lists the candidate rules with their sources and the order to build them. Each later rule is its own change.

Out of this change: the rules after U-CLI-01, and a generated symbol diff between tags (design D4).
