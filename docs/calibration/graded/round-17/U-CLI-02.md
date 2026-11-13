# U-CLI-02: calibration grades, round 17

All 21 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-13 by one grader; see [round 17](../../round-17.md). Private stores are named by their letter of [round 15](../../round-15.md), and their code is described, not listed. Every command is registered in `di.xml`.

| # | where | what | verdict | reason |
|---|---|---|---|---|
| 1 | corpus | MSP AdminRestriction, the command that restricts admin access by IP | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 2 | corpus | MSP CmsImportExport, the CMS page import command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 3 | corpus | MageMojo Cron, the cron runner command | TP | `execute()` ends without a `return`; the store's cron jobs would stop running, so it fails with a `TypeError` on Symfony Console 5.4. |
| 4 | corpus | MageMojo Cron, the command that runs one cron job | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 5 | corpus | ScandiPWA PersistedQuery, the cache flush command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 6 | store E | a third-party captcha module's command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 7 | store E | a third-party order deletion module's command | TP | `execute()` has a `return;`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 8 | store E | a custom SEO module's command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 9 | store A | a custom integration module's queue command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 10 | store A | a custom price check command | TP | `execute()` has a `return;`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 11 | store C | a custom cache warmer command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 12 | store C | a custom data migration command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 13 | store D | a custom integration module's import or export command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 14 | store D | a custom integration module's import or export command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 15 | store D | a custom integration module's import or export command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 16 | store D | a custom integration module's import or export command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 17 | store D | a custom integration module's import or export command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 18 | store D | a custom integration module's import or export command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 19 | store D | a custom integration module's import or export command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 20 | store D | a custom image compression command | TP | `execute()` ends without a `return`, so it fails with a `TypeError` on Symfony Console 5.4. |
| 21 | store D | a custom media cleanup command | TP | `execute()` has a `return;`, so it fails with a `TypeError` on Symfony Console 5.4. |
