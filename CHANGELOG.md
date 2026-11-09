# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

A rule added to or removed from the default set, or a changed default severity, is always listed: it can change the result of your CI.

## [Unreleased]

### Changed

- Findings in a module set to `0` in the `modules` list of `app/etc/config.php` are not reported: Magento does not run that module. Each one is listed as a `disabled-module` notice instead, and the module's configuration no longer counts for others, such as a Proxy in its `di.xml`. A module missing from the list counts as enabled. In the Magento core of the calibration project, where `Magento_TwoFactorAuth` is disabled, this removes 16 of 895 findings; on a Magento 2.3.3 project with four disabled modules, 43 of 169. **This can change a CI result.**

### Fixed

- A magic `set*()` or `uns*()` of a `DataObject` subclass, such as a model, keeps the model's type, so a chain like `$model->setStatus(1)->save()` in a loop is reported. Before, PHPStan typed the magic call as `mixed` and the loop rules missed the call after it. In the calibration corpus this adds 1 P-LOOP-03 finding, in the Magento core 3; all are saves in a loop. **This can change a CI result.**
- The project index is rebuilt when a module is added, enabled or disabled. Before, a new module with no `di.xml`, `events.xml` or other indexed file kept an outdated index from the cache.
- Code in a trait gives one finding per rule and line, however many classes use the trait. Before, PHPStan analysed the trait once for each such class and every finding was repeated: in the calibration corpus one line of a blog module's trait gave 8 extra P-DI-06 findings. P-DI-06 now names the trait in its message, so a baseline that holds such a finding lists it again until the baseline is generated anew.

## [0.3.1] - 2026-11-09

### Fixed

