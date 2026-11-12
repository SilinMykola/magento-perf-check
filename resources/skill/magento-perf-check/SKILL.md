---
name: magento-perf-check
description: Run magento-perf-check on a Magento 2 or Mage-OS project and act on its findings. Use after changing PHP, templates or XML (layout, di.xml, events.xml, sections.xml, db_schema.xml, queues, indexers, GraphQL schemas) in a Magento module, or when asked to check Magento code for performance or Full Page Cache problems.
---

# magento-perf-check

`magento-perf-check` finds performance and Full Page Cache anti-patterns in Magento code: database queries in loops, state changes in GET requests, sessions injected without a Proxy, observers on hot events, and misconfigured layout, sections, queues, indexers and GraphQL caching. It reads the project and never writes into it.

## Run it

From the Magento project root, on the module you changed:

```sh
vendor/bin/magento-perf-check --paths=app/code/Vendor/Module --format=json
```

- Without `--paths` it analyzes `app/code` and `app/design`.
- If the tool is installed elsewhere, add `--project=<magento root>`.
- Exit code 0: no finding at the `--fail-on` severity (default `error`); 1: such findings exist; 2: the tool failed. On 2, read stderr and report it; do not treat the run as clean.

## Read the result

The JSON has `summary` (counts by severity and rule) and `findings`. Each finding has `ruleId`, `severity`, `file` (relative to the project), `line`, `message` and `docUrl`.

The rule page explains what the rule catches, why it is slow, a bad and a good example, how to suppress it and its known false positives. Read it before changing code. When the package is installed, the same page is also at `vendor/silin/magento-perf-check/docs/rules/<ruleId>.md`.

## Act on findings

1. Group findings by rule and handle `error` before `warning` before `info`.
2. For each finding, open the code and check whether the rule's claim holds there. A finding is a reason to check, not a verdict. Compare with the page's "Known false positives".
3. If it holds, apply the fix the rule page shows (for example, fetch items with one collection before the loop instead of `getById()` inside it). Keep the change minimal and keep behavior the same.
4. If it does not hold, or the cost is bounded (for example, a loop capped at a few items), suppress it the way the rule page shows and state the reason in the comment:
   - PHP: `// @phpstan-ignore <identifier> (reason)` on the line before;
   - XML: `<!-- perf-ignore <ruleId>: reason -->` directly before the element;
   - JavaScript: `// perf-ignore <ruleId>: reason` on the line before.
   Never suppress without a reason.
5. Run the tool again on the same paths and confirm the finding is gone or suppressed.

In your report, list for each finding: rule, file and line, what you did (fixed, suppressed with reason, left for a human) and why.

## Do not

- Do not disable a rule in `magento-perf-check.neon` to make a run pass; that hides it for the whole project.
- Do not regenerate a baseline (`--generate-baseline`) to hide new findings unless asked.
- Do not change code under `vendor/`; report core findings instead.
