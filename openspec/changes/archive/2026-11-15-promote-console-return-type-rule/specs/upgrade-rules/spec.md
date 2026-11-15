## MODIFIED Requirements

### Requirement: U-CLI-01 console command without the Symfony 7.4 return type
U-CLI-01 SHALL report a class that extends `Symfony\Component\Console\Command\Command` and declares `execute()` without a native `int` return type. Its version SHALL be 2.4.9, and it SHALL be in the default set. It SHALL also report such an `execute()` that comes from a trait, at the class. It SHALL NOT report a named class declared in an `if`, `elseif` or `else` branch, nor a class in a Composer plugin package, which `bin/magento` does not load.

#### Scenario: Missing return type
- **WHEN** a command class declares `protected function execute(InputInterface $input, OutputInterface $output)`
- **THEN** U-CLI-01 reports it at the method

#### Scenario: Declared under a version check
- **WHEN** the same class is declared inside an `if` block
- **THEN** U-CLI-01 reports nothing

#### Scenario: Command of a Composer plugin
- **WHEN** a command without the return type is in a package of type `composer-plugin`
- **THEN** U-CLI-01 reports nothing

#### Scenario: Default run on 2.4.9
- **WHEN** a project without a configuration file has such a command and is run with `--target-version=2.4.9`
- **THEN** U-CLI-01 reports it
