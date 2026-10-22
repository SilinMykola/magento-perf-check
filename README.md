# magento-perf-rules

Static analysis for Magento 2 and Mage-OS performance and Full Page Cache anti-patterns. It combines type-aware PHPStan rules with checks for layout XML. Later milestones add `sections.xml`, `events.xml`, `di.xml`, GraphQL schemas, message queues and indexers.

**Status:** milestone M0 (skeleton). Three rules work end to end; the full catalog and the roadmap are in [`docs/SPEC.md`](docs/SPEC.md). The package is not on Packagist yet.

## Requirements

- PHP 8.1 or newer.
- PHPStan 1.12 or 2.x. Stock Magento 2.4.9 ships 1.12, and both majors are supported (see [ADR 1](docs/adr/0001-support-phpstan-1-12-and-2.md)).

## Usage

Until the first release, run the tool from a checkout of this repository:

```sh
git clone <this repository> magento-perf-rules
cd magento-perf-rules
composer install
php bin/magento-perf-check --project=/path/to/magento
```

The tool reads the project and never writes into it. It needs the project's `vendor/autoload.php`, so run `composer install` in the project first. PHP and XML are analyzed on the host; no database or container is needed.

| Option | Meaning |
|---|---|
| `--project=<path>` | Magento project to analyze. Default: the current directory. |
| `--paths=<p1,p2>` | Paths relative to the project. Default: `app/code,app/design`. Paths under `vendor/magento` or `vendor/mage-os` are analyzed only when given here, for example `--paths=vendor/magento`. |
| `--include-tests` | Also analyze test code. By default `Test/` and `dev/tests/` directories below the analyzed paths are skipped. A path given in `--paths` that is itself test code is always analyzed. |
| `--format=table\|json` | Output format. Default: `table`. |
| `--fail-on=error\|warning\|info` | Lowest severity that makes the command exit with 1. Default: `error`. |
| `--memory-limit=<value>` | Memory limit for PHPStan. Default: `2G`. |

To check one module or folder, pass it in `--paths`, for example `--paths=app/code/Acme/Promo`. A single file works too.

Exit codes:
- `0`: no finding at or above `--fail-on`.
- `1`: at least one such finding.
- `2`: the tool itself failed, for example invalid options, a path that is not a Magento project, or a PHPStan crash.

Some files cannot be analyzed, such as XML that does not parse or PHP with syntax errors. They are skipped and counted as analysis notices in the summary; `--format=json` lists them.

### Configuration file

Put `magento-perf-rules.neon` in the project root, or pass another file with `--config=<file>`. All keys are optional:

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

### Inside an existing PHPStan setup

The PHP rules are a regular PHPStan extension. With [phpstan/extension-installer](https://github.com/phpstan/extension-installer) they are picked up automatically; otherwise include `extension.neon` in your `phpstan.neon`. They report at every level. To suppress one finding, use its identifier and give a reason:

```php
// @phpstan-ignore magentoPerf.loopModelLoad (the caller passes at most 3 ids)
```

PHPStan types collections created through generated factories only when `generated/code` exists or [bitexpert/phpstan-magento](https://github.com/bitExpert/phpstan-magento) is installed. Without either, such calls are skipped rather than reported ([ADR 2](docs/adr/0002-bitexpert-phpstan-magento-as-suggestion.md)).

## Rules

| ID | Finds | Severity | Sources |
|---|---|---|---|
| [X-LAY-01](docs/rules/X-LAY-01.md) | `cacheable="false"` on a frontend layout block: every page with that handle bypasses Full Page Cache | error (`default`, `catalog_*`, `cms_*`), warning (other handles) | A, B |
| [P-COL-02](docs/rules/P-COL-02.md) | `getFirstItem()` on a DB collection without `setPageSize()`: the query has no `LIMIT` | error | B |
| [P-LOOP-01](docs/rules/P-LOOP-01.md) | Model `load()` or repository `get()`/`getById()` inside a loop | error | A, B |

## What our rules are based on

Every rule cites at least one primary source:
- **A:** official Adobe or Hyvä documentation.
- **B:** Magento core code pinned to a specific commit (currently [`a385d02f`](https://github.com/magento/magento2/commit/a385d02f60ee6dfa052f12cdc7d82e956581fba5)), or a magento/magento2 issue with a confirmed reproduction or measurements.

Community sources (**C**) are used only as supplementary explanation. A rule with only C sources stays experimental and is off by default.

Each rule page ends with a **Basis** section listing its sources, their levels and what each one confirms. The audit of the whole catalog is in [`docs/source-audit.md`](docs/source-audit.md).

Precision has not been measured yet. Calibration on Magento core, Mage-OS and popular open-source modules is milestone M2, and its results will be published in `docs/calibration.md`.

Static analysis gives heuristics, not guarantees: treat each finding as a reason to check, not a verdict.

## Development

```sh
composer test        # PHPUnit, with PHPStan 2.x
bin/test-phpstan1    # the same suite against PHPStan 1.12, in a temporary copy
composer analyse     # PHPStan at level max on src/
```

Architectural decisions are in [`docs/adr/`](docs/adr/).
