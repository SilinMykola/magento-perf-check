# U-CLI-02: console command whose `execute()` can return something other than an int

| | |
|---|---|
| Severity | `error` (`magentoPerf.consoleCommandExecuteResult`) |
| Status | default set, after [calibration round 17](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-17.md): 21 findings on the corpus and private stores, all correct |
| Analyzer | PHPStan |
| Upgrade rule | for Magento 2.4.4-p12, 2.4.5-p11 and 2.4.6: runs when the installed version or `--target-version` is one of those or newer ([configuration](../configuration.md#upgrade-rules)) |
| Protects | compatibility ([ADR 8](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0008-scope-beyond-performance.md)) |

## What it catches

A class that extends `Symfony\Component\Console\Command\Command` and declares `execute()` without the native return type `int` (or `never`), when that `execute()` can give something other than an int:
- it can end without a `return`, for example after its last statement, or with a `void` return type;
- it has a `return;` without a value;
- it returns a value whose type is never an int, such as `true` or a string;
- it contains `yield`, so it returns a `Generator`.

A returned value that may be an int, such as the result of a method without a return type, is not reported.

On Mage-OS and in a checkout of the Magento core, the installed version cannot be read, so the rule runs only with `--target-version`, and without it the rule is skipped without a notice ([configuration](../configuration.md#upgrade-rules)). Neither is an `execute(...): int` that can end without a `return`: PHP fails it on every version, so it is not an upgrade break, and PHPStan reports it as a missing return. Neither are a named class declared in an `if`, `elseif` or `else` branch and a command in a Composer plugin package, as for [U-CLI-01](U-CLI-01.md).

## Why it breaks

Magento moved from Symfony Console 4.4 to 5.4 in 2.4.6, and in the patch releases 2.4.4-p12 and 2.4.5-p11. Symfony Console 4.4 only logged a deprecation when `execute()` returned something other than an int. 5.4 throws:

```
TypeError: Return value of "Vendor\Module\Console\Command\Sync::execute()" must be of the type int, "null" returned.
```

So the command fails whenever it takes such a path, which for most findings is every time it runs. Unlike [U-CLI-01](U-CLI-01.md), which is about 2.4.9, the class still loads, so other commands keep working.

## Example

Bad:

```php
class Sync extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->synchronizer->run();
    }
}
```

Good:

```php
class Sync extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->synchronizer->run();

        return Cli::RETURN_SUCCESS;
    }
}
```

`Cli` is `Magento\Framework\Console\Cli`. `Command::SUCCESS` exists only from Symfony Console 5.1, so on a 2.4.4 or 2.4.5 store still on 4.4 use `Cli::RETURN_SUCCESS` or `0`. The fix also satisfies U-CLI-01.

## How to suppress

```php
// @phpstan-ignore magentoPerf.consoleCommandExecuteResult (reason)
```

## Known false positives

- A path that ends without a `return` but never runs, such as the end of a loop that only exits by `return` or by an exception PHPStan cannot see.
- A command that is registered nowhere never runs, so it breaks nothing until it is registered.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Backward incompatible changes, 2.4.6, Symfony dependencies upgraded to latest LTS version](https://developer.adobe.com/commerce/php/development/backward-incompatible-changes/) ([source L620-L628](https://github.com/AdobeDocs/commerce-php/blob/c926b34df397aea703764dad1f275c70c6fa4f29/src/pages/development/backward-incompatible-changes/index.md?plain=1#L620-L628)) | A | Given for core's `AbstractCacheTypeManageCommand` as an example: a command extending `Symfony\Component\Console\Command\Command` "must return the `int` type"; "check the return type for the `execute` method to avoid errors when executing command-line commands". |
| [composer.json at 2.4.6#L85](https://github.com/magento/magento2/blob/d846142a3ab8b49597dfb8bd7508d875efdab19a/composer.json#L85), [2.4.5-p11#L76](https://github.com/magento/magento2/blob/ef1ad91d13cb1a5f8f471d084354dbdd8fe1f5ba/composer.json#L76), [2.4.4-p12#L81](https://github.com/magento/magento2/blob/52b27a0277f0be82a87c3d6b12d83d1ce409ea3f/composer.json#L81) | B | Magento requires `symfony/console` 5.4 from these versions; 2.4.5-p10 and 2.4.4-p11 still require `~4.4.0`. |
| [symfony/console v5.4.47 Command.php#L298-L302](https://github.com/symfony/console/blob/c4ba980ca61a9eb18ee6bcc73f28e475852bb1ed/Command/Command.php#L298-L302) | B | `Command::run()` throws a `TypeError` when `execute()` returns a non-int. |
| [Cli.php#L49](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Console/Cli.php#L49), [at 2.4.5-p11#L45](https://github.com/magento/magento2/blob/ef1ad91d13cb1a5f8f471d084354dbdd8fe1f5ba/lib/internal/Magento/Framework/Console/Cli.php#L45), [at 2.4.4-p12#L44](https://github.com/magento/magento2/blob/52b27a0277f0be82a87c3d6b12d83d1ce409ea3f/lib/internal/Magento/Framework/Console/Cli.php#L44) | B | `Cli::RETURN_SUCCESS` is `0`, in the 2.4.4 and 2.4.5 lines too. |
| [symfony/console v4.4.42 Command.php#L255-L259](https://github.com/symfony/console/blob/cce7a9f99e22937a71a16b23afa762558808d587/Command/Command.php#L255-L259) | B | Symfony Console 4.4 only triggered a deprecation for the same case. |

Verified 2026-11-13.
