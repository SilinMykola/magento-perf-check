# CI and Git hooks

The examples below assume the tool is installed in the project. If it runs from its own clone, see [Installing](configuration.md#installing) and use that path with `--project` instead of `vendor/bin/magento-perf-check`.

- **GitHub Actions:** copy [`ci/github-actions.yml`](ci/github-actions.yml) to `.github/workflows/`. It uploads SARIF to code scanning and annotates pull requests through `--format=github`. Actions are pinned to commit SHAs. The repo.magento.com credentials go into a `COMPOSER_AUTH` secret.
- **GrumPHP:** merge [`ci/grumphp.yml`](ci/grumphp.yml) into your `grumphp.yml`. It is a plain `shell` task: the commit fails when a finding reaches `--fail-on` (default `error`). GrumPHP runs it on the files tracked by git. Checked with GrumPHP 2.26.0: the task fails on a project with findings and passes on a clean one.
- **Plain Git hook:** see below.
- **Other CI:** use `--format=checkstyle` or `--format=sarif` and the exit codes (0 clean, 1 findings, 2 tool error).

On a project with existing findings, start with a [baseline](baseline.md) so that CI fails only on new ones.

## Git pre-commit hook

Save this as `.git/hooks/pre-commit` in the Magento project and make it executable (`chmod +x .git/hooks/pre-commit`):

```sh
#!/bin/sh
# Blocks the commit when a finding reaches --fail-on (default: error).
vendor/bin/magento-perf-check --output=var/magento-perf-check.txt
```

The console shows one line with the counts and where the report is; the full report is in `var/magento-perf-check.txt`. The `.gitignore` of magento/magento2 excludes `/var/*`; check that your project's does too, so the report is never committed. Exit code 1 (findings) or 2 (tool error) stops the commit.

- The hook checks the files on disk under the analyzed paths, not only the staged changes.
- Add `--baseline=<file>` to block only new findings, or `--fail-on=warning` to be stricter.
- `git commit --no-verify` skips the hook once.

