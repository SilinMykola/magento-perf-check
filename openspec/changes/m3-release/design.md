# Design

## Context

After M2 the tool has 29 calibrated rules (24 default, 5 experimental) and green CI, but it has only ever run from a clone. `bin/magento-perf-check` already finds `vendor/autoload.php` both in a clone and when installed under `vendor/silin/magento-perf-check/bin`, and the PHPStan runner locates `phpstan/phpstan` through Composer's installed packages, so the dependency layout is expected to work but is unverified. Documentation links are built from `RuleRegistry::DOCS_BASE`, a local path, and the SARIF reporter turns that into a `file://` URI. There is no version anywhere in the code, no `.gitattributes` and no changelog. `docs/SPEC.md` is in Russian.

## Goals / Non-Goals

**Goals:**
- `composer require --dev silin/magento-perf-check` works on stock Magento 2.4.9 and the first run takes under 2 minutes including install.
- Links in findings work for anyone who sees the report.
- The README sells and starts; reference pages answer details.
- Publication steps are prepared so that each one is a single action the owner approves.

**Non-Goals:**
- A PHAR or a global install mode.
- New rules, rule fixes or recalibration.
- Automating releases (tagging from CI, release notes generation).

## Decisions

### D1. Version lives in a constant
A `Version::VERSION` constant (namespace root) holds the version, `0.1.0` for this release. Alternatives: `Composer\InstalledVersions::getPrettyVersion()` returns `dev-main` or a commit in a clone and cannot produce a tag name, and the docs link needs a tag. The cost is one manual bump per release, guarded by a test that compares the constant with the newest `CHANGELOG.md` entry.

Between releases the constant carries a `-dev` suffix (for example `0.2.0-dev`), the changelog has an `Unreleased` section, and the test checks that the constant without the suffix is higher than the newest released entry.

### D2. Documentation links point at the tag, or `main` for dev builds
`docUrl()` returns `https://github.com/SilinMykola/magento-perf-check/blob/v<VERSION>/docs/rules/<ID>.md`. For a `-dev` version it uses `blob/main`, because a rule added after the last tag has no page there. The SARIF reporter uses the URL as-is instead of wrapping it in `file://`. The local `docs/rules/` copy still ships in the package for offline reading, but findings no longer point at it.

### D3. `.gitattributes` decides the archive
Packagist serves GitHub's `git archive` zip, which honors `export-ignore`. Ignored: `tests/`, `openspec/`, `docs/calibration/`, `docs/runs/`, `docs/SPEC.md`, `docs/source-audit.md`, `docs/adr/`, `bin/calibration/`, `bin/test-phpstan1`, `phpunit.xml.dist`, `phpstan.neon.dist`, `.github/`, `AGENTS.md`, `.gitattributes`, `.gitignore`. Kept: everything users read or execute. A test runs `git archive HEAD --format=tar | tar -t` and checks the list, so a new top-level directory has to be classified on purpose. ADRs and the SPEC stay on GitHub, which the README links.

### D4. Install check in a disposable copy
A fresh `rsync` copy of `/path/to/magento-2.4.9` under `var/install-check/` (git-ignored; rsync time not counted). In the copy:
1. build the dist archive with `git archive --format=zip`, as Packagist will serve it, and offer it through a `package` repository with version 0.1.0 and a `file://` dist URL;
2. `time composer require --dev silin/magento-perf-check:0.1.0` with the copy's own lock file;
3. `time vendor/bin/magento-perf-check` from the copy's root.

The `composer.lock` diff must show only the package itself and its new dependencies (`nette/neon` is not in Magento's lock). If Composer needs repo.magento.com credentials to resolve, the run uses the host's existing Composer auth; their presence is reported, never their value. After publication the same check is repeated with Packagist instead of the path repository. Results go to `docs/runs/m3-install.md`.

Changed during apply: the plan was a path repository with `"symlink": false` (SPEC section 9a). It took 333 s because Composer mirrors the whole development checkout, including the git-ignored 2 GB corpus in `var/`, which no user would get. The archive install has no symlink either and measures what Packagist delivers. Stock 2.4.9 has an empty `app/code`, so the first run is a "clean project" run; a second run with `--paths=vendor/magento/module-catalog` shows real findings.

### D5. README content from calibration
Example output is a `--format=table` run on the corpus copy restricted to one module, picked so that the shown findings are TP rows in `docs/calibration/graded/`. Numbers in "why trust it" (rule counts, precision range, corpus size) are copied from `docs/calibration.md`, not recomputed. Badges: CI status, license, PHP version, Packagist version; the Packagist badge is added only after publication, so no badge points at a missing package.

### D6. Skill format
`resources/skill/magento-perf-check/SKILL.md` uses the Agent Skills layout (front matter with `name` and `description`, then instructions), so it can be copied into a project's or user's skills directory as-is. It tells the agent to: run `vendor/bin/magento-perf-check --format=json` (or with `--paths` for the module being changed), group findings by rule, open each rule page before changing code, prefer the fix shown there, and not suppress a finding without a reason in the suppression comment. `docs/agents.md` carries a shorter version for `AGENTS.md` and explains where to copy the skill. A test checks the front matter and that every `--option` in both files is an option the CLI accepts.

### D7. SPEC translation
`docs/SPEC.md` is translated to English in place; tables, rule IDs, links and decisions stay unchanged. The translation is reviewed against the original by diffing the structure (headings, table rows, link targets must match one-to-one).

### D8. Publication order (each step needs the owner's OK)
1. Push the M3 commits; CI green.
2. Make the repository public.
3. Submit `https://github.com/SilinMykola/magento-perf-check` to Packagist and enable the GitHub hook.
4. Tag `v0.1.0` and push the tag; create a GitHub release from the changelog entry.
5. Repeat the install check from Packagist; add the Packagist badge.

### D9. PRs to module authors
From `docs/calibration/graded/`, list TP findings in third-party modules (not core) with the module, version, file, line and rule. The list stays outside the repository (it names third-party code issues) and is handed to the owner; each PR is opened only after the owner picks it.

## Risks / Trade-offs

- [Composer resolution on 2.4.9 may pull a newer `webonyx/graphql-php` or touch `phpstan/phpstan`] → the lock diff check catches it; the constraints already accept the locked versions (15.x and 1.12).
- [Links to `blob/v0.1.0` are dead until the repo is public and the tag exists] → acceptable; the tag is created in the same release step.
- [Manual version bump forgotten] → the changelog test fails.
- [Host PHP 8.5 vs. the project's platform config] → if Composer refuses on platform checks, the run uses the copy's own `config.platform`; any override is recorded in the run log.

## Migration Plan

No released users. Local scripts that read `docUrl` as a path (none in the repository besides the reporters) switch to the URL.

## Open Questions

- None blocking. The owner decides the timing of each publication step and which module PRs to open.
