# X-SETUP-01: heavy work in recurring setup scripts

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | PHPStan, identifier `magentoPerf.heavyRecurringSetup` |

## What it catches

In the `install()` method of a class named `Recurring` or `RecurringData` in a `Setup` namespace, any of:
- a loop;
- a call to `load()`, `getItems()`, `getSize()` or `save()`;
- `reindexAll()`, `reindexList()` or `reindexRow()`.

One finding per method points at the first such statement.

## Why it hurts performance

`Recurring` and `RecurringData` run on every `setup:upgrade`, for every module, whether or not the module changed. Heavy work there slows every deployment, and on large catalogs it can add minutes of downtime. Work that has to happen once belongs in a data or schema patch, which runs once and is recorded.

## Example

Bad:

```php
class RecurringData implements InstallDataInterface
{
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->indexerRegistry->get('catalogsearch_fulltext')->reindexAll();
    }
}
```

Good: a data patch (`Setup/Patch/Data/...` implementing `DataPatchInterface`), or nothing at all if the indexer cron handles it.

## How to suppress

```php
// @phpstan-ignore magentoPerf.heavyRecurringSetup (checks a handful of config rows only)
```

## Known false positives

- Small loops over a fixed handful of items, such as store views, cost little.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Extension lifecycle](https://developer.adobe.com/commerce/php/development/prepare/extension-lifecycle) | A | The `Recurring` class runs after every schema installation or upgrade stage, and `RecurringData` after every data stage. |
| [Installer.php#L1159-L1174](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/setup/src/Magento/Setup/Model/Installer.php#L1159-L1174) | B | The installer runs the recurring script of every module. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
