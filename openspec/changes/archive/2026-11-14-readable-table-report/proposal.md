# Proposal

## Why

The default table report printed every finding as a line with its full message and a documentation link under it, grouped by file, without colors. On a real project with 83 findings that is a long wall of text: a reader cannot see at a glance which rules fire, how often, and where. Users read the table; tools read JSON, SARIF, Checkstyle or GitHub annotations.

## What Changes

- The table report shows one block per rule and severity, errors first, then warnings, then info, and within a severity the rule with most findings first. A block has the severity, the rule ID, the rule's title and its number of findings on one line (a line with two findings shows as `5 (2x)`), the documentation link under it, and one line per file: `file:line, line`.
- Without `--verbose`, the messages of single findings are left out, and an info rule lists at most 5 files with "… and N more files". `--verbose` (`-v`) lists every file and, under it, the line and message of each finding.
- `--color=auto|always|never`: in a terminal (`auto`, the default) the severity is a colored label, counts are colored, and the rule ID is an OSC 8 link to its page. A non-empty `NO_COLOR` or `TERM=dumb` turns this off; a report written with `--output` never has colors.
- The text of the summary line stays as it was; the table is not meant for parsing, JSON is. A hint names `--verbose` and `--format=json`.
