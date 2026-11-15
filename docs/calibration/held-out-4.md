# Fourth check on modules the rules were not tuned on

The [third held-out check](held-out-3.md) ran on the 50 most-starred GitHub modules. This one runs on 100 more GitHub modules, which no rule had seen. It decides nothing: the thresholds apply to the calibration rounds. The false positives it found were fixed in the rules, or are explained below where they were not.

## Modules

The `github2` part of the [calibration corpus](corpus.json), built on 2026-11-15: GitHub repositories with the topic `magento2-module`, not archived and not forks, most stars first, that hold one module of type `magento2-module` not yet in the corpus nor in the `github` part, whose module name is not that of a module the Magento copy has installed, and whose requirements the corpus copy meets. Each was cloned at its default branch; the table links the commit that was analyzed. 261 repositories were passed over on the way: 50 in the `github` part, 39 already in the other parts, 104 a requirement the copy does not meet, 57 no `etc/module.xml` at the root or in `src/`, 6 no `composer.json` of type `magento2-module`, 4 a non-standard `registration.php`, and 1 that ships its own `Magento_Customer`.

The part is a copy of the Magento 2.4.9 corpus project with the 125 Packagist modules and Hyvä Theme of the other parts, so the modules these depend on are present. Only the 100 new modules were analyzed.

