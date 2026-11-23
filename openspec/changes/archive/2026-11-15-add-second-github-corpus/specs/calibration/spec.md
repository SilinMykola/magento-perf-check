## ADDED Requirements

### Requirement: Second GitHub corpus part
The calibration corpus SHALL have a fourth part, `github2`, built under `var/corpus/github2` as the `github` part is built, with 100 modules: GitHub repositories with the topic `magento2-module`, most stars first, under the same selection, passing over every repository whose module or Composer name is in the `github` part too, or whose module name is that of a module of the Magento installation, such as `Magento_Customer`. Its repositories SHALL be cloned into `var/corpus/github-clones`. `docs/calibration/corpus.json` SHALL list them in a `github2` section with the fields of the `github` section. `bin/calibration/run github2` SHALL analyze the paths of its modules only. It SHALL read the modules to pass over from the `github` section of the manifest, so the part SHALL be rebuilt after the `github` part is. The `packagist`, `extended` and `github` parts SHALL stay unchanged.

#### Scenario: Module of the first GitHub part
- **WHEN** a repository's Composer name is that of a module in the `github` part
- **THEN** the repository is not copied into `github2` and the manifest records it as skipped

#### Scenario: Only the new modules are analyzed
- **WHEN** `bin/calibration/run github2` runs
- **THEN** the report holds findings only under the paths of the modules listed in the `github2` section of the manifest

#### Scenario: Module named like a core module
- **WHEN** a repository declares the module `Magento_Customer`
- **THEN** the repository is not copied and the manifest records it as skipped
