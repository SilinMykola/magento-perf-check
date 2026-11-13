## ADDED Requirements

### Requirement: GitHub corpus part
The calibration corpus SHALL have a third part, `github`, built under `var/corpus/github` as a hard-linked copy of the `extended` part and of the modules of the `packagist` part. It SHALL hold up to 50 modules from the most-starred GitHub repositories with the topic `magento2-module` that are not archived and not forks. A repository SHALL be passed over when its module or Composer name is already in the copy, when it holds no single module of type `magento2-module`, when one of its requirements is neither installed in the copy at a fitting version nor a module of the copy, when its `registration.php` fails the check of the `extended` part, when it contains a symbolic link, or when it cannot be cloned. The `packagist` and `extended` parts SHALL stay unchanged.

#### Scenario: Module already in the corpus
- **WHEN** a repository's Composer name is that of a module in the `extended` part
- **THEN** the repository is not copied and the manifest records it as skipped

#### Scenario: Requirement missing
- **WHEN** a repository requires a package that is neither installed in the copy nor a module of the copy
- **THEN** the repository is not copied and the manifest records the requirement

#### Scenario: Only the new modules are analyzed
- **WHEN** `bin/calibration/run github` runs
- **THEN** the report holds findings only under the paths of the modules listed in the `github` section of the manifest

### Requirement: GitHub part manifest and clones
The repositories of the `github` part SHALL be cloned into `var/corpus/github-clones`, which a rebuild of the part keeps and reuses without a fetch. `docs/calibration/corpus.json` SHALL list every repository of the part with its Composer name, module, stars and commit, and every skipped repository with the reason.

#### Scenario: Rebuild on the same machine
- **WHEN** the `github` part is built a second time
- **THEN** every repository that is selected again keeps the commit of the first build