| Repository at the analyzed commit | Module | Stars |
|---|---|---|
| [Zwernemann/magento2-withdrawl](https://github.com/Zwernemann/magento2-withdrawl/tree/5e7a5974ef99178b72bbe6d528b0229c31db1096) | `Zwernemann_Withdrawal` | 31 |
| [karliuka/m2.ReCaptcha](https://github.com/karliuka/m2.ReCaptcha/tree/900c0aed33e8064c85e3264ae0b074d6bfdee1bd) | `Faonni_ReCaptcha` | 29 |
| [yireo/Yireo_AutoFlushCache](https://github.com/yireo/Yireo_AutoFlushCache/tree/9c576ea93603238e7908bd46b6f2cc994942225b) | `Yireo_AutoFlushCache` | 28 |
| [customgento/module-default-store-code-remover-m2](https://github.com/customgento/module-default-store-code-remover-m2/tree/6295bbecc86a312ed243010fd8488f047562f383) | `CustomGento_DefaultStoreCodeRemover` | 28 |
| [magepal/magento2-form-field-manager](https://github.com/magepal/magento2-form-field-manager/tree/6d505463a058a95f6f656707e9e5d626d2fbf705) | `MagePal_FormFieldManager` | 27 |
| [magepal/magento2-checkout-success-misc-script](https://github.com/magepal/magento2-checkout-success-misc-script/tree/3a5cd98ddc30c1c999b6a02136d90d1b98ed6f11) | `MagePal_CheckoutSuccessMiscScript` | 27 |
| [MagePsycho/magento2-store-view-pricing](https://github.com/MagePsycho/magento2-store-view-pricing/tree/e89555f1b20c99f2926ca1e1b283234e074b311b) | `MagePsycho_StorePricing` | 27 |
| [mage-os-lab/module-blog](https://github.com/mage-os-lab/module-blog/tree/a51c723c4f602a64f2317a2a8f5e130899897a33) | `MageOS_Blog` | 26 |
| [wiardvanrij/m2-opcache-manager](https://github.com/wiardvanrij/m2-opcache-manager/tree/11c8600a45095bdf63d9c65ba1973b1f29329f17) | `Webfixit_OpCache` | 26 |
| [customgento/module-cookiebot-m2](https://github.com/customgento/module-cookiebot-m2/tree/a83325e4843bc25a5628b380a01091d89c6dce56) | `CustomGento_Cookiebot` | 26 |
| [nordcomputer/magento2-showoutofstockprice](https://github.com/nordcomputer/magento2-showoutofstockprice/tree/d99015cad7f34d4bbfa43d13a0883ced557fa62d) | `Nordcomputer_Showoutofstockprice` | 24 |
| [magefan/module-catalog](https://github.com/magefan/module-catalog/tree/f268a687b9f9f365788f65dcc4e8db8f2dd9aa73) | `Magefan_Catalog` | 23 |
| [jetrails/magento2-cloudflare](https://github.com/jetrails/magento2-cloudflare/tree/bd574829c6107cb599aeb9d03ffb0baca388ec08) | `JetRails_Cloudflare` | 23 |
| [baldwin-agency/magento2-module-less-js-compiler](https://github.com/baldwin-agency/magento2-module-less-js-compiler/tree/c966c47eb7e96d9df108136568c7cd0283ba2f8d) | `Baldwin_LessJsCompiler` | 23 |
| [vaimo/module-admin-auto-login](https://github.com/vaimo/module-admin-auto-login/tree/181d6df1e93e39b32ea407d1e2da794eee3ce1d1) | `Vaimo_AdminAutoLogin` | 21 |
| [magefan/module-notifications](https://github.com/magefan/module-notifications/tree/879e11b8eed181cdfbc15618fe897e0231d4eedc) | `Magefan_Notifications` | 21 |
| [pagseguro/payment-magento](https://github.com/pagseguro/payment-magento/tree/1ee0243fa1121b2e291fc6555ffdff5d0ef3746f) | `PagBank_PaymentMagento` | 21 |
| [kirmorozov/mage-postgres-compatibility](https://github.com/kirmorozov/mage-postgres-compatibility/tree/62556a30a9c23268c6e5693ef088c85f5a737099) | `Morozov_PgCompat` | 21 |
| [magefan/module-blog-m22](https://github.com/magefan/module-blog-m22/tree/9127622ce2701fbb359f599b92d49b084217f330) | `Magefan_BlogM22` | 21 |
| [MagePsycho/magento2-product-discount-label](https://github.com/MagePsycho/magento2-product-discount-label/tree/b46418a44ec5c6e6bd8d992b9b77e2b5ecf6992f) | `MagePsycho_DiscountLabel` | 21 |
| [elgentos/CategoryTiling](https://github.com/elgentos/CategoryTiling/tree/404807fd63d92397ca90859e61fced103ee81def) | `Elgentos_CategoryTiling` | 21 |
| [rohitkundale/order-comment](https://github.com/rohitkundale/order-comment/tree/1c649cef13fe94df4780be4441efb57b6f573092) | `RohitKundale_OrderComment` | 20 |
| [sbodak/magento2-b2b-disable-add-to-cart-button-for-guest](https://github.com/sbodak/magento2-b2b-disable-add-to-cart-button-for-guest/tree/6ceea3739c7b0b1fcbb8069c81a9706e678a73c8) | `Bodak_DisableAddToCart` | 20 |
| [alex-79/magento2-hide-default-store-code-from-url](https://github.com/alex-79/magento2-hide-default-store-code-from-url/tree/e20e75ce94edc19814f25179056262bac9d775b5) | `Noon_HideDefaultStoreCode` | 20 |
| [magefan/module-dsu-client](https://github.com/magefan/module-dsu-client/tree/bbf6c3c1185b7ae92f69aba61945444a6ff43c23) | `Magefan_DSUClient` | 19 |
| [crealoz/easy-audit-free](https://github.com/crealoz/easy-audit-free/tree/ccb999492fca1685ece6ef59ebdb75d2beb19b75) | `Crealoz_EasyAudit` | 19 |
| [iMi-digital/magento2-friendly-captcha](https://github.com/iMi-digital/magento2-friendly-captcha/tree/c6d7be00302a84826b1e06f037691d8bc45309e0) | `IMI_FriendlyCaptcha` | 19 |
| [mpchadwick/Mpchadwick_MwscanUtils2](https://github.com/mpchadwick/Mpchadwick_MwscanUtils2/tree/550555ae6872e6d8f0ede38edf52569a55f8f6cc) | `Mpchadwick_MwscanUtils` | 19 |
| [iMi-digital/magento2-store-switch-all-store-views](https://github.com/iMi-digital/magento2-store-switch-all-store-views/tree/8852a645d31cbc1d7d41abcaa521b5ca81c9c75b) | `IMI_StoreSwitch` | 18 |
| [angeo-dev/module-llms-txt](https://github.com/angeo-dev/module-llms-txt/tree/057d3dd12644215b9f0fcc0e248cfaefd6ed467e) | `Angeo_LlmsTxt` | 18 |
| [magefan/module-auto-related-product](https://github.com/magefan/module-auto-related-product/tree/36802115a28e72366adb656cbfd5f96dff037665) | `Magefan_AutoRelatedProduct` | 18 |
| [magefan/module-dsu-server](https://github.com/magefan/module-dsu-server/tree/bcfc65e2c0a5251e1392abb5a83a9846ef9d8bb1) | `Magefan_DSUServer` | 18 |
| [magefan/module-html-sitemap](https://github.com/magefan/module-html-sitemap/tree/2413d4fe9027accca23d2b6cbbf068da2c147db7) | `Magefan_HtmlSitemap` | 18 |
| [hieuhidro/core-web-vitals](https://github.com/hieuhidro/core-web-vitals/tree/44543f90499c826f26445674799f96ae1734cdc7) | `Hidro_CoreWebVitals` | 18 |
| [magefan/module-facebook-pixel](https://github.com/magefan/module-facebook-pixel/tree/4da9697b66caaa5d9e26c8be4f546e5a804a6db2) | `Magefan_FacebookPixel` | 17 |
| [magefan/module-urlwebapi](https://github.com/magefan/module-urlwebapi/tree/3734f91f9f94963ec994861eb3f8aafab92e1b32) | `Magefan_UrlWebapi` | 17 |
| [elninotech/ElNino_ProductLinksNavigator](https://github.com/elninotech/ElNino_ProductLinksNavigator/tree/6f15bde681992ead978a0d83a741d94e7dc7d48f) | `ElNino_ProductLinksNavigator` | 17 |
| [ctidigital/magento2-cache-permissions](https://github.com/ctidigital/magento2-cache-permissions/tree/68dad9104387614bea2196bc58c9f00b197a0f94) | `CtiDigital_AdvancedAclPermissions` | 17 |
| [magepow/magento-2-infinite-scroll](https://github.com/magepow/magento-2-infinite-scroll/tree/cd7ff4ab959204d8934e79d71e4b4d1899f5cec9) | `Magepow_InfiniteScroll` | 17 |
| [Adyen/adyen-magento2-express-checkout](https://github.com/Adyen/adyen-magento2-express-checkout/tree/31227a6d54fccb723cb59c1d2a9df00594fcf716) | `Adyen_ExpressCheckout` | 17 |
| [basecom/magento2-speculation-rules-toolbox](https://github.com/basecom/magento2-speculation-rules-toolbox/tree/0e7e45cae235a72102a6ba7606b5d160f727ec79) | `Basecom_SpeculationRulesToolbox` | 17 |
| [TheFrankman/magento2-customhandles](https://github.com/TheFrankman/magento2-customhandles/tree/251f6d3df7dcc9f2ee9c8771de23c7cddf834255) | `Fc_CustomHandles` | 17 |
| [r-martins/PagSeguro-Magento-Transparente-M2](https://github.com/r-martins/PagSeguro-Magento-Transparente-M2/tree/bc6a6e9331f727fabddeb1ae0080055501305c2b) | `RicardoMartins_PagSeguro` | 17 |
| [magepow/magento-2-banner-slider](https://github.com/magepow/magento-2-banner-slider/tree/2149d32795076c9b3531234543359116c76d82a8) | `Magiccart_Magicslider` | 17 |
| [8WireDigital/magento2-module-noreservations](https://github.com/8WireDigital/magento2-module-noreservations/tree/4000806fb2df27f4bc43732ff7667885ac43769f) | `EightWire_NoReservations` | 16 |
| [fransyrcc/disabled-products-redirect](https://github.com/fransyrcc/disabled-products-redirect/tree/adf94fed143dff88e99261da589e427013baa05c) | `Sysforall_DisabledProductsRedirect` | 16 |
| [magepow/magento-2-size-chart](https://github.com/magepow/magento-2-size-chart/tree/57ef160979633d8f38449b59bdf36462271cddc4) | `Magepow_Sizechart` | 16 |
| [magefan/module-blog-custom-field](https://github.com/magefan/module-blog-custom-field/tree/cca7d035f4ae584ea67e0aa5da3e5dd2c0a10a32) | `Magefan_BlogCustomField` | 16 |
| [mageplaza/magento-2-multi-flat-rates](https://github.com/mageplaza/magento-2-multi-flat-rates/tree/205b8b281726313b0f690aa870e28e324fa73134) | `Mageplaza_Multiflatrates` | 16 |
| [backendorf/magento2-installment](https://github.com/backendorf/magento2-installment/tree/f6285f57bc515228457a5b5ba6aeddd946c223c2) | `Backendorf_Installment` | 16 |
| [galacticlabs/customer-group-payment-filters](https://github.com/galacticlabs/customer-group-payment-filters/tree/8573d24431af478faf4942e7baa9fe82d04f080c) | `GalacticLabs_CustomerGroupPaymentFilters` | 16 |
| [galacticlabs/disable-compare-products](https://github.com/galacticlabs/disable-compare-products/tree/2d8fb69fa48e1714fa8079bdcf4b41e1252a139f) | `GalacticLabs_DisableCompareProducts` | 16 |
| [robaimes/magento2-improved-admin-ui](https://github.com/robaimes/magento2-improved-admin-ui/tree/29903b7d2cc45aaa416983e8c3fb794299394e46) | `Aimes_ImprovedAdminUi` | 16 |
| [yireo/Yireo_NewRelic2](https://github.com/yireo/Yireo_NewRelic2/tree/4f797d984beb2d7312ceea1fb8744a7d50095033) | `Yireo_NewRelic2` | 15 |
| [magespecialist/m2-MSP_OwlCarousel2](https://github.com/magespecialist/m2-MSP_OwlCarousel2/tree/41863382cb1f4cc7eac638ab07ff816932af714a) | `Msp_OwlCarousel2` | 15 |
| [MdnAgency/magento-cookie-consent](https://github.com/MdnAgency/magento-cookie-consent/tree/9a53c1dd98bfbfbeba6635a89b94264547d927f8) | `Maisondunet_CookieConsent` | 15 |
| [Hevelop/invoice-request](https://github.com/Hevelop/invoice-request/tree/e0cbf8ac1a9738e9706f5f2f1c4d7022dc6bb7eb) | `Hevelop_InvoiceRequest` | 15 |
| [sbodak/magento2-b2b-disable-customer-registration](https://github.com/sbodak/magento2-b2b-disable-customer-registration/tree/713441b83c1986818d93182c4127775e139c6002) | `Bodak_DisableRegistration` | 14 |
| [elisei/correios-cws](https://github.com/elisei/correios-cws/tree/8b1c7874ce448b00d19fe6434bcf5fa9ebf9af10) | `O2TI_SigepWebCarrier` | 14 |
| [opengento/magento2-category-import-export](https://github.com/opengento/magento2-category-import-export/tree/9f946254ac62511d7ca7f02d887b464202780cb8) | `Opengento_CategoryImportExport` | 14 |
| [magefan/module-crowdin-integration](https://github.com/magefan/module-crowdin-integration/tree/900ddd907a9f08a1a3bdfde266a048ee9ccf629c) | `Magefan_Crowdin` | 14 |
| [hypershopbv/Hypershop_SpikePerformance](https://github.com/hypershopbv/Hypershop_SpikePerformance/tree/2f50640e8a7411b810414310d5448d5de7734240) | `Hypershop_SpikePerformance` | 13 |
| [mooore-digital/magento2-module-add-to-cart-notification](https://github.com/mooore-digital/magento2-module-add-to-cart-notification/tree/e145104b823a84823b60454e340f1043c327107c) | `Mooore_AddToCartNotification` | 13 |
| [opengento/magento2-makegento-cli](https://github.com/opengento/magento2-makegento-cli/tree/237fa52665de5ff77f1c56cc02028700b8b17ed1) | `Opengento_MakegentoCli` | 13 |
| [magentiz/magento-2-split-db](https://github.com/magentiz/magento-2-split-db/tree/0ea3040e51d0b60c187b9bbb42b49188cb0a721c) | `Magentiz_SplitDb` | 12 |
| [ScottParsons/module-sampleuicomponent](https://github.com/ScottParsons/module-sampleuicomponent/tree/bdefb7b6e4ee8f822b86aed8d80725abeb8c6a01) | `SussexDev_Sample` | 12 |
| [magepow/magento-2-cancel-order](https://github.com/magepow/magento-2-cancel-order/tree/2133acbbd4be1cab7dfecbc7976475a0db81fa1f) | `Magepow_CancelOrder` | 12 |
| [opengento/magento2-saleable](https://github.com/opengento/magento2-saleable/tree/ffd9ac17aa81075ee293464fde1f0ec25117a1ac) | `Opengento_Saleable` | 12 |
| [daanbeverdam/checkout-block-provider](https://github.com/daanbeverdam/checkout-block-provider/tree/d34bb7e51d02a0f6cdca0bcce35196463667a77f) | `DaanBeverdam_CheckoutBlockProvider` | 12 |
| [o0mohd0o/magento2-alpine-checkout](https://github.com/o0mohd0o/magento2-alpine-checkout/tree/eac3a38a298dbe5c5a7b0962c33434de09c89ea0) | `Bonlineco_Checkout` | 12 |
| [hypershopbv/Hypershop_ExcludeFooterScripts](https://github.com/hypershopbv/Hypershop_ExcludeFooterScripts/tree/5ef00e183bab0ab084d352daed8d56f52fce2c61) | `Hypershop_ExcludeFooterScripts` | 12 |
| [aelmizeb/magento2-reports](https://github.com/aelmizeb/magento2-reports/tree/83137cd42ef4c792291967b7e7d20898d5103d52) | `Originalapp_Reports` | 12 |
| [MagePsycho/magento2-async-bulk-operations-ui](https://github.com/MagePsycho/magento2-async-bulk-operations-ui/tree/b4a9fa2937844a38ef049e0d93bf7c92100b260e) | `MagePsycho_AsyncOperationsUi` | 12 |
| [HexBrain/Magento2ViewProductButton](https://github.com/HexBrain/Magento2ViewProductButton/tree/bf35ad6c3f8516af1a83525318e98dbd5a1922cf) | `HexBrain_ProductView` | 12 |
| [Siteation/magento2-storeinfo-menus](https://github.com/Siteation/magento2-storeinfo-menus/tree/a5d4c2356d93a2b85f836c67f7af2bb1799e6d9d) | `Siteation_StoreInfoMenus` | 11 |
| [yireo/Yireo_SyncGraphQlSessionWithFrontend](https://github.com/yireo/Yireo_SyncGraphQlSessionWithFrontend/tree/93527f4826c1599f48a37c3c86738e7000e8b810) | `Yireo_SyncGraphQlSessionWithFrontend` | 11 |
| [gaiterjones/magento2-module-buyxgety](https://github.com/gaiterjones/magento2-module-buyxgety/tree/b86c8cbb2ec52f2b48bccde6225adf8c331197f6) | `Gaiterjones_BuyXGetY` | 11 |
| [customgento/module-admin-payment-m2](https://github.com/customgento/module-admin-payment-m2/tree/c7da9031b2859ef97cea356d92dc1a0a5fc3ae01) | `CustomGento_AdminPayment` | 11 |
| [opengento/magento2-product-breadcrumbs](https://github.com/opengento/magento2-product-breadcrumbs/tree/6c2b5d139aacfb66c60f84226eb522cef0d4729b) | `Opengento_ProductBreadcrumbs` | 11 |
| [Pixel-Open/magento-plausible](https://github.com/Pixel-Open/magento-plausible/tree/71cd26066b633afca9c1db56c8561d7ecb081bd8) | `PixelOpen_Plausible` | 11 |
| [williankeller/magento2-floating-buy-button](https://github.com/williankeller/magento2-floating-buy-button/tree/99284464c0d5546ed60028201be6af67962b0635) | `Magestat_FloatingBuyButton` | 11 |
| [magevision/magento2-admin-category-product-thumbnail](https://github.com/magevision/magento2-admin-category-product-thumbnail/tree/341e11b7e48675b53a9e960e18dcffe41a038a9c) | `MageVision_AdminCategoryProductThumbnail` | 11 |
| [MagestyApps/magento2-fix-discount-rules](https://github.com/MagestyApps/magento2-fix-discount-rules/tree/2b33df35353ab804cc9805268c3b9808652ccc1a) | `MagestyApps_FixRules` | 11 |
| [opengento/magento2-webapi-logger](https://github.com/opengento/magento2-webapi-logger/tree/48657f36859b84c1cdcf969dd93e3bdbad186fc4) | `Opengento_WebapiLogger` | 11 |
| [mbissonho/remember-admin-last-page-magento2](https://github.com/mbissonho/remember-admin-last-page-magento2/tree/7ef6990521e32147016aadd106f1c7a42dd206a5) | `Mbissonho_RememberAdminLastPage` | 10 |
| [elgentos/magento2-baseurlpath](https://github.com/elgentos/magento2-baseurlpath/tree/8c99c628eee4e52dce21b3a3d300e7becd37df3e) | `Elgentos_BaseUrlPath` | 10 |
| [mage-os-lab/module-newsletter-coupon](https://github.com/mage-os-lab/module-newsletter-coupon/tree/82f1dedf22c41cdd6e1c91ea391aa852be4b41d6) | `MageOS_NewsletterCoupon` | 10 |
| [williankeller/magento2-log-webapi](https://github.com/williankeller/magento2-log-webapi/tree/26b2529059d617cc69ae1c80e5d1c2109d30a95a) | `Magestat_LogWebapi` | 10 |
| [magepow/magento-2-flip-book](https://github.com/magepow/magento-2-flip-book/tree/916d372ac7ec1b0d1c98d80946db8ddf1f4883f5) | `Magepow_Flipbook` | 10 |
| [ScottParsons/module-cloudflare](https://github.com/ScottParsons/module-cloudflare/tree/79aa52bc12b052923b090969ea673df3de570489) | `SussexDev_Cloudflare` | 10 |
| [MagePsycho/magento2-seo-suite](https://github.com/MagePsycho/magento2-seo-suite/tree/f70b7283b7ff18679f9a7653057c6e612274ae5f) | `MagePsycho_SeoSuite` | 10 |
| [magefan/module-product-label](https://github.com/magefan/module-product-label/tree/1d501ed974b2819ea7c9dacf9603b53d5944fbcc) | `Magefan_ProductLabel` | 10 |
| [yireo/Yireo_AdditionalEndpointsGraphQl](https://github.com/yireo/Yireo_AdditionalEndpointsGraphQl/tree/527b05b3e1d33bc3645fdff3f1813b75969385c3) | `Yireo_AdditionalEndpointsGraphQl` | 10 |
| [magepow/magento-2-multiple-translate](https://github.com/magepow/magento-2-multiple-translate/tree/fcd8d2492c84a6db6c297a3e98df13ddd6e4c273) | `Magepow_MultiTranslate` | 10 |
| [yireo/Yireo_BackendReindexer](https://github.com/yireo/Yireo_BackendReindexer/tree/d2a0465a2bf06ca5a87fa22166a6cb35207fbe33) | `Yireo_BackendReindexer` | 10 |
| [pixelperfectat/module-hyva-cookie-consent](https://github.com/pixelperfectat/module-hyva-cookie-consent/tree/e7ec7cd39b7139a043875fe24f14918cf0737d32) | `Pixelperfect_HyvaCookieConsent` | 10 |
| [customgento/module-remove-product-comparison-m2](https://github.com/customgento/module-remove-product-comparison-m2/tree/3707155e3f944331f2302e87ded2d7668b41b956) | `CustomGento_RemoveProductComparison` | 10 |
| [mooore-digital/magento2-module-elasticsearch-relevance](https://github.com/mooore-digital/magento2-module-elasticsearch-relevance/tree/f20c8fd48ee31f7bf2f99e433eec34c842710223) | `Mooore_ElasticsearchRelevance` | 10 |
| [CoullWorks/magento2-catalog-infinite-scroll](https://github.com/CoullWorks/magento2-catalog-infinite-scroll/tree/074c2e0f2dd664eabc84d337b1adc1984f2a9cc9) | `CoullWorks_CatalogInfiniteScroll` | 9 |
| [OxCom/magento2-currency-services](https://github.com/OxCom/magento2-currency-services/tree/2f657538603d263caacc7468c803fafe31340696) | `OxCom_MagentoCurrencyServices` | 9 |

## Result

The run ([`runs/github2/`](runs/github2/github2.meta.json), tool commit d68c380) enabled every rule, experimental ones included: 474 findings, `incomplete: false`, one notice for a receiver of unknown type. 33 modules have no finding. 187 findings come from 18 rules of the default set.

Up to 12 findings per rule of the default set were drawn with a fixed seed (20261115: the findings of each rule sorted by file and line; for a rule with more than 12, Python's `random.Random(20261115).sample(findings, 12)`, one generator drawing for the rules in alphabetical order, and the drawn findings sorted again by file and line) and graded once by reading the code, by graders who did not write the rules, with the verdicts of the [method](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration.md#method). The false positives were checked again against the code before the rules were changed. No time or memory was measured.

| Rule | Findings | Graded | TP | TP-low | FP |
|---|---|---|---|---|---|
| J-DEP-01 | 1 | 1 | 1 | 0 | 0 |
| P-DI-01 | 59 | 12 | 0 | 12 | 0 |
| P-DI-06 | 18 | 12 | 0 | 11 | 1 |
| P-FPC-05 | 5 | 5 | 0 | 0 | 5 |
| P-FPC-10 | 1 | 1 | 0 | 1 | 0 |
| P-LOOP-01 | 32 | 12 | 2 | 9 | 1 |
| P-LOOP-03 | 22 | 12 | 0 | 12 | 0 |
| P-LOOP-11 | 7 | 7 | 0 | 7 | 0 |
| P-PLG-01 | 8 | 8 | 2 | 6 | 0 |
| T-01 | 11 | 11 | 0 | 11 | 0 |
| T-05 | 2 | 2 | 1 | 1 | 0 |
| U-CLI-01 | 8 | 8 | 8 | 0 | 0 |
| U-CLI-02 | 4 | 4 | 4 | 0 | 0 |
| X-AREA-01 | 2 | 2 | 0 | 2 | 0 |
| X-DB-01 | 1 | 1 | 1 | 0 | 0 |
| X-DB-02 | 1 | 1 | 1 | 0 | 0 |
| X-EVT-02 | 2 | 2 | 0 | 0 | 2 |
| X-LAY-05 | 3 | 3 | 3 | 0 | 0 |
| **All** | **187** | **104** | **23** | **72** | **9** |

95 of 104 verdicts are correct (91 %) and 23 of those 95 matter in practice. Most correct findings sit in admin actions, on pages that start a session anyway, or on short lists, as in the earlier checks. The upgrade rules are the exception: all 12 findings of U-CLI-01 and U-CLI-02 are console commands that break `bin/magento`. Seven of the nine false positives were in P-FPC-05 (all five of its findings), P-LOOP-01 and one form of P-DI-06; the other two are X-EVT-02. The findings of experimental rules were not graded here.

## What this run changed

- **P-FPC-05** reported four `Cache-Control` headers set to `sprintf('public, max-age=%d', $ttl)`: a computed value, which the rule took as one that may disable caching, although it asks for caching. A computed value now counts by the literals it is built from, by concatenation or as the format of `sprintf()`: it is reported when they say `no-cache`, `no-store` or `private`, and not when they say `public` or `max-age` without those and its other parts can only be numbers. The fifth finding calls `setHeader()` on what `$page->getLayout()->getBlock()` returns, a block, where it only sets the block's `header` data; `setHeader()` on a block is no longer reported. That finding itself stays: the page factory sits in a property without a type, so the analysis cannot tell that the receiver is a block. Counted as false, it is 1 of 5 for this rule here.
- **P-LOOP-01** reported `$this->productAttributeRepository->get($id)` in a loop over stores where `$id` never changes. The attribute repository goes through `Eav\Model\Config::getAttribute()`, which keeps the attribute, so only the first iteration queries. `get()` and `getById()` on the product, category, product attribute, customer, order, cart and EAV attribute repositories, which all keep what they load by their arguments, are no longer reported when no enclosing loop changes any argument; a loop that may change it in other ways, through a reference, `extract()` or a call that takes it by reference, or that saves or deletes, still counts.
- **P-DI-06** reported a lazy getter that keeps the object manager in a local variable before it stores the dependency: `$objectManager = ObjectManager::getInstance(); $this->productRepository = $objectManager->get(...)`. That form is allowed now, like the one-statement getter.
- **X-EVT-02** reported a New Relic observer on `model_save_after` and `model_delete_after` that records a metric named after the class of every saved or deleted model. That is the generic monitoring the rule page lists as a known false positive: no entity event could replace it. It stays reported; a rule cannot tell a metric for every model from work that only some models need.

Effect of these changes on every part of the corpus, with all rules on and the result cache cleared before each run:

| Part | Before | After | Removed |
|---|---|---|---|
| Magento 2.4.9 core | 2888 | 2887 | 1 P-DI-06, the lazy getter of `Checkout\Block\Cart\ValidationMessages::getMinimumAmountErrorMessage()` with the object manager in a local variable |
| packagist (25 modules) | 552 | 552 | none |
| extended (100 modules, Hyvä) | 1602 | 1601 | 1 P-DI-06, the same form in `ADM\QuickDevBar\Profiler\Db` |
| github (50 modules) | 194 | 194 | none |
| github2 (100 modules) | 474 | 468 | the four `public` headers of P-FPC-05, the P-LOOP-01 and the P-DI-06 false positives |

No finding was added. Of the removed ones, only the github2 ones were graded, all as false here; none is in an earlier graded sample.

## Limitations

- 100 modules, picked by stars, and one grading of each finding. A verdict can miss a caller outside the files read.
- Impact was judged from the code, not measured.
- The default branch of a repository changes; the commits in the table are what was graded.

## Grading tables

### J-DEP-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Mooore/AddToCartNotification/view/frontend/web/js/catalog/add-to-cart-mixin.js:1 | TP | A mixin on core's add-to-cart widget depends on `jquery/ui`, so every product, category and widget listing loads all of `jquery/compat`. |

### P-DI-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Adyen/ExpressCheckout/Block/Buttons/AbstractButton.php:126 | TP-low | The shortcut buttons are added only through `shortcut_buttons_container` on the product page (cache miss) and the cart, where the customer session is started anyway. |
| app/code/Bonlineco/Checkout/Observer/ValidateCheckoutAccess.php:30 | TP-low | The observer listens only to the checkout page's predispatch and reads the quote at once, so a Proxy saves nothing. |
| app/code/Bonlineco/Checkout/ViewModel/CheckoutData.php:37 | TP-low | Used only in the checkout handle, inside a `cacheable="false"` block, where the session is needed anyway. |
| app/code/JetRails/Cloudflare/Model/Adminhtml/Api/Overview/Configuration.php:28 | TP-low | An admin API model, built by admin controllers, where the admin session is started anyway, or by CLI commands; the injected session is never used. |
| app/code/Magefan/AutoRelatedProduct/Model/ActionValidator.php:70 | TP-low | Built only on frontend page renders (cache miss) through a layout plugin and observer, where the session is started anyway. |
| app/code/O2TI/SigepWebCarrier/Block/Adminhtml/Plp/Edit/Button/GetShippingReport.php:47 | TP-low | A button of an admin form, where the admin session is started anyway. |
| app/code/PixelOpen/Plausible/Session/Goals.php:22 | TP-low | Used only by frontend plugins that store goals in the session, so every path needs it anyway. |
| app/code/RicardoMartins/PagSeguro/Helper/Cookie.php:30 | TP-low | Building the cookie manager is cheap; only the guideline issue remains. |
| app/code/RicardoMartins/PagSeguro/Helper/Data.php:115 | TP-low | Built on product pages (cache miss), checkout and payment actions, which start the session anyway; in cron no session starts. |
| app/code/RicardoMartins/PagSeguro/Model/Method/Boleto.php:71 | TP-low | The payment method is built mainly to list methods on checkout, where the session is started anyway. |
| app/code/RicardoMartins/PagSeguro/Model/Method/Twocc.php:80 | TP-low | `isAvailable()` always reads the admin session, so a Proxy would start it anyway on the checkout path. |
| app/code/Yireo/SyncGraphQlSessionWithFrontend/Plugin/AddCustomerTokenToCustomerSection.php:26 | TP-low | A plugin on the customer section, which runs only in `customer/section/load` and reads the session at once. |

### P-DI-06

| Location | Verdict | Reason |
|---|---|---|
| app/code/Hidro/CoreWebVitals/Model/Html/Modifier/FooterCSS.php:87 | TP-low | Gets the merge strategy from the object manager once per storefront page render (cache miss): a design issue only. |
| app/code/Hidro/CoreWebVitals/Model/Html/Modifier/FooterCSS.php:88 | TP-low | `ObjectManager::getInstance()->create(Merged::class, ...)` where an injected factory would do, once per page render. |
| app/code/JetRails/Cloudflare/Controller/Adminhtml/Action.php:40 | TP-low | An admin controller constructor creates a model named from the request path, once per admin request. |
| app/code/MagePsycho/StorePricing/Model/Preference/Catalog/Product/Attribute/Backend/Enddate.php:25 | TP-low | Creates a helper in `setScope()`; the backend is cached per attribute, so a few times per request. |
| app/code/MagePsycho/StorePricing/Model/Preference/Catalog/Product/Attribute/Backend/Price.php:18 | TP-low | The same, once per price attribute backend per request. |
| app/code/Magefan/AutoRelatedProduct/Model/AutoRelatedProductAction.php:132 | TP-low | Fetches a soft dependency in the constructor when another module is enabled; runs from cron, an admin action and a CLI command. |
| app/code/Magefan/FacebookPixel/Model/Pixel/Cart/Content.php:78 | FP | A lazy getter: `if (null === $this->productRepository)` stores `$objectManager->get(...)` in that property and returns it, the allowed form; only the object manager goes through a local variable first. |
| app/code/Magefan/HtmlSitemap/Block/Adminhtml/System/Config/Form/DynamicRow.php:40 | TP-low | An admin config field gets a renderer from the object manager once per admin config page render. |
| app/code/Magefan/ProductLabel/Block/Adminhtml/System/Config/Form/Info.php:48 | TP-low | An admin config block, once per admin config page render. |
| app/code/Magentiz/SplitDb/Console/Command/ChangeDbMode.php:58 | TP-low | A one-off CLI command creates the deployment config writer from the object manager. |
| app/code/Magepow/Sizechart/Controller/Adminhtml/Sizechart/Save.php:19 | TP-low | An admin save controller creates a serializer from the object manager, once per save. |
| app/code/Magiccart/Magicslider/Controller/Adminhtml/Index/Edit.php:54 | TP-low | An admin edit action gets a collection factory from the object manager, once per page load. |

### P-FPC-05

| Location | Verdict | Reason |
|---|---|---|
| app/code/Angeo/LlmsTxt/Controller/Index/AgenticSitemap.php:82 | FP | The header is `sprintf('public, max-age=%d', ...)` with a TTL of at least 1: it asks for caching rather than disabling it. |
| app/code/Angeo/LlmsTxt/Controller/Index/Index.php:105 | FP | The 304 branch sets `Cache-Control` to `sprintf('public, max-age=%d', ...)`, always public. |
| app/code/Angeo/LlmsTxt/Controller/Index/Index.php:161 | FP | `applyHeaders()` sets `Cache-Control` to `sprintf('public, max-age=%d', ...)`, always public. |
| app/code/Angeo/LlmsTxt/Controller/Index/MdMirror.php:198 | FP | `buildResult()` sets `Cache-Control` to `sprintf('public, max-age=%d', ...)`, always public. |
| app/code/Magepow/Sizechart/Controller/Index/Index.php:26 | FP | `setHeader()` is called on the result of `$page->getLayout()->getBlock(...)`, a block, not the response; the module has no layout that names such a block, so the call never sets a header. |

### P-FPC-10

| Location | Verdict | Reason |
|---|---|---|
| app/code/Yireo/BackendReindexer/Controller/Adminhtml/Indexer/MassReindex.php:85 | TP-low | The admin controller calls `reindexAll()` per selected indexer: a full rebuild is the explicit purpose of this rare admin action. |

### P-LOOP-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/MageOS/Blog/Controller/Adminhtml/Author/InlineEdit.php:54 | TP-low | Admin grid inline edit loads each edited author with `getById()` (a `load()` per id) inside `foreach ($items ...)`; admin-only and bounded by the rows edited on one grid page. |
| app/code/MageOS/Blog/Controller/Adminhtml/Author/MassDisable.php:35 | TP-low | Admin mass action reloads each author already in the `Filter` collection with `getById()` before saving it; admin-only and bounded by the selection. |
| app/code/MageOS/Blog/Controller/Adminhtml/Post/InlineEdit.php:54 | TP-low | Admin grid inline edit loads each edited post with `PostRepository::getById()` (a `load()` plus 5 link queries in `hydrateLinks`); admin-only and bounded by the rows edited. |
| app/code/MageOS/Blog/Controller/Adminhtml/Post/MassDisable.php:36 | TP-low | Admin mass unpublish reloads each selected post through `getById()` (load plus 5 link queries) although the collection already holds them; admin-only and bounded by the selection. |
| app/code/MageOS/Blog/Model/Breadcrumb/PostCategoryPicker.php:48 | TP-low | Called once per blog post page render (`Block/Breadcrumbs.php:103`); it reloads each category already returned by `getList()`, only the handful of categories of one post. |
| app/code/MageOS/Blog/ViewModel/Search/Results.php:148 | TP | On the storefront blog search page (`blog_search_index.xml`) each hit is hydrated with `PostRepository::getById()`, a `load()` plus 5 link queries, so a page of 10 posts costs about 60 queries; search pages vary by `q`, so the page cache helps little. |
| app/code/MageOS/NewsletterCoupon/Model/SubscriberInformationRepository.php:107 | TP-low | The loop runs over `$marketingEmailIntegrations`, which the module's `etc/di.xml` leaves empty; the email does not change between iterations and `CustomerRegistry::retrieveByEmail` caches a found customer. |
| app/code/Magefan/Catalog/Observer/Catalog/Product/FullPathBreadcrumbs.php:61 | TP | On `catalog_controller_product_init_after`, every storefront product page cache miss without a current category loads every category of the product with a full `CategoryRepository::get()`; the list has no fixed bound. |
| app/code/Magefan/Crowdin/Model/GetTranslationEntity.php:243 | TP-low | A REST endpoint for the Crowdin integration loads one category once per active store; bounded by the store count. |
| app/code/Magefan/Crowdin/Model/GetTranslationEntity.php:286 | TP-low | The same endpoint loads one product once per active store with a per-store cache key; bounded by the store count. |
| app/code/Magefan/Crowdin/Model/GetTranslationEntity.php:435 | FP | `productAttributeRepository->get($id)` gets the same `$id` in every iteration of the store loop; `Eav\Model\Config::getAttribute()` caches the attribute after the first call, so no query runs per iteration. |
| app/code/O2TI/SigepWebCarrier/Model/PlpManagement.php:115 | TP-low | A REST back-office action checks each order id of a new shipping manifest with `OrderRepository::get()`; rare and bounded by the orders of one manifest. |

### P-LOOP-03

| Location | Verdict | Reason |
|---|---|---|
| app/code/IMI/FriendlyCaptcha/Setup/Patch/Data/MigrateEuEndpointConfig.php:51 | TP-low | A data patch runs once at `setup:upgrade` and re-saves the config rows of one path, one per scope. |
| app/code/MageOS/Blog/Controller/Adminhtml/Author/InlineEdit.php:60 | TP-low | Admin grid inline edit saves each edited author; admin-only and bounded by the rows edited. |
| app/code/MageOS/Blog/Controller/Adminhtml/Author/MassEnable.php:37 | TP-low | Admin mass enable saves each selected author; admin-only and bounded by the selection. |
| app/code/MageOS/Blog/Controller/Adminhtml/Category/InlineEdit.php:60 | TP-low | Admin grid inline edit saves each edited blog category. |
| app/code/MageOS/Blog/Controller/Adminhtml/Category/MassEnable.php:37 | TP-low | Admin mass enable saves each selected blog category. |
| app/code/MageOS/Blog/Controller/Adminhtml/Post/InlineEdit.php:60 | TP-low | Admin grid inline edit saves each edited post. |
| app/code/MageOS/Blog/Controller/Adminhtml/Post/MassDisable.php:38 | TP-low | Admin mass unpublish saves each selected post. |
| app/code/MageOS/Blog/Controller/Adminhtml/Tag/InlineEdit.php:60 | TP-low | Admin grid inline edit saves each edited tag. |
| app/code/MageOS/Blog/Controller/Adminhtml/Tag/MassDelete.php:35 | TP-low | Admin mass delete calls `deleteById()` for each selected tag. |
| app/code/MagePsycho/StorePricing/Observer/Preference/Catalog/SwitchPriceAttributeScopeOnConfigChange.php:78 | TP-low | Replaces core's observer for an admin price-scope config change and saves each `price` attribute, a handful, as core does. |
| app/code/O2TI/SigepWebCarrier/Cron/DeleteStalePlps.php:91 | TP-low | A daily cron deletes the stale shipping manifests one by one, a small list. |
| app/code/Opengento/CategoryImportExport/Model/Import/Categories.php:66 | TP-low | An admin CSV category import saves each category through `CategoryRepository::save()`; core has no batch API for categories and the action is rare. |

### P-LOOP-11

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magefan/AutoRelatedProduct/Controller/Adminhtml/Rule/InlineEdit.php:58 | TP-low | `_objectManager->create(...)->load($modelid)` once per edited row of an admin grid inline edit. |
| app/code/Magefan/AutoRelatedProduct/Controller/Adminhtml/Rule/MassStatus.php:71 | TP-low | `_objectManager->create(...)->load($id)` once per selected rule of an admin mass action. |
| app/code/Magefan/ProductLabel/Controller/Adminhtml/Rule/InlineEdit.php:58 | TP-low | The same code as the AutoRelatedProduct inline edit, in admin. |
| app/code/Magefan/ProductLabel/Controller/Adminhtml/Rule/MassStatus.php:71 | TP-low | The same code as the AutoRelatedProduct mass status, in admin. |
| app/code/Magefan/ProductLabel/Controller/Adminhtml/Rule/Save.php:85 | TP-low | `_objectManager->get(ImageUpload::class)` in a loop over two fixed keys on an admin save; `get()` returns the shared instance. |
| app/code/Magepow/CancelOrder/view/frontend/templates/product.phtml:6 | TP-low | `$objectManager->get(Image::class)` per item of a cancel-order email; `get()` returns the shared instance. |
| app/code/Magepow/Flipbook/Controller/Adminhtml/Flip/InlineEdit.php:33 | TP-low | `_objectManager->create(...)->load($modelid)` once per edited row of an admin grid inline edit. |

### P-PLG-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Backendorf/Installment/Plugin/Hyva/Theme/ViewModel/ProductListItem.php:31 | TP | Calls `$proceed()` unchanged and only appends HTML built from the product, which an after plugin also gets; global plugin on Hyvä's product list item, once per product of every listing render. |
| app/code/GalacticLabs/CustomerGroupPaymentFilters/Plugin/Block/Aminhtml/Group/Edit/FormPlugin.php:42 | TP-low | All work happens before an unconditional `return $proceed();`; once per admin customer group edit page. |
| app/code/IMI/FriendlyCaptcha/Plugin/GraphQl/ContactUsPlugin.php:36 | TP-low | Only throws when the mutation is disabled, then calls `$proceed`; a before plugin can throw the same; once per `contactUs` mutation. |
| app/code/MageOS/NewsletterCoupon/Plugin/CheckNewsletterCoupon.php:43 | TP-low | Checks and a request parameter change before an unconditional `return $proceed();`; once per coupon apply. |
| app/code/Mbissonho/RememberAdminLastPage/Plugin/Backend/ResumeOnSecretKeyBounce.php:90 | TP-low | `$proceed()` first, then only reads the result and the subject; admin only, once per request. |
| app/code/Mooore/ElasticsearchRelevance/Plugin/Elasticsearch5/SearchAdapter/MapperPlugin.php:30 | TP-low | `$proceed($request)` first, then only sets `min_score` on the result; once per search request. |
| app/code/Nordcomputer/Showoutofstockprice/Plugin/ShowOutOfStockProductsPlugin.php:25 | TP | `$proceed()` first, then only filters the result; on the frontend configurable block and the swatch renderers, several times per configurable product on product and listing pages. |
| app/code/RohitKundale/OrderComment/Plugin/Model/Checkout/PaymentInformationManagement.php:91 | TP-low | Reads the request and sets a session value before an unconditional `$proceed()` with unchanged arguments; once per payment information save. |

### T-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Hevelop/InvoiceRequest/view/frontend/templates/address/edit.phtml:4 | TP-low | Creates core's Company widget on the uncacheable address edit page, the same pattern as core's `address/edit.phtml`. |
| app/code/Hevelop/InvoiceRequest/view/frontend/templates/address/edit.phtml:5 | TP-low | Creates core's Telephone widget on the same page. |
| app/code/Hevelop/InvoiceRequest/view/frontend/templates/address/edit.phtml:6 | TP-low | Creates core's Fax widget on the same page. |
| app/code/MageOS/Blog/view/frontend/templates/author/view.phtml:64 | TP-low | One card block per post on the author page (10 per page by default), only on a page cache miss. |
| app/code/MageOS/Blog/view/frontend/templates/category/view.phtml:32 | TP-low | One card block per post on the blog category page, only on a page cache miss. |
| app/code/MageOS/Blog/view/frontend/templates/post/listing.phtml:22 | TP-low | One card block per post on the blog listing, only on a page cache miss. |
| app/code/MageOS/Blog/view/frontend/templates/product/related-posts.phtml:46 | TP-low | One card block per related post on the product page, behind an `ifconfig` that is off by default, at most 3. |
| app/code/MageOS/Blog/view/frontend/templates/search/results.phtml:33 | TP-low | One card block per result on the blog search page, bounded by the page size. |
| app/code/MageOS/Blog/view/frontend/templates/tag/view.phtml:32 | TP-low | One card block per post on the blog tag page, only on a page cache miss. |
| app/code/Magefan/FacebookPixel/view/frontend/templates/pixel.phtml:83 | TP-low | Creates a script block in the head of every storefront page, only when a setting is on and only on a page cache miss. |
| app/code/Opengento/ProductBreadcrumbs/view/frontend/templates/product/breadcrumbs.phtml:27 | TP-low | Creates core's breadcrumbs block once on the product page when server-side rendering is on, only on a page cache miss. |

### T-05

| Location | Verdict | Reason |
|---|---|---|
| app/code/Maisondunet/CookieConsent/view/frontend/templates/cookie_consent.phtml:23 | TP | A 75 KB script loads synchronously in the head of every page (`default.xml`), and nothing calls it before `DOMContentLoaded`, so `defer` would work. |
| app/code/RicardoMartins/PagSeguro/view/frontend/templates/preparePagSeguro.phtml:13 | TP-low | A script without `defer` blocks rendering, but only the checkout page includes the template. |

### U-CLI-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Crealoz/EasyAudit/Console/RunAuditCommand.php:48 | TP | Extends Symfony `Command`, `execute()` without a return type, registered in `CommandList` (`etc/di.xml:16`): every `bin/magento` call fails on 2.4.9. |
| app/code/Magefan/DSUClient/Console/Request.php:70 | TP | The same, registered as `dsuRequest` (`etc/di.xml:17`). |
| app/code/Magefan/DSUClient/Console/UpdateCommand.php:74 | TP | The same; five virtual types of it are registered in `CommandList` (`etc/di.xml`). |
| app/code/Magentiz/SplitDb/Console/Command/ChangeDbMode.php:52 | TP | The same, registered as `db_mode` (`etc/di.xml:10-13`). |
| app/code/Opengento/MakegentoCli/Console/Command/Database/Entity.php:40 | TP | The same, registered as `makegento:db-schema:entity` (`etc/di.xml:18`). |
| app/code/Originalapp/Reports/Console/Command/StatCommand.php:42 | TP | The same, registered as `originalapp-reports:stats` (`etc/di.xml:61`). |
| app/code/Webfixit/OpCache/Console/Command/Clear.php:64 | TP | The same, registered as `opcache_clear` (`etc/di.xml:6`). |
| app/code/Webfixit/OpCache/Console/Command/Status.php:40 | TP | The same, registered as `opcache_status` (`etc/di.xml:7`). |

### U-CLI-02

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magefan/DSUClient/Console/Request.php:70 | TP | `execute()` ends with no `return`, so the registered command throws a `TypeError` on every run on Symfony Console 5.4 or later. |
| app/code/Magefan/DSUClient/Console/UpdateCommand.php:74 | TP | `execute()` ends with no `return`; all five registered virtual commands fail on every run. |
| app/code/Webfixit/OpCache/Console/Command/Clear.php:64 | TP | `execute()` only writes a line and has no `return`. |
| app/code/Webfixit/OpCache/Console/Command/Status.php:40 | TP | `execute()` only prints the status and has no `return`. |

### X-AREA-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Yireo/NewRelic2/etc/events.xml:22 | TP-low | Declared in the global `etc/events.xml`, but `controller_action_postdispatch_adminhtml_process_massReindex` is a Magento 1 action name that no Magento 2 controller dispatches: dead configuration, one declaration's cost. |
| app/code/Yireo/NewRelic2/etc/events.xml:26 | TP-low | The same for `controller_action_postdispatch_adminhtml_process_reindexProcess`. |

### X-DB-01

| Location | Verdict | Reason |
|---|---|---|
| app/code/Magefan/ProductLabel/etc/db_schema.xml:43 | TP | `magefan_product_label_rule_product` has no primary or unique key, and a cron and an admin action rewrite it with deletes and inserts over rules times products. |

### X-DB-02

| Location | Verdict | Reason |
|---|---|---|
| app/code/Opengento/WebapiLogger/etc/db_schema.xml:26 | TP | An index on `log_id` duplicates the primary key of `webapi_logs`, which gets a row per logged API request. |

### X-EVT-02

| Location | Verdict | Reason |
|---|---|---|
| app/code/Yireo/NewRelic2/etc/events.xml:38 | FP | The observer records a New Relic metric named after the class of every saved model: generic monitoring that needs every model, the case the rule page lists as a known false positive; it also returns at once when New Relic is off. |
| app/code/Yireo/NewRelic2/etc/events.xml:42 | FP | The same for every deleted model. |

### X-LAY-05

| Location | Verdict | Reason |
|---|---|---|
| app/code/Adyen/ExpressCheckout/view/frontend/layout/default.xml:4 | TP | Adds Adyen's 137 KB `adyen.css` as a blocking stylesheet on every storefront page, while the express buttons render only on the product page and the cart. |
| app/code/Msp/OwlCarousel2/view/frontend/layout/default.xml:3 | TP | Adds `owl.carousel.min.css` to every page although only pages that start a carousel need it. |
| app/code/RicardoMartins/PagSeguro/view/frontend/layout/default_head_blocks.xml:4 | TP | `pagseguro.css` styles only checkout payment fields, yet every storefront page loads it. |
