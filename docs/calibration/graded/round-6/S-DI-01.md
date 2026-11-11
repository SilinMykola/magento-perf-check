# S-DI-01: calibration sample

2 of 2 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/FireGento/FastSimpleImport/Model/Import/Category.php:239` (extended)

[S-DI-01] Proxies and interceptors MUST never be explicitly requested in constructors. (Magento2.Classes.DiscouragedDependencies)

```
   233          ManagerInterface $eventManager,
   234          UploaderFactory $imageUploaderFactory,
   235          Filesystem $filesystem,
   236          VersionFeaturesFactory $versionFeatures,
   237          ObjectRelationProcessor $objectRelationProcessor,
   238          TransactionManagerInterface $transactionManager,
>  239          CategoryResourceModelFactory $resourceFactory,
   240          CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
   241          CategoryRepositoryInterface $categoryRepository,
   242          UrlPersistInterface $urlPersist,
   243          Config $config,
   244          array $data = []
   245      ) {
```

## 2. `app/code/Payone/Core/Model/ConfigProvider.php:187` (extended)

[S-DI-01] Proxies and interceptors MUST never be explicitly requested in constructors. (Magento2.Classes.DiscouragedDependencies)

```
   181          \Payone\Core\Helper\Consumerscore $consumerscoreHelper,
   182          \Payone\Core\Model\Api\Payolution\PrivacyDeclaration $privacyDeclaration,
   183          \Magento\Checkout\Model\Session $checkoutSession,
   184          \Magento\Customer\Model\Session $customerSession,
   185          \Payone\Core\Helper\Shop $shopHelper,
   186          \Payone\Core\Model\ResourceModel\SavedPaymentData $savedPaymentData,
>  187          \Payone\Core\Model\Methods\Ratepay\Installment\Proxy $ratepayInstallment,
   188          \Payone\Core\Helper\Toolkit $toolkitHelper,
   189          \Payone\Core\Helper\Ratepay $ratepayHelper
   190      ) {
   191          parent::__construct($ccConfig, $dataHelper);
   192          $this->dataHelper = $dataHelper;
   193          $this->countryHelper = $countryHelper;
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/FireGento/FastSimpleImport/Model/Import/Category.php:239` | FP | CategoryResourceModelFactory is the generated Factory of FireGento's own Model\Import\Proxy\Category\ResourceModel class; the sniff matched the namespace segment Proxy, not a generated \Proxy class. |  |  |
| 2 | `app/code/Payone/Core/Model/ConfigProvider.php:187` | TP-low | The constructor names the generated Proxy of Ratepay Installment, which builds the real object only on first use, so it costs nothing at run time. |  |  |
