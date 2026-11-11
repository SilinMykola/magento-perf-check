## MODIFIED Requirements

### Requirement: Projects with the PHPStan extension installer
The analysis SHALL run only this package's rules and the extensions the command includes itself, whatever `phpstan/extension-installer` lists in the analyzed project or next to the tool. The command SHALL keep the installer from adding extensions, so that no file is included twice and no other extension's rules run. From `bitexpert/phpstan-magento` the analysis SHALL use the type extensions; the command SHALL switch off those of its own rules that have parameters.

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

#### Scenario: bitExpert's own rules
- **WHEN** `bitexpert/phpstan-magento` is installed next to the tool
- **THEN** the generated configuration sets `magento.checkCollectionViaFactory`, `magento.checkServiceContracts`, `magento.checkResourceModelsUsedDirectly` and `extdn.setTemplateDisallowedForBlockClasses` to `false`
- **AND** without bitExpert the generated configuration has none of these parameters
