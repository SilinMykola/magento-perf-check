# P-COL-02: calibration sample

30 of 44 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Mageplaza/Blog/Model/Author.php:88` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    82       * @param $name
    83       *
    84       * @return DataObject
    85       */
    86      public function getAuthorByName($name)
    87      {
>   88          return $this->getCollection()->addFieldToFilter('name', $name)->getFirstItem();
    89      }
    90  
    91      /**
    92       * @return array
    93       */
    94      public function getIdentities()
```

## 2. `app/code/Tweakwise/Magento2Tweakwise/Model/AttributeSlugRepository.php:153` (extended)

[P-COL-02] Tweakwise\Magento2Tweakwise\Model\ResourceModel\AttributeSlug\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   147  
   148          if (!$collection->getSize()) {
   149              throw new NoSuchEntityException(__('No slug found for attribute "%1".', $attribute));
   150          }
   151  
   152          /** @var AttributeSlug $attributeSlug */
>  153          $attributeSlug = $collection->getFirstItem();
   154          return $attributeSlug;
   155      }
   156  
   157      /**
   158       * {@inheritdoc}
   159       */
```

## 3. `app/code/Dotdigitalgroup/Email/Model/Queue/Customer/EmailUpdateConsumer.php:187` (extended)

[P-COL-02] Dotdigitalgroup\Email\Model\ResourceModel\Contact\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   181                  $emailUpdateData->getEmail(),
   182                  $emailUpdateData->getWebsiteId()
   183              );
   184  
   185          if ($orphaned->getSize()) {
   186              /** @var Contact $row */
>  187              $row = $orphaned->getFirstItem();
   188              $this->contactResource->delete($row);
   189          }
   190      }
   191  }
```

## 4. `app/code/Dotdigitalgroup/Email/Block/Adminhtml/Dashboard/Information.php:207` (extended)

