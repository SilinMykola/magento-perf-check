# U-LOG-01: `addError()`, `addInfo()` and the other Monolog 1 methods

| | |
|---|---|
| Severity | `error` (`magentoPerf.monologAddMethod`) |
| Status | default set, after [calibration round 19](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-19.md): 38 findings on private stores, all correct |
| Analyzer | PHPStan |
| Upgrade rule | for Magento 2.4.4: runs when the installed version or `--target-version` is 2.4.4 or newer ([configuration](../configuration.md#upgrade-rules)) |
| Protects | compatibility ([ADR 8](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0008-scope-beyond-performance.md)) |

## What it catches

A call of `addDebug()`, `addInfo()`, `addNotice()`, `addWarning()`, `addError()`, `addCritical()`, `addAlert()` or `addEmergency()`, or of `warn()`, `err()`, `crit()` or `emerg()`, on an object whose type is `Monolog\Logger` or a class that extends it, such as Magento's `Magento\Framework\Logger\Monolog` or a module's own logger, a nullable one included. Inside such a logger class, `parent::addError()`, `self::addError()` and `static::addError()` count too.

Not reported:
- a logger class that declares the method itself: it keeps it after the upgrade;
- the PSR-3 methods `debug()`, `info()`, `error()` and the others;
- the same names on anything that is not a Monolog logger, such as `addError()` of the message manager;
- a call on `Psr\Log\LoggerInterface`: Magento resolves the interface to `Magento\Framework\Logger\LoggerProxy` from 2.4.2 ([app/etc/di.xml at 2.4.2#L10](https://github.com/magento/magento2/blob/33242e4b19cf207d7b73f7791ef894b48bb41f8a/app/etc/di.xml#L10); [at 2.4.1](https://github.com/magento/magento2/blob/445b0f1a3d6a91b5da32d311077c2112ef0b1503/app/etc/di.xml#L10) it was `Monolog`), which never had these methods, so such a call fails from 2.4.2, an earlier change;
- a logger whose type PHPStan cannot tell, such as an untyped property without a `@var`;
- `Logger::addError()` on a class name: a static call of an instance method, which PHPStan reports itself;
- `$class::addError()` with a class in a variable.

On Mage-OS and in a checkout of the Magento core, the installed version cannot be read, so the rule runs only with `--target-version`, and without it the rule is skipped without a notice ([configuration](../configuration.md#upgrade-rules)).

## Why it breaks

Magento 2.4.4 moved from Monolog 1 to Monolog 2. Monolog 2 removed every `add*` method and the aliases `warn()`, `err()`, `crit()` and `emerg()`, so from 2.4.4 such a call throws:

```
Error: Call to undefined method Vendor\Module\Logger\Logger::addError()
```

Most such calls are in a `catch` block or on a failure path: the error that was meant to be logged becomes a fatal `Error`, which the caller usually does not catch. A call on the normal path, such as `addInfo()` after each order, fails every time.

## Example

Bad:

```php
try {
    $this->client->send($order);
} catch (\Exception $e) {
    $this->logger->addError($e->getMessage());
}
```

Good:

```php
try {
    $this->client->send($order);
} catch (\Exception $e) {
    $this->logger->error($e->getMessage());
}
```

The PSR-3 methods take the same arguments and also exist in Monolog 1, so the fix works before 2.4.4 too.

## How to suppress

```php
// @phpstan-ignore magentoPerf.monologAddMethod (reason)
```

## Known false positives

- A logger class that adds the method back through `__call()`.
- A receiver declared as `Monolog\Logger` or `Magento\Framework\Logger\Monolog` that at runtime is a subclass declaring the method: the rule goes by the declared type. The same holds for `static::` in a logger class whose subclass declares the method.

None was seen in round 19.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [magento2 composer.json at 2.4.4#L74](https://github.com/magento/magento2/blob/0f9a056c8d83c4f319626b3e56ec52a533999f25/composer.json#L74), [at 2.4.3#L70](https://github.com/magento/magento2/blob/4c36116dcf878e127059d9be9566a119783583f2/composer.json#L70) | B | Magento 2.4.4 requires `monolog/monolog` `^2.3`; 2.4.3 and its patch releases up to 2.4.3-p3 require `^1.17`. |
| [Monolog 2.0 UPGRADE.md#L6-L7](https://github.com/Seldaek/monolog/blob/68545165e19249013afd1d6f7485aecff07a2d22/UPGRADE.md#L6-L7) | C | "Removed non-PSR-3 methods to add records, all the `add*` (e.g. `addWarning`) methods as well as `emerg`, `crit`, `err` and `warn`." |
| [Monolog 1.27.1 Logger.php#L401-L495](https://github.com/Seldaek/monolog/blob/904713c5929655dc9b97288b69cfeedad610c9a1/src/Monolog/Logger.php#L401-L495), [2.3.0 Logger.php](https://github.com/Seldaek/monolog/blob/df991fd88693ab703aa403413d83e15f688dae33/src/Monolog/Logger.php) | C | Monolog 1 declares `addDebug()` to `addEmergency()`; Monolog 2.3.0 has none of them. |

Adobe's [backward-incompatible changes for 2.4.4](https://developer.adobe.com/commerce/php/development/backward-incompatible-changes/) do not mention Monolog (checked 2026-11-13). Verified 2026-11-13.
