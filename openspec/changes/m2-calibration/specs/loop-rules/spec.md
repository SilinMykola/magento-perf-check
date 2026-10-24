# Spec Delta

## MODIFIED Requirements

### Requirement: P-LOOP-03 save or delete in a loop
P-LOOP-03 SHALL report, as `info`, in loop context:
- `save()` or `delete()` on an `AbstractModel` subtype;
- `save()`, `delete()` or `deleteById()` on a type named `*RepositoryInterface`, or one that implements such an interface;
- `save()` or `delete()` on a resource model (subtype of `Magento\Framework\Model\ResourceModel\Db\AbstractDb`).

Calibration lowered it from `warning`: most correct findings loop over small, capped sets.

#### Scenario: Repository save in foreach
- **WHEN** `$this->productRepository->save($product)` is called inside a `foreach`
- **THEN** P-LOOP-03 reports an info finding

#### Scenario: Save outside loop
- **WHEN** `save()` is called once after the loop
- **THEN** P-LOOP-03 reports nothing
