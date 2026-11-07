## ADDED Requirements

### Requirement: Console commands and cron jobs
The index SHALL know the classes in the `commands` argument of `CommandList` or `CommandListInterface` in a global `di.xml`, and the `instance` classes of jobs in `crontab.xml`. A virtual type SHALL count as the class it is based on.

#### Scenario: Command
- **WHEN** `etc/di.xml` adds `Acme\Promo\Console\Sync` to the `commands` of `CommandListInterface`
- **THEN** the index reports that class as a console command

#### Scenario: Area di.xml
- **WHEN** only `etc/adminhtml/di.xml` adds the command
- **THEN** the index does not report it

#### Scenario: Cron job
- **WHEN** `etc/crontab.xml` has a job with `instance="Acme\Promo\Cron\Sync"`
- **THEN** the index reports that class as a cron job
