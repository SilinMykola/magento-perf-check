# X-GQL-02: calibration grades, round 22

3 of 3 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/MageOS/Blog/Model/Resolver/Post/AuthorResolver.php:42` | TP | `BlogPost.author` is reached through a list of posts (`items: [BlogPost!]!`, 20 per page by default), and `AuthorRepository::getById()` loads without a cache: one query per post. |
| 2 | `app/code/MageOS/Blog/Model/Resolver/Post/CategoriesResolver.php:40` | TP | For each post of the list it loads every category with `getById()`, a load plus link queries. |
| 3 | `app/code/MageOS/Blog/Model/Resolver/Post/TagsResolver.php:40` | TP | For each post of the list it loads every tag the same way: queries grow with posts times tags. |

The Magento 2.4.9 core, which no earlier round graded for this rule, gives 16 more, all graded ([run](../../runs/core-x-gql-02-round-22.json)):

| # | where | verdict | reason |
|---|---|---|---|
| 4 | `vendor/magento/module-bundle-graph-ql/Model/Resolver/BundlePriceDetails.php:69` | FP | `cartRepository->get()` of the item's quote id returns the quote that the cart query loaded already (`QuoteRepository::$quotesById`): all items of the list share it, so no query runs per item. |
| 5 | `vendor/magento/module-catalog-graph-ql/Model/Resolver/Category/ProductsCount.php:72` | TP | One `COUNT` query per category, and `product_count` is reached through category lists and trees. |
| 6 | `vendor/magento/module-catalog-graph-ql/Model/Resolver/CategoryTree.php:77` | FP | For a node of `children` the resolver returns the children the hydrator set, before the query; the query runs only for the top-level `category` field. |
| 7 | `vendor/magento/module-catalog-inventory-graph-ql/Model/Resolver/OnlyXLeftInStockResolver.php:54` | TP-low | `productRepository->get()` per configurable product of a list, but in this install `Magento_InventoryGraphQl` redefines the field with its own resolver, so the file does not run. |
| 8 | `vendor/magento/module-catalog-inventory-graph-ql/Model/Resolver/QuantityResolver.php:82` | TP-low | `productRepository->get()` per configurable product of a product list, but the resolver returns at once while `cataloginventory/options/not_available_message` is 2, its default, so the query does not run unless a store changes that setting. |
| 9 | `vendor/magento/module-catalog-inventory-graph-ql/Model/Resolver/StockStatusProvider.php:70` | TP-low | A stock status load per product of a list, but `Magento_InventoryGraphQl` redefines `stock_status` in this install. |
| 10 | `vendor/magento/module-catalog-inventory-graph-ql/Model/Resolver/StockStatusProvider.php:80` | TP-low | The same per cart item, also redefined. |
| 11 | `vendor/magento/module-catalog-inventory-graph-ql/Model/Resolver/StockStatusProvider.php:95` | TP-low | The same per bundle option of a bundle cart item, also redefined. |
| 12 | `vendor/magento/module-catalog-inventory-graph-ql/Model/Resolver/StockStatusProvider.php:115` | TP-low | `productRepository->get()` per configurable cart item, also redefined. |
| 13 | `vendor/magento/module-downloadable-graph-ql/Resolver/Order/Item/Links.php:121` | TP-low | A link collection query per downloadable order item, a rarely queried case. |
| 14 | `vendor/magento/module-gift-message-graph-ql/Model/Resolver/Cart/Item/GiftMessage.php:57` | TP-low | Per cart item, but only with item gift messages enabled, which is off by default, and carts are short. |
| 15 | `vendor/magento/module-gift-message-graph-ql/Model/Resolver/Order/GiftMessage.php:83` | TP | A full order load without a cache per order of `customer.orders.items`, 20 per page by default, before any setting is checked. |
| 16 | `vendor/magento/module-gift-message-graph-ql/Model/Resolver/Order/Item/GiftMessage.php:60` | TP-low | The order is kept per id; a message load per item that has one, only with item gift messages enabled. |
| 17 | `vendor/magento/module-review-graph-ql/Model/Resolver/Product/RatingSummary.php:85` | TP | One review summary query per product, a field commonly requested on category and search listings. |
| 18 | `vendor/magento/module-wishlist-graph-ql/Model/Resolver/WishlistItems.php:69` | TP-low | One collection per wishlist of `Customer.wishlists`, which holds one wishlist in Open Source. |
| 19 | `vendor/magento/module-wishlist-graph-ql/Model/Resolver/WishlistItemsResolver.php:95` | TP-low | The same. |
