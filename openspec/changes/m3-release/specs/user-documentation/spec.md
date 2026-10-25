# Spec Delta

## Purpose

Gives users a short README that gets them running and convinced, reference pages for everything else, and guidance that lets coding agents run the tool and act on its findings.

## ADDED Requirements

### Requirement: README structure
`README.md` SHALL contain, in this order: a one-line description, badges, an example of real output, a quick start of at most three commands, a section on why the findings can be trusted (source levels and calibration), and links to the reference pages. It SHALL NOT contain the rule tables or the option reference.

#### Scenario: README links every reference page
- **WHEN** the test suite runs
- **THEN** it fails unless `README.md` links `docs/rules.md`, `docs/configuration.md`, `docs/baseline.md` and `docs/ci.md`, and each linked file exists

### Requirement: Example output is real
The example output and the example findings in the README SHALL be copied from actual runs of the tool on the calibration corpus, with the file and line they came from, and only findings graded TP in calibration SHALL be shown as examples of real problems.

#### Scenario: Example finding traceable
- **WHEN** a reader looks up an example finding shown in the README
- **THEN** the same rule, file and line appear as a TP row in `docs/calibration/graded/<ID>.md`

### Requirement: Rule list
`docs/rules.md` SHALL list every implemented rule with ID, what it finds, severity and source levels, in one table for the default set and one for experimental rules, each ID linking to its rule page.

#### Scenario: Rule list matches the registry
- **WHEN** the test suite runs
- **THEN** it fails unless every rule in the registry appears in `docs/rules.md` in the table matching its status, with its default severity

### Requirement: Guidance for coding agents
The repository SHALL ship a snippet for a project's `AGENTS.md` in `docs/agents.md` and a skill at `resources/skill/magento-perf-check/SKILL.md`. Both SHALL tell the agent how to run the tool with `--format=json`, how to read the result, and to treat findings as reasons to check, citing the rule page.

#### Scenario: Skill is well-formed
- **WHEN** the test suite runs
- **THEN** it fails unless the skill file has front matter with `name` and `description`, and every command it shows uses only options the tool accepts
