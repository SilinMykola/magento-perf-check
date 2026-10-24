# X-EVT-01: calibration sample

2 of 2 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-two-factor-auth/etc/adminhtml/events.xml:18` (core)

[X-EVT-01] Observer "two_factor_auth_admin_user_load_after" on "admin_user_load_after" runs on every load of this entity type. Observe a more specific event if one exists.

```
    12          <observer name="two_factor_auth_check" instance="Magento\TwoFactorAuth\Observer\ControllerActionPredispatch"/>
    13      </event>
    14      <event name="admin_user_save_after">
    15          <observer name="two_factor_auth_admin_user_save_after" instance="Magento\TwoFactorAuth\Observer\AdminUserSaveAfter"/>
    16      </event>
    17      <event name="admin_user_load_after">
>   18          <observer name="two_factor_auth_admin_user_load_after" instance="Magento\TwoFactorAuth\Observer\AdminUserLoadAfter"/>
    19      </event>
    20  </config>
```

## 2. `vendor/magento/module-catalog-inventory/etc/events.xml:13` (core)

[X-EVT-01] Observer "inventory" on "catalog_product_load_after" runs on every load of this entity type. Observe a more specific event if one exists.

```
     7  -->
     8  <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
     9      <event name="catalog_block_product_status_display">
    10          <observer name="inventory" instance="Magento\CatalogInventory\Observer\DisplayProductStatusInfoObserver"/>
    11      </event>
    12      <event name="catalog_product_load_after">
>   13          <observer name="inventory" instance="Magento\CatalogInventory\Observer\AddInventoryDataObserver"/>
    14      </event>
    15      <event name="sales_quote_item_qty_set_after">
    16          <observer name="inventory" instance="Magento\CatalogInventory\Observer\QuantityValidatorObserver"/>
    17      </event>
    18      <event name="checkout_submit_all_after">
    19          <observer name="inventory" instance="Magento\CatalogInventory\Observer\CheckoutAllSubmitAfterObserver"/>
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-two-factor-auth/etc/adminhtml/events.xml:18` | TP-low | Adminhtml-only observer adds tfa_providers on admin_user load; admin users load about once per admin request, so the extra lookup is negligible. | re-check |  |
| 2 | `vendor/magento/module-catalog-inventory/etc/events.xml:13` | TP | AddInventoryDataObserver calls assignStatusToProduct, which fetches stock status via stockRegistryProvider on every product model load. | re-check |  |
