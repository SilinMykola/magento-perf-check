## ADDED Requirements

### Requirement: Projects with the PHPStan extension installer
When `phpstan/extension-installer` already includes the `extension.neon` of this package or of `bitexpert/phpstan-magento`, the command SHALL NOT include that file again in the configuration it generates. It SHALL read the installer's list from the Composer autoloader that PHPStan uses first: the analyzed project's, then the tool's own.

#### Scenario: Installer includes both extensions
- **WHEN** the project's installer list holds this package and `bitexpert/phpstan-magento`
- **THEN** the analysis runs and reports the package's findings
- **AND** the generated configuration still includes `extension-experimental.neon`

#### Scenario: No installer
- **WHEN** neither autoloader knows the installer's list
- **THEN** the command includes both files itself, as before

## MODIFIED Requirements

### Requirement: Notices reach the reader
The analysis notice summary SHALL go to stderr when the format is `sarif`, `checkstyle` or `github`, or when `--output` is used. A missing or empty `generated/code` SHALL be a notice with source `generated-code`, unless bitexpert/phpstan-magento is installed next to the tool or loaded by the extension installer.

#### Scenario: SARIF with an unparsable file
- **WHEN** the command runs with `--format=sarif` and a file does not parse
- **THEN** stderr has the notice summary

#### Scenario: No generated code
- **WHEN** the project has no `generated/code`
- **THEN** the report has one `generated-code` notice

#### Scenario: bitexpert/phpstan-magento through the installer
- **WHEN** the project has no `generated/code` and the extension installer loads bitexpert/phpstan-magento
- **THEN** no `generated-code` notice is given

#### Scenario: Notice of a disabled rule
- **WHEN** P-COL-02 is not enabled and the code calls `getFirstItem()` on a receiver of unknown type
- **THEN** the result contains no `untyped-receiver` notice
