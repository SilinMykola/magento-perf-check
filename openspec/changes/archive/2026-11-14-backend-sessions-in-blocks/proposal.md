# Proposal

## Why

P-FPC-02 reports reads of customer data from a session in frontend blocks and view models. It also reports the backend sessions (`Magento\Backend\Model\Session`, `Magento\Backend\Model\Session\Quote`, `Magento\Backend\Model\Auth\Session`). Magento 2.4.9 core sets their customer and quote only while an order is created in the admin ([Order/Create.php#L132-L139](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Sales/Controller/Adminhtml/Order/Create.php#L132-L139)); on the storefront `Session\Quote::getQuote()` returns a new, empty quote ([Session/Quote.php#L158-L187](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Backend/Model/Session/Quote.php#L158-L187)). The storefront does write other data into `Magento\Backend\Model\Session`: the contact form keeps the visitor's input there through `DataPersistor`, but no customer or quote. A block that reads them builds an admin form, such as a payment form for orders created in the admin, and puts no customer of the storefront into a cached page. Of the P-FPC-02 findings graded in earlier rounds that the corpus still shows, 9 of the 35 false ones are such calls, in two admin payment forms of `adyen/module-payment`.

## What Changes

- P-FPC-02 no longer reports a call on a backend session or a subtype of one.
