# Proposal

## Why

M2 calibrated the rules, so the tool is ready for its first release (SPEC section 9, M3). Today it only runs from a clone of a private repository: there is no Composer install path that has been tested on a real Magento project, the README mixes a pitch with reference material, and documentation links in findings point at local files, which is useless in GitHub code scanning. The M3 acceptance criterion is: the package installs and works on a clean project within 2 minutes.

## What Changes

- **Installable package.**
  - A version constant and `--version` on the command.
  - `.gitattributes` with `export-ignore` for what users do not need (tests, OpenSpec, calibration data, development scripts), so the Composer dist archive stays small. `docs/rules/` stays in the archive.
  - `composer.json` gets `homepage`, `support` and `authors`.
  - `CHANGELOG.md` (Keep a Changelog format) with the 0.1.0 entry; a test keeps it in step with the version constant.
- **Documentation links become URLs.** Every finding links to the rule page on GitHub at the release tag (`https://github.com/SilinMykola/magento-perf-check/blob/v<version>/docs/rules/<ID>.md`), so SARIF `helpUri`, JSON and the table carry a link that works anywhere. **BREAKING** for anyone parsing `docUrl` as a local path (no released consumers exist).
- **Install verification.** Install the package with Composer into a throwaway copy of the test Magento project (path repository with `"symlink": false`, SPEC section 9a), run it there with the defaults, and record the wall-clock time of install plus first run in `docs/runs/m3-install.md`. The test project itself stays read-only.
- **README rewrite** for GitHub: one-line pitch, badges, a real finding as example output, a three-line quick start, a "why trust it" section (A/B sources, calibration numbers), and real findings from the calibration corpus. Reference material moves out:
  - `docs/rules.md`: the rule tables (default and experimental);
  - `docs/configuration.md`: options, exit codes, the configuration file, use inside an existing PHPStan setup;
  - `docs/baseline.md`;
  - `docs/ci.md`: GitHub Actions, GrumPHP, other CI.
- **Guidance for coding agents** (SPEC M3 "AGENTS.md and skill"):
  - `docs/agents.md` with a snippet projects paste into their own `AGENTS.md`;
  - a skill in `resources/skill/magento-perf-check/SKILL.md` that tells an agent how to run the tool, read the JSON output and treat findings.
  - The repository's own `AGENTS.md` is reworded for outside contributors.
- **English SPEC.** `docs/SPEC.md` is translated to English, as the repository convention requires; content and decisions unchanged.
- **Publication, only after the owner's explicit OK** (outward-facing and hard to reverse): make the repository public, submit it to Packagist, tag `v0.1.0`, then repeat the install check from Packagist.
- **PRs to module authors** (SPEC M3): prepare a list of candidate true positives from the calibration samples in popular modules. Opening any PR needs the owner's OK per PR; drafts are not kept in the repository.

Out of M3: new rules and the follow-ups in `docs/calibration/follow-ups.md` (M4), a PHAR build, GitLab Code Quality format.

## Capabilities

### New Capabilities

- `distribution`: how the package is versioned, what its Composer archive contains, the changelog, and the verified install path into a Magento project.
- `user-documentation`: the README structure, the split reference docs, and the guidance and skill for coding agents.

### Modified Capabilities

- `rule-documentation`: the documentation link in findings becomes a versioned GitHub URL instead of a local path.
- `cli`: adds `--version`, and `--output=<file>` to write the report to a file (requested by the owner during M3, for Git hooks).
- `loop-rules`: P-LOOP-02 no longer reports a collection created in the iterated expression of a top-level `foreach` (found on a real project during M3; the corpus findings did not change).
- `event-rules`: X-EVT-01 names `core_collection_abstract_load_after` as firing after every collection load, instead of calling it an entity event (found on a real project during M3).
- `calibration`: the default-set rule table it keeps in step lives in `docs/rules.md` instead of the README.

## Impact

- **Code:** `RuleRegistry::docUrl()`, a version constant, `Options`/`Application` for `--version`; tests for both and for the changelog.
- **Files:** `.gitattributes`, `CHANGELOG.md`, `composer.json`, `README.md`, new `docs/*.md`, `resources/skill/`, `AGENTS.md`, translated `docs/SPEC.md`, `docs/runs/m3-install.md`.
- **Data:** a temporary copy of the test Magento project under `var/` (git-ignored) for the install check; reuses the M2 corpus copy if it is still intact.
- **Outward-facing:** GitHub visibility, Packagist, the tag and PRs to third parties happen only after the owner confirms each step.
