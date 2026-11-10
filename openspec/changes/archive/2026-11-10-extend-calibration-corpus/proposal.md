# Proposal

## Why

Most experimental rules cannot leave experimental: the calibration corpus of 25 modules gives them fewer than 10 findings, which SPEC section 7 calls insufficient data (P-API-01 has 1, S-TPL-02 has 0). The corpus has no Hyvä code either, although T-03 and T-04 are Hyvä rules and M6 starts with Hyvä. The held-out checks ran on 37 more modules, but their copies were not kept, so nobody can rerun a rule on them. SPEC milestone M8 asks for a larger corpus that can be rebuilt.

## What Changes

- **A second corpus part, `extended`.** `bin/calibration/build-corpus extended` builds `var/corpus/extended`, a copy of the Magento part without its `app/code`. Into it go:
  - the next Packagist packages of type `magento2-module` by downloads, after the 25 of the first part, up to 100 modules;
  - Hyvä Theme 1.5.3 from its GitHub tags: the theme module, base layout reset, email module, order cancellation web API module and the default theme. All are OSL-3.0.
- **Safety before the analysis loads them.** The analysis loads the project's autoloader, which runs each module's `registration.php` and `cli_commands.php`. A package whose `registration.php` holds anything other than the standard `ComponentRegistrar::register` call is skipped, with the reason recorded; the held-out checks did this by hand. A `cli_commands.php` is removed from the copy.
- **Manifest.** `docs/calibration/corpus.json` gets an `extended` section with each package, its version, its downloads on the selection date and the SHA-256 of its archive, and the skipped packages with reasons.
- **Runs.** `bin/calibration/run extended` runs every rule, experimental rules included, on `app/code` and `app/design` of the new part.
- **Round 6.** The experimental rules are graded on the new part, up to 30 sampled findings each, with the procedure of round 1, in `docs/calibration/round-6.md`. A rule that meets the thresholds there can move to the default set; that move goes into `CHANGELOG.md`.
- The first part stays as it is: the 25 modules keep their pinned versions, so earlier grading stays valid.

Out of this change: grading the default set on the new part, and new rules.
