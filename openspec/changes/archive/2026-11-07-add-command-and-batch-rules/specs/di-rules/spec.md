## ADDED Requirements

### Requirement: P-DI-04 console command with eager dependencies
P-DI-04 SHALL report, as `info`, the constructor of a class registered in the `commands` argument of `CommandList` or `CommandListInterface` in a global `di.xml`, when it has required parameters typed with a class that is not a factory, not a `\Proxy`, not one that commands of core already create on every run, and not configured with a `\Proxy` for that argument. One command SHALL give one finding, which names those parameters. It is off by default.

#### Scenario: Command with a repository
- **WHEN** a command registered in `CommandListInterface` takes `ProductRepositoryInterface $products`
- **THEN** P-DI-04 reports its constructor and names `$products`

#### Scenario: Proxy in di.xml
- **WHEN** `di.xml` configures a `\Proxy` for each class parameter of the command
- **THEN** P-DI-04 reports nothing

#### Scenario: Created anyway
- **WHEN** the command takes only `Magento\Framework\App\State` and `StoreManagerInterface`
- **THEN** P-DI-04 reports nothing

#### Scenario: Optional parameter
- **WHEN** the command takes `?Api $api = null`
- **THEN** P-DI-04 reports nothing, because the object manager passes the default

#### Scenario: Only factories
- **WHEN** the command takes only `*Factory` parameters and scalars
- **THEN** P-DI-04 reports nothing

#### Scenario: Not a registered command
- **WHEN** a class extends the console `Command` but is not in `CommandList`
- **THEN** P-DI-04 reports nothing
