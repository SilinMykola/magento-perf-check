# magento-perf-check

Static analysis for Magento 2 and Mage-OS performance and Full Page Cache anti-patterns. It combines type-aware PHPStan rules with checks for layout XML, `sections.xml`, `events.xml`, `di.xml`, `db_schema.xml`, message queues, indexers and GraphQL schemas.

**Status:** milestone M2 done: 29 rules are implemented and [calibrated](docs/calibration.md) on Magento core and popular modules; 24 of them are on by default. The full catalog and the roadmap are in [`docs/SPEC.md`](docs/SPEC.md). The package is not on Packagist yet.

## Requirements

- PHP 8.1 or newer.
- PHPStan 1.12 or 2.x. Stock Magento 2.4.9 ships 1.12, and both majors are supported (see [ADR 1](docs/adr/0001-support-phpstan-1-12-and-2.md)).

## Usage

Until the first release, run the tool from a checkout of this repository:

```sh
git clone https://github.com/SilinMykola/magento-perf-check.git
cd magento-perf-check
composer install
php bin/magento-perf-check --project=/path/to/magento
```

The tool reads the project and never writes into it. It needs the project's `vendor/autoload.php`, so run `composer install` in the project first. PHP and XML are analyzed on the host; no database or container is needed.

| Option | Meaning |
|---|---|
| `--project=<path>` | Magento project to analyze. Default: the current directory. |
| `--paths=<p1,p2>` | Paths relative to the project. Default: `app/code,app/design`. Paths under `vendor/magento` or `vendor/mage-os` are analyzed only when given here, for example `--paths=vendor/magento`. |
| `--include-tests` | Also analyze test code. By default `Test/` and `dev/tests/` directories below the analyzed paths are skipped. A path given in `--paths` that is itself test code is always analyzed. |
| `--format=table\|json\|sarif\|checkstyle\|github` | Output format. Default: `table`. `sarif` is SARIF 2.1.0 (GitHub code scanning); `checkstyle` suits Jenkins and similar tools; `github` prints GitHub Actions annotations. |
| `--fail-on=error\|warning\|info` | Lowest severity that makes the command exit with 1. Default: `error`. |
| `--memory-limit=<value>` | Memory limit for PHPStan. Default: `2G`. |

To check one module or folder, pass it in `--paths`, for example `--paths=app/code/Acme/Promo`. A single file works too.

Exit codes:
- `0`: no finding at or above `--fail-on`.
- `1`: at least one such finding.
- `2`: the tool itself failed, for example invalid options, a path that is not a Magento project, or a PHPStan crash.

Some files cannot be analyzed, such as XML that does not parse or PHP with syntax errors. They are skipped and counted as analysis notices in the summary; `--format=json` lists them.

### Configuration file

Put `magento-perf-check.neon` in the project root, or pass another file with `--config=<file>`. All keys are optional:

```neon
magentoPerf:
    # Paths to analyze, relative to the project. --paths on the command line wins.
    paths:
        - app/code
        - app/design
    # Also analyze Test/ and dev/tests/ directories. --include-tests turns it on too.
    includeTests: false
    rules:
        # Turn a rule off, or change its severity (error, warning, info).
        P-COL-02:
            enabled: false
        X-LAY-01:
            severity: warning
    layout:
        # Extra handle prefixes that X-LAY-01 must not report.
        allowlistHandles:
            - acme_account_
        # true: use only the prefixes above instead of adding them to the default list.
        replaceAllowlist: false
```

The tool rejects unknown keys, unknown rule IDs and invalid values with exit code 2, so a typo never silently changes the analysis. Disabled rules and severity overrides apply to XML and PHP findings alike, and to the `--fail-on` decision.

### Baseline for existing projects

To adopt the tool on a project with many existing findings, record them once and fail only on new ones:

```sh
php bin/magento-perf-check --project=/path/to/magento --generate-baseline=perf-baseline.json
php bin/magento-perf-check --project=/path/to/magento --baseline=perf-baseline.json
```

