# Proposal

## Why

Several experimental rules cannot be decided for lack of findings: X-GQL-02, U-JS-01 and P-LOOP-06 have fewer than the 10 graded findings a decision needs, and the planned rules P-EXT-01 and P-FPC-07 find about 6 and 7 candidates on the 175 modules of the corpus (measured with a text search on 2026-11-15). The `github` part took the 50 most-starred GitHub repositories with the topic `magento2-module`; the next ones by stars have not been seen by any rule.

## What Changes

- **A fourth corpus part, `github2`.** `bin/calibration/build-corpus github2` builds `var/corpus/github2` the way the `github` part is built: a hard-linked copy of the `extended` part and of the `packagist` modules, into whose `app/code` go 100 more GitHub repositories, most stars first, with the same selection, passing over every repository whose module or Composer name is already in the `github` part as well. A repository whose module has the name of a module of the Magento installation is passed over too: a first build took one that ships its own `Magento_Customer`, which the check of `app/code` misses because core modules live in `vendor/`. No module of the `github` part has such a name, so that part would be built the same. The clones go to the same `var/corpus/github-clones`. The modules to pass over come from the `github` section of the manifest, so a rebuild of `github` calls for a rebuild of `github2`.
- **Manifest.** `docs/calibration/corpus.json` gets a `github2` section with the same fields as `github`.
- **Runs.** `bin/calibration/run github2` runs every rule, experimental rules included, on the paths of its modules only, and writes to `runs/github2/`.
- The `packagist`, `extended` and `github` parts stay unchanged, so earlier grading stays valid.

Out of this change: grading on the new part, which a later round does.
