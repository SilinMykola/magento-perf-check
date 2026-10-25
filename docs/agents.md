# Coding agents

Two ways to let a coding agent run the tool and act on its findings.

## Snippet for AGENTS.md

Paste this into the project's `AGENTS.md` (or the agent's equivalent instructions file):

````markdown
## Performance check

After changing PHP, templates or XML of a Magento module, run:

```sh
vendor/bin/magento-perf-check --paths=app/code/Vendor/Module --format=json
```

- Exit code 0 is clean, 1 means findings at the `--fail-on` severity (default `error`), 2 means the tool failed: report stderr, do not treat the run as clean.
- Each finding has `ruleId`, `severity`, `file`, `line`, `message` and `docUrl`. Read the rule page (`docUrl`, or `vendor/silin/magento-perf-check/docs/rules/<ruleId>.md`) before changing code.
- A finding is a reason to check, not a verdict. Fix it as the rule page shows, or suppress it the way the page shows with the reason in the comment. Never suppress without a reason, never disable a rule or regenerate the baseline to make the run pass.
- Run the command again to confirm.
````

## Skill

The package ships a skill in the Agent Skills format: [`resources/skill/magento-perf-check/SKILL.md`](../resources/skill/magento-perf-check/SKILL.md). It covers the same steps in more detail: how to run the tool, read the JSON, check each finding against the rule page, fix or suppress it, and report what was done.

Copy the `magento-perf-check` directory into the directory your agent loads skills from, for example the project's skills directory, so it travels with the repository:

```sh
cp -R vendor/silin/magento-perf-check/resources/skill/magento-perf-check <skills directory>/
```

Refresh the copy after updating the package.
