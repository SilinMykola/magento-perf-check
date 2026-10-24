# P-COL-01: calibration sample

5 of 5 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-security/Block/Adminhtml/Session/Activity.php:78` (core)

[P-COL-01] count() on Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection loads every row of the collection to count it. Use getSize(), which runs a COUNT query.

```
    72      /**
    73       * @return bool
    74       * @since 100.1.0
    75       */
    76      public function areMultipleSessionsActive()
    77      {
>   78          return count($this->getSessionInfoCollection()) > 1;
    79      }
    80  
    81      /**
    82       * @return string
    83       * @since 100.1.0
    84       */
```

## 2. `vendor/magento/module-downloadable/Model/Product/Type.php:241` (core)

[P-COL-01] count() on Magento\Downloadable\Model\ResourceModel\Sample\Collection loads every row of the collection to count it. Use getSize(), which runs a COUNT query.

```
   235       *
   236       * @param \Magento\Catalog\Model\Product $product
   237       * @return boolean
   238       */
   239      public function hasSamples($product)
   240      {
>  241          return count($this->getSamples($product)) > 0;
   242      }
   243  
   244      /**
   245       * Check if product can be bought
   246       *
   247       * @param \Magento\Catalog\Model\Product $product
```

## 3. `vendor/magento/module-catalog/Model/ResourceModel/Product/Collection.php:729` (core)

[P-COL-01] count() on Magento\Catalog\Model\ResourceModel\Product\Collection loads every row of the collection to count it. Use getSize(), which runs a COUNT query.

```
   723          $this->prepareStoreId();
   724  
   725          if (isset($this->_selectAttributes['media_gallery'])) {
   726              $this->addMediaGalleryData();
   727          }
   728  
>  729          if (count($this)) {
   730              $this->_eventManager->dispatch('catalog_product_collection_load_after', ['collection' => $this]);
   731          }
   732  
   733          return $this;
   734      }
   735  
```

## 4. `app/code/Magefan/Blog/Model/Post.php:770` (packagist)

[P-COL-01] count() on Magefan\Blog\Model\ResourceModel\Tag\Collection loads every row of the collection to count it. Use getSize(), which runs a COUNT query.

```
   764      /**
   765       * Retrieve post tags count
   766       * @return int
   767       */
   768      public function getTagsCount()
   769      {
>  770          return count($this->getRelatedTags());
   771      }
   772  
   773      /**
   774       * Retrieve post comments
   775       * @param  boolean $active
   776       * @return \Magefan\Blog\Model\ResourceModel\Comment\Collection
```

## 5. `vendor/magento/module-paypal/Model/Billing/Agreement.php:290` (core)

[P-COL-01] count() on Magento\Paypal\Model\ResourceModel\Billing\Agreement\Collection loads every row of the collection to count it. Use getSize(), which runs a COUNT query.

```
   284       *
   285       * @param int $customerId
   286       * @return bool
   287       */
   288      public function needToCreateForCustomer($customerId)
   289      {
>  290          return $customerId ? count($this->getAvailableCustomerBillingAgreements($customerId)) == 0 : false;
   291      }
   292  
   293      /**
   294       * Add order relation to current billing agreement
   295       *
   296       * @param int|\Magento\Sales\Model\Order $orderId
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-security/Block/Adminhtml/Session/Activity.php:78` | FP | The collection is memoized and the activity template iterates it, so rows are loaded anyway. | re-check |  |
| 2 | `vendor/magento/module-downloadable/Model/Product/Type.php:241` | FP | Samples are memoized on the product and samples.phtml iterates them right after hasSamples(). | re-check |  |
| 3 | `vendor/magento/module-catalog/Model/ResourceModel/Product/Collection.php:729` | FP | count($this) runs in _afterLoad(), when the collection is already loaded. | re-check |  |
| 4 | `app/code/Magefan/Blog/Model/Post.php:770` | FP | getRelatedTags() is memoized and the post templates iterate the tags right after the count. |  |  |
| 5 | `vendor/magento/module-paypal/Model/Billing/Agreement.php:290` | TP-low | Rows are loaded only to count, but the filter is the active agreements of one customer, usually none or one. |  |  |
