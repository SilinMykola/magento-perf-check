# Spec Delta

## Purpose

Lets projects run the analysis where they already check code: in GitHub Actions (results in code scanning and on pull requests) and in GrumPHP before a commit. The repository's own CI checks the package on every supported PHP and PHPStan version.

## ADDED Requirements

### Requirement: GitHub Actions example for projects
The repository SHALL ship `docs/ci/github-actions.yml`, a workflow that projects can copy. It runs `magento-perf-check` twice:
- with `--format=sarif`, uploading the log with `github/codeql-action/upload-sarif`;
- with `--format=github`, to annotate the pull request.

The workflow SHALL pin every action to a full commit SHA, with the version tag as a comment.

#### Scenario: Example is valid
- **WHEN** the test suite runs
- **THEN** a test parses `docs/ci/github-actions.yml` as YAML and checks that it calls `magento-perf-check` with `--format=sarif` and `--format=github`, and that every `uses:` is pinned to a 40-character SHA

### Requirement: GrumPHP task example
The repository SHALL document a GrumPHP `shell` task that runs `magento-perf-check` and fails the commit on findings at the configured `--fail-on` level. An example `docs/ci/grumphp.yml` SHALL be checked to work with GrumPHP.

#### Scenario: GrumPHP runs the task
- **WHEN** GrumPHP runs with the example configuration on the fixture project that has findings
- **THEN** the task fails and shows the findings, and passes on a project without findings

### Requirement: Repository CI
The repository SHALL have `.github/workflows/ci.yml`. It runs the test suite on PHP 8.1, 8.2, 8.3, 8.4 and 8.5 with PHPStan 2.x, the suite against PHPStan 1.12, the self-analysis, and `openspec validate --specs`.

#### Scenario: Workflow covers the matrix
- **WHEN** the test suite runs
- **THEN** a test checks that `.github/workflows/ci.yml` lists PHP 8.1 to 8.5 and a PHPStan 1.12 job
