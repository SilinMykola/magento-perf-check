# AGENTS.md

Instructions for anyone changing this repository, people and coding agents alike.

## Start here
1. Read [`docs/SPEC.md`](docs/SPEC.md): the rule catalog, the architecture and the milestones. It is the source of truth.
2. Changes are planned with [OpenSpec](https://github.com/Fission-AI/OpenSpec): `openspec/specs/` holds the current requirements, `openspec/changes/` the change in progress. A new rule or a behavior change starts as an OpenSpec change.
3. Record architectural decisions as ADRs in `docs/adr/NNNN-title.md`.

## Non-negotiable rules
- **Source policy (SPEC section 2).** Every rule needs at least one level A (official Adobe or Hyvä documentation) or B (Magento core code pinned to a commit, or a magento/magento2 issue with measurements) source. Level C (blogs, skills, other standards) is supplementary only. Without an A or B source a rule stays experimental.
- **Precision over quantity.** Every rule ships with positive and negative fixtures and `docs/rules/<ID>.md` with a **Basis** section. A rule enters the default set only after calibration (SPEC section 7, [`docs/calibration.md`](docs/calibration.md)).
- Do not reimplement checks that [magento/magento-coding-standard](https://github.com/magento/magento-coding-standard) already covers (SPEC section 4.9).
- Never invent facts, versions or links. If something is unverified, say so in the ADR or the pull request.
- A rule added to or removed from the default set, or a changed default severity, goes into `CHANGELOG.md`: it can change a user's CI result.

## Checks before a pull request

```sh
composer test        # PHPUnit, with PHPStan 2.x
bin/test-phpstan1    # the same suite against PHPStan 1.12
bin/test-install 1.12.1 0.11.0 installer skipped   # Composer install of this checkout into an empty project, then a run
composer analyse     # PHPStan at level max on src/
openspec validate --specs
```

Both PHPStan majors must pass: Magento 2.4.9 pins `phpstan/phpstan ^1.9` (1.12 in its lock file), see [ADR 1](docs/adr/0001-support-phpstan-1-12-and-2.md).

## Running against a Magento project
- Run the tool on the host against a Magento checkout: `php bin/magento-perf-check --project=/path/to/magento`. The project needs its own `composer install`; no database or container is needed.
- Treat that project as read-only: do not install this package into it through a symlinked path repository, and do not change its files (SPEC section 9a). To test an install, use a copy.

## Conventions
- Code, comments, documentation and commit messages are in English.
- Commit messages say what changed and why.
