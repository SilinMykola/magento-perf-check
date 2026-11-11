# P-LOOP-04: calibration sample

4 of 4 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/design/frontend/Hyva/default/Magento_ProductAlert/templates/email/stock.phtml:22` (extended)

[P-LOOP-04] getProductUrl() builds a product URL inside a loop: one URL rewrite query per product without a request path. Call addUrlRewrite() on the product collection.

```
    16          <?= $escaper->escapeHtml(__('In stock alert! We wanted you to know that these products are now available:')) ?>
    17      </p>
    18      <table>
    19      <?php foreach ($products as $product): ?>
    20          <tr>
    21              <td class="col photo">
>   22                  <a href="<?= $escaper->escapeUrl($product->getProductUrl()) ?>"
    23                     title="<?= $escaper->escapeHtml($product->getName()) ?>"
    24                     class="product photo">
    25                      <?= $block->getImage($product, 'product_thumbnail_image', ['class' => 'photo image'])->toHtml() ?>
    26                  </a>
    27              </td>
    28              <td class="col item">
```

## 2. `app/design/frontend/Hyva/default/Magento_ProductAlert/templates/email/price.phtml:35` (extended)

[P-LOOP-04] getProductUrl() builds a product URL inside a loop: one URL rewrite query per product without a request path. Call addUrlRewrite() on the product collection.

```
    29                      <?= $block->getImage($product, 'product_thumbnail_image', ['class' => 'photo image'])->toHtml() ?>
    30                  </a>
    31              </td>
    32              <td class="col item">
    33                  <p>
    34                      <strong class="product name">
>   35                          <a href="<?= $escaper->escapeUrl($product->getProductUrl()) ?>">
    36                              <?= $escaper->escapeHtml($product->getName()) ?>
    37                          </a>
    38                      </strong>
    39                  </p>
    40                  <?php if ($shortDescription = $block->getFilteredContent($product->getShortDescription())): ?>
    41                      <p><small><?= /* @noEscape */  $shortDescription ?></small></p>
```

## 3. `app/design/frontend/Hyva/default/Magento_ProductAlert/templates/email/price.phtml:26` (extended)

[P-LOOP-04] getProductUrl() builds a product URL inside a loop: one URL rewrite query per product without a request path. Call addUrlRewrite() on the product collection.

```
    20      </p>
    21      <table>
    22      <?php /** @var Product $product */ ?>
    23      <?php foreach ($products as $product): ?>
    24          <tr>
    25              <td class="col photo">
>   26                  <a href="<?= $escaper->escapeUrl($product->getProductUrl()) ?>"
    27                     title="<?= $escaper->escapeHtml($product->getName()) ?>"
    28                     class="product photo">
    29                      <?= $block->getImage($product, 'product_thumbnail_image', ['class' => 'photo image'])->toHtml() ?>
    30                  </a>
    31              </td>
    32              <td class="col item">
```

## 4. `app/design/frontend/Hyva/default/Magento_ProductAlert/templates/email/stock.phtml:31` (extended)

[P-LOOP-04] getProductUrl() builds a product URL inside a loop: one URL rewrite query per product without a request path. Call addUrlRewrite() on the product collection.

```
    25                      <?= $block->getImage($product, 'product_thumbnail_image', ['class' => 'photo image'])->toHtml() ?>
    26                  </a>
    27              </td>
    28              <td class="col item">
    29                  <p>
    30                      <strong class="product name">
>   31                          <a href="<?= $escaper->escapeUrl($product->getProductUrl()) ?>">
    32                              <?= $escaper->escapeHtml($product->getName()) ?>
    33                          </a>
    34                      </strong>
    35                  </p>
    36                  <?php if ($shortDescription = $block->getFilteredContent($product->getShortDescription())): ?>
    37                      <p><small><?= /* @noEscape */  $shortDescription ?></small></p>
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/design/frontend/Hyva/default/Magento_ProductAlert/templates/email/stock.phtml:22` | TP-low | A product alert email lists the few products of one customer, loaded by the alert cron through the repository; the first getProductUrl() per product runs one url_rewrite lookup. |  |  |
| 2 | `app/design/frontend/Hyva/default/Magento_ProductAlert/templates/email/price.phtml:35` | FP | The getProductUrl() call on line 26 of the same iteration already stored the request path on the product, so this second call runs no query. |  |  |
| 3 | `app/design/frontend/Hyva/default/Magento_ProductAlert/templates/email/price.phtml:26` | TP-low | A product alert email lists the few products of one customer, loaded by the alert cron through the repository; the first getProductUrl() per product runs one url_rewrite lookup. |  |  |
| 4 | `app/design/frontend/Hyva/default/Magento_ProductAlert/templates/email/stock.phtml:31` | FP | The getProductUrl() call on line 22 of the same iteration already stored the request path on the product, so this second call runs no query. |  |  |
