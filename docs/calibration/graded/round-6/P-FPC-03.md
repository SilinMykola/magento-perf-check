# P-FPC-03: calibration sample

1 of 1 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Mageplaza/Blog/Helper/Data.php:281` (extended)

[P-FPC-03] A value specific to a visitor or a moment is added to the HTTP context: the page cache keeps one copy per value. Use a value shared by many visitors, such as a customer group, and load personal data through customer sections.

```
   275       * Set Customer Id in Context
   276       */
   277      public function setCustomerContextId()
   278      {
   279          $customer = $this->customerSession->getCustomerData();
   280          if (!$this->_httpContext->getValue('mp_customer_id') && $customer) {
>  281              $this->_httpContext->setValue('mp_customer_id', $customer->getId(), 0);
   282          }
   283      }
   284  
   285      /**
   286       * @return DataObject
   287       */
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Mageplaza/Blog/Helper/Data.php:281` | TP | setCustomerContextId() runs in the cacheable blog post view controller and the customer account links plugin and puts the logged-in customer's id into the HTTP context, so each customer gets their own page cache copy. |  |  |
