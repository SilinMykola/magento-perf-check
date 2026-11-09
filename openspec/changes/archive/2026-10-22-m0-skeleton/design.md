# Design

## Context

The repository has no code yet. See proposal.md (Why) for motivation, and specs/ for the required behavior.

Facts checked on 2026-10-21 that shape the approach:

- Test project `/path/to/magento-2.4.9`:
  - Locks `phpstan/phpstan` 1.12.33 (`require-dev: ^1.9`), `nikic/php-parser` v5.8.0 and `magento/magento-coding-standard` 40.
  - Has neither `bitexpert/phpstan-magento` nor `phpstan/extension-installer`.
  - Core lives in `vendor/magento` (about 410 packages), not in `app/code/Magento`. `app/code` holds 4 custom modules.
- Packagist:
  - `bitexpert/phpstan-magento` 0.44.0 (2026-09-14) and 0.43.0 require `phpstan/phpstan ^2.0`; the last 1.x-compatible line (0.32.0, 2024-08) requires `~1.12.0`.
  - Current `phpstan/phpstan` releases are 2.3.1 and 1.12.34.
- Host: PHP 8.5.11, Composer 2.10.3. The target code base requires PHP 8.1+ (SPEC section 8).

## Goals / Non-Goals

**Goals:**
- One architecture that both later rule families use unchanged: XML rules plug into the XML analyzer, PHP rules into the PHPStan extension, and both share rule metadata and the report.
- Prove it on core: a full run over `vendor/magento` finishes and produces counts and timing.
- Tests run against PHPStan 1.12 and 2.x.

**Non-Goals:**
- Configuration file `magento-perf-rules.neon`, XML `perf-ignore` comments, baseline, and SARIF/Checkstyle/GitHub/GitLab formats (all M1). In M0 the X-LAY-01 allowlist is a built-in default.
- Cross-method loop analysis through Collectors (M4).
- Precision calibration (M2). M0 counts findings but does not grade them.
- CI workflow files: the repository has no remote yet. Both PHPStan versions are checked locally by a script.

## Decisions

### D1. Runner runs PHPStan as a subprocess and merges its JSON output
`magento-perf-check`:
- Runs the XML analyzer in-process.
- Writes a temporary neon config into the system temp directory, never into the project. The config sets the analyzed paths, includes `extension.neon`, and sets the project's `vendor/autoload.php` as a bootstrap file. It also scans `generated/code` when that directory exists.
- Runs PHPStan with `--error-format=json --no-progress`.
- Parses the JSON output and keeps only messages that start with a known `[RULE-ID]` prefix.
- Merges these with the XML findings into one result model.

Alternative considered: calling PHPStan's internal API in-process. Rejected because the internal API differs between 1.12 and 2.x and is not covered by PHPStan's backward-compatibility promise; the CLI and its JSON format are stable in both versions.

The message prefix, rather than the JSON `identifier` field, is the key for mapping messages to rules. Whether PHPStan 1.12 JSON output contains `identifier` is unverified; the prefix works in both versions and also helps users who read raw PHPStan output.

To keep only this tool's rules, the generated config does not set a level. Filtering by prefix is the guarantee. If PHPStan's `customRulesetUsed` parameter turns built-in rules off in both versions, the generated config uses it to save time; this is checked in task 3.

Recorded as an ADR.

### D2. PHPStan 1.12 and 2.x from one code base
- Composer constraint: `phpstan/phpstan: ^1.12 || ^2.0`.
- Rules use only API present in both versions: `Rule`, `RuleErrorBuilder` with `->identifier()`, `Scope`, `ObjectType`, `isSuperTypeOf()`, and node classes such as `Foreach_`, `For_`, `While_`, `Do_`, `MethodCall`, `Closure` and `ArrowFunction`, which exist unchanged in php-parser 4 and 5.
- Where the APIs diverge, a small adapter isolates the difference. None is expected for the three M0 rules.
- PHPStan 2.x makes `->identifier()` mandatory; 1.12 already supports it.
- The tests run against both: the default dev install uses 2.x, and `bin/test-phpstan1` installs 1.12 into a scratch copy and runs the same suite.

Alternative considered: requiring 1.12 only (rejected, it cuts off projects that moved to 2.x) or 2.x only (rejected, it does not install into a standard Magento 2.4.9 project).

Recorded as an ADR.

### D3. bitExpert/phpstan-magento is suggested, not required
A hard dependency on it would fix the PHPStan major version: 0.44 needs 2.x, 0.32 needs 1.12, and nothing covers both. It is listed in `suggest`. The runner includes its `extension.neon` when it is installed next to the tool.

