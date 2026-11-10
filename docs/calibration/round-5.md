# Calibration, round 5

This round measures P-API-01, the first compatibility rule ([ADR 8](../adr/0008-scope-beyond-performance.md)). The verdicts and thresholds are those of round 1 ([`docs/calibration.md`](../calibration.md)). Rules not listed keep their earlier result.

## Run

- **Corpus:** the calibration corpus of round 1: the Magento 2.4.9 core (`--paths=vendor/magento`) and the 25 Packagist modules (`--paths=app/code`), with P-API-01 enabled. Run on 2026-11-10 with the code of 0.5.0-dev.
- **Grading:** every finding was graded. The rule does not claim a cost, so `TP` means a request with the field fails, and the impact share counts findings confirmed on a running store.
- **Other rules:** the core gives 961 findings before the change and 962 after, the new one being the only difference; the modules give 221 both times.

## Results

| Rule | Severity | Findings | Sampled | TP | TP-low | FP | Unclear | Precision | Impact share | Decision |
|---|---|---|---|---|---|---|---|---|---|---|
| [P-API-01](../rules/P-API-01.md) | warning | 1 | 1 | 1 | 0 | 0 | 0 | 100 % | 100 % | experimental |

**P-API-01.** The one finding is `$sort` of `Magento\InventoryInStorePickup\Model\SearchRequest`, built for `SearchRequestInterface`, which has `getSort()` and no `setSort()`. On a running Magento 2.4.9 store, `GET` and `POST /rest/V1/inventory/in-store-pickup/pickup-locations/` with a sort order fail with `"%fieldName" is not supported`, `fieldName` `Sort`; without it, the same request returns a search result. The modules have no finding.

An earlier scan without the conditions on web API input types and on the getter of the requested type gave 11 candidates in the modules and 24 in the core. Two of the core candidates that reach a web API input type are left out by the getter condition: `Category::$url` and `Order::$data` have no getter on their data interfaces, so a request cannot name them.

## Decision

P-API-01 stays experimental: with 1 finding, the data is insufficient.
