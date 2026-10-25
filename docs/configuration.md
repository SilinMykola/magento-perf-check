# Running and configuring

## Requirements

- PHP 8.1 or newer.
- PHPStan 1.12 or 2.x. Stock Magento 2.4.9 ships 1.12, and both majors are supported (see [ADR 1](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0001-support-phpstan-1-12-and-2.md)).

## Installing

Install it as a development dependency of the Magento project:

```sh
composer require --dev silin/magento-perf-check
vendor/bin/magento-perf-check
```

The tool reads the project and never writes into it. PHP and XML are analyzed on the host; no database or container is needed.

To analyze a project without adding a dependency to it, install the tool in its own directory and point it at the project with `--project`. The project still needs its own `composer install`, because PHPStan loads the project's `vendor/autoload.php`:

```sh
git clone https://github.com/SilinMykola/magento-perf-check.git
cd magento-perf-check
composer install
php bin/magento-perf-check --project=/path/to/magento
```

## Options

| Option | Meaning |
|---|---|
| `--project=<path>` | Magento project to analyze. Default: the current directory. |
| `--paths=<p1,p2>` | Paths relative to the project. Default: `app/code,app/design`. Paths under `vendor/magento` or `vendor/mage-os` are analyzed only when given here, for example `--paths=vendor/magento`. |
| `--include-tests` | Also analyze test code. By default `Test/` and `dev/tests/` directories below the analyzed paths are skipped. A path given in `--paths` that is itself test code is always analyzed. |
| `--format=table\|json\|sarif\|checkstyle\|github` | Output format. Default: `table`. `sarif` is SARIF 2.1.0 (GitHub code scanning); `checkstyle` suits Jenkins and similar tools; `github` prints GitHub Actions annotations. |
| `--fail-on=error\|warning\|info` | Lowest severity that makes the command exit with 1. Default: `error`. |
| `--config=<file>` | Configuration file. Default: `magento-perf-check.neon` in the project root, when it exists. |
| `--baseline=<file>`, `--generate-baseline=<file>` | Report only new findings; see [Baseline](baseline.md). |
| `--memory-limit=<value>` | Memory limit for PHPStan. Default: `2G`. |
| `--version` | Print the version. |

To check one module or folder, pass it in `--paths`, for example `--paths=app/code/Acme/Promo`. A single file works too.

Exit codes:
- `0`: no finding at or above `--fail-on`.
- `1`: at least one such finding.
- `2`: the tool itself failed, for example invalid options, a path that is not a Magento project, or a PHPStan crash.

Some files cannot be analyzed, such as XML that does not parse or PHP with syntax errors. They are skipped and counted as analysis notices in the summary; `--format=json` lists them.

## Configuration file

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

The tool rejects unknown keys, unknown rule IDs and invalid values with exit code 2, so a typo never silently changes the analysis. Disabled rules and severity overrides apply to XML and PHP findings alike, and to the `--fail-on` decision. [Experimental rules](rules.md#experimental-off-by-default) are off until you set `enabled: true`.

## Inside an existing PHPStan setup

The PHP rules are a regular PHPStan extension. With [phpstan/extension-installer](https://github.com/phpstan/extension-installer) they are picked up automatically; otherwise include `vendor/silin/magento-perf-check/extension.neon` in your `phpstan.neon`. They report at every level. To suppress one finding, use its identifier and give a reason:

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

PHPStan types collections created through generated factories only when `generated/code` exists or [bitexpert/phpstan-magento](https://github.com/bitExpert/phpstan-magento) is installed. Without either, such calls are skipped rather than reported ([ADR 2](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0002-bitexpert-phpstan-magento-as-suggestion.md)).
