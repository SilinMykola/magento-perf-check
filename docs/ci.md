# CI and Git hooks

- **GitHub Actions:** copy [`ci/github-actions.yml`](ci/github-actions.yml) to `.github/workflows/`. It uploads SARIF to code scanning and annotates pull requests through `--format=github`. Actions are pinned to commit SHAs. The repo.magento.com credentials go into a `COMPOSER_AUTH` secret.
- **GrumPHP:** merge [`ci/grumphp.yml`](ci/grumphp.yml) into your `grumphp.yml`. It is a plain `shell` task: the commit fails when a finding reaches `--fail-on` (default `error`). GrumPHP runs it on the files tracked by git. Checked with GrumPHP 2.26.0: the task fails on a project with findings and passes on a clean one.
- **Other CI:** use `--format=checkstyle` or `--format=sarif` and the exit codes (0 clean, 1 findings, 2 tool error).

On a project with existing findings, start with a [baseline](baseline.md) so that CI fails only on new ones.
