# P-LOOP-06: calibration sample

1 of 1 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Dotdigitalgroup/Email/Model/Product/Attribute.php:151` (extended)

[P-LOOP-06] getAttributeText() is called inside a loop: with the table source model, the default for a select attribute, one query per call. Load the options once with getAllOptions() and look labels up in that array.

```
   145  
   146                  //fetch attribute value from product depending on input type
   147                  switch ($inputType) {
   148                      case 'multiselect':
   149                      case 'select':
   150                      case 'dropdown':
>  151                          $value = $productModel->getAttributeText($attributeCode);
   152                          break;
   153                      case 'date':
   154                      case 'datetime':
   155                          $value = $this->dateTimeFactory->create()->date(
   156                              \DateTime::ATOM,
   157                              $productModel->getData($attributeCode)
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Dotdigitalgroup/Email/Model/Product/Attribute.php:151` | TP | Catalog and order sync crons call this per product, and each configured select attribute runs one Table source option query per product. |  |  |
