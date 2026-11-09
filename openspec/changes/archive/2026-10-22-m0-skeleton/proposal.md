# Proposal

## Why

The repository holds only a specification (`docs/SPEC.md`). Before the 32 MVP rules of M1 can be written, the architecture has to be proven end to end: one command that runs a DOM-based XML analyzer and a PHPStan extension and merges the result into one report. Each layer is exercised by a real rule and run against real Magento core code. The source policy (SPEC section 2) also has to hold before any rule is built on a catalog entry: today several ✅/⏳ rules cite only level C sources or unpinned `2.4-develop` links.

## What Changes

- **Source audit** of every ✅ and ⏳ rule in the SPEC catalog. Each rule gets at least one verified level A/B source, with code links pinned to `magento/magento2@a385d02f`; otherwise the rule is moved to 🧪. The results are written to `docs/source-audit.md`, and the catalog in `docs/SPEC.md` is corrected.
- **Composer package skeleton** (`type: phpstan-extension`). It works with PHPStan 1.12 and 2.x, requires PHP 8.1+, and runs its own test and self-analysis scripts.
- **`bin/magento-perf-check`**: one entry point. It analyzes a Magento project by path (`--project`) without being installed into it, runs the XML analyzer and PHPStan, prints one merged report (table or JSON) and exits with code 0/1/2.
- **XML analyzer**: file discovery in `app/code`, `app/design` and explicit paths; detection of area and module; findings in the shared format.
- **PHPStan extension**: a loop-context marker (`foreach`/`for`/`while`/`do`, and closures passed to `array_map`, `array_walk` and `->walk()`), per-rule identifiers, and registration through `phpstan/extension-installer`.
- **Three end-to-end rules** with positive and negative fixtures and `docs/rules/<ID>.md` pages that include a Basis section:
  - X-LAY-01: `cacheable="false"` on a frontend layout block.
  - P-COL-02: `getFirstItem()` on a DB collection without a limit.
  - P-LOOP-01: model `load()` or repository `get()`/`getById()` inside a loop.
- **ADRs**:
  - Dependency on bitExpert/phpstan-magento.
  - Support for PHPStan 1.12 and 2.x.
  - How the runner orchestrates PHPStan and merges results.
- **Acceptance run** on Magento 2.4.9 core (`vendor/magento`) in the read-only test project, recording the finding counts and the run time.

Out of M0 (later milestones): configuration file, suppression comments for XML, baseline, SARIF/Checkstyle/CI formats (M1); calibration (M2).

## Capabilities

### New Capabilities

- `cli`: the `magento-perf-check` command — project path, analyzed paths, output formats, `--fail-on`, exit codes, merged report.
- `xml-analysis`: discovery of Magento XML files, resolution of area, module and layout handle, and the contract by which XML rules report findings.
- `phpstan-extension`: how the PHP rules plug into PHPStan (auto-registration, identifiers, loop context, compatibility with PHPStan 1.12 and 2.x).
- `rule-documentation`: rule metadata (ID, severity, sources with A/B/C level), the `docs/rules/<ID>.md` pages with a Basis section, the source audit, and the doc link in each finding.
- `layout-rules`: layout XML rules (X-LAY-*); this change adds X-LAY-01.
- `collection-rules`: collection and fetch rules (P-COL-*); this change adds P-COL-02.
- `loop-rules`: N+1 rules for code inside loops (P-LOOP-*); this change adds P-LOOP-01.

### Modified Capabilities

None (no existing specs).

## Impact

- New code: `bin/`, `src/`, `tests/`, `composer.json`, `extension.neon`, `phpstan.neon(.dist)`, `phpunit.xml.dist`.
- New docs: `docs/adr/`, `docs/rules/X-LAY-01.md`, `docs/rules/P-COL-02.md`, `docs/rules/P-LOOP-01.md`, `docs/source-audit.md`, and an M0 run report. `docs/SPEC.md` catalog corrections come from the audit. `README.md` changes accordingly.
- Dependencies:
  - `phpstan/phpstan` `^1.12 || ^2.0`.
  - Dev only: PHPUnit and `phpstan/extension-installer`.
  - bitExpert/phpstan-magento only as a suggestion (see design).
- The test Magento project at `/path/to/magento-2.4.9` is read and never modified.
