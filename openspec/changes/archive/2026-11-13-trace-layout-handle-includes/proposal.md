# Proposal

## Why

X-LAY-01 looks at one layout file at a time. In [round 15](../../../docs/calibration/round-15.md) 5 of its 8 false positives on private stores came from that:
- three custom handles that only the customer's order view, print and order e-mail include through `<update handle="..."/>`, which X-LAY-01 reported as if they were pages;
- two theme files of Adobe Commerce customer account pages: the theme file has no `<update handle="customer_account"/>`, while the module's own layout file of the same handle has it.

Magento builds a page from every layout file of each of its handles and merges a handle named in `<update handle="..."/>` recursively ([Layout/Merge.php#L619-L630](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Model/Layout/Merge.php#L619-L630), [#L722-L727](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Model/Layout/Merge.php#L722-L727)). So a handle that only personal pages include never reaches a cacheable page, and a page is an account page when any of its layout files includes `customer_account`.

## What Changes

- An index of the storefront layout handles of the project: for each handle, the handles whose layout files include it, and whether one of its layout files includes `customer_account`. It reads the `view/frontend` and `view/base` layouts and the storefront routes of every enabled module, Composer packages included, and the layouts of frontend themes in `app/design` and in Composer packages, leaving out a theme's folder of a module that is not enabled, as Magento does ([app/etc/di.xml#L926-L935](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/etc/di.xml#L926-L935), [ModuleOutput.php#L56-L64](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/File/Collector/Decorator/ModuleOutput.php#L56-L64)).
- X-LAY-01 and X-LAY-03 leave out a handle that another of its layout files makes a customer account page.
- X-LAY-01 also leaves out a handle that other handles include when every handle that includes it is personal. A route's own handle is a page and is not judged by its includers. X-LAY-03 does not follow includes: a controller may add a shop-wide handle itself, as core adds `catalog_product_view_type_<type>`, which only the cart's configure page includes.
- A handle that nothing includes keeps today's behavior.
- The account check inside a file counts `<update handle="customer_account"/>` only as a direct child of the root, in any letter case, as core merges it ([Layout/Merge.php#L722-L727](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Model/Layout/Merge.php#L722-L727)); one nested in `<body>` no longer silences the file.
