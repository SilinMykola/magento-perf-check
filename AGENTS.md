# AGENTS.md

Instructions for anyone changing this repository, people and coding agents alike.

## Start here
1. Read `docs/SPEC.md` completely before any work. It is the source of truth: rule catalog, architecture, milestones (M0–M5).
2. Work milestone by milestone. Begin with **M0** (source audit, skeleton, 3 end-to-end rules: X-LAY-01, P-COL-02, P-LOOP-01, ADRs).
3. Record architectural decisions as ADRs in `docs/adr/NNNN-title.md`.

## Non-negotiable rules
- **Source policy (SPEC section 2).** Every rule needs at least one level A (official Adobe/Hyvä docs) or B (Magento core code pinned to a commit, or magento/magento2 issue with measurements) source. Level C (blogs, skills, other standards) is supplementary only. No A/B source means the rule stays experimental.
- **Precision over quantity.** Every rule ships with positive and negative fixtures. Do not add a rule to the default set without calibration (SPEC section 7).
- Do not reimplement checks already covered by magento/magento-coding-standard (SPEC section 4.9).
- Never invent facts, versions or links. If something is unverified, say so in the ADR or PR.

## Environment
- Host: macOS, PHP 8.5, Composer 2.10. Run the tool on the host.
- Test Magento: `~/projects/magento-2.4.9.ddev` (DDEV + Mutagen, vendor synced to host).
  - Run against it with `php bin/magento-perf-check --project=$HOME/projects/magento-2.4.9.ddev`.
  - **Read-only.** Do not modify that project (no composer require, no symlinks into it). See SPEC section 9a.
- Magento 2.4.9 pins `phpstan/phpstan ^1.9` (1.12.33 in lock): PHPStan 1.12 support matters (SPEC section 8).

## Conventions
- Code, docs, README, commit messages: English.
- Each rule: `docs/rules/<ID>.md` with a **Basis** section listing sources and their level.
