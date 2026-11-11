## MODIFIED Requirements

### Requirement: Projects with the PHPStan extension installer
The analysis SHALL run only this package's rules and the extensions the command includes itself, whatever `phpstan/extension-installer` lists in the analyzed project or next to the tool. The command SHALL keep the installer from adding extensions, so that no file is included twice and no other extension's rules run.

#### Scenario: Installer includes both extensions
- **WHEN** the project's installer list holds this package and `bitexpert/phpstan-magento`
- **THEN** the analysis runs and reports the package's findings
- **AND** the generated configuration still includes `extension-experimental.neon`

#### Scenario: Installer lists other extensions
- **WHEN** the project's installer list also holds an extension with its own rule
- **THEN** no error of that rule is in the report

#### Scenario: No installer
- **WHEN** neither the project nor the tool has the installer
- **THEN** the command includes this package's files, and `bitexpert/phpstan-magento`'s when it is installed next to the tool

### Requirement: Notices reach the reader
The analysis notice summary SHALL go to stderr when the format is `sarif`, `checkstyle` or `github`, or when `--output` is used. A missing or empty `generated/code`, without bitexpert/phpstan-magento installed next to the tool, SHALL be a notice with source `generated-code`.

#### Scenario: SARIF with an unparsable file
- **WHEN** the command runs with `--format=sarif` and a file does not parse
- **THEN** stderr has the notice summary

#### Scenario: No generated code
- **WHEN** the project has no `generated/code`
- **THEN** the report has one `generated-code` notice

#### Scenario: bitexpert/phpstan-magento through the installer
- **WHEN** the project has no `generated/code` and only the project's extension installer lists bitexpert/phpstan-magento
- **THEN** the report has one `generated-code` notice: the project's copy is not loaded

#### Scenario: Notice of a disabled rule
- **WHEN** P-COL-02 is not enabled and the code calls `getFirstItem()` on a receiver of unknown type
- **THEN** the result contains no `untyped-receiver` notice
