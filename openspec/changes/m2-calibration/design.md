# Design

## Context

See proposal.md for motivation, and the specs for the required behaviour. The rule fixes come from the conclusions in `docs/runs/m1-core.md`; the false-positive patterns behind them are in `docs/runs/m1-spot-checks/`.

The owner decided on 2026-10-23:
- **Corpus:** a copy of the test project in `var/`, Packagist modules in its `app/code`, and a separate Mage-OS install.
- **Grading:** A single grader grades every sampled finding and the owner re-checks part of them.
- **Order:** fixes first, then demotion.
- **GrumPHP:** a documented `shell` task.

Facts checked on 2026-10-23:
- **Disk:** the test project is 998 MB; 141 GB are free.
- **Mage-OS:** `https://repo.mage-os.org/` answers. `mage-os/project-community-edition` is published there.
- **Packagist:** lists 12,165 packages of type `magento2-module`, with download counts in its search API. The top entries are, for example, `mageplaza/module-core` (11.9 M), `mageplaza/module-smtp`, `fastly/magento2`, `magefan/module-community` and `adyen/module-payment`.
- **Composer autoload in Magento:** the root `composer.json` maps `app/code/` with PSR-0, so modules copied into `app/code/<Vendor>/<Module>` autoload without regenerating the autoloader.

## Goals / Non-Goals

**Goals:**
- Precision numbers that anyone can reproduce: a pinned corpus, a recorded seed, graded tables in the repository, and the report generated from them.
- Every rule decision follows mechanically from the thresholds, and the registry, the SPEC and the README all show the same decision.
- CI examples that projects can copy unchanged.

**Non-Goals:**
- Recall. Missed findings are not measured; calibration only judges what the rules report.
- Measuring the impact of each finding in time or memory. `TP-low` marks negligible impact qualitatively.
- New rules, or the ⏳ rules (M4).

## Decisions

### D1. Corpus layout and build scripts
`bin/calibration/build-corpus` builds three directories under `var/corpus/`, which is git-ignored:

- **`magento/`:** `rsync -a` of `~/projects/magento-2.4.9.ddev`, without `var/`, `pub/static`, `pub/media` and `.ddev`. `generated/code` stays for types. The source is only read. A marker file plus `find -newer`, the same check as in the M0/M1 runs, proves it was not written to.
- **`magento/app/code/<Vendor>/<Module>`:** Packagist modules. They come from the dist archive of each package's newest stable version whose `require` is satisfied by the copy's `composer.lock` (Magento framework and module versions) and by PHP 8.4. Constraints are checked with `composer/semver`, added as a dev dependency. The target directory comes from the package's `registration.php`/`etc/module.xml`. Archives are verified against the SHA-1 that Packagist publishes, and their SHA-256 goes into the manifest.
- **`mage-os/`:** `composer create-project mage-os/project-community-edition` from `https://repo.mage-os.org/`. The pinned version is the newest one that installs; it is recorded in the manifest. The install uses `--no-scripts` and `--ignore-platform-req=php`, because analysis does not run Magento and the host is PHP 8.5. `app/etc` is created when the project template lacks it, since `--project` requires it.

**Selection** walks the Packagist search results for `type=magento2-module` sorted by downloads and takes the first 25 that install. Packages are skipped, with the reason recorded, when they:
- are abandoned;
- are metapackages or language packs;
- are Hyvä packages, which need the Hyvä theme stack;
- have unmet `require` constraints.

**Dependencies** between selected packages, such as `mageplaza/module-core`, are satisfied when both are in the list. A dependency that is not in the list leaves its types unknown. Such rules then report nothing for that code: a missed finding, not a false one, which matches the precision-first policy.

### D2. Runs and sampling
- **Runs:** `bin/calibration/run` runs `magento-perf-check --format=json` three times:
  - core: `--paths=vendor/magento` in `magento/`;
  - Packagist modules: `--paths=app/code` in `magento/`;
  - Mage-OS: `--paths=vendor/mage-os` in `mage-os/`.

  Test code stays excluded by default. Each JSON goes to `docs/calibration/runs/` with the tool's git commit.
- **Sampling:** `bin/calibration/sample` merges the runs, drops paths with `-sample-data/`, and draws up to 30 findings per rule with `mt_srand(20261023)` and a stable sort. It writes one file per rule, `docs/calibration/graded/<RULE>.md`.
- **Grading files:** each sampled finding gets a ±6-line code excerpt and empty verdict and reason fields. The first 3 findings of each file, in seed order, are marked **owner re-check**.

### D3. Grading format and report generation
- **Tables:** each graded file has one table row per finding: `| # | location | verdict | reason | owner |`. The `owner` column is empty, `✓` (confirmed), or `was <verdict>` (changed). The owner edits these files in the editor.
- **Report:** `bin/calibration/report` parses the tables and computes counts and precision. It applies the thresholds from the calibration spec and writes `docs/calibration.md`: the method, a per-rule table and the limitations section. The script fails on rows without a verdict, or with an unknown verdict.
- **Applying decisions:** the report also prints the decisions as a list. The registry (`src/Rule/catalog.php`), the SPEC catalog, the delta specs of the affected rule families and the README are updated by hand from that list. A test (D4) checks that the registry matches the report.

