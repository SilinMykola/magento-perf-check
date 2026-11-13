## ADDED Requirements

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
U-LOG-01 SHALL have the version 2.4.4, and SHALL be in the default set, at `error`, after its calibration round.

#### Scenario: Version gate
- **WHEN** the project runs Magento 2.4.3-p3 without `--target-version`
- **THEN** U-LOG-01 does not run, and with `--target-version=2.4.4` it runs
