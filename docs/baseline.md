# Baseline for existing projects

To adopt the tool on a project with many existing findings, record them once and fail only on new ones:

```sh
vendor/bin/magento-perf-check --generate-baseline=perf-baseline.json
vendor/bin/magento-perf-check --baseline=perf-baseline.json
```

The file is written relative to the current directory, never into the analyzed project when you use `--project`. Commit it next to your CI configuration.

A finding is matched by rule, file and message, not by line, so moving code does not resurface it. A second identical finding in the same file is new and gets reported. The summary shows how many findings the baseline hid, and how many entries matched nothing because those issues were fixed. Regenerate the file to drop those entries.

`--baseline` and `--generate-baseline` cannot be combined. A missing or broken baseline file makes the command exit with 2 before the analysis starts.
