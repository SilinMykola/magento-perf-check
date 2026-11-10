# Design

## D1. A separate copy, not more modules in `app/code` of the first part

The first part's runs and samples (rounds 1 to 5) are tied to its `app/code`. Adding modules there would change every later rerun of those rounds. The new part is an rsync of `var/corpus/magento` without `app/code`, about 830 MB. The Magento source project named by `CORPUS_SOURCE_PROJECT` is not read at all.

## D2. Selection

The same rule as the first part: packages of type `magento2-module` by Packagist downloads, not abandoned, whose newest stable version fits the corpus (`magento/*`, `laminas/*`, `symfony/*` and PHP 8.4.0). In addition:
- packages already in the first part are passed over;
- `hyva-themes/*` packages are no longer skipped, since the Hyvä stack is present;
- a module whose name is already in the copy is skipped, as in the first part;
- the target is 100 modules.

The held-out modules of 2026-11-07 are not excluded: their pages decided nothing, and they are among the next packages by downloads.

## D3. `registration.php` check

A package is skipped when its `registration.php`, with comments and whitespace removed by PHP's tokenizer, is anything other than one `ComponentRegistrar::register()` call. The call must have a `ComponentRegistrar::MODULE` (or `THEME`) type, a string literal name and `__DIR__`. An optional `declare(strict_types=1)` and the `use` line of `ComponentRegistrar` are allowed. This keeps code that runs at autoload time out of the corpus. The check lives in `bin/calibration/registration-check.php` so a unit test can load it. The Hyvä packages go through the same check, and the build stops if one fails. A symlinked `registration.php` fails too. Magento's `app/etc/registration_globlist.php` also makes the autoloader run `cli_commands.php` at the root of a module; the analysis does not need it, so the build removes it from the copy and the manifest lists it under `removed`.

## D4. Hyvä

Hyvä Theme is not on Packagist. The five repositories are on GitHub under `hyva-themes` (OSL-3.0). The build resolves each pinned tag to its commit, downloads the tarball of that commit and puts the code where a Composer install would make it available. The manifest records the commit and the archive's SHA-256; a rebuild stops when a tag points at another commit. The archive itself is not compared, because GitHub does not promise byte-identical generated archives:
- modules into `app/code/Hyva/<Module>`;
- the default theme into `app/design/frontend/Hyva/default`.

The tags are those that the default theme 1.5.3 requires.

## D5. Grading scope

Round 6 grades experimental rules only, because they are the ones without data. The default set already has rounds 1 to 3 and two held-out checks. The sample, seed and verdicts follow the calibration spec. A finding in Hyvä code counts like any other.
