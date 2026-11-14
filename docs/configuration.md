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

Only the [upgrade rules](#upgrade-rules) look at the Magento version. Precision is calibrated on Magento 2.4.9 only.

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
| `--target-version=<version>` | Also run the [upgrade rules](#upgrade-rules) for changes up to this Magento version, such as `2.4.9` or `2.4.8-p3`. An invalid version exits with code 2. |
| `--memory-limit=<value>` | Memory limit for PHPStan. Default: `2G`. |
| `--version` | Print the version. |

To check one module or folder, pass it in `--paths`, for example `--paths=app/code/Acme/Promo`. A single file works too.

Exit codes:
- `0`: no finding at or above `--fail-on`.
- `1`: at least one such finding.
- `2`: the tool itself failed or the result cannot be trusted: invalid options, a path that is not a Magento project, nothing to analyze in the given paths, or a PHPStan crash. When a PHPStan worker dies or hits `--memory-limit`, PHPStan drops its findings, so the report may hold only XML findings. `"incomplete": true` marks it in `--format=json`, the reason goes to stderr, and no baseline is written.

Some files cannot be analyzed, such as XML that does not parse, PHP with syntax errors, or a class PHPStan cannot reflect. They are skipped and counted as analysis notices in the summary; `--format=json` lists them. With `--format=sarif`, `checkstyle` or `github`, or with `--output`, that summary goes to stderr.

A module set to `0` in the `modules` list of `app/etc/config.php` does not run, so it is not analyzed: one `disabled-module` notice names it when it lies in the analyzed paths, and its configuration does not count for other modules (a Proxy in its `di.xml`, for example). The file is read as text, not run. A module missing from that list counts as enabled.

The experimental S-* rules ([S-PERF-01](rules/S-PERF-01.md) and the others) are sniffs of magento/magento-coding-standard. When one of them is enabled, the command runs the project's phpcs (`vendor/squizlabs/php_codesniffer`) with the project's coding standard and only those sniffs, on the same paths; test code and disabled modules are left out as for the other rules. The coding standard's own ruleset leaves some sniffs out of more files, such as `Fixtures` directories. Without phpcs or the coding standard in the project, one `coding-standard` notice says so and the other rules run as usual.

## Upgrade rules

Rules with the `U-` prefix, such as [U-CLI-01](rules/U-CLI-01.md), find code that breaks with a change in a Magento release. Each one names the first version with that change, one per release line when the change also shipped in patch releases of older lines. A rule runs when the installed version or `--target-version` has the change:
- before an upgrade, `--target-version=2.4.9` on a 2.4.8 project runs the rules for 2.4.9;
- without the option, the rules for the installed version and older ones run, since the code they find is broken now;
- a target below the installed version hides nothing.

The installed version is read from `vendor/magento/magento2-base/composer.json`; a pre-release suffix such as `-rc2` is ignored. Mage-OS has its own version numbers, and a core checkout has no such file: there, upgrade rules run only with `--target-version`, and one `upgrade-version` notice says so while the configuration enables one of them. A rule that the configuration enables and that does not run because neither version has its change is named in an `upgrade-version` notice as well. A rule of the default set that does not run is skipped without a notice. [U-CLI-02](rules/U-CLI-02.md) is in the default set; the other upgrade rules are experimental, so they also need `enabled: true`.

The version gate is part of the `magento-perf-check` command, so with PHPStan directly the upgrade rules, U-CLI-02 of the default set included, are only in `extension-experimental.neon`. Through it they run whatever the installed version: on 2.4.8, U-CLI-01 reports code that still works there, and before 2.4.4-p12 or 2.4.5-p11, U-CLI-02 reports a command that Symfony Console 4.4 still runs, with a deprecation, and before 2.4.4, U-LOG-01 reports calls that Monolog 1 still has. Ignore their identifiers until the upgrade.

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
        # A prefix also covers the hyva_ copy of a handle: acme_account_ covers hyva_acme_account_index.
        allowlistHandles:
            - acme_account_
        # true: use only the prefixes above instead of adding them to the default list.
        replaceAllowlist: false
```

The tool rejects unknown keys, unknown rule IDs and invalid values with exit code 2, so a typo never silently changes the analysis. Disabled rules and severity overrides apply to XML and PHP findings alike, and to the `--fail-on` decision. [Experimental rules](rules.md#experimental-off-by-default) are off until you set `enabled: true`.

## Inside an existing PHPStan setup

PHPStan's result cache does not see XML here: after changing `di.xml` or another configuration file, run `phpstan clear-result-cache`. The `magento-perf-check` command handles this itself.

The PHP rules are a regular PHPStan extension. With [phpstan/extension-installer](https://github.com/phpstan/extension-installer) they are picked up automatically; otherwise include `vendor/silin/magento-perf-check/extension.neon` in your `phpstan.neon`. They report at every level. The `magento-perf-check` command runs only its own rules, with the types of `bitexpert/phpstan-magento` when it is installed next to the tool but not its rules that have a switch (its test-only rule on `getCollectionMock()` runs only with `--include-tests`): other extensions that the installer would add, such as `phpstan/phpstan-strict-rules`, are left out of its analysis. To suppress one finding, use its identifier and give a reason:

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

`create()` of a generated factory is typed from the factory's name, so `generated/code` is not needed for it. Generated proxies and extension attributes are typed only when `generated/code` exists or [bitexpert/phpstan-magento](https://github.com/bitExpert/phpstan-magento) is installed next to the tool: 0.32.0 or later with PHPStan 1.12, 0.40.0 or later with PHPStan 2. Version 0.30.0 and older do not declare the parameters that switch its own rules off, so the command leaves such a version out and says so in a `generated-code` notice. Versions 0.11.0 to 0.21.0 set only a lower bound on PHPStan 1.x (`^1.1.0` to `^1.6.2`), so a module that requires one of them installs it next to PHPStan 1.12. Without `generated/code` or a usable bitexpert/phpstan-magento, calls on them are skipped rather than reported ([ADR 2](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0002-bitexpert-phpstan-magento-as-suggestion.md)).
