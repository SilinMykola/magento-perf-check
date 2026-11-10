# U-CLI-01: console command whose `execute()` has no `int` return type

| | |
|---|---|
| Severity | `error` (`magentoPerf.consoleCommandReturnType`) |
| Status | experimental, off by default: [calibration round 7](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-7.md) found 9 findings, all correct (fewer than 10: insufficient data). Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | PHPStan |
| Upgrade rule | for Magento 2.4.9: runs when the installed version or `--target-version` is 2.4.9 or newer ([configuration](../configuration.md#upgrade-rules)) |
| Protects | compatibility ([ADR 8](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0008-scope-beyond-performance.md)) |

## What it catches

A class that extends `Symfony\Component\Console\Command\Command` and declares `execute()`, itself or through a trait, without the native return type `int`: with no return type, or with another one such as `void` or `?int`. A return type of `never` is accepted. A finding from a trait is reported at the class.

The rule does not report:
- a named class declared in an `if`, `elseif` or `else` branch: it is usually one variant behind a version check, next to a compatible one. An anonymous class there is reported, since it breaks when that line runs. A class in a `switch` or a `try` is reported;
- a command in a Composer plugin package, such as the `DeployCommand` of `magento/magento-composer-installer`: `bin/magento` does not load it, and that one is not registered as a command at all. A plugin that adds a command to Composer through a `CommandProvider` is missed, although it breaks too when Composer runs from the project's `vendor/bin` on Symfony Console 7.4;
- a command that inherits `execute()` without declaring its own: the parent is reported.

## Why it breaks

Magento 2.4.9 requires Symfony Console 7.4, whose `Command::execute()` declares `: int`. PHP refuses to load a subclass whose `execute()` is not compatible:

```
PHP Fatal error: Declaration of Vendor\Module\Console\Command\Sync::execute(...) must be compatible with Symfony\Component\Console\Command\Command::execute(...): int
```

This is a fatal error, not an exception, so nothing catches it. Most modules register a command in the `commands` argument of `Magento\Framework\Console\CommandListInterface` in `di.xml`. `bin/magento` creates all of those when it builds its command list, so on an installed 2.4.9 store one such command makes every `bin/magento` call fail, not only its own. A command added to `Magento\Framework\Console\CommandLoader` instead is created only when it runs, or when the commands are listed. That follows from the code linked below; it was not run on a store.

Seen on 2026-11-10 in the calibration corpus: loading `Snowdog\Menu\Console\Command\NodesValidatorCommand` on 2.4.9 gives the fatal error above.

## Example

Bad:

```php
class Sync extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
        return 0;
    }
}
```

Good:

```php
class Sync extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        return Command::SUCCESS;
    }
}
```

`: int` is also valid on Symfony Console 6.4, so the fix works on 2.4.8 and older too.

## How to suppress

```php
// @phpstan-ignore magentoPerf.consoleCommandReturnType (reason)
```

## Known false positives

None known: the PHP check is exact. A command that is registered nowhere, or only in `CommandLoader`, breaks less: only when it is loaded.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Backward incompatible changes, 2.4.9, Symfony 7.4 LTS support](https://developer.adobe.com/commerce/php/development/backward-incompatible-changes/) | A | "Custom classes that extend Symfony core classes must have updated type declarations and method signatures aligned with Symfony 7.4". |
| [composer.json#L89](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/composer.json#L89) | B | Magento requires `symfony/console` `^7.4`. |
| [symfony/console Command.php#L240](https://github.com/symfony/console/blob/92f58bc4bf97a92ed1b9f367f0cd44f20bde0e87/Command/Command.php#L240) | B | `Command::execute()` declares `: int` in Symfony Console 7.4. |
| [Cli.php#L196-L222](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Console/Cli.php#L196-L222) | B | `bin/magento` creates every command of `CommandListInterface` when it builds its default command list. |
| [Cli.php#L125](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Console/Cli.php#L125), [#L391-L397](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Console/Cli.php#L391-L397) | B | Commands of `CommandLoader` are created only when one of them is asked for. |

Verified 2026-11-10.