Without bitExpert, receivers created by a factory are typed from `generated/code` when the project has it. Otherwise they stay unknown, and the rules skip them: a false negative, never a false positive. The M0 run records how many P-COL-02 candidates are missed this way: the number of `getFirstItem()` calls on unknown types, as an analysis notice count.

Recorded as an ADR.

### D4. Loop context through a NodeVisitor attribute
A PHPStan-registered NodeVisitor marks each node inside a loop body with an attribute (`magentoPerfInLoop`). It covers:
- `foreach`, `for`, `while` and `do` bodies, excluding the `foreach` iterable and the `for` init.
- The body of a closure or arrow function passed directly as an argument to `array_map`, `array_walk` or a `->walk()` method call.

Rules read the attribute from the node; they do not walk parents. This is the standard way to give rules context in both PHPStan majors.

Alternative considered: a parent-node attribute plus an upward walk. Rejected because the `parent` attribute is no longer set by default in PHPStan 2.x.

### D5. P-COL-02 limit tracking is intra-method and per variable
For `getFirstItem()` on an `AbstractDb` subtype, the rule first walks the method-call chain to its root. A `setPageSize()` or `getSelect()->limit()` call in that chain clears the finding. If the root is a variable or property fetch, the rule checks whether an earlier statement in the same function calls `setPageSize()` or `getSelect()->limit()` on that same variable or property; if so, the finding is cleared.

SPEC section 4.2 also lists `setCurPage()`. In `AbstractDb::_renderLimit()`, however, the limit is applied only when a page size is set, so `setCurPage()` alone does not limit the query. M0 follows the code. The audit (task 1) verifies this against the pinned commit and corrects the SPEC wording.

Earlier statements are searched through the function's statements up to the call, without control-flow analysis. A `setPageSize` call inside an `if` still counts. This is a deliberate bias toward fewer false positives.

### D6. X-LAY-01 uses DOM with line numbers
- Layout files are loaded with `DOMDocument` (`LIBXML_NONET`, no external entities). Rules query them with XPath `//block[@cacheable='false']`.
- `DOMNode::getLineNo()` gives the line. Comments and `<argument>` text never match the XPath.
- Area comes from the path (`view/<area>/layout`, `app/design/<area>`). Module comes from the nearest `etc/module.xml` upward, or from the theme's `<Module_Name>` directory.

### D7. Rule metadata in one PHP registry
One registry class holds the metadata of every rule: ID, default severity, status, doc path and sources with their level. The runner reads it to assign severity and doc links. A PHPUnit test reads it to check that every rule has its doc page with a Basis section and no `2.4-develop` blob links.

Doc links in findings are `<base>/docs/rules/<ID>.md`. Until the repository is published, `<base>` is the absolute path of the tool's own `docs/` directory. The base is one constant, to be replaced with the public repository URL at M3.

### D8. Working names
- Composer package: `magento-perf-rules/magento-perf-rules`.
- PHP namespace: `MagentoPerfRules\`.

These are working names until the vendor and the name are decided (SPEC open question 1). Renaming before M3 touches `composer.json`, the namespace and docs only.

`license` is left out of `composer.json` until the license decision (SPEC open question 2).

### D9. Code compatibility
Source code is written for PHP 8.1: no readonly classes, no DNF types, no typed class constants. Self-analysis runs PHPStan at `max` on `src/`. PHPUnit is chosen in a version range that installs on both PHP 8.1 and 8.5.

## Risks / Trade-offs

- [Loading the project's Composer autoloader inside PHPStan pulls in Magento's `registration.php` files and may fail on host PHP 8.5 or without the container] → The M0 run on the test project is the check. On failure, fall back to `scanDirectories` without bootstrap and record it in the ADR.
- [PHPStan over all of `vendor/magento` may need several GB of memory and long run time] → The runner passes `--memory-limit` through (default `2G`) and keeps PHPStan's result cache in a temp directory keyed by project path. Time and peak memory go into the M0 run report.
- [PHPStan 1.12 running on host PHP 8.5 may emit deprecations or fail] → Checked in task 2. If it fails, the 1.12 test leg runs under PHP 8.4 (DDEV image or a Homebrew `php@8.4`) and the README states it.
- [Loop context misses loops in called methods] → Accepted for M0 (false negatives). Cross-method analysis is M4.
- [P-COL-02 per-variable tracking misses limits set through another variable that refers to the same collection] → Possible false positive. Listed under "Known false positives" in `docs/rules/P-COL-02.md` and measured in M2 calibration.
- [Source audit demotes a rule that M1 planned] → Intended by the source policy. Catalog totals in SPEC are updated in the same commit.

## Migration Plan

Not applicable: first code in the repository.

## Open Questions

- Final Composer vendor and package name, and license (SPEC open questions 1–2). They do not block M0; the working names from D8 are used until then.
