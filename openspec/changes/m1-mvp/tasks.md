# Tasks

**Definition of done for every rule task:**
- fixtures cover every scenario of the rule in its spec file;
- the key condition is broken on purpose, the test is seen to fail, and the condition is restored;
- the rule is registered in `RuleRegistry` with its sources from `docs/source-audit.md`;
- `docs/rules/<ID>.md` follows the SPEC section 5 template, including a Basis section;
- `composer test`, `bin/test-phpstan1` and `composer analyse` pass.

Each group may be committed on its own once its tasks are done.

## 1. Foundations

- [x] 1.1 Add `nette/neon ^3.4` and `webonyx/graphql-php ^14.11 || ^15.0` to `composer.json`, then run `composer install`. Verify: install succeeds, and `bin/test-phpstan1` still passes.
- [x] 1.2 Map PHPStan identifiers to rule ID and severity in the registry (design D4). Keep the `[RULE-ID]` prefix as a fallback. Verify: the runner integration tests pass, and a new unit test fails when a rule in `extension.neon` emits an identifier missing from the registry.
- [x] 1.3 Extend XML discovery to `sections.xml`, `communication.xml`, `indexer.xml`, `mview.xml`, `db_schema.xml` and `etc/*.graphqls` (area `graphql`). Verify: tests cover both "Additional configuration files" scenarios in `specs/xml-analysis/spec.md`.
- [x] 1.4 Implement XML suppression comments centrally in `XmlAnalyzer` (design D9). A comment without a reason becomes a notice. Verify: tests cover all four "Suppression comments in XML" scenarios with X-LAY-01; with the sibling check removed, the "Comment not adjacent" test fails.

## 2. Project index

- [ ] 2.1 Implement `ProjectIndex` (design D1):
  - module discovery from `app/code` and `vendor/composer/installed.json`;
  - DI proxies per class argument, including virtual types and parent-class arguments;
  - plugin classes, observers per event, mview view IDs, and tables with a primary key;
  - fingerprinted cache in the temp directory.

  Verify: unit tests on a fixture project cover every scenario in `specs/project-index/spec.md`, including the cache rebuild after a `di.xml` change.
- [ ] 2.2 Expose the index to PHP rules through the `magentoPerf.projectRoot` parameter and a lazily loaded service. The runner builds the index before PHPStan starts. Verify: tests cover both "Project index available to PHP rules" scenarios in `specs/phpstan-extension/spec.md`.
- [ ] 2.3 Time the index build on `~/projects/magento-2.4.9.ddev`, cold and cached. Verify: both times are recorded for `docs/runs/m1-core.md`, and the test project is unchanged (`find -newer` marker).
- [ ] 2.4 Write ADR 0004 (project index). Verify: the file exists with Context, Decision and Consequences, and cites the measured build time.

## 3. Configuration

- [ ] 3.1 Implement the loader and validator for `magento-perf-rules.neon` and `--config` (design D2). Verify: tests cover every scenario of "Configuration file location", "Configuration keys" and "Invalid configuration rejected" in `specs/configuration/spec.md`.
- [ ] 3.2 Apply rule settings to the merged findings (design D3), and give command-line options precedence. Verify: tests cover "Rule settings apply to every analyzer" and "Command line overrides configuration"; with the severity override removed, the "Downgraded rule" test fails.
- [ ] 3.3 Write ADR 0005 (configuration format), and document the file and its keys in `README.md`. Verify: the README example config passes the loader in a test.

## 4. Baseline

- [ ] 4.1 Implement `--generate-baseline` and `--baseline` (design D10). Order: configuration, then baseline, then `--fail-on`. Count hidden findings and unmatched entries. Verify: tests cover every scenario in `specs/baseline/spec.md`; with counts ignored, the "Second occurrence" test fails.
- [ ] 4.2 Document the baseline workflow in `README.md`. Verify: the documented commands run against the fixture project.

## 5. Output formats

- [ ] 5.1 Implement the SARIF 2.1.0 reporter (design D13). Verify: the "SARIF output" scenario test passes, and the output validates against the SARIF 2.1.0 JSON schema, a test fixture downloaded once and stored with its source URL.
- [ ] 5.2 Implement the Checkstyle reporter. Verify: the "Checkstyle output" scenario test passes, and the XML is well-formed with escaped messages.

## 6. Collection rules

- [ ] 6.1 Replace `CollectionLimitVisitor` with `CollectionStateVisitor`, which tracks limited, loaded and unique-filter state (design D5). Apply it to P-COL-02. Verify:
  - every scenario of the modified "P-COL-02 scope" requirement passes;
  - the M0 P-COL-02 tests still pass;
  - P-COL-02 FP 6 and 10 from `docs/runs/m0-core.md` (count before getFirstItem) are reproduced as negative fixtures.
- [ ] 6.2 Implement P-COL-01. Verify: per definition of done.

## 7. Loop rules

