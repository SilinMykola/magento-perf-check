## MODIFIED Requirements

### Requirement: Test code excluded by default
By default the command SHALL skip test code below the analyzed paths: any directory named `Test` and any `dev/tests` directory, for both XML and PHP. `--include-tests` SHALL turn the exclusion off. A path given in `--paths` that itself lies inside test code SHALL be analyzed. Excluded test code SHALL NOT be a source of symbols either: a class declared there SHALL NOT replace a class of the same name for other code.

#### Scenario: Module tests skipped
- **WHEN** `app/code/Acme/Promo/Test/Integration/ExportTest.php` contains a P-LOOP-01 violation and the command runs without `--include-tests`
- **THEN** no finding for that file is reported

#### Scenario: Tests included on request
- **WHEN** the same project is analyzed with `--include-tests`
- **THEN** the P-LOOP-01 violation in the test file is reported

#### Scenario: Test directory given explicitly
- **WHEN** the command runs with `--paths=app/code/Acme/Promo/Test`
- **THEN** files under that directory are analyzed

#### Scenario: Stub of a core class in test code
- **WHEN** `app/code/Acme/Other/Test/Unit/_stubs/Magento/Customer/Model/Session.php` declares a plain `Magento\Customer\Model\Session` and another module injects that session without a Proxy
- **THEN** P-DI-01 reports the other module's class

#### Scenario: Test path next to its parent
- **WHEN** `--paths=app/code/Acme/Demo,app/code/Acme/Demo/Test/Unit` is given
- **THEN** the code in `Test/Unit` is analyzed

### Requirement: Notices reach the reader
The analysis notice summary SHALL go to stderr when the format is `sarif`, `checkstyle` or `github`, or when `--output` is used. A missing or empty `generated/code`, without bitexpert/phpstan-magento installed, SHALL be a notice with source `generated-code`.

#### Scenario: SARIF with an unparsable file
- **WHEN** the command runs with `--format=sarif` and a file does not parse
- **THEN** stderr has the notice summary

#### Scenario: No generated code
- **WHEN** the project has no `generated/code`
- **THEN** the report has one `generated-code` notice

#### Scenario: Notice of a disabled rule
- **WHEN** P-COL-02 is not enabled and the code calls `getFirstItem()` on a receiver of unknown type
- **THEN** the result contains no `untyped-receiver` notice
