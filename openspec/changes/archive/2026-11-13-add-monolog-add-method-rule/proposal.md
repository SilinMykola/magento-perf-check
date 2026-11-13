# Proposal

## Why

Magento 2.4.4 moved from Monolog 1 to Monolog 2, which removed the non-PSR-3 methods `addDebug()` to `addEmergency()` and the aliases `warn()`, `err()`, `crit()` and `emerg()`. Code that calls them on a Monolog logger, usually a module's own logger class, works up to 2.4.3-p3 and throws "Call to undefined method" from 2.4.4. On a project still on 2.4.3, neither PHPStan nor magento-coding-standard can see it, since the vendor still has Monolog 1. Adobe's backward-incompatible changes for 2.4.4 do not mention it (checked 2026-11-13).

| Source | Level | What it says |
|---|---|---|
| [magento2 composer.json at 2.4.4#L74](https://github.com/magento/magento2/blob/0f9a056c8d83c4f319626b3e56ec52a533999f25/composer.json#L74), [at 2.4.3#L70](https://github.com/magento/magento2/blob/4c36116dcf878e127059d9be9566a119783583f2/composer.json#L70) | B | 2.4.4 requires `monolog/monolog` `^2.3`; 2.4.3 to 2.4.3-p3 require `^1.17`. |
| [Monolog 2.0 UPGRADE.md#L6-L7](https://github.com/Seldaek/monolog/blob/68545165e19249013afd1d6f7485aecff07a2d22/UPGRADE.md#L6-L7) | C | The `add*` methods, `emerg`, `crit`, `err` and `warn` were removed. |

## What Changes

- U-LOG-01, at `error`, an upgrade rule for 2.4.4: such a call on a receiver of type `Monolog\Logger` or a subclass, when the method there is Monolog's own or missing. A logger that declares the method itself, the PSR-3 methods, and other classes with the same method names, such as the message manager, are left out. A call on `Psr\Log\LoggerInterface` is left out too: it fails from 2.4.2, where Magento resolves the interface to `LoggerProxy`, which is another change without data.
- Measured on 2026-11-13 with `--target-version=2.4.4`: the core, the 25 modules and the extended part give no finding; one payment module of the extended part calls these methods about 500 times on its own logger, which declares them. Five private stores give 38 in four stores, 23 TP and 15 TP-low; 31 come from one store. A sample of 30 gives an impact share of 50 %. With that, round 19 puts the rule in the default set.

**This can change a CI result:** on a project on 2.4.4 or later, or with such a `--target-version`, each call gives an `error` finding.
