# Tasks

## 1. Version and links

- [x] 1.1 Add the `Version` constant (`0.1.0`) and `--version` to the command; tests for the output, the exit code and that no project is checked (D1)
- [x] 1.2 Build documentation links as GitHub URLs at `v<version>`, or `main` for `-dev`; SARIF uses the URL without `file://`; update reporter tests (D2)
- [x] 1.3 Add `CHANGELOG.md` with the 0.1.0 entry (default and experimental rules, formats, baseline, CI examples) and the version/changelog test, including the `-dev` case

## 2. Package contents

- [x] 2.1 Add `.gitattributes` with `export-ignore` entries (D3) and a test that lists `git archive HEAD` and checks kept and excluded paths
- [x] 2.2 Add `homepage`, `support` (issues, source) and `authors` to `composer.json`; `composer validate --strict` passes

## 3. Install check

- [x] 3.1 Make a fresh copy of the test Magento project under `var/install-check/`; confirm the original is untouched (D4)
- [x] 3.2 Install from the `git archive` zip (a path repository with `"symlink": false` was tried first, see design D4), time install and the first run without options, inspect the `composer.lock` diff
- [x] 3.3 Run with `--paths=vendor/magento/module-catalog` in the copy to confirm findings and links in an installed package
- [x] 3.4 Write `docs/runs/m3-install.md`: versions, commands, times, lock diff summary, outcome against the 2-minute criterion

## 4. Documentation

- [x] 4.1 Move reference material from the README into `docs/rules.md`, `docs/configuration.md`, `docs/baseline.md` and `docs/ci.md`; fix links pointing at README sections
- [x] 4.2 Test that `docs/rules.md` matches the registry (table by status, severity) and that README links every reference page
- [x] 4.3 Pick example output and real findings from TP rows in `docs/calibration/graded/`, rerunning the tool on the corpus copy for the exact output (D5)
- [x] 4.4 Rewrite `README.md`: pitch, badges (no Packagist badge yet), example output, three-line quick start, why trust it, real findings, links
- [x] 4.5 Write `docs/agents.md` and `resources/skill/magento-perf-check/SKILL.md`; test front matter and that every option shown exists (D6)
- [x] 4.6 Reword the repository's `AGENTS.md` for outside contributors
- [x] 4.7 Translate `docs/SPEC.md` to English; check headings, table rows and links match the original one-to-one (D7)

- [x] 4.8 Fix P-LOOP-02 reporting a collection created in the iterated expression of a top-level `foreach` (found on a real project); fixture cases for it and for an inner loop; corpus re-run shows no change in default-set findings
- [x] 4.9 X-EVT-01: name `core_collection_abstract_load_after` as firing after every non-EAV collection load (found on a real project); fixture, message test, rule page with a pinned core source
- [x] 4.10 README: run without installing, next to the quick start
- [x] 4.11 `--output=<file>`: report to a file with a one-line summary, checked before the analysis; tests; Git pre-commit hook example in `docs/ci.md`, checked in a throwaway repository
- [x] 4.12 Fixes from an independent review: P-LOOP-02 again reports collections created in loop conditions and the step of `for` (regression of 4.8), X-EVT-01 names non-EAV collections only, `--output` rejects a directory and the `--baseline` file before the analysis, links from shipped docs to files outside the Composer archive point at GitHub, the archive test runs in a worktree; corpus re-run shows no change in default-set findings

## 5. Verification

- [x] 5.1 `composer test`, `bin/test-phpstan1`, `composer analyse`, `openspec validate --specs` and `openspec validate m3-release` pass
- [x] 5.2 Break each new behavior covered by a new test (version output, URL format, changelog check, archive list, rules list, skill options), see the test fail, restore
- [x] 5.3 Show the owner the changed files and wait for OK before pushing

## 6. Publication (each step only after the owner's explicit OK)

- [x] 6.1 Push; CI green
- [x] 6.2 Make the repository public
- [ ] 6.3 Submit to Packagist and enable the GitHub hook
- [x] 6.4 Tag `v0.1.0`, push the tag, create the GitHub release from the changelog entry
- [ ] 6.5 Repeat the install check from Packagist, add the result to `docs/runs/m3-install.md`, add the Packagist badge
- [ ] 6.6 Hand the owner the list of third-party TP findings for possible PRs (kept outside the repository); open PRs only for the ones the owner picks (D9)
