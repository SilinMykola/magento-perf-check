# Design

## Context

M0 left a working pipeline:
- the XML analyzer (`FileDiscovery`, `XmlAnalyzer`, `XmlRule`);
- the PHPStan extension (two NodeVisitors, two rules) and the subprocess runner;
- the rule registry, the table and JSON reporters, and test-code exclusion.

See proposal.md for scope, and `docs/adr/0001`–`0003` for decisions already taken. The facts from the M0 core run that drive this design are in `docs/runs/m0-core.md`.

Facts checked on 2026-10-22:
- Magento 2.4.9 has no `nette/neon` (lock file).
- `webonyx/graphql-php` constraints in `magento/framework`:
  - 2.4.4: `~14.11.3`;
  - 2.4.5: `~14.11.6`;
  - 2.4.6–2.4.8: `^15.0`;
  - 2.4.9: `^15.0`, with 15.34.0 locked.
- Core `Magento_Customer` `etc/frontend/sections.xml` invalidates `*` on `customer/account/logout`, `loginPost`, `createPost` and `editPost`.
- 83 of 85 core `db_schema.xml` files contain a primary constraint. Modules such as `Magento_GiftMessage` add columns to `sales_order` without one, so X-DB-01 cannot judge a file on its own.

## Goals / Non-Goals

**Goals:**
- Every rule reads cross-file facts from one project index instead of scanning on its own.
- Every rule setting (disable, severity, allowlist) is applied in one place, for XML and PHP findings alike.
- Each new rule keeps the M0 bar: positive and negative fixtures, a test checked against broken behavior, a docs page with Basis, and green on PHPStan 1.12 and 2.x.

**Non-Goals:**
- Measured precision and thresholds (M2). M1 records counts and spot checks only.
- The "storefront query without `@cache`" half of X-GQL-01 (see D11).
- Configuration inside the project's `phpstan.neon` (SPEC 3.3 mentions it as an alternative). M1 supports only `magento-perf-rules.neon`.
- GitHub and GitLab report formats (M2).

## Decisions

### D1. Project index built once, from Composer metadata, shared through a cache file
`ProjectIndex` lists module roots without walking all of `vendor`:
- every `app/code/<Vendor>/<Module>`;
- every package in `vendor/composer/installed.json` whose install path has `etc/module.xml`.

It reads only the files it needs: `etc/**/di.xml`, `etc/**/events.xml`, `etc/mview.xml` and `etc/db_schema.xml`. The result is serialized to `<temp>/magento-perf-rules/<md5(project)>/index-<fingerprint>.json`. The fingerprint is an md5 over the paths, sizes and mtimes of those files, so any change rebuilds the index.

- **Runner:** builds the index first and passes its project root to PHPStan as the parameter `magentoPerf.projectRoot`.
- **PHPStan:** a `ProjectIndex` service loads the same cache file lazily. In a project's own PHPStan setup, without the runner, it builds the index itself from `%currentWorkingDirectory%`.

Alternative considered: passing the index as a NEON parameter. Rejected: a full Magento index would make a very large config, and the cache file lets parallel workers share one build.

Recorded as ADR 0004.

### D2. Configuration file read with nette/neon
- **Format:** `magento-perf-rules.neon`, parsed with `nette/neon ^3.4`. NEON matches the PHPStan files Magento developers already know.
- **Validation:** a hand-written validator checks the `magentoPerf` section and fails on unknown keys, which gives precise messages.
- **Why not PHPStan's bundled NEON parser:** the phar prefixes its dependencies, so they are not public API.

Recorded as ADR 0005.

### D3. Rule settings applied after analysis, in one place
Disabled rules and severity overrides are applied to the merged finding list, for XML and PHP alike, before the baseline and before `--fail-on`. PHP rules still run when disabled, which costs little and keeps PHPStan's config simple.

Users who run PHPStan directly disable a rule the PHPStan way, with `ignoreErrors` by identifier. The README documents this.

### D4. PHP findings are mapped to rules by identifier
M0 maps PHPStan messages to rules by their `[RULE-ID]` prefix. Since then, ADR 0003 recorded that the JSON output of both PHPStan majors contains `identifier`. M1 needs severity per variant: P-DI-01 is `error` for sessions and `warning` for the cookie manager. The registry therefore maps identifiers to a rule ID and a severity, for example:
- `magentoPerf.sessionWithoutProxy` → P-DI-01, error;
- `magentoPerf.cookieManagerWithoutProxy` → P-DI-01, warning.

The `[RULE-ID]` message prefix stays, for people reading raw PHPStan output, and serves as a fallback when the identifier is missing.

### D5. Collection state tracking replaces CollectionLimitVisitor
The M0 visitor tracked only "limit set" per root key (`$var`, `$this->prop`). It becomes `CollectionStateVisitor`, which tracks three facts per root key in source order within a function:
- **limited:** `setPageSize`, `getSelect()->limit|limitPage`;
- **loaded:** `load()`, `getItems()`, `->count()`, `count($x)` and `foreach ($x as …)`;
- **unique filter:** `addIdFilter()`, and `addFieldToFilter`/`addAttributeToFilter` with a literal field in {`entity_id`, `sku`, `increment_id`, `username`} (optionally `alias.` prefixed) and a scalar or `['eq' => …]` value.

It marks `getFirstItem()` calls (P-COL-02) and `count()` calls (P-COL-01) with the facts that apply. A chain is checked at the node, as in M0.

`getSize()` is not "loaded": it runs a COUNT query and leaves `_items` empty.

