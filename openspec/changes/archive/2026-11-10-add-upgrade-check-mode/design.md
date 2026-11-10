# Design

Researched on 2026-11-10. The Magento tags are pinned:
- 2.4.7 = `fcebd3a4ad2a6863af2e2092f5e548cbd81cb0f8`;
- 2.4.8 = `94b8544e82fd84d1443060cddb5481b7fd462de2`;
- 2.4.9 = `755e34dd689021c5165db9d35ecff74f7dc51527`.

Adobe docs were read from their GitHub sources: `AdobeDocs/commerce-php@a4da583a` and `AdobeDocs/commerce-operations.en@616f161e`.

## D1. What exists already

**Upgrade Compatibility Tool.**
- The [overview](https://experienceleague.adobe.com/en/docs/commerce-operations/upgrade-guide/upgrade-compatibility-tool/overview) says "The Upgrade Compatibility Tool is available for Adobe Commerce instances only". The [prerequisites](https://experienceleague.adobe.com/en/docs/commerce-operations/upgrade-guide/upgrade-compatibility-tool/prerequisites) require Commerce access keys.
- Its CLI reference gives version 3.0.28, with targets up to 2.4.10-alpha1.
- Its [error catalog](https://github.com/AdobeDocs/commerce-operations.en/blob/616f161efe8b0fa6b722fb301c5fdd06db2e2ce9/help/upgrade/upgrade-compatibility-tool/error-messages.md) covers:
  - core symbols that do not exist in the target;
  - non-API and deprecated usage;
  - db_schema conflicts with removed core tables;
  - GraphQL schema diffs between two live endpoints.
- It has no checks for behavior changes.
- Whether it runs on an Open Source tree was not tried.

**Magento PhpStorm plugin** (`magento/magento2-phpstorm-plugin`, HEAD `f816fe6b`).
- It can run the external UCT.
- It also has its own existence and deprecation inspections, registered with `enabledByDefault="false"`.
- Their bundled index has version keys from 2.3.0 to 2.4.4-beta4 only.
- Rebuilding the index is an action shown only in internal mode.

**PHPStan** at level 0 reports `method.childReturnType` and `method.abstract`, but only against the vendor of the target version. This was checked on a small file with PHPStan 2.3.1.

Our mode runs on the installed version of an Open Source project, needs no credentials, and has one sourced rule per change. On Mage-OS it needs an explicit target (D2).

## D2. Installed version and target

- **Installed version.** It is the `version` of `vendor/magento/magento2-base/composer.json`, the same file the corpus build reads.
  - Mage-OS installs `mage-os/magento2-base` with its own version numbers, which do not map to Magento's by rule. On Mage-OS, and in a core checkout without `magento2-base`, upgrade rules run only with `--target-version`, and one notice says so.
- **Target.** A rule runs when it applies to the installed version or to `--target-version`, each judged on its own release line. A target below the installed version therefore hides nothing the project already has, and an installed `2.4.7-p8` keeps the rules its own patch line lists when the target is `2.4.8`.
- **Comparing.** Versions are compared as `x.y.z` with an optional `-pN`. A pre-release suffix (`-beta1`, `-rc2`) is dropped before the comparison, so a 2.4.9 RC counts as 2.4.9.
- **Patch lines.** A change can ship in a minor release and in patch releases of older lines, as APSB25-88 did. So a rule lists one first version per release line, for example `2.4.9`, `2.4.8-p3` and `2.4.7-p8`. The rule applies to a version when that version is at or above the first version listed for its line, or when its line is newer than every line listed.
- **Status.** Version gating applies on top of a rule's status. An experimental upgrade rule runs only when it is enabled, like any other rule. U-CLI-01 starts as experimental until calibration.
- An invalid `--target-version` exits with code 2.

## D3. Candidates, in build order

| # | Change | Version | Source | Detection | Notes |
|---|---|---|---|---|---|
| 1 | Symfony Console 7.4: `Command::execute()` declares `: int`. An override without it is a fatal error when the class loads. | 2.4.9 | A: [backward-incompatible changes, "Symfony 7.4 LTS support"](https://developer.adobe.com/commerce/php/development/backward-incompatible-changes/): "Custom classes that extend Symfony core classes must have updated type declarations and method signatures aligned with Symfony 7.4". B: `composer.json` requires `symfony/console ^7.4` at 2.4.9 and `^6.4` at 2.4.8; [Command.php#L240 of v7.4.14](https://github.com/symfony/console/blob/92f58bc4bf97a92ed1b9f367f0cd44f20bde0e87/Command/Command.php#L240) | A subclass of `Symfony\Component\Console\Command\Command` overrides `execute()` without a native `int` return type | U-CLI-01 in this change. The core fixed 86 of its own commands for this in PR #10274 |
| 2 | `@api` `Magento\Framework\MessageQueue\QueueInterface` gained `subscribeQueue()` and `clearQueue()` | 2.4.9 | B: [QueueInterface.php#L68-L75](https://github.com/magento/magento2/blob/755e34dd689021c5165db9d35ecff74f7dc51527/lib/internal/Magento/Framework/MessageQueue/QueueInterface.php#L68-L75) | A class that implements it lacks either method | Not in Adobe's generated change list |
| 3 | Monolog 3: handlers, processors and formatters take `LogRecord`, not `array` | 2.4.8 | A: same page, "Upgraded monolog/monolog dependency". B: `Logger/Handler/Base.php` at the 2.4.7 and 2.4.8 tags | An override of `write`, `handle`, `isHandling`, `__invoke` or `format` with an `array $record` parameter | Leave out classes declared under a version check, such as `if (Logger::API < 3)` |
| 4 | MySQL 8.4 `restrict_fk_on_non_standard_key`: a foreign key must reference a whole primary or unique key | 2.4.8 | A: same page, "MySQL 8.4 foreign key restrictions" | `db_schema.xml` foreign constraints, checked against the merged schema | XML. Corpus hits not counted yet |
| 5 | Zend_Cache replaced by symfony/cache: `Cache\Core` and `Cache\Frontend\Adapter\Zend` are no longer used | 2.4.9 | A: same page, "Zend_Cache replaced with symfony/cache". B: `@deprecated` on both classes at 2.4.9 | A `di.xml` plugin, preference or virtual type on either class | The break is silent: the plugin stops running |
| 6 | Removed libraries: `laminas/laminas-mail`, `-mime`, `-oauth` and `-file` (2.4.8); `laminas/laminas-mvc` and `magento/magento-zf-db` (2.4.9) | 2.4.8, 2.4.9 | A: 2.4.8 release notes. B: `composer.json` at the tags | Use of their classes, not imports, when the module does not require the package itself | Version-guarded code must be left out (one corpus module uses Laminas Mail only below 2.4.8) |
| 7 | GraphQL types and queries removed in 2.4.9 (`CustomerGroup`, `CartRule`, `CatalogRule` and their queries) | 2.4.9 | A: the 2.4.8 to 2.4.9 reference. B: `schema.graphqls` at both tags | An extension `.graphqls` that extends or references them | Few hits: these types existed only in 2.4.7 and 2.4.8 |
| 8 | Module `Magento_Elasticsearch7` removed | 2.4.8 | A: the 2.4.7 to 2.4.8 reference | A `<sequence>`, `di.xml` or PHP reference to it | |

Rejected:
- GraphQL alias and query length limits: client side and configurable.
- Field type changes that only a resolver's return value could break.
- Protected methods removed from a class whose parent still has them: the call still resolves.
- Configuration-only changes: admin password length, 2FA, collation.
- The security bulletins of 2025 and 2026 carry no developer guidance beyond APSB25-88, which P-API-01 covers.

## D4. A generated symbol diff, later

A data file per pair of versions could drive one rule. It would list removed classes, methods and constants, interfaces that gained methods, and native signature changes. That is what UCT and the plugin index do. Adobe's generated reference is not usable as it is:
- about 60 XSD files it calls removed still exist at 2.4.9;
- most of its return-type changes are docblock-only;
- it misses `QueueInterface`.

A diff would have to come from the tags themselves and keep only native facts.

## D5. False positives to expect

Code declared or used under a version check is the main source: a class declared twice under `if (Logger::API < 3)`, or Laminas Mail used only when `versionCompare('2.4.8')` says older. Rules that look at a declaration must skip a class declared inside a conditional. Rules that look at uses must allow suppression.
