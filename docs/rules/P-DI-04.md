# P-DI-04: console command in CommandList with dependencies that are not lazy

| | |
|---|---|
| Severity | `info` |
| Status | experimental, off by default |
| Analyzer | PHPStan and the project's `di.xml` files, identifier `magentoPerf.commandWithoutProxy` |

## What it catches

A class registered in the `commands` argument of `Magento\Framework\Console\CommandList` or `CommandListInterface` in a global `etc/di.xml`, when its constructor, its own or inherited, has required parameters typed with a class. One command gives one finding, which names the parameters. It is reported on the constructor, or on the class when the constructor is inherited.

These parameters are left out:

- factories, and classes type-hinted as a `\Proxy`;
- optional parameters, for which the object manager passes the default;
- arguments configured with a `\Proxy` in `di.xml`, in any area;
- types that commands of core take without a Proxy, so that every run creates them anyway: `App\State`, `App\Cache\Manager`, `ScopeConfigInterface`, `ObjectManagerInterface`, `StoreManagerInterface` and `Psr\Log\LoggerInterface`;
- `Filesystem`, which among the commands in `CommandList` only those of sample data take, because its constructor just stores three cheap arguments.

## Why it hurts performance

On an installed store, every `bin/magento` run creates `CommandListInterface`, and so all the objects in its `commands` argument, whichever command was asked for. Each command's dependencies are created with it, and their dependencies in turn. The rule does not know which constructors are costly: no source measures this for a given class, so the severity is `info`. Of 25 graded findings on 56 modules, one grader rated 15 as naming only dependencies that are cheap to create.

## Example

Bad:

```xml
<type name="Magento\Framework\Console\CommandListInterface">
    <arguments>
        <argument name="commands" xsi:type="array">
            <item name="acme_sync" xsi:type="object">Acme\Sync\Console\Sync</item>
        </argument>
    </arguments>
</type>
```

Good, with the same list:

```xml
<type name="Acme\Sync\Console\Sync">
    <arguments>
        <argument name="api" xsi:type="object">Acme\Sync\Model\Api\Proxy</argument>
    </arguments>
</type>
```

## How to suppress

```php
// @phpstan-ignore magentoPerf.commandWithoutProxy (reason)
```

## Known false positives

- A dependency that is cheap to create. The rule cannot tell, so a finding means "check these", not "these are slow".
- A dependency that another command of core or of the same project creates anyway, other than the types listed above.

A `\Proxy` in any area counts, so one configured only in `etc/adminhtml/di.xml` hides a finding, although it does not apply to `bin/magento`. A command registered through a `CommandLoader`, which creates commands only when asked, is not reported.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Proxies](https://developer.adobe.com/commerce/php/development/components/proxies) | A | Constructor injection instantiates a chain of objects, which costs when a resource-intensive one is not needed. The real instance behind a proxy is created only when one of its methods is called. The page does not mention console commands. |
| [Cli.php#L196-L232](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Console/Cli.php#L196-L232) | B | Every run on an installed store creates `CommandListInterface`. That its `commands` argument creates each command follows from how the object manager resolves `xsi:type="object"`. |

Verified 2026-11-07.
