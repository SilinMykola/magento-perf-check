# Calibration, round 7

This round measures U-CLI-01, the first upgrade rule (SPEC milestone M7). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** both parts of the calibration corpus ([`corpus.json`](corpus.json)): the Magento 2.4.9 core (`--paths=vendor/magento`), the 25 modules of round 1 (`--paths=app/code`) and the extended part (`--paths=app/code,app/design`), with every experimental rule enabled. The installed version is 2.4.9, so the rule runs without `--target-version`. Run on 2026-11-10 with the code of 0.5.0-dev.
- **Grading:** every finding was graded. `TP` means PHP cannot load the class on 2.4.9; the impact share counts a finding as significant when the module registers the command in `di.xml`, so that every `bin/magento` call fails. The runs are not stored: `runs/` keeps those of rounds 1 and 6, and the findings are listed below.
- **Other rules:** no finding of another rule changed.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [U-CLI-01](../rules/U-CLI-01.md) | error | 9 | 9 | 9 | 0 | 0 | 0 | 100 % | 100 % | experimental |

**U-CLI-01.** The core gives no finding, the 25 modules give 1 and the extended part 8:
- `Snowdog\Menu\Console\Command\NodesValidatorCommand`;
- `MSP\AdminRestriction\Command\RestrictIp` and `MSP\CmsImportExport\Command\ImportPage`;
- `MageMojo\Cron\Console\Command\CronCommand` and `CronExecuteCommand`;
- `Mageplaza\GoogleRecaptcha\Console\Adminhtml\Command\Disable` and `Enable`;
- `ScandiPWA\PersistedQuery\Console\Command\PersistedQueryFlushCommand`;
- `Sivaschenko\CleanMedia\Command\CatalogMedia`.

Each declares `execute()` with no return type in a class that extends Symfony's `Command`, and each module registers it in the `commands` argument of `CommandList` in its `di.xml`, checked on 2026-11-10. Loading the Snowdog class on the 2.4.9 corpus gives `PHP Fatal error: Declaration of ...::execute(...) must be compatible with Symfony\Component\Console\Command\Command::execute(...): int`; the other eight have the same declaration. All of these modules are the newest versions whose requirements allow 2.4.9.

A first run also reported `MagentoHackathon\Composer\Magento\Command\DeployCommand` in the core's `magento/magento-composer-installer`. That package is a Composer plugin, which `bin/magento` does not load, and the plugin does not register that class as a Composer command either. The rule now leaves Composer plugins out, as P-DI-02 does.

## Decision

U-CLI-01 stays experimental: with 9 findings, the data is insufficient, although every one is correct.
