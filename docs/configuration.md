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

To analyze a project without adding a dependency to it, install the tool in its own directory and point it at the project with `--project`. The host needs PHP 8.1 or newer whatever the project runs on. The project still needs its own `composer install`, because PHPStan loads the project's `vendor/autoload.php`:

```sh
git clone https://github.com/SilinMykola/magento-perf-check.git
cd magento-perf-check
composer install
php bin/magento-perf-check --project=/path/to/magento
```

## Magento versions

The code has no check of the Magento version. Precision is calibrated on Magento 2.4.9 only.

| Magento | `phpstan/phpstan` in the stock project | `composer require` | Own clone with `--project` |
|---|---|---|---|
| 2.4.9 | `^1.9` | checked | checked |
| 2.4.6 to 2.4.8 | `^1.9` | not tried | not tried |
| 2.4.5 | `^1.6.8` | not tried | not tried |
| 2.4.4 | `~1.2.0` | conflict | ran on `module-cms` |
| 2.4.3 | `^0.12.77` | conflict | ran on `module-cms` |
| 2.4.0 to 2.4.2 | `>=0.12.3 <=0.12.23` | below what the tool accepts | ran on `module-cms` of 2.4.0 |
| 2.3 | not checked | not checked | not checked |

The tool needs PHPStan 1.12 or 2.x. On 2.4.5 to 2.4.8 the constraint allows 1.12, but a project locked to an older 1.x has to update PHPStan for the install to resolve. Findings on versions other than 2.4.9 were not graded ([check](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/runs/older-magento.md)).

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
| `--output=<file>` | Write the report, in the chosen `--format`, to `<file>` (relative to the current directory) instead of the console, and print a one-line summary. The exit code is the same. The file's directory must exist. |
| `--memory-limit=<value>` | Memory limit for PHPStan. Default: `2G`. |
| `--version` | Print the version. |

To check one module or folder, pass it in `--paths`, for example `--paths=app/code/Acme/Promo`. A single file works too.

Exit codes:
- `0`: no finding at or above `--fail-on`.
- `1`: at least one such finding.
- `2`: the tool itself failed or the result cannot be trusted: invalid options, a path that is not a Magento project, nothing to analyze in the given paths, or a PHPStan crash. When a PHPStan worker dies or hits `--memory-limit`, PHPStan drops its findings, so the report may hold only XML findings. `"incomplete": true` marks it in `--format=json`, the reason goes to stderr, and no baseline is written.

Some files cannot be analyzed, such as XML that does not parse, PHP with syntax errors, or a class PHPStan cannot reflect. They are skipped and counted as analysis notices in the summary; `--format=json` lists them. With `--format=sarif`, `checkstyle` or `github`, or with `--output`, that summary goes to stderr.

A module set to `0` in the `modules` list of `app/etc/config.php` does not run, so it is not analyzed: one `disabled-module` notice names it when it lies in the analyzed paths, and its configuration does not count for other modules (a Proxy in its `di.xml`, for example). The file is read as text, not run. A module missing from that list counts as enabled.

## Security

Analysis runs code from the project. PHPStan loads the project's `vendor/autoload.php`, which in a stock Magento project requires the `registration.php` of every module in `app/code`. Do not analyze code you do not trust outside a sandbox.

## Temporary files

The generated PHPStan configuration, its result cache and the project index live in `magento-perf-check-<uid>/<md5 of the project path>/` under the system temp directory, which only your user can read. Without PHP's posix extension the directory has no `<uid>` and its owner is not checked. The cache can reach several hundred MB for a large project and is not cleaned automatically. Deleting the directory is safe; the next run is slower. Set `TMPDIR` to move it.

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
        X-LAY-03:
            severity: warning
    layout:
        # Extra handle prefixes that X-LAY-01 and X-LAY-03 must not report.
        allowlistHandles:
            - acme_account_
        # true: use only the prefixes above instead of adding them to the default list.
        replaceAllowlist: false
```

The tool rejects unknown keys, unknown rule IDs and invalid values with exit code 2, so a typo never silently changes the analysis. Disabled rules and severity overrides apply to XML and PHP findings alike, and to the `--fail-on` decision. [Experimental rules](rules.md#experimental-off-by-default) are off until you set `enabled: true`.

## Inside an existing PHPStan setup

PHPStan's result cache does not see XML here: after changing `di.xml` or another configuration file, run `phpstan clear-result-cache`. The `magento-perf-check` command handles this itself.

The PHP rules are a regular PHPStan extension. With [phpstan/extension-installer](https://github.com/phpstan/extension-installer) they are picked up automatically; otherwise include `vendor/silin/magento-perf-check/extension.neon` in your `phpstan.neon`. They report at every level. The `magento-perf-check` command installed in the project works with the installer too. Other extensions that the installer loads, such as `phpstan/phpstan-strict-rules`, then run in its analysis as well, and their errors show as `phpstan` notices. To suppress one finding, use its identifier and give a reason:

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

`create()` of a generated factory is typed from the factory's name, so `generated/code` is not needed for it. Generated proxies and extension attributes are typed only when `generated/code` exists or [bitexpert/phpstan-magento](https://github.com/bitExpert/phpstan-magento) is installed. Without either, calls on them are skipped rather than reported ([ADR 2](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0002-bitexpert-phpstan-magento-as-suggestion.md)).
