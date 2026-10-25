# M3 install check on Magento 2.4.9

- **Date:** 2026-10-24.
- **Package:** `silin/magento-perf-check` 0.1.0, before publication.
- **Host:** Apple M4, 10 cores, 16 GB, macOS, PHP 8.5.11, Composer 2.10.3.
- **Project:** a copy of `~/projects/magento-2.4.9.ddev` (`magento/magento2-base` 2.4.9, locked `phpstan/phpstan` 1.12.33, two custom modules in `app/code`). The copy was made with `rsync` into `var/install-check/` without `pub/media`, `var` and `.ddev`; the copy time is not counted. The original's `composer.json` and `composer.lock` checksums were the same before and after.

Acceptance criterion (SPEC section 9, M3): the package installs and works on a clean project within 2 minutes.

## Result

| Step | Command | Wall time | Outcome |
|---|---|---|---|
| Install | `composer require --dev silin/magento-perf-check:0.1.0` | 14.3 s | 2 packages installed: the tool and `nette/neon` 3.4.9 |
| First run | `vendor/bin/magento-perf-check` | 4.6 s | exit 0, 1 finding (P-DI-01, info) in `app/code` |
| **Total** | | **18.9 s** | criterion met |

- **Lock file:** only `silin/magento-perf-check` and `nette/neon` were added. No locked package changed: PHPStan stayed at 1.12.33 and `webonyx/graphql-php` at 15.34.0.
- **Credentials:** the project's `auth.json` for repo.magento.com was present in the copy (copied with the project); no extra credentials were set up.
- **Installed size:** 564 KB in `vendor/silin/magento-perf-check`; `tests/`, `openspec/` and calibration data are not in the archive.
- **Version and links:** `vendor/bin/magento-perf-check --version` printed `magento-perf-check 0.1.0`. Findings link to `https://github.com/SilinMykola/magento-perf-check/blob/v0.1.0/docs/rules/<ID>.md`, also as SARIF `helpUri`; the SARIF driver reports version 0.1.0.

### Run with real findings

`vendor/bin/magento-perf-check --paths=vendor/magento/module-catalog --format=json` in the same copy: exit 1, 13.2 s, 122 findings (16 error, 39 warning, 67 info; P-DI-06 56, P-DI-01 38, P-LOOP-01 12, P-LOOP-03 11, P-LOOP-02 4, P-COL-01 1) and 1 notice.

## How the package was delivered

Packagist serves the `git archive` zip of a tag. To test exactly that before publication, the archive was built with `git archive --format=zip` from the working tree (138 KB) and offered to Composer through a `package` repository with version 0.1.0 and a `file://` dist URL. No symlink is involved, as SPEC section 9a requires.

A first attempt used a path repository with `"symlink": false`, as SPEC section 9a suggests. It took 333 s and copied 2.1 GB, because Composer mirrors the whole working copy, including the git-ignored `var/` (the calibration corpus) and `vendor/`. That is an artifact of installing from a development checkout, not what users get, so it was removed from the copy and replaced by the archive install above.

## After publication

To be repeated with Packagist as the only source of the package (task 6.5 of change m3-release).
