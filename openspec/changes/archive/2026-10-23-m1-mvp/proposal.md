# Proposal

## Why

M0 proved the architecture on three rules. The audited catalog (`docs/SPEC.md` section 4, after the M0 source audit) has 29 rules marked ✅. M1 makes the tool usable on real projects: every ✅ rule implemented and documented, ways to configure and silence it, a baseline for legacy code, and report formats that CI systems read. The M0 core run (`docs/runs/m0-core.md`) also found two precision problems that must be fixed before more rules build on the same code: P-COL-02 reports already-loaded collections and unique-key lookups, and the X-LAY-01 allowlist misses personal handles that core itself makes uncacheable.

## What Changes

- **26 new rules**, every ✅ rule not shipped in M0. Each has positive and negative fixtures and a `docs/rules/<ID>.md` page with a Basis section.
  - PHP rules (PHPStan extension):
    - P-LOOP-02, P-LOOP-03, P-LOOP-07.
    - P-COL-01.
    - P-DI-01, P-DI-02, P-DI-03, P-DI-06.
    - P-FPC-01, P-FPC-02, P-FPC-05, P-FPC-06, P-FPC-09, P-FPC-10.
    - P-PLG-01.
    - T-01 (`.phtml`).
    - X-SETUP-01 (`Setup/Recurring*.php`).
  - XML and schema rules (XML analyzer):
    - X-LAY-02.
    - X-EVT-01, X-EVT-02, X-EVT-03.
    - X-SEC-01.
    - X-GQL-01 (`.graphqls`).
    - X-MQ-01, X-IDX-01, X-DB-01.
- **Project index.** The XML analyzer and the PHP rules look up `di.xml`, `events.xml`, `mview.xml` and `db_schema.xml` across the whole project, including core packages, while reporting only on the analyzed paths. This is needed where a fact lives in another module: a Proxy configured elsewhere, an mview view declared by another module, or a primary key declared by the module that owns the table.
- **Precision fixes from M0:**
  - P-COL-02 skips collections already loaded in the method, and lookups by primary key or a unique column.
  - X-LAY-01 allows the personal handles found in core and makes the allowlist configurable.
- **Configuration file** `magento-perf-rules.neon`. It is read from the project root, or from `--config`, and controls:
  - paths and test inclusion;
  - disabled rules and severity overrides;
  - the X-LAY-01 allowlist.
- **XML suppression** with `<!-- perf-ignore <ID>: reason -->` before the element. The reason is mandatory.
- **Baseline.** `--generate-baseline` writes the current findings to a file; `--baseline` hides findings that are in it and reports only new ones.
- **Output formats.** SARIF 2.1.0 and Checkstyle join table and JSON; the existing formats stay unchanged.
- **Behavior change for existing users:** default runs report more findings, because 26 rules are added. Teams with a strict CI threshold can use the new baseline (SPEC section 8 treats adding rules to the default set as a minor version).
- **Acceptance run** on Magento 2.4.9: counts per rule, run time, and a spot check of up to 10 findings per new rule, recorded in `docs/runs/m1-core.md`.

Out of M1 (later milestones):
- precision calibration and its thresholds (M2);
- GitHub and GitLab formats and the CI workflow (M2);
- ⏳ and 🧪 rules (M4/M5);
- cross-method loop analysis (M4).

## Capabilities

### New Capabilities

- `configuration`: the `magento-perf-rules.neon` file: location, keys, validation, and how it combines with command-line options.
- `baseline`: generating a baseline file and filtering known findings with it.
- `project-index`: the project-wide index of Magento configuration that rules consult. It covers DI proxies and plugins, observers, mview views and table primary keys.
- `di-rules`: object lifecycle and DI rules (P-DI-*).
- `fpc-rules`: Full Page Cache and cache rules in PHP (P-FPC-*).
- `plugin-rules`: plugin rules (P-PLG-*).
- `event-rules`: observer subscription rules in `events.xml` (X-EVT-*).
- `customer-data-rules`: `sections.xml` rules (X-SEC-*).
- `template-rules`: `.phtml` rules (T-*).
- `graphql-rules`: GraphQL schema rules (X-GQL-*).
- `queue-rules`: message queue configuration rules (X-MQ-*).
- `indexer-rules`: indexer and mview rules (X-IDX-*).
- `setup-rules`: setup script rules (X-SETUP-*).
- `db-schema-rules`: `db_schema.xml` rules (X-DB-*).

### Modified Capabilities

- `cli`: the output formats requirement gains SARIF and Checkstyle. New options are added: `--config`, `--baseline`, `--generate-baseline`. Rule settings from configuration apply to the report.
- `xml-analysis`: adds suppression comments, `.graphqls` files and the additional `etc/` files rules need (`sections.xml`, `communication.xml`, `indexer.xml`, `mview.xml`, `db_schema.xml`).
- `phpstan-extension`: adds `.phtml` analysis and access to the project index from PHP rules.
- `layout-rules`: the X-LAY-01 allowlist changes (more personal handles, configurable); X-LAY-02 is added.
- `collection-rules`: P-COL-02 scope changes (already-loaded collections, unique-key filters); P-COL-01 is added.
- `loop-rules`: P-LOOP-02, P-LOOP-03 and P-LOOP-07 are added.

## Impact

- **Code:** new rules under `src/PhpStan/Rule/` and `src/Xml/Rule/`, an index builder, a configuration loader, baseline support, SARIF and Checkstyle reporters. `extension.neon` and the rule registry are extended.
- **Dependencies:**
  - `nette/neon ^3.4`, to read the configuration file. Not present in Magento 2.4.9, so there is no conflict.
  - `webonyx/graphql-php ^14.11 || ^15.0`, to parse `.graphqls`. Magento 2.4.4–2.4.5 require `~14.11`, and 2.4.6–2.4.9 require `^15.0`.
- **Docs:** 26 rule pages, an updated README, ADRs for the project index and the configuration format, and `docs/runs/m1-core.md`.
- The test Magento project stays read-only.