- [ ] 7.1 Track collections created inside loop bodies (design D6), and implement P-LOOP-02. Verify: per definition of done.
- [ ] 7.2 Implement P-LOOP-03. Verify: per definition of done.
- [ ] 7.3 Implement P-LOOP-07, including `getIdBySku` on the product model and resource model. Verify: per definition of done.

## 8. DI rules

- [ ] 8.1 Implement P-DI-01 with the project index and two identifiers (session → error, cookie manager → warning). Verify: per definition of done, including a fixture whose Proxy is configured only in `etc/frontend/di.xml` of another module.
- [ ] 8.2 Implement P-DI-02. Verify: per definition of done.
- [ ] 8.3 Implement P-DI-03. Verify: per definition of done.
- [ ] 8.4 Implement P-DI-06 with the documented exceptions. Verify: per definition of done.

## 9. FPC rules

- [ ] 9.1 Implement P-FPC-01. Verify: per definition of done.
- [ ] 9.2 Implement P-FPC-02. Verify: per definition of done.
- [ ] 9.3 Implement P-FPC-05 with its scope (GET-capable frontend controllers, GraphQL resolvers). Verify: per definition of done.
- [ ] 9.4 Implement P-FPC-06. Verify: per definition of done.
- [ ] 9.5 Implement P-FPC-09. Verify: per definition of done.
- [ ] 9.6 Implement P-FPC-10, with plugin detection through the project index only. Verify: per definition of done.

## 10. Plugins, templates, setup

- [ ] 10.1 Implement P-PLG-01 and plugin class detection. Verify: per definition of done.
- [ ] 10.2 Add `phtml` to the runner's `fileExtensions` (design D8), and implement T-01. Verify: per definition of done, plus both "Template analysis" scenarios in `specs/phpstan-extension/spec.md`.
- [ ] 10.3 Implement X-SETUP-01. Verify: per definition of done.

## 11. Layout and XML configuration rules

- [ ] 11.1 Extend the X-LAY-01 default allowlist and make it configurable. Verify: every scenario of the modified "X-LAY-01 default allowlist" requirement passes, and `docs/rules/X-LAY-01.md` lists the new handles with their source (`docs/runs/m0-core.md`).
- [ ] 11.2 Implement X-LAY-02. Verify: per definition of done.
- [ ] 11.3 Implement X-EVT-01, X-EVT-02 and X-EVT-03, including the "Event rule findings" requirement. Verify: per definition of done for each rule.
- [ ] 11.4 Implement X-SEC-01. Verify: per definition of done.
- [ ] 11.5 Implement X-MQ-01. Verify: per definition of done.
- [ ] 11.6 Implement X-IDX-01 with the project index. Verify: per definition of done.
- [ ] 11.7 Implement X-DB-01 with the project index. Verify: per definition of done, including the "Extending a core table" scenario against a fixture `vendor/magento` module.

## 12. GraphQL

- [ ] 12.1 Implement GraphQL schema loading with webonyx (design D12), with notices for unparsable files. Implement X-GQL-01 (the `cacheIdentity` half). Verify: per definition of done, plus the "Unparsable schema files" scenario.
- [ ] 12.2 Write ADR 0006 (GraphQL parser dependency). Verify: the file exists and cites the per-version webonyx constraints from design.md.

## 13. Catalog and README

- [ ] 13.1 In `docs/SPEC.md`:
  - split X-GQL-01 into X-GQL-01 (`cacheIdentity`, ✅) and X-GQL-04 (Query field without `@cache`, 🧪);
  - add to the X-LAY-01 row the handles allowed by default;
  - recount the totals.

  Verify: a recount of the catalog rows matches the "Итого" line.
- [ ] 13.2 Update `README.md`: the rule table with all 29 default rules and their source levels; options `--config`, `--baseline`, `--generate-baseline` and the new formats; disabling rules in a project's own PHPStan setup through `ignoreErrors`. Verify: every rule ID in the README links to an existing page.

## 14. Acceptance

- [ ] 14.1 Run `magento-perf-check` on `~/projects/magento-2.4.9.ddev` twice: with default paths, and with `--paths=vendor/magento` (cold cache). Record findings per rule, notices, wall time and peak RSS in `docs/runs/m1-core.md`. Verify: exit code 0 or 1, and the test project is unchanged.
- [ ] 14.2 Spot-check up to 10 core findings per new rule, and again 10 for P-COL-02 after the fixes. Mark each TP or FP with a reason in `docs/runs/m1-core.md`, and list the rules that need a fix or a severity change before M2. Verify: every rule with findings has a spot-check table.
- [ ] 14.3 Run the full check: `composer test`, `bin/test-phpstan1`, `composer analyse`, and `openspec validate m1-mvp --strict`. Verify: all exit 0.

## Workflow follow-up

- List the changed files with links for the owner (no diffs in chat), commit, then archive the change with `/opsx:archive`.
