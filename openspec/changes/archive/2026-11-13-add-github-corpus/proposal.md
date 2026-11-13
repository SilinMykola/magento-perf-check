# Proposal

## Why

Every module in the corpus comes from Packagist, chosen by downloads. Much Magento code that shops install is published only as a GitHub repository, or has few downloads: free modules of extension vendors, agency tools, developer utilities. The rules have not seen that code. The held-out checks ran on Packagist modules too, and their copies were not kept.

## What Changes

- **A third corpus part, `github`.** `bin/calibration/build-corpus github` builds `var/corpus/github`. It is a copy of the `extended` part and of the `packagist` modules, made with hard links so that it takes almost no disk space. Into its `app/code` go the 50 most-starred GitHub repositories with the topic `magento2-module`, not archived and not forks, that hold one module and fit the copy.
- **Clones next to the corpus.** The repositories are cloned with `--depth 1` into `var/corpus/github-clones`, git-ignored like the rest of `var/corpus`. A clone that is already there is reused as it is, so a rebuild on the same machine takes the same commits.
- **Selection.** A repository is passed over when:
  - its module name or Composer name is already in the copy;
  - it has no `composer.json` of type `magento2-module`, or no `etc/module.xml` at its root or in `src/`;
  - a requirement is neither installed in the copy at a version that fits nor a module of the copy;
  - its `registration.php` holds more than the standard call;
  - it contains a symbolic link, or cannot be cloned.

  `cli_commands.php` is removed, as in the `extended` part.
- **Manifest.** `docs/calibration/corpus.json` gets a `github` section: each repository with its Composer name, module, stars on the selection date and commit, and the skipped repositories with reasons.
- **Runs.** `bin/calibration/run github` runs every rule, experimental rules included, on the paths of the new modules only, and writes to `runs/github/`.

- **Third held-out check.** Up to 12 findings per rule of the default set are graded in `docs/calibration/held-out-3.md`, with a fixed seed, as in the second check. Like the earlier checks it decides nothing; a false positive it finds is fixed in the rule.

Out of this change: grading the experimental rules on this part.