The file is written to the current directory, never into the analyzed project. Commit it next to your CI configuration.

A finding is matched by rule, file and message, not by line, so moving code does not resurface it. A second identical finding in the same file is new and gets reported. The summary shows how many findings the baseline hid, and how many entries matched nothing because those issues were fixed. Regenerate the file to drop those entries.

### Inside an existing PHPStan setup

The PHP rules are a regular PHPStan extension. With [phpstan/extension-installer](https://github.com/phpstan/extension-installer) they are picked up automatically; otherwise include `extension.neon` in your `phpstan.neon`. They report at every level. To suppress one finding, use its identifier and give a reason:

```php
// @phpstan-ignore magentoPerf.loopModelLoad (the caller passes at most 3 ids)
```

To turn a rule off in your own PHPStan setup, ignore its identifier (the identifiers are listed on the rule pages):

```neon
parameters:
    ignoreErrors:
        -
            identifier: magentoPerf.loopSave
```

The XML rules and `magento-perf-check.neon` only apply when you run `magento-perf-check`. To analyze templates in your own setup, add `phtml` to PHPStan's `fileExtensions`.

PHPStan types collections created through generated factories only when `generated/code` exists or [bitexpert/phpstan-magento](https://github.com/bitExpert/phpstan-magento) is installed. Without either, such calls are skipped rather than reported ([ADR 2](docs/adr/0002-bitexpert-phpstan-magento-as-suggestion.md)).

## CI and Git hooks

- **GitHub Actions:** copy [`docs/ci/github-actions.yml`](docs/ci/github-actions.yml) to `.github/workflows/`. It uploads SARIF to code scanning and annotates pull requests through `--format=github`. Actions are pinned to commit SHAs. The repo.magento.com credentials go into a `COMPOSER_AUTH` secret.
- **GrumPHP:** merge [`docs/ci/grumphp.yml`](docs/ci/grumphp.yml) into your `grumphp.yml`. It is a plain `shell` task: the commit fails when a finding reaches `--fail-on` (default `error`). GrumPHP runs it on the files tracked by git. Checked with GrumPHP 2.26.0: the task fails on a project with findings and passes on a clean one.
- **Other CI:** use `--format=checkstyle` or `--format=sarif` and the exit codes (0 clean, 1 findings, 2 tool error).

## Rules

24 rules in the default set and 5 experimental ones, after [calibration](docs/calibration.md) on Magento core and popular modules. Each links to its page with examples, suppression and the sources it rests on. Rules marked ⏳ (after calibration) in the [SPEC catalog](docs/SPEC.md) are not implemented yet.

| ID | Finds | Severity | Sources |
|---|---|---|---|
| [X-LAY-02](docs/rules/X-LAY-02.md) | ESI ttl on a block in an entity-specific handle | error | A, B |
| [P-LOOP-01](docs/rules/P-LOOP-01.md) | Model load() or repository get()/getById() inside a loop | error | A, B |
| [P-LOOP-02](docs/rules/P-LOOP-02.md) | Collection created and loaded inside a loop | error | A, B |
| [P-LOOP-03](docs/rules/P-LOOP-03.md) | save() or delete() of a model or repository inside a loop | info | A, B |
| [P-LOOP-07](docs/rules/P-LOOP-07.md) | Composite product type methods inside a loop | warning | B |
| [P-COL-01](docs/rules/P-COL-01.md) | count() on a database collection instead of getSize() | warning | B |
| [P-DI-01](docs/rules/P-DI-01.md) | Session or cookie manager injected without a Proxy | warning (session), info (cookie manager) | A, B |
| [P-DI-03](docs/rules/P-DI-03.md) | Event dispatched in a constructor | error | A |
| [P-DI-06](docs/rules/P-DI-06.md) | Direct ObjectManager::getInstance() in PHP classes | info | A |
| [P-FPC-01](docs/rules/P-FPC-01.md) | $_isScopePrivate in a block | info | A, B |
| [P-FPC-05](docs/rules/P-FPC-05.md) | No-cache or private headers on cacheable requests | warning | A, B |
| [P-FPC-06](docs/rules/P-FPC-06.md) | State change in a GET-only controller | error | A |
| [P-FPC-09](docs/rules/P-FPC-09.md) | Full page cache or all caches cleaned from code | error | B |
| [P-FPC-10](docs/rules/P-FPC-10.md) | reindexAll() in an observer, plugin or controller | error | A, B |
| [T-01](docs/rules/T-01.md) | getLayout()->createBlock() in a template | info | A |
| [X-EVT-01](docs/rules/X-EVT-01.md) | Observer on model_load_after or <entity>_load_after | warning | A, B |
| [X-EVT-02](docs/rules/X-EVT-02.md) | Observer on generic model save/delete events | warning | B |
| [X-EVT-03](docs/rules/X-EVT-03.md) | Observer on per-block render events | warning | A, B |
| [X-SEC-01](docs/rules/X-SEC-01.md) | sections.xml invalidates all sections or on every POST | error (`name="*"`), warning (all sections) | A, B |
| [X-GQL-01](docs/rules/X-GQL-01.md) | GraphQL Query field with @cache but no cacheIdentity | error | A, B |
| [X-MQ-01](docs/rules/X-MQ-01.md) | Message queue topic synchronous by default | warning | A, B |
| [X-IDX-01](docs/rules/X-IDX-01.md) | Indexer without an mview subscription | warning | A, B |
| [X-SETUP-01](docs/rules/X-SETUP-01.md) | Heavy work in Setup/Recurring or RecurringData | warning | A, B |
| [X-DB-01](docs/rules/X-DB-01.md) | db_schema.xml table without a primary key | error | A |

### Experimental (off by default)

Calibration measured these below their precision threshold. Enable one with `enabled: true` in `magento-perf-check.neon`; with PHPStan directly, also include `extension-experimental.neon`.

| ID | Finds | Severity | Sources |
|---|---|---|---|
| [X-LAY-01](docs/rules/X-LAY-01.md) | Block with cacheable="false" in a frontend layout | error (`default`, `catalog_*`, `cms_*`), warning (other handles) | A, B |
| [P-COL-02](docs/rules/P-COL-02.md) | getFirstItem() on a database collection without setPageSize() | error | B |
| [P-DI-02](docs/rules/P-DI-02.md) | Constructor does more than assign dependencies | warning | A |
| [P-FPC-02](docs/rules/P-FPC-02.md) | Block or view model reads customer data from the session | warning | A, B |
| [P-PLG-01](docs/rules/P-PLG-01.md) | around plugin where before/after would do | warning | A |

## What our rules are based on

Every rule cites at least one primary source:
- **A:** official Adobe or Hyvä documentation.
- **B:** Magento core code pinned to a specific commit (currently [`a385d02f`](https://github.com/magento/magento2/commit/a385d02f60ee6dfa052f12cdc7d82e956581fba5)), or a magento/magento2 issue with a confirmed reproduction or measurements.

Community sources (**C**) are used only as supplementary explanation. A rule with only C sources stays experimental and is off by default.

Each rule page ends with a **Basis** section listing its sources, their levels and what each one confirms. The audit of the whole catalog is in [`docs/source-audit.md`](docs/source-audit.md).

Precision is measured on Magento 2.4.9 core and the 25 most-downloaded Packagist modules: [`docs/calibration.md`](docs/calibration.md) gives the method, per-rule counts, precision and the decision for each rule. Rules below their threshold were downgraded or made experimental; patterns left to fix are in [`docs/calibration/follow-ups.md`](docs/calibration/follow-ups.md).

Static analysis gives heuristics, not guarantees: treat each finding as a reason to check, not a verdict.

## Development

```sh
composer test        # PHPUnit, with PHPStan 2.x
bin/test-phpstan1    # the same suite against PHPStan 1.12, in a temporary copy
composer analyse     # PHPStan at level max on src/
```

Architectural decisions are in [`docs/adr/`](docs/adr/).
