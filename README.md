# magento-perf-check

Finds performance and Full Page Cache anti-patterns in Magento 2 and Mage-OS code before they reach production: database queries in loops, state changes in cacheable GET requests, session objects created on every page, misconfigured layout, events, indexers, queues and GraphQL caching.

[![CI](https://github.com/SilinMykola/magento-perf-check/actions/workflows/ci.yml/badge.svg)](https://github.com/SilinMykola/magento-perf-check/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/silin/magento-perf-check.svg)](https://packagist.org/packages/silin/magento-perf-check)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP 8.1+](https://img.shields.io/badge/php-8.1%2B-777bb4.svg)](docs/configuration.md#requirements)
[![PHPStan 1.12 and 2.x](https://img.shields.io/badge/PHPStan-1.12%20%7C%202.x-brightgreen.svg)](docs/configuration.md#inside-an-existing-phpstan-setup)

```text
$ vendor/bin/magento-perf-check --paths=app/code/Mollie/Payment/Controller/Express/Process.php,app/code/Magefan/Community/Model/GetCategoryByProduct.php
app/code/Magefan/Community/Model/GetCategoryByProduct.php
     73  error    [P-LOOP-01] Magento\Catalog\Api\CategoryRepositoryInterface::get() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().
         https://github.com/SilinMykola/magento-perf-check/blob/v0.2.0/docs/rules/P-LOOP-01.md

app/code/Mollie/Payment/Controller/Express/Process.php
     58  error    [P-FPC-06] Mollie\Payment\Controller\Express\Process handles GET only but calls Magento\Quote\Api\CartRepositoryInterface::save(). Change state only on POST or PUT: GET responses can be cached and served without running this code.
         https://github.com/SilinMykola/magento-perf-check/blob/v0.2.0/docs/rules/P-FPC-06.md

2 finding(s): 2 error, 0 warning, 0 info. Analyzed app/code/Mollie/Payment/Controller/Express/Process.php, app/code/Magefan/Community/Model/GetCategoryByProduct.php in 0.8 s.
```

Real output on two published modules (`magefan/module-community` 2.4.9 and `mollie/magento2` 3.2.0); both findings were confirmed by reading the code during [calibration](docs/calibration.md).

## Quick start

```sh
composer require --dev silin/magento-perf-check
vendor/bin/magento-perf-check
```

Or check a project without adding a dependency to it (the project still needs its own `composer install`):

```sh
git clone https://github.com/SilinMykola/magento-perf-check.git && cd magento-perf-check && composer install
php bin/magento-perf-check --project=/path/to/magento
```

Calibrated on Magento 2.4.9, where both ways were checked. Stock Magento 2.4.4 and older pin a PHPStan version below 1.12, so `composer require` cannot work there: use the clone. It ran that way on `vendor/magento/module-cms` of 2.4.0, 2.4.3 and 2.4.4, without grading the findings ([check](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/runs/older-magento.md)). The install was not tried on 2.4.5 to 2.4.8, and Magento 2.3 is not checked.

It analyzes `app/code` and `app/design` with type-aware [PHPStan](https://phpstan.org/) rules and XML checks, and never writes into the project. No database or container is needed. It does load the project's autoloader, which runs the `registration.php` of every module, so do not analyze code you do not trust outside a sandbox. On a stock Magento 2.4.9 project, install plus the first run took under 20 seconds ([measured](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/runs/m3-install.md)).

## What it finds

45 rules: 33 on by default, 12 experimental. A few of them:

- **Queries in loops:** `load()`, `getById()`, `getList()` or a new collection inside a loop (P-LOOP-01, P-LOOP-02, P-LOOP-09, P-LOOP-10).
- **Full Page Cache breakers:** `cacheable="false"` on the default, catalog and CMS handles (X-LAY-03), state changes in GET-only controllers (P-FPC-06), no-cache headers on cacheable pages (P-FPC-05), cache flushes and `reindexAll()` from request code (P-FPC-09, P-FPC-10), ESI blocks on entity pages (X-LAY-02).
- **Expensive wiring:** sessions injected without a Proxy (P-DI-01), events dispatched in constructors (P-DI-03), observers on every model load or block render (X-EVT-01, X-EVT-03).
- **Configuration:** `sections.xml` that reloads all customer data on every POST (X-SEC-01), GraphQL `@cache` without an identity (X-GQL-01), synchronous queue topics (X-MQ-01), indexers without mview (X-IDX-01), tables without a primary key (X-DB-01).

Full list with severities and sources: [docs/rules.md](docs/rules.md). Every rule has its own page with bad and good examples, how to suppress it and its known false positives.

### Found in real modules

From the calibration corpus (Magento 2.4.9 core and the 25 most-downloaded Packagist modules), graded as true positives:

- **P-LOOP-01** in `mailchimp/mc-magento2` 103.4.86: one product `getById()` per visible order item on the checkout success page.\
  `app/code/Ebizmarts/MailChimp/Block/Pixel/Order.php:96`
- **P-LOOP-01** in core: category repository `get()` per anchor category of every product category, on product save and import.\
  `vendor/magento/module-catalog-url-rewrite/Model/Product/AnchorUrlRewriteGenerator.php:70`
- **P-LOOP-01** in core: one `getById()` per configurable variation on admin product save; variations can number in the hundreds.\
  `vendor/magento/module-configurable-product/Controller/Adminhtml/Product/Initialization/Helper/Plugin/UpdateConfigurations.php:80`
- **P-FPC-06** in `mollie/magento2` 3.2.0: a GET action saves the quote and returns a cacheable page, so a repeated request can be served from cache without running the save.\
  `app/code/Mollie/Payment/Controller/Express/Process.php:58`
- **P-LOOP-03** in `adyen/module-payment` 11.3.0: a cron job deletes expired rows one by one where one `DELETE` query would do.\
  `app/code/Adyen/Payment/Cron/StateDataCleanUp.php:57`

## What our rules are based on

Every rule in the default set cites at least one primary source:
- **A:** official Adobe or Hyvä documentation.
- **B:** Magento core code pinned to a specific commit (currently [`a385d02f`](https://github.com/magento/magento2/commit/a385d02f60ee6dfa052f12cdc7d82e956581fba5)), or a magento/magento2 issue with a confirmed reproduction or measurements.

Community sources (**C**) are used only as supplementary explanation. A rule with only C sources stays experimental and is off by default. Each rule page ends with a **Basis** section listing its sources, their levels and what each one confirms; the audit of the whole catalog is in [`docs/source-audit.md`](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md).

Precision is measured, not assumed. Every rule ran on Magento 2.4.9 core and the 25 most-downloaded Packagist modules, and up to 30 findings per rule were graded by reading the code ([method and per-rule results](docs/calibration.md)):
- the rules with at least 10 graded findings in the default set (P-LOOP-01, P-LOOP-03, P-DI-01, P-DI-06, P-FPC-01, P-PLG-01, T-01, X-AREA-01) were right in 90–100 % of them;
- four rules measured below their threshold and are experimental, off by default;
- rules that were right but mostly found low-impact cases were lowered to `warning` or `info`;
- the remaining rules have fewer than 10 findings in the corpus, too few to measure, so they keep their severity. That includes P-LOOP-02 (`error`, 3 of 3 correct, all low impact) and X-EVT-02 (0 of 1). X-LAY-03, P-LOOP-05, P-COL-01 and X-GQL-03 have none.

[Round 2](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-2.md) fixed the known false positives and measured again on the same corpus, so its figures are optimistic for code the rules have not seen. [A separate check](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/held-out.md) on 15 other modules found 49 of 52 graded findings correct, of which 3 matter in practice, and [a second one](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/held-out-2.md) on 22 more found all 72 correct, of which 3 matter, once the one false positive it showed was fixed. Expect most findings to be correct and cheap to live with, not urgent. Each finding was graded once, by one grader, without a second review. Those runs [undercounted](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/held-out.md#what-the-first-runs-missed): of 29 sampled findings that the later fixes added, 28 were correct and 9 matter.

Static analysis gives heuristics, not guarantees: treat each finding as a reason to check, not a verdict.

## Related

- [magento2-best-practices](https://github.com/SilinMykola/magento2-best-practices-skill): Magento coding rules, test layers, fixtures and PHPUnit conventions; detects a magento/magento2 checkout.

## Documentation

- [Rules](docs/rules.md): the full list, with links to each rule's page.
- [Running and configuring](docs/configuration.md): options, exit codes, `magento-perf-check.neon`, use inside an existing PHPStan setup.
- [Baseline](docs/baseline.md): adopt the tool on a project with existing findings.
- [CI and Git hooks](docs/ci.md): GitHub Actions with code scanning, GrumPHP, other CI.
- [Coding agents](docs/agents.md): an `AGENTS.md` snippet and a skill that run the tool and act on its findings.
- [Changelog](CHANGELOG.md).

## Development

```sh
composer test        # PHPUnit, with PHPStan 2.x
bin/test-phpstan1    # the same suite against PHPStan 1.12, in a temporary copy
composer analyse     # PHPStan at level max on src/
```

The design and roadmap are in [`docs/SPEC.md`](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/SPEC.md), architectural decisions in [`docs/adr/`](https://github.com/SilinMykola/magento-perf-check/tree/main/docs/adr/). Contributors: see [`AGENTS.md`](https://github.com/SilinMykola/magento-perf-check/blob/main/AGENTS.md) for the rules every change follows.

## License

MIT, see [LICENSE](LICENSE).
