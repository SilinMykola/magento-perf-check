# Proposal

## Why

Projects installed with Composer 1 (Magento 2.1 to 2.3 era) keep `vendor/composer/installed.json` as a plain list whose entries have no `install-path`; that key came with Composer 2. The index builder skipped every entry without it, so no Composer package was indexed: not `vendor/magento`, not any third-party module. Rules then missed facts that core declares. On three private Magento 2.1/2.2 projects this gave graded false positives: a P-DI-01 whose Proxy core sets in `module-catalog/etc/di.xml`, an X-IDX-01 whose view core declares in `module-catalog-search/etc/mview.xml`, and two P-FPC-02 on blocks placed only on pages that core layouts make uncacheable. The spec already says every package under `vendor` with `etc/module.xml` is indexed.

## What Changes

- An `installed.json` entry without `install-path` is read from Composer's default location, `vendor/<package name>`.
