## MODIFIED Requirements

### Requirement: Target version input
An invalid `--target-version` SHALL end the command with exit code 2. When the Magento version cannot be read from `vendor/magento/magento2-base/composer.json`, as on Mage-OS or in a core checkout, and no `--target-version` is given, upgrade rules SHALL NOT run, and while the configuration enables one of them one notice SHALL say so. An upgrade rule that the configuration enables and that does not run because neither version has its change SHALL be named in a notice too. An upgrade rule of the default set that does not run SHALL be skipped without a notice. Upgrade rules SHALL follow their status: an experimental one runs only when enabled.

#### Scenario: Invalid target
- **WHEN** the command runs with `--target-version=latest`
- **THEN** it exits with code 2 and names the option

#### Scenario: Mage-OS without a target
- **WHEN** the project has `vendor/mage-os/magento2-base` and no `vendor/magento/magento2-base`, and no target is given
- **THEN** no upgrade rule runs, and the report has one notice about it when the configuration enables an upgrade rule

#### Scenario: Default-set upgrade rule that does not run
- **WHEN** the project runs Magento 2.4.5-p10, no configuration enables an upgrade rule, and no target is given
- **THEN** U-CLI-02 does not run and the report has no `upgrade-version` notice

## ADDED Requirements

### Requirement: U-CLI-02 console command whose execute() can return a non-int
U-CLI-02 SHALL report a class that extends `Symfony\Component\Console\Command\Command` and declares `execute()` without a native `int` or `never` return type, when that `execute()` can end without a `return`, has a `return` without a value, returns a value whose type is never an int, or contains `yield`. A returned value that may be an int SHALL NOT be reported.

#### Scenario: No return at all
- **WHEN** a command's `execute()` has no return type and its body ends without a `return`
- **THEN** U-CLI-02 reports it

#### Scenario: Returns a boolean
- **WHEN** a command's `execute()` has no return type and returns `true`
- **THEN** U-CLI-02 reports it

#### Scenario: Returns an int without a return type
- **WHEN** a command's `execute()` has no return type and every path returns `0` or `Cli::RETURN_SUCCESS`
- **THEN** U-CLI-02 reports nothing, and U-CLI-01 still does for 2.4.9

#### Scenario: Returns a value that may be an int
- **WHEN** a command's `execute()` returns the result of a method whose return type is not declared
- **THEN** U-CLI-02 reports nothing

#### Scenario: Ends by throwing
- **WHEN** every path of a command's `execute()` returns an int or throws
- **THEN** U-CLI-02 reports nothing

### Requirement: U-CLI-02 versions and scope
U-CLI-02 SHALL have the versions 2.4.4-p12, 2.4.5-p11 and 2.4.6, and SHALL be in the default set, at `error`, after its calibration round. It SHALL NOT report a named class declared in an `if`, `elseif` or `else` branch, nor a class in a Composer plugin package.

#### Scenario: Version gate
- **WHEN** the project runs Magento 2.4.5-p10 without `--target-version`
- **THEN** U-CLI-02 does not run, and with `--target-version=2.4.6` it runs