### D6. Loop-created collections tracked per loop body
`LoopContextVisitor` already marks loop bodies. A small addition records variables assigned from `->create()` inside the current loop body; the set is cleared when the body is left.

P-LOOP-02 then reports `load|getItems|getSize|getFirstItem|count` on a database collection when either holds:
- the chain root is a `create()` call;
- the root is a variable recorded in the same loop body.

### D7. Plugin, observer and controller detection
- **Observers:** implement `Magento\Framework\Event\ObserverInterface`.
- **Controllers:** implement `Magento\Framework\App\ActionInterface`.
- **GraphQL resolvers:** implement `ResolverInterface` or `BatchResolverInterface`.
- **Plugins:** have no interface, so the project index's plugin list decides, plus a short name ending in `Plugin` as a fallback (P-PLG-01 spec). P-FPC-10 uses only the index, because a wrong guess there yields an `error`.

### D8. Templates analyzed through PHPStan
The runner adds `fileExtensions: [php, phtml]` to its generated config. T-01 is a PHPStan rule on `MethodCall` named `createBlock` whose receiver is a `getLayout()` call, active only when the file ends in `.phtml`. Untyped `$block` variables in templates are no problem: T-01 is syntactic, and typed rules skip unknown receivers.

### D9. XML suppression through DOM siblings
Before a rule's finding is kept, the analyzer looks at the element's `previousSibling`. It skips whitespace-only text nodes and expects a comment matching `^\s*perf-ignore\s+(<ID>)\s*:\s*(\S.*)$`. The same element can carry several comments, one per rule.

The check runs centrally in `XmlAnalyzer`, so individual rules return elements and know nothing about suppression. The `Finding` gains an optional element reference for this.

### D10. Baseline as readable JSON
The baseline file looks like this:

```json
{"version": 1, "entries": [{"ruleId": "...", "file": "...", "message": "...", "count": 2}]}
```

Entries are sorted by file, rule and message, so diffs stay readable in code review. Identity is `(ruleId, file, message)`. Line numbers are excluded, so moving code does not resurface findings. Messages contain class and method names but no line numbers, so they are stable.

Order of filters: configuration (disable, severity), then baseline, then `--fail-on`.

### D11. X-GQL-01 ships the `cacheIdentity` half only
The SPEC row also flags "storefront query without `@cache`". The audit found no A/B source that tells storefront queries from private ones. Core leaves many `Query` fields without `@cache` on purpose, such as cart and customer queries, which are private.

M1 implements only the sourced, low-false-positive half: `@cache` on a `Query` field without `cacheIdentity` and without `cacheable: false`.

The owner confirmed this on 2026-10-22. The other half becomes a separate experimental rule, X-GQL-04 "Query field without `@cache`" (🧪), and the SPEC catalog is split accordingly.

### D12. GraphQL schemas parsed with webonyx/graphql-php
- **Dependency:** `^14.11 || ^15.0`, matching every Magento 2.4.4–2.4.9 constraint.
- **Parsing:** `GraphQL\Language\Parser::parse()` parses SDL fragments with `extend type Query` and custom directives without schema validation. Lines come from the field node's `loc`.

Alternative considered: a regex scan. Rejected: multi-line field definitions and nested arguments make it fragile.

Recorded as ADR 0006.

### D13. SARIF and Checkstyle
- **SARIF 2.1.0:**
  - one run; `tool.driver.name` `magento-perf-rules`;
  - `rules` built from the registry: `id`, `shortDescription` = title, `helpUri` = doc link, `defaultConfiguration.level`;
  - results use `artifactLocation.uri` relative to `%SRCROOT%` (`originalUriBaseIds` points at the project) and `region.startLine`.
  - `helpUri` must be a URI. Until the repository is public, it is a `file://` URI of the rule page; it switches to the public URL at M3 (same constant as `RuleRegistry::DOCS_BASE`).
- **Checkstyle:** `<checkstyle version="4.3">`, `<file name>` relative to the project, `<error line severity message source>`, with `source` = rule ID. `info` maps to Checkstyle `info`.

## Risks / Trade-offs

- **[Project index and P-DI-01 on core]** Core injects sessions widely and proxies most of them in `di.xml`. Any gap in proxy resolution (virtual types, `<type>` on a parent class) turns into false `error`s. → Virtual types are resolved through `<virtualType type=...>`. Proxies configured for a parent class's argument apply to subclasses that do not override it. Core counts and a spot check go into `docs/runs/m1-core.md`, and P-DI-01 is downgraded if it is noisy.
- **[nette/neon or webonyx version conflicts in other projects]** A project that pins `nette/neon` 2.x or `webonyx/graphql-php` below 14.11 cannot install the tool. → Accepted. Neither occurs in Magento 2.4.4–2.4.9. The `--project` mode in a separate checkout still works.
- **[26 rules in one change]** Large review surface. → Tasks are grouped by rule family. Each group lands its own fixtures, docs and checks against broken behavior, so groups can be reviewed and committed one at a time.
- **[Severity per identifier]** A rule whose identifier is missing from the registry would get no severity. → A unit test asserts that every identifier emitted by `extension.neon` rules is mapped.
- **[`.phtml` analysis cost]** Adding `phtml` to `fileExtensions` grows the core run. → The cost is measured in the acceptance run.

## Migration Plan

- Rule IDs and identifiers from M0 do not change.
- `CollectionLimitVisitor` is replaced; its attribute name changes internally, which is not public API.
- Existing configs: none exist yet.

