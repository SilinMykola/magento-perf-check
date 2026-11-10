## ADDED Requirements

### Requirement: Upgrade rules run by version
Every upgrade rule SHALL list, per release line, the first Magento version that has the change it checks. A rule applies to a version when that version is at or above the version listed for its line, or when its line is newer than every line listed. The rule SHALL run when it applies to the installed version or to the value of `--target-version`.

#### Scenario: Planning an upgrade
- **WHEN** the installed version is 2.4.8 and the command runs with `--target-version=2.4.9`
- **THEN** the rules for 2.4.9 run

#### Scenario: Already upgraded
- **WHEN** the installed version is 2.4.9 and no target is given
- **THEN** the rules for 2.4.9 run, and so do the rules for 2.4.8

#### Scenario: Older target
- **WHEN** the installed version is 2.4.7 and the command runs with `--target-version=2.4.8`
- **THEN** the rules for 2.4.9 do not run

#### Scenario: Target below the installed version
- **WHEN** the installed version is 2.4.9 and the command runs with `--target-version=2.4.8`
- **THEN** the rules for 2.4.9 run

#### Scenario: Installed patch already has the change
- **WHEN** the installed version is 2.4.7-p8, the command runs with `--target-version=2.4.8`, and a rule lists `2.4.8-p3` and `2.4.7-p8`
- **THEN** the rule runs, because the installed version already has the change

#### Scenario: Patch release of an older line
- **WHEN** a rule lists `2.4.9` and `2.4.8-p3`, and the target is 2.4.8-p4
- **THEN** the rule runs

### Requirement: Version comparison
Versions SHALL be compared as `x.y.z` with an optional `-pN` patch number; a pre-release suffix SHALL be dropped before the comparison.

#### Scenario: Release candidate
- **WHEN** the installed version is `2.4.9-rc2`
- **THEN** it counts as 2.4.9

### Requirement: Target version input
An invalid `--target-version` SHALL end the command with exit code 2. When the project has no `vendor/magento/magento2-base/composer.json`, as on Mage-OS or in a core checkout, and no `--target-version` is given, upgrade rules SHALL NOT run, and one notice SHALL say so. Upgrade rules SHALL follow their status: an experimental one runs only when enabled.

#### Scenario: Invalid target
- **WHEN** the command runs with `--target-version=latest`
- **THEN** it exits with code 2 and names the option

#### Scenario: Mage-OS without a target
- **WHEN** the project has `vendor/mage-os/magento2-base` and no `vendor/magento/magento2-base`, and no target is given
- **THEN** no upgrade rule runs and the report has one notice about it

### Requirement: U-CLI-01 console command without the Symfony 7.4 return type
U-CLI-01 SHALL report a class that extends `Symfony\Component\Console\Command\Command` and declares `execute()` without a native `int` return type. Its version SHALL be 2.4.9, and it SHALL start as experimental. It SHALL NOT report a class declared inside a conditional statement.

#### Scenario: Missing return type
- **WHEN** a command class declares `protected function execute(InputInterface $input, OutputInterface $output)`
- **THEN** U-CLI-01 reports it at the method

#### Scenario: Declared under a version check
- **WHEN** the same class is declared inside an `if` block
- **THEN** U-CLI-01 reports nothing