### D4. Consistency checks
- A test reads `docs/calibration.md` and checks, for each rule, that its decision matches the registry: severity, and status ✅/🧪.
- The existing README rule test is extended: rules with experimental status are listed in their own "Experimental (off by default)" table, not in the default-set table.

### D5. Experimental rules off by default
- **XML rules and the runner:** `Configuration::apply()` drops findings of rules whose registry status is experimental unless the configuration sets `enabled: true` for them.
- **PHP rules used directly with PHPStan:** each PHP rule that calibration makes experimental moves from `extension.neon` to `extension-experimental.neon`. A project can include that file to opt in. The runner always includes both files and filters by configuration, so `enabled: true` works there too.

### D6. Rule fixes
Each fix gets fixtures for the new scenarios, a check against broken behavior and a rule-page update.

| Rule | Approach |
|---|---|
| P-COL-01 | On entering a function, `CollectionStateVisitor` collects the root keys that are loaded anywhere in it. A `count()` whose collection is loaded anywhere else in the function counts as loaded. |
| P-DI-02 | `isPlainValue()` accepts casts, and `??`/`?:` defaults that are `new` or the ObjectManager. Assignments to local variables follow the same rules. Files in a Composer package of type `composer-plugin` (nearest `composer.json`, cached per directory) or in `magento/magento-coding-standard` are skipped. |
| P-FPC-02 | `getCustomerGroupId` leaves the method list. Classes in a `\Block\Adminhtml\` namespace are skipped. |
| P-FPC-05 | The rule works on statements (`Stmt\Expression`, `Stmt\Return_`) and searches each for no-cache calls, so a chain gives one finding. `setHeader` counts only with a non-literal value or a literal containing `no-cache`, `no-store` or `private`. Allowlisted core controllers are skipped by class name prefix. |
| P-FPC-06 | Adminhtml controllers are skipped. A visitor marks calls inside an `execute()` whose return statements all return a redirect or forward: a chain on a `*redirect*` factory's `create()`, `setPath`/`setUrl`/`setRefererUrl`/`setRefererOrBaseUrl`, or `_redirect()`/`_forward()`. Such calls are skipped. This is syntactic, and it errs towards fewer findings. |
| P-FPC-09 | The enclosing class is checked through reflection: Symfony `Command` subclasses, `ImporterInterface` implementations and `App\Config\Value` subclasses are skipped, as is code in a `\Setup\` namespace or under `setup/`. |
| X-SEC-01 | The core action list is extended. `<action name="*">` with exactly one section, `messages`, is skipped. |
| X-EVT-01 | `*_collection_load_after` events and `Magento\Framework\EntityManager\Observer\*` instances are skipped. |
| X-SETUP-01 | Loops are removed from the heavy set; heavy calls inside loops are still found, and the finding points at the call. |

### D7. GitHub output and CI files
- **`GithubReporter`:** writes `::<level> file=…,line=…,title=<ID>::<message>`. The message escapes `%`, `\r` and `\n`; property values also escape `:` and `,`.
- **Action SHAs:** `docs/ci/github-actions.yml` and `.github/workflows/ci.yml` pin actions to full commit SHAs. The SHAs are looked up with `gh api` at implementation time for the latest release tags of `actions/checkout`, `shivammathur/setup-php` and `github/codeql-action`. None are written from memory.
- **Testing the workflows:** YAML and pin format are checked by tests (ci-integration spec). The workflows cannot run before the repository has a remote. `actionlint` checks them if it is available, and is installed only after asking.

### D8. GrumPHP example checked in a scratch copy
`docs/ci/grumphp.yml` defines a `shell` task that calls `vendor/bin/magento-perf-check --fail-on=error`.

To check it, a temporary copy of the fixture project gets `phpro/grumphp` installed, and `grumphp run` runs with the example twice: on the fixture with findings (it must fail) and on a clean fixture (it must pass). The repository does not take a dependency on GrumPHP.

## Risks / Trade-offs

- **Grading by a single grader can be biased towards the rules' own logic.** The owner re-checks at least 3 findings per rule; every change is recorded, and `docs/calibration.md` states who graded. With about 30 findings per rule, precision has a wide error margin, so the report gives counts next to percentages.
- **Popular Packagist modules may not install on 2.4.9, or may depend on missing packages.** Skipped packages are recorded with reasons and the next package is taken. Unknown types reduce findings, not precision.
- **Mage-OS may not install on host PHP 8.5.** `--ignore-platform-req=php`; if dependency resolution still fails, the newest Mage-OS release that does install is used, and its version is recorded.
- **P-FPC-06 redirect detection is syntactic.** Unusual result types are not recognized, so those controllers stay reported; the trade-off favours fewer findings. Measured in calibration like every other rule.
- **Labelling volume:** about 25 rules × up to 30 findings is around 600–750 findings. The work is split per rule family across parallel agents as in M1, and every agent writes the shared table format.
- **Corpus size in `var/`:** a few GB, git-ignored. `bin/calibration/build-corpus --clean` removes it.

## Migration Plan

- Severity and status changes are user-visible: per SPEC section 8, changing the default set is a minor version. They are listed in `docs/calibration.md` and will go into the changelog at M3.
- `extension-experimental.neon` is new. Projects that include only `extension.neon` lose rules that became experimental; this is intended.
