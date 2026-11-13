# upgrade-rules Specification

## Purpose
Upgrade rules: find code in the project that breaks with a change in a Magento release, and run them by the installed version and the version given with `--target-version`.

## Requirements

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

### Requirement: U-CLI-01 console command without the Symfony 7.4 return type
U-CLI-01 SHALL report a class that extends `Symfony\Component\Console\Command\Command` and declares `execute()` without a native `int` return type. Its version SHALL be 2.4.9, and it SHALL start as experimental. It SHALL also report such an `execute()` that comes from a trait, at the class. It SHALL NOT report a named class declared in an `if`, `elseif` or `else` branch, nor a class in a Composer plugin package, which `bin/magento` does not load.

#### Scenario: Missing return type
- **WHEN** a command class declares `protected function execute(InputInterface $input, OutputInterface $output)`
- **THEN** U-CLI-01 reports it at the method

#### Scenario: Declared under a version check
- **WHEN** the same class is declared inside an `if` block
- **THEN** U-CLI-01 reports nothing

#### Scenario: Command of a Composer plugin
- **WHEN** a command without the return type is in a package of type `composer-plugin`
- **THEN** U-CLI-01 reports nothing

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

### Requirement: U-JS-01 jqXHR methods that jquery-migrate added
U-JS-01 SHALL report a call of `success`, `error` or `complete` in a call chain that starts with `$.ajax`, `$.get`, `$.post`, `$.getJSON` or `$.getScript`, also written with `jQuery`, or with `get`, `post`, `put` or `delete` on an object named `storage` that is not itself a property, and goes on only through `done`, `fail`, `always` or `progress`, which return the same jqXHR. It SHALL read storefront and admin scripts. Its version SHALL be 2.4.6, and it SHALL start as experimental, at `error`.

#### Scenario: Old copy of a core script
- **WHEN** a theme's `Magento_Review/web/js/process-reviews.js` has `$.ajax({...}).done(...).complete(...)`
- **THEN** U-JS-01 reports the `complete` call

#### Scenario: mage/storage
- **WHEN** a script has `storage.post(url, payload).success(...)`
- **THEN** U-JS-01 reports it

#### Scenario: Admin script
- **WHEN** a module's `view/adminhtml/web/js/form.js` has `$.post(url, data).success(...)`
- **THEN** U-JS-01 reports it

#### Scenario: Call inside the arguments of another
- **WHEN** a script has `$.ajax({success: function () { $.get(url).complete(fn); }})`
- **THEN** U-JS-01 reports the `complete` call

#### Scenario: Not a jqXHR
- **WHEN** a script has `this.city().clear().error(false)`, `this.storage.get(key).success(...)`, `$('.form').on(...).error(...)`, `window.jQuery.ajax(url).success(...)`, `$j.ajax(url).success(...)`, `$.ajax(url)?.success(...)`, or the same code in a comment or a string
- **THEN** U-JS-01 reports nothing

#### Scenario: jQuery 3 methods
- **WHEN** a chain on `$.get(url)` uses only `done`, `fail`, `always` or `then`, or calls `success` after `then`
- **THEN** U-JS-01 reports nothing

### Requirement: U-LOG-01 Monolog 1 methods that Monolog 2 removed
U-LOG-01 SHALL report a call of an `add*` method of Monolog 1, `warn`, `err`, `crit` or `emerg` on a receiver whose type, without `null`, is `Monolog\Logger` or a subclass that does not declare the method itself, or through `parent::`, `self::` or `static::` inside such a class. It SHALL NOT report it on `Psr\Log\LoggerInterface` or another class.

#### Scenario: Module's own logger
- **WHEN** a class `ShopLogger extends \Monolog\Logger` declares no methods and a service calls `$this->logger->addError('failed')` on it
- **THEN** U-LOG-01 reports it

#### Scenario: Project already on Monolog 2
- **WHEN** the project's Monolog has no `addWarning()` and code calls `addWarning()` on a `Monolog\Logger` subclass
- **THEN** U-LOG-01 reports it

#### Scenario: Logger that declares the method
- **WHEN** a logger class extending `Monolog\Logger` declares its own `addError()`
- **THEN** U-LOG-01 reports nothing for calls of it

#### Scenario: Not a Monolog logger
- **WHEN** code calls `addError()` on the message manager, or on `Psr\Log\LoggerInterface`
- **THEN** U-LOG-01 reports nothing

#### Scenario: Parent call
- **WHEN** a logger class that extends `Monolog\Logger` calls `parent::addError()`
- **THEN** U-LOG-01 reports it

### Requirement: U-LOG-01 version and status
U-LOG-01 SHALL have the version 2.4.4, and SHALL be experimental, at `error`, after its calibration round: its impact share is below the 50 % an experimental rule needs to enter the default set.

#### Scenario: Version gate
- **WHEN** the project runs Magento 2.4.3-p3 without `--target-version`, with U-LOG-01 enabled
- **THEN** U-LOG-01 does not run, and with `--target-version=2.4.4` it runs

#### Scenario: Off by default
- **WHEN** the configuration does not enable U-LOG-01
- **THEN** U-LOG-01 does not run, whatever the version