[P-COL-02] Dotdigitalgroup\Email\Model\ResourceModel\FailedAuth\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   201      public function getAuthStatus()
   202      {
   203          $collection = $this->failedAuthCollectionFactory->create()
   204              ->loadByStoreId($this->storeIdFromParam);
   205  
   206          /** @var FailedAuth $failedAuth */
>  207          $failedAuth = $collection->getFirstItem();
   208  
   209          /** @var FailedAuthModel $failedAuth */
   210          if ($failedAuth->getId()) {
   211              return ($failedAuth->isLocked()) ? __('Locked') : __('Not locked');
   212          } else {
   213              return __('Not locked');
```

## 5. `app/code/Buckaroo/Magento2/Helper/PaymentGroupTransaction.php:450` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   444      public function getGroupTransactionByTrxId($trxId)
   445      {
   446          $collection = $this->groupTransactionFactory->create()
   447              ->getCollection()
   448              ->addFieldToFilter('transaction_id', $trxId);
   449  
>  450          return $collection->getFirstItem();
   451      }
   452  
   453      /**
   454       * Get successful group transactions for orderId with giftcard label
   455       *
   456       * @param string|null $orderId
```

## 6. `app/code/Magefan/Translation/Model/Config.php:175` (extended)

[P-COL-02] Magento\Config\Model\ResourceModel\Config\Data\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   169       * Get today usage.
   170       *
   171       * @return int
   172       */
   173      public function getTodayUsage(): int
   174      {
>  175          $configValue = (string)$this->configCollectionFactory
   176              ->create()
   177              ->addFieldToFilter('path', ['eq' => self::XML_PATH_API_USAGE])
   178              ->getFirstItem()
   179              ->getValue();
   180  
   181          $data = json_decode($configValue, true);
```

## 7. `app/code/Buckaroo/Magento2/Model/SecondChanceRepository.php:628` (extended)

[P-COL-02] Buckaroo\Magento2\Model\ResourceModel\SecondChance\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   622      public function getSecondChanceByToken($token)
   623      {
   624          $collection = $this->secondChanceCollectionFactory->create();
   625          $collection->addFieldToFilter('token', $token);
   626          // Allow completed records to be accessed - they should still work for customers
   627  
>  628          $secondChance = $collection->getFirstItem();
   629          if (!$secondChance->getId()) {
   630              throw new NoSuchEntityException(__('Invalid token.'));
   631          }
   632  
   633          // Always load the base order (without suffixes) for recreation
   634          $baseOrderId = $this->getBaseOrderId($secondChance->getOrderId());
```

## 8. `app/code/Dotdigitalgroup/Email/Model/Cron/JobChecker.php:49` (extended)

[P-COL-02] Dotdigitalgroup\Email\Model\ResourceModel\Cron\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    43       *
    44       * @return string
    45       */
    46      public function getLastJobFinishedAt($jobCode)
    47      {
    48          /** @var \Magento\Cron\Model\Schedule $lastSuccessfulJob */
>   49          $lastSuccessfulJob = $this->cronCollection->create()
    50              ->addFieldToFilter('job_code', $jobCode)
    51              ->addFieldToFilter('status', 'success')
    52              ->setOrder('finished_at', 'desc')
    53              ->getFirstItem();
    54  
    55          return $lastSuccessfulJob->getFinishedAt();
```

## 9. `app/code/Buckaroo/Magento2/Model/ConfigProvider/Method/Giftcards.php:284` (extended)

[P-COL-02] Buckaroo\Magento2\Model\ResourceModel\Giftcard\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   278  
   279              $logo = $this->getGiftcardLogo($code);
   280  
   281              // Try to get custom logo from collection
   282              $giftcardCollection = $this->giftcardCollectionFactory->create();
   283              $giftcardCollection->addFieldToFilter('servicecode', $code);
>  284              $giftcard = $giftcardCollection->getFirstItem();
   285  
   286              if ($giftcard->getId() && $giftcard->getLogo()) {
   287                  $logo = $this->storeManager->getStore()->getBaseUrl(
   288                      UrlInterface::URL_TYPE_MEDIA
   289                  ) . $giftcard->getLogo();
   290              }
```

## 10. `app/code/Tweakwise/Magento2Tweakwise/Model/AttributeSlugRepository.php:230` (extended)

[P-COL-02] Tweakwise\Magento2Tweakwise\Model\ResourceModel\AttributeSlug\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   224              ->addFieldToFilter('store_id', (string)$storeId);
   225          if (!$collection->getSize()) {
   226              throw new NoSuchEntityException(__('No slug found for attribute "%1".', $slug));
   227          }
   228  
   229          /** @var AttributeSlug $attributeSlug */
>  230          $attributeSlug = $collection->getFirstItem();
   231          return $attributeSlug;
   232      }
   233  
   234      /**
   235       * @return void
   236       */
```

## 11. `app/code/CtiDigital/Configurator/Component/AdminUsers.php:151` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   145       * @param $roleName
   146       * @return int|null
   147       */
   148      private function getUserRoleFromName($roleName)
   149      {
   150          $role = $this->roleFactory->create();
>  151          $role = $role->getCollection()->addFieldToFilter('role_name', $roleName)->getFirstItem();
   152  
   153          return $role->getId();
   154      }
   155  
   156      /**
   157       *  Validate that required data is not empty
```

## 12. `app/code/CtiDigital/Configurator/Component/AdminRoles.php:92` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    86          if ($roleCount > 0) {
    87              $this->log->logInfo(
    88                  sprintf('Admin Role "%s" creation skipped: Already exists in database', $roleName)
    89              );
    90  
    91              //Get exisiting Role
>   92              $role = $role->getCollection()->addFieldToFilter('role_name', $roleName)->getFirstItem();
    93              $this->setResourceIds($role, $resources);
    94  
    95              return;
    96          }
    97  
    98          $this->log->logInfo(
```

## 13. `app/code/KiwiCommerce/CronScheduler/Model/ResourceModel/Schedule/Collection.php:61` (extended)

[P-COL-02] KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    55      {
    56          $this->getSelect()->reset('columns')
    57              ->columns(['executed_at'])
    58              ->where('executed_at is not null and job_code ="kiwicommerce_cronscheduler_status"')
    59              ->order('finished_at desc');
    60  
>   61          $last = $this->getFirstItem();
    62          if ($last) {
    63              return $last->getExecutedAt();
    64          } else {
    65              return null;
    66          }
    67      }
```

## 14. `app/code/Mageplaza/SocialLogin/Model/Social.php:411` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   405       * @return User
   406       */
   407      public function getUserBySocial($identify, $type)
   408      {
   409          $user = $this->_userModel;
   410  
>  411          $socialCustomer = $this->getCollection()
   412              ->addFieldToFilter('social_id', $identify)
   413              ->addFieldToFilter('type', $type)->addFieldToFilter('user_id', ['notnull' => true])
   414              ->getFirstItem();
   415  
   416          if ($socialCustomer && $socialCustomer->getId()) {
   417              $user->load($socialCustomer->getUserId());
```

## 15. `app/code/Mageplaza/SocialLogin/Model/Social.php:178` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   172       */
   173      public function getCustomerBySocial($identify, $type)
   174      {
   175          $websiteId = $this->storeManager->getWebsite()->getId();
   176          $customer  = $this->customerFactory->create();
   177  
>  178          $socialCustomer = $this->getCollection()
   179              ->addFieldToFilter('social_id', $identify)
   180              ->addFieldToFilter('type', $type)
   181              ->addFieldToFilter('status', ['null' => 'true'])
   182              ->addFieldToFilter('website_id', $websiteId)
   183              ->getFirstItem();
   184  
```

## 16. `app/code/Mageplaza/Blog/Block/Frontend.php:476` (extended)

[P-COL-02] Magento\Sales\Model\ResourceModel\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   470              }
   471  
   472              $categories = $this->helperData->getCategoryCollection($post->getCategoryIds());
   473              // Only the top-priority category is shown here — this was capping by
   474              // blog/sidebar/categories/maximum, an unrelated field for the SIDEBAR
   475              // widget, which left it unbounded whenever that field was empty.
>  476              $_cat = $categories->getFirstItem();
   477              if ($_cat->getId()) {
   478                  $categoryHtml[] = '<a class="mp-info" href="'
   479                      . $this->escapeUrl($this->helperData->getBlogUrl(
   480                          $_cat,
   481                          HelperData::TYPE_CATEGORY
   482                      ))
```

## 17. `app/code/Buckaroo/Magento2/Model/ResourceModel/Order/Handler/State.php:73` (extended)

[P-COL-02] Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    67          if ($order->getPayment() &&
    68              $order->getPayment()->getMethod() == 'buckaroo_magento2_payperemail'
    69          ) {
    70              $config = $this->configProviderMethodFactory->get(PayPerEmail::CODE);
    71              if ($config->isEnabledB2B((int)$order->getStoreId())
    72                  && $order->getState() == Order::STATE_PROCESSING
>   73                  && $order->getInvoiceCollection() && $order->getInvoiceCollection()->getFirstItem()
    74                  && $order->getInvoiceCollection()->getFirstItem()->getState() == 1
    75              ) {
    76                  $this->logger->addDebug(sprintf(
    77                      '[ORDER_STATUS] | [Handler] | [%s:%s] - Skip update order status for PayPerEmail | order: %s',
    78                      __METHOD__,
    79                      __LINE__,
```

## 18. `app/code/CtiDigital/Configurator/Component/TaxRules.php:157` (extended)

[P-COL-02] Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   151          $rateIds = [];
   152          $rateNamesArray = explode(',', $rateNames);
   153  
   154          foreach ($rateNamesArray as $name) {
   155              $rateCollection = $this->rateCollectionFactory->create()
   156                  ->addFieldToSelect('tax_calculation_rate_id');
>  157              $rate = $rateCollection->addFieldToFilter('code', $name)->getFirstItem();
   158              $rateIds[] = $rate->getId();
   159          }
   160  
   161          return $rateIds;
   162      }
   163  
```

## 19. `app/code/Mageplaza/SocialLogin/Model/Social.php:431` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   425       * @param $identifier
   426       *
   427       * @return DataObject
   428       */
   429      public function getUser($type, $identifier)
   430      {
>  431          return $this->getCollection()
   432              ->addFieldToSelect('user_id')
   433              ->addFieldToSelect('social_customer_id')
   434              ->addFieldToFilter('type', $type)
   435              ->addFieldToFilter('social_id', base64_decode($identifier))
   436              ->addFieldToFilter('status', self::STATUS_LOGIN)
   437              ->getFirstItem();
```

## 20. `app/code/Mageplaza/Blog/Controller/Post/Preview.php:354` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   348      {
   349          try {
   350              switch ($action) {
   351                  /** Comment action */
   352                  case self::COMMENT:
   353                      $model->addData($data)->save();
>  354                      $cmtHasReply = $model->getCollection()
   355                          ->addFieldToFilter('comment_id', $data['reply_id'])
   356                          ->getFirstItem();
   357                      if ($cmtHasReply->getId()) {
   358                          $cmtHasReply->setHasReply(1)->save();
   359                      }
   360  
```

## 21. `app/code/CtiDigital/Configurator/Component/TaxRules.php:180` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   174          $taxClassIds = [];
   175          $taxClassNamesArray = explode(',', $names);
   176          $classModel = $this->classModelFactory->create();
   177          $classCollection = $classModel->getCollection();
   178  
   179          foreach ($taxClassNamesArray as $name) {
>  180              $class = $classCollection->addFieldToFilter('class_name', $name)->getFirstItem();
   181              $classId = $class->getId();
   182  
   183              if ($classId == 0) {
   184                  $classModel->setClassName($name)
   185                      ->setClassType($type)
   186                      ->save();
```

## 22. `app/code/Dotdigitalgroup/Email/Setup/Patch/Data/SetDefaultRoleForSystemAlerts.php:80` (extended)

[P-COL-02] Magento\Authorization\Model\ResourceModel\Role\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    74       *
    75       * @return void
    76       */
    77      public function apply()
    78      {
    79          if (!$this->scopeConfig->isSetFlag(Config::XML_PATH_CONNECTOR_SYSTEM_ALERTS_USER_ROLES)) {
>   80              $defaultRole = $this->roleCollection->create()
    81                  ->setRolesFilter()
    82                  ->getFirstItem();
    83  
    84              $this->configResource->saveConfig(
    85                  Config::XML_PATH_CONNECTOR_SYSTEM_ALERTS_USER_ROLES,
    86                  $defaultRole->getId(),
```

## 23. `app/code/Buckaroo/Magento2/Model/SecondChance/FollowUpOrderFinder.php:197` (extended)

[P-COL-02] Magento\Sales\Model\ResourceModel\Order\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   191       *
   192       * @param Collection $collection
   193       * @return OrderInterface|null
   194       */
   195      private function getFirstOrder(Collection $collection): ?OrderInterface
   196      {
>  197          $order = $collection->getFirstItem();
   198  
   199          return $order instanceof OrderInterface && $order->getId() ? $order : null;
   200      }
   201  }
```

## 24. `app/code/Mageplaza/Blog/Helper/Data.php:295` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   289      {
   290          $collection = $this->getAuthorCollection();
   291          if (!$collection) {
   292              return null;
   293          }
   294  
>  295          $author = $collection->getFirstItem();
   296  
   297          return $author->getId() ? $author : null;
   298      }
   299  
   300      /**
   301       * @param string $host hostname or "host:port"
```

## 25. `app/code/Dotdigitalgroup/Email/Controller/Adminhtml/Queue/Message.php:62` (extended)

[P-COL-02] Dotdigitalgroup\Email\Ui\DataProvider\Queue\Listing\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    56  
    57          $message = $this->queueCollectionFactory
    58              ->create()
    59              ->loadMessageById($messageId);
    60  
    61          if ($message->getSize()) {
>   62              $data = $message->getFirstItem()->getBody();
    63          } else {
    64              $data = '';
    65          }
    66          $resultJson = $this->jsonFactory->create();
    67          $resultJson->setData($data);
    68  
```

## 26. `app/code/CtiDigital/Configurator/Component/ApiIntegrations.php:104` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
    98          $integration = $this->integrationFactory->create();
    99          $integrationCount = $integration->getCollection()
   100              ->addFieldToFilter('name', $integrationData['name'])
   101              ->getSize();
   102  
   103          if ($integrationCount > 0) {
>  104              $integration = $integration
   105                  ->getCollection()
   106                  ->addFieldToFilter('name', $integrationData['name'])
   107                  ->getFirstItem();
   108  
   109              $this->log->logComment(
   110                  sprintf('API Integration "%s" already exists: Creation skipped', $integration->getName())
```

## 27. `app/code/Dotdigitalgroup/Email/Block/Adminhtml/Dashboard/Information.php:228` (extended)

[P-COL-02] Dotdigitalgroup\Email\Model\ResourceModel\FailedAuth\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   222      public function getLastFailedAuth()
   223      {
   224          $collection = $this->failedAuthCollectionFactory->create()
   225              ->loadByStoreId($this->storeIdFromParam);
   226  
   227          /** @var FailedAuth $failedAuth */
>  228          $failedAuth = $collection->getFirstItem();
   229  
   230          if ($failedAuth->getId()) {
   231              return $this->formatTime($failedAuth->getLastAttemptDate(), IntlDateFormatter::LONG);
   232          } else {
   233              return __('None found');
   234          }
```

## 28. `app/code/Magmodules/Channable/Model/Returns/Repository.php:161` (extended)

[P-COL-02] Magmodules\Channable\Model\Returns\Collection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   155                  $collection->addFieldToFilter($attribute, ['in' => $value]);
   156              } else {
   157                  $collection->addFieldToFilter($attribute, $value);
   158              }
   159          }
   160          if ($getFirst) {
>  161              return $collection->getFirstItem();
   162          } else {
   163              return $collection;
   164          }
   165      }
   166  }
```

## 29. `app/code/Mageplaza/Reports/Model/CardsManageFactory.php:147` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   141       * @throws Exception
   142       */
   143      public function getCurrentConfig()
   144      {
   145          if ($this->_authSession->getUser()) {
   146              $userId  = $this->_authSession->getUser()->getId();
>  147              $current = $this->_bookmark->create()->getCollection()
   148                  ->addFieldToFilter('namespace', 'mageplaza_reports_cards')
   149                  ->addFieldToFilter('user_id', $userId)
   150                  ->addFieldToFilter('identifier', 'current')->getFirstItem();
   151          } else {
   152              $userId  = '1';
   153              $current = $this->_bookmark->create()->getCollection()
```

## 30. `app/code/Mageplaza/Blog/Controller/Post/View.php:310` (extended)

[P-COL-02] Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().

```
   304      {
   305          try {
   306              switch ($action) {
   307                  /** Comment action */
   308                  case self::COMMENT:
   309                      $model->addData($data)->save();
>  310                      $cmtHasReply = $model->getCollection()
   311                          ->addFieldToFilter('comment_id', $data['reply_id'])
   312                          ->getFirstItem();
   313                      if ($cmtHasReply->getId()) {
   314                          $cmtHasReply->setHasReply(1)->save();
   315                      }
   316                      $this->_eventManager->dispatch('blog_post_comment', ['comment_data' => $data]);
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Mageplaza/Blog/Model/Author.php:88` | FP | Blog authors are one row per admin user and a display name matches one author in practice. |  |  |
| 2 | `app/code/Tweakwise/Magento2Tweakwise/Model/AttributeSlugRepository.php:153` | FP | A unique index on (attribute, store_id) allows at most one row. |  |  |
| 3 | `app/code/Dotdigitalgroup/Email/Model/Queue/Customer/EmailUpdateConsumer.php:187` | FP | The module keeps one non-customer contact row per email and website, so the filter matches one row. |  |  |
| 4 | `app/code/Dotdigitalgroup/Email/Block/Adminhtml/Dashboard/Information.php:207` | FP | The controller writes one failed-auth row per store (load by store_id, then update), so at most one row matches. |  |  |
| 5 | `app/code/Buckaroo/Magento2/Helper/PaymentGroupTransaction.php:450` | FP | saveGroupTransaction() skips the insert when the transaction id exists, so one row per transaction_id. |  |  |
| 6 | `app/code/Magefan/Translation/Model/Config.php:175` | FP | The usage path is saved only in the default scope through configWriter, so one core_config_data row matches. |  |  |
| 7 | `app/code/Buckaroo/Magento2/Model/SecondChanceRepository.php:628` | FP | The token is a generated random value per Second Chance record, so it matches one row. |  |  |
| 8 | `app/code/Dotdigitalgroup/Email/Model/Cron/JobChecker.php:49` | TP-low | Loads all success rows of one job from cron_schedule, bounded by the cron history cleanup, once per cron run. |  |  |
| 9 | `app/code/Buckaroo/Magento2/Model/ConfigProvider/Method/Giftcards.php:284` | FP | The giftcard table holds one admin-managed row per service code. |  |  |
| 10 | `app/code/Tweakwise/Magento2Tweakwise/Model/AttributeSlugRepository.php:230` | FP | A unique index on (slug, store_id) allows at most one row. |  |  |
| 11 | `app/code/CtiDigital/Configurator/Component/AdminUsers.php:151` | FP | role_name identifies one group role in practice; the call runs in a one-off configurator command. |  |  |
| 12 | `app/code/CtiDigital/Configurator/Component/AdminRoles.php:92` | FP | role_name identifies one group role in practice; the call runs in a one-off configurator command. |  |  |
| 13 | `app/code/KiwiCommerce/CronScheduler/Model/ResourceModel/Schedule/Collection.php:61` | TP-low | Reads only executed_at for one job's cron_schedule rows, bounded by history cleanup, on admin cron pages. |  |  |
| 14 | `app/code/Mageplaza/SocialLogin/Model/Social.php:411` | FP | social_id plus type and a non-null user_id identify one linked social account. |  |  |
| 15 | `app/code/Mageplaza/SocialLogin/Model/Social.php:178` | FP | social_id plus type and website identify one linked social account. |  |  |
| 16 | `app/code/Mageplaza/Blog/Block/Frontend.php:476` | TP-low | Loads the few categories of one blog post to show the first. |  |  |
| 17 | `app/code/Buckaroo/Magento2/Model/ResourceModel/Order/Handler/State.php:73` | FP | Order::getInvoiceCollection() already iterated the collection for a saved order, so getFirstItem() runs no query. |  |  |
| 18 | `app/code/CtiDigital/Configurator/Component/TaxRules.php:157` | FP | The tax rate resource declares code as a unique field, so at most one row. |  |  |
| 19 | `app/code/Mageplaza/SocialLogin/Model/Social.php:431` | FP | type, social_id and status identify one linked social account. |  |  |
| 20 | `app/code/Mageplaza/Blog/Controller/Post/Preview.php:354` | FP | comment_id is the primary key of mageplaza_blog_comment. |  |  |
| 21 | `app/code/CtiDigital/Configurator/Component/TaxRules.php:180` | FP | class_name is unique per class_type, so at most two rows, and the reused collection loads only on the first iteration. |  |  |
| 22 | `app/code/Dotdigitalgroup/Email/Setup/Patch/Data/SetDefaultRoleForSystemAlerts.php:80` | TP-low | Loads all admin group roles once, in a one-off data patch. |  |  |
| 23 | `app/code/Buckaroo/Magento2/Model/SecondChance/FollowUpOrderFinder.php:197` | FP | Both callers set setPageSize(1) on the collection before passing it in. |  |  |
| 24 | `app/code/Mageplaza/Blog/Helper/Data.php:295` | FP | A customer is linked to one blog author, so customer_id matches one row. |  |  |
| 25 | `app/code/Dotdigitalgroup/Email/Controller/Adminhtml/Queue/Message.php:62` | FP | main_table.id is the queue message primary key. |  |  |
| 26 | `app/code/CtiDigital/Configurator/Component/ApiIntegrations.php:104` | FP | integration.name has a unique constraint. |  |  |
| 27 | `app/code/Dotdigitalgroup/Email/Block/Adminhtml/Dashboard/Information.php:228` | FP | The controller writes one failed-auth row per store (load by store_id, then update), so at most one row matches. |  |  |
| 28 | `app/code/Magmodules/Channable/Model/Returns/Repository.php:161` | unclear | The filters come from the caller, and no caller in the corpus passes getFirst = true; the only caller iterates the returned collection. |  |  |
| 29 | `app/code/Mageplaza/Reports/Model/CardsManageFactory.php:147` | FP | namespace, user_id and identifier 'current' identify the single current bookmark of one admin user. |  |  |
| 30 | `app/code/Mageplaza/Blog/Controller/Post/View.php:310` | FP | comment_id is the primary key of mageplaza_blog_comment. |  |  |
