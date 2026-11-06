# Check on older Magento versions

- **Date:** 2026-11-06.
- **Tool:** commit `5b56db3`, run from its own clone with `--project`.
- **Host:** PHP 8.5.11, Composer 2.10.3. PHP 8.1 to 8.4 were not tried against these projects.
- **Projects:** `magento/project-community-edition` from `https://mirror.mage-os.org`, installed with `--no-scripts --no-plugins --ignore-platform-reqs`. Without plugins `app/etc` is not deployed, so its PHP files were copied by hand from `vendor/magento/magento2-base/app/etc`.
- **Scope:** `--paths=vendor/magento/module-cms` only, not a whole project and not `app/code`.

## Result

| Magento | `phpstan/phpstan` in the project's `require-dev` | `composer require --dev` of the tool (dry run) | Run with `--project` |
|---|---|---|---|
| 2.4.4 | `~1.2.0` | conflict on `phpstan/phpstan` | exit 1, 11 findings |
| 2.4.3 | `^0.12.77` | conflict on `phpstan/phpstan` | exit 1, 11 findings |
| 2.4.0 | `>=0.12.3 <=0.12.23` (Composer metadata of the mirror) | not tried | exit 1, 11 findings |

Exit code 1 means the run completed and reported findings. All three versions reported the same rules: P-DI-01 (1), P-DI-06 (6), P-LOOP-01 (2), P-LOOP-03 (2). Those findings were not graded, so this shows that the tool runs on these versions, not how precise it is there. Precision is measured on Magento 2.4.9 only.

The dry runs also reported that the host's PHP does not satisfy the projects' PHP constraint. The PHPStan conflict does not depend on the host's PHP.

Composer metadata of the mirror gives the constraint in the other stock projects: `>=0.12.3 <=0.12.23` in 2.4.1 and 2.4.2, `^1.6.8` in 2.4.5, `^1.9` in 2.4.6 to 2.4.9. The install was not tried on 2.4.5 to 2.4.8.

Magento 2.3 was not checked: the mirror does not offer it.