- The loop rules leave out a call after which the loop is left also when the loop is inside a `try`, such as a method body wrapped in `try { ... } catch (\Throwable $e) { ... }` ([#4](https://github.com/SilinMykola/magento-perf-check/issues/4)). An exception reaches that `catch` only after leaving the loop. A `try` inside the loop still counts: its `catch` can go on with the next iteration. In the Magento core this removes 1 of 111 P-LOOP-01 findings, at `error`, and 1 of 155 P-LOOP-03 findings; the calibration corpus has none. **This can change a CI result.**

## [0.3.0] - 2026-11-09

### Changed

- P-DI-01, in the default set, no longer reports a session in the constructor of a controller action: every action starts a session anyway, on the frontend through the customer `ContextPlugin` and in the admin through the request validation. On 78 modules this removes 42 of 182 findings; the 6 of them graded in calibration were all low impact. The cookie manager is still reported there. **This can change a CI result.**
- P-DI-06, in the default set at `info`, no longer reports the constructor fallback written as an `if` on a missing parameter (`if (!isset($param))`, `empty()`, `=== null`, `== null`, `!$param`, `is_null()`), nor `$objectManager = ObjectManager::getInstance()` in a constructor when only `?:` and `??` fallbacks use the variable. Calibration graded both as false positives. On 78 modules this removes 7 of 81 findings, in the Magento core 22 of 343.
- P-LOOP-03, in the default set at `info`, no longer reports a `save()` or `delete()` after which the loop is left in the same iteration by `break`, `return` or `throw`: it runs at most once. In the calibration corpus this removes 1 of 57 findings, in the Magento core 5 of 160.
- The other loop rules leave out such a call too: a `load()`, `getById()` or other call after which the loop is left in the same iteration is not loop context. In the Magento core this removes 2 of 113 P-LOOP-01 findings, at `error`, and 6 of 11 P-LOOP-10 findings; the calibration corpus has none. **This can change a CI result.**
- P-DI-06, in the default set at `info`, no longer reports the lazy getter that fills a missing property once: `if ($this->dependency === null) { $this->dependency = ObjectManager::getInstance()->get(...); }`. The object manager is asked once per object, unless the property is reset, so calibration graded these correct but of low impact; they were most of the rule's findings. A getter that returns the object without keeping it is still reported. The missing-value test may now also be `!is_object()` or `!$x instanceof Type`, here and in the constructor fallback, and a comment left alone at the end of the block, such as `// phpcs:enable`, no longer makes either one reported. In the calibration corpus this removes 13 of 52 findings, in the Magento core 247 of 321.

## [0.2.0] - 2026-11-07

### Added

- Experimental rules, off by default: P-DI-04 at `info`, on a console command that every `bin/magento` run creates with its dependencies; P-COL-04 at `warning`, on a collection walked without a page size in a cron job or console command.
- Experimental rules, off by default: P-LOOP-04, P-LOOP-06 at `warning`, on a product URL or an attribute option text inside a loop; P-COL-03, X-PLG-02 at `info`, on `addAttributeToSelect('*')` and on a plugin that runs for every rendered block.
- Experimental rules, off by default: X-GQL-02 at `warning`, on a database query in a resolver of a field on a type returned inside a list; P-FPC-03 at `error`, on a value specific to a visitor or a moment in the HTTP context.
- New rules in the default set, which can change a CI result: X-LAY-03 at `error`, on `cacheable="false"` in the `default`, `catalog_*` and `cms_*` handles; P-LOOP-05, P-LOOP-09, P-LOOP-10, P-IDX-01 and, back from experimental, P-PLG-01 at `warning`; X-DB-02, X-AREA-01 and X-GQL-03 at `info`. **X-LAY-03 can fail a CI run that passed before.**

### Fixed

- P-FPC-05, in the default set, no longer reports `setHeader('Pragma', 'no-cache')`, which can change a CI result: neither the built-in page cache nor the Varnish configuration reads `Pragma` from a response.
- A PHPStan worker crash or memory limit no longer ends in exit code 0: the command exits with 2, marks the JSON report `"incomplete": true`, says why on stderr and writes no baseline. **This can change a CI result.**
- With the `magento-perf-check` command, PHP rules that read XML (P-DI-01, P-FPC-10, P-PLG-01) no longer report stale results from PHPStan's cache after `di.xml` or another indexed file changes. Before this, adding a Proxy changed nothing, and removing one could go unreported.
- Two runs on one project at the same time no longer return each other's results.
- A stub of a core class in a module's `Test` directory no longer replaces the real class for all other code. In the calibration corpus, one module's stubs of `Magento\Customer\Model\Session` and `Customer` hid 24 findings in other modules. **This can change a CI result.**
- `create()` of a factory that is not generated yet is typed from its name, so findings no longer depend on whether `setup:di:compile` has run. On 56 modules without `generated/code`, this adds 54 findings of P-LOOP-01, P-LOOP-02, P-LOOP-03, P-LOOP-07 and P-LOOP-10 and removes none; with generated factories the same findings were there already. **This can change a CI result.**
- Untyped private properties take their type from the constructor. **This can change a CI result.**
- P-LOOP-03, P-LOOP-10, P-FPC-06 and X-SETUP-01 also recognize EAV resource models, such as those of products and customers. **This can change a CI result.**
- A `schema.graphqls` with `//` or `/* */` comments or `extends type`, which Magento reads, is analyzed. Before, it was skipped with a notice, so X-GQL-01 did not see it. **This can change a CI result.**
- The project index finds a Composer module whose `etc/module.xml` is in `src/`.
- A single `.phtml` file in `--paths` is analyzed.
- XML of a module symlinked into the analyzed path is analyzed.
- A test directory given in `--paths` next to its parent directory is analyzed. Before, the parent excluded it.
- `--paths` entries with `./` or a repeated entry no longer report the same finding twice, and `--output` cannot overwrite the `--baseline` file through a relative path.
- A `--generate-baseline` file that cannot be written fails before the analysis.

### Changed

- The experimental P-LOOP-04 and P-LOOP-06 recognize a product typed `ProductInterface`, as a repository returns it. On 56 modules this adds 2 P-LOOP-04 findings.
- P-LOOP-02 does not report a collection that is created and limited in a `while`, `do` or `for` loop: that is paging. A `foreach`, a `for` over `count()` and a limit of one row are still reported.
- P-LOOP-09 also counts a `for` with `count()` or `sizeof()` in its condition as a loop over items. **This can change a CI result.**
- Nothing to analyze is an error, exit code 2, instead of a clean run: a project without `app/code` and `app/design`, or paths with no PHP, template or XML file. **This can change a CI result.**
- `--paths` entries with `..` are rejected. **This can change a CI result.**
- P-LOOP-01 no longer reports `Magento\Directory\Model\Currency::load()` or `get()`/`getById()` on the `Magento\Store\Api` store, website and group repositories: no query runs.
- P-COL-01 no longer reports `count($this)` in a collection's `_afterLoad()`, or a collection on the object itself (`count($this->rows)`, `count($this->getRows())`), which is usually memoized and read elsewhere.
- X-LAY-01 is split: the `default`, `catalog_*` and `cms_*` handles are now X-LAY-03, which is on by default. X-LAY-01 stays experimental and covers other handles, at `warning`. If you opted in to X-LAY-01, change `perf-ignore X-LAY-01` comments, baseline entries and severity settings for those handles to X-LAY-03.
- X-LAY-01 and X-LAY-03 no longer report `hyva_checkout_`, `shipping_tracking_`, `productalert_unsubscribe_` and `sendfriend_` handles, or a layout that includes the `customer_account` handle.
- P-FPC-01 no longer reports `$_isScopePrivate = false`, which is the default and marks nothing private.
- P-FPC-06 no longer reports actions that return only JSON or raw results: they are sent with no-cache headers.
- P-LOOP-02 reports one finding per collection, on its first loading call.
- Loop rules no longer treat the body of a `foreach` over `array_chunk()` as a loop: one query per batch is the fix.
- X-SETUP-01 reports only writes and reindexing on a matching receiver type, and leaves out calls inside an `if`. It reports every such call instead of the first one per method.
- P-COL-02 (experimental) accepts a comparison of `getSize()` or `getTotalCount()` with 1, `setPage()` and `setIdFilter()`, and stays silent when the same function uses the rows anyway.
- P-COL-01 and P-COL-02 count `getColumnValues()` as using the rows.
- P-FPC-05 no longer reports a controller's own method named `setNoCacheHeaders()` or similar; what it sets inside is still reported.
- P-PLG-01 no longer reports an around plugin with a local variable assigned before `$proceed` and read after it.
- P-COL-02 notices about receivers of unknown type appear only when that rule is enabled. It is experimental and off by default, so without this every default run printed a notice about a rule that did not run.
- Analysis notices reach stderr with `--format=sarif`, `checkstyle` and `github`, and with `--output`.
- A missing or empty `generated/code` is an analysis notice, because some findings are missed without it.
- A file PHPStan cannot reflect is skipped with a notice, like a file it cannot parse, because it made PHPStan drop every finding of the run.
- Workers get 30 minutes instead of PHPStan's default 10.
- The temp directory is now `magento-perf-check-<uid>`, readable only by your user (with PHP's posix extension). The old `magento-perf-check` directory in the system temp directory can be deleted.

### Documentation

- Analysis runs the project's autoloader, and with it module code: do not analyze untrusted code outside a sandbox.
- The README lists the default-set rules that look weak on a small sample, and says each finding had one grader.
- Two checks on modules the rules were not tuned on: [15 modules](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/held-out.md) and [22 more](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/held-out-2.md). Most findings are correct and few matter in practice.

## [0.1.1] - 2026-11-06

### Changed

- Documentation only: the install from Packagist comes first again, and the docs say which Magento versions can install the package. No rule or behavior changed.

## [0.1.0] - 2026-11-06

First release.

### Added

- `magento-perf-check` command: analyzes a Magento 2 or Mage-OS project by path (`--project`) without writing into it, merging XML and PHPStan findings in one report.
- Output formats: `table`, `json`, `sarif` (SARIF 2.1.0), `checkstyle` and `github` (GitHub Actions annotations).
- `--fail-on`, `--paths`, `--include-tests`, `--memory-limit`, `--config`, `--output` (write the report to a file, for example from a Git hook) and `--version` options; exit codes 0 (clean), 1 (findings), 2 (tool error).
- `magento-perf-check.neon` configuration file: paths, test code, per-rule `enabled` and `severity`, X-LAY-01 handle allowlist.
- Baseline: `--generate-baseline` and `--baseline`, matching findings by rule, file and message.
- PHPStan extension (`extension.neon`, `extension-experimental.neon`) for use inside an existing PHPStan setup; PHPStan 1.12 and 2.x are supported.
- Examples for GitHub Actions and GrumPHP.
- Rules in the default set, calibrated on Magento 2.4.9 core and 25 popular modules:
  - `error`: P-DI-03, P-FPC-06, P-FPC-09, P-FPC-10, P-LOOP-01, P-LOOP-02, X-DB-01, X-GQL-01, X-LAY-02, X-SEC-01;
  - `warning`: P-COL-01, P-DI-01, P-FPC-05, P-LOOP-07, X-EVT-01, X-EVT-02, X-EVT-03, X-IDX-01, X-MQ-01, X-SETUP-01;
  - `info`: P-DI-06, P-FPC-01, P-LOOP-03, T-01.
- Experimental rules, off by default: P-COL-02, P-DI-02, P-FPC-02, P-PLG-01, X-LAY-01.

[Unreleased]: https://github.com/SilinMykola/magento-perf-check/compare/v0.3.1...HEAD
[0.3.1]: https://github.com/SilinMykola/magento-perf-check/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/SilinMykola/magento-perf-check/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/SilinMykola/magento-perf-check/compare/v0.1.1...v0.2.0
[0.1.1]: https://github.com/SilinMykola/magento-perf-check/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/SilinMykola/magento-perf-check/releases/tag/v0.1.0
