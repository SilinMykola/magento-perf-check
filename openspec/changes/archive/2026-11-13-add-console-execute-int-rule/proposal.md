# Proposal

## Why

U-CLI-01 covers the Magento 2.4.9 break: Symfony Console 7.4 declares `execute(...): int`, so a command without that return type cannot be loaded. An earlier break of the same method is not covered. Magento moved from Symfony Console 4.4 to 5.4 in 2.4.6, and in the patch releases 2.4.4-p12 and 2.4.5-p11. Symfony 4.4 only logs a deprecation when `execute()` returns something other than an int; 5.4 throws a `TypeError`, so such a command fails whenever it takes that path.

| Source | Level | What it says |
|---|---|---|
| [Adobe: Backward incompatible changes, 2.4.6](https://developer.adobe.com/commerce/php/development/backward-incompatible-changes/) ("Symfony dependencies upgraded to latest LTS version"; [source L620-L628](https://github.com/AdobeDocs/commerce-php/blob/c926b34df397aea703764dad1f275c70c6fa4f29/src/pages/development/backward-incompatible-changes/index.md?plain=1#L620-L628)) | A | A command extending `Symfony\Component\Console\Command\Command` "must return the `int` type"; "check the return type for the `execute` method to avoid errors when executing command-line commands". |
| [magento2 composer.json at 2.4.6#L85](https://github.com/magento/magento2/blob/d846142a3ab8b49597dfb8bd7508d875efdab19a/composer.json#L85), [2.4.5-p11#L76](https://github.com/magento/magento2/blob/ef1ad91d13cb1a5f8f471d084354dbdd8fe1f5ba/composer.json#L76), [2.4.4-p12#L81](https://github.com/magento/magento2/blob/52b27a0277f0be82a87c3d6b12d83d1ce409ea3f/composer.json#L81) | B | Magento requires symfony/console 5.4 from these versions; 2.4.5-p10 and 2.4.4-p11 still require ~4.4.0. |
| [symfony/console v5.4.47 Command.php#L298-L302](https://github.com/symfony/console/blob/c4ba980ca61a9eb18ee6bcc73f28e475852bb1ed/Command/Command.php#L298-L302), [v4.4.42#L255-L259](https://github.com/symfony/console/blob/cce7a9f99e22937a71a16b23afa762558808d587/Command/Command.php#L255-L259) | B | 5.4 throws a `TypeError` when `execute()` returns a non-int; 4.4 triggers a deprecation. |

## What Changes

- U-CLI-02, at `error`, an upgrade rule for 2.4.4-p12, 2.4.5-p11 and 2.4.6: a console command whose `execute()` has no native `int` (or `never`) return type and can return something other than an int: it can end without a `return`, has a `return;`, returns a value that is never an int, or yields. A value that may be an int is not reported.
- It leaves out what U-CLI-01 leaves out: a named class in an `if` branch, and Composer plugins.
- An `upgrade-version` notice names only upgrade rules that the configuration enables: a rule of the default set that does not run, on an older version or where the version cannot be read, is skipped without one, so that a default run does not get a notice on every project.
- Measured on 2026-11-13 with `--target-version=2.4.6`: the core gives no finding, the calibration corpus 5, five private stores 16; every one is a command registered in `di.xml` that ends without returning an int, so all 21 are correct and matter. With that, round 17 puts the rule in the default set; on a project already on 2.4.6 or later it reports commands that fail today.

**This can change a CI result:** on a project on 2.4.4-p12, 2.4.5-p11, 2.4.6 or later, or with such a `--target-version`, an affected command gives an `error` finding.
