# U-CLI-01: calibration grades, round 20

4 of 4 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Divante/PimcoreIntegration/Console/Command/AssetsImportCommand.php:71` | TP | The command extends Symfony Command (Console 7.4.14 installed, execute(): int) and is registered in CommandList in di.xml, so every bin/magento call, cron included, fails with a fatal error. |
| 2 | `app/code/Divante/PimcoreIntegration/Console/Command/CategoryImportCommand.php:78` | TP | The command extends Symfony Command (Console 7.4.14 installed, execute(): int) and is registered in CommandList in di.xml, so every bin/magento call, cron included, fails with a fatal error. |
| 3 | `app/code/Divante/PimcoreIntegration/Console/Command/ProductImportCommand.php:77` | TP | The command extends Symfony Command (Console 7.4.14 installed, execute(): int) and is registered in CommandList in di.xml, so every bin/magento call, cron included, fails with a fatal error. |
| 4 | `app/code/Macopedia/CategoryImporter/Console/Command/CategoriesCommand.php:137` | TP | The command extends Symfony Command (Console 7.4.14 installed, execute(): int) and is registered in CommandList in di.xml, so every bin/magento call, cron included, fails with a fatal error. |
