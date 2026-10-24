# P-LOOP-03: calibration sample

30 of 142 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-cron/Observer/ProcessCronQueueObserver.php:660` (core)

[P-LOOP-03] Magento\Cron\Model\Schedule::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   654                      $this->invalid[$jobCode][] = $scheduledAt;
   655                  }
   656                  continue;
   657              }
   658              if (!$alreadyScheduled) {
   659                  // time matches cron expression
>  660                  $schedule->save();
   661              }
   662          }
   663      }
   664  
   665      /**
   666       * Create a schedule of cron job.
```

## 2. `vendor/magento/module-payment-services-paypal/Cron/SyncOrderPaymentStatus.php:113` (core)

[P-LOOP-03] Magento\Sales\Api\OrderRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   107              if ($wasRefreshed) {
   108                  $this->logger->debug('Payment status for order ' . $order->getIncrementId() . ' was refreshed');
   109              } else {
   110                  $this->logger->debug('Payment status for order ' . $order->getIncrementId() . ' was not refreshed');
   111              }
   112  
>  113              $this->orderRepository->save($order);
   114          }
   115      }
   116  
   117      /**
   118       * Check if the cron job should run
   119       *
```

## 3. `app/code/Mollie/Payment/Service/Order/DeletePaymentReminder.php:45` (packagist)

[P-LOOP-03] Mollie\Payment\Api\PendingPaymentReminderRepositoryInterface::delete() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    39  
    40          $criteria = $this->criteriaBuilderFactory->create();
    41          $criteria->addFilter(PendingPaymentReminderInterface::CUSTOMER_ID, '', 'null');
    42          $criteria->addFilter(PendingPaymentReminderInterface::HASH, $this->encryptor->hash($email));
    43  
    44          foreach ($this->paymentReminderRepository->getList($criteria->create())->getItems() as $reminder) {
>   45              $this->paymentReminderRepository->delete($reminder);
    46          }
    47      }
    48  }
```

## 4. `vendor/magento/module-customer/Model/Config/Backend/Show/Customer.php:153` (core)

[P-LOOP-03] Magento\Eav\Model\Entity\Attribute\AbstractAttribute::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   147              $website = $this->storeManager->getWebsite($this->getScopeCode());
   148              foreach ($this->_getAttributeObjects() as $attributeObject) {
   149                  $attributeObject->setWebsite($website);
   150                  $attributeObject->load($attributeObject->getId());
   151                  $attributeObject->setData('scope_is_required', null);
   152                  $attributeObject->setData('scope_is_visible', null);
>  153                  $attributeObject->save();
   154              }
   155          } elseif ($this->getScope() == ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
   156              $defaultValue = $this->configSource->get(ScopeConfigInterface::SCOPE_TYPE_DEFAULT . '/' . $this->getPath());
   157              $valueConfig = $this->getValueConfig($defaultValue === [] ? '' : $defaultValue);
   158              foreach ($this->_getAttributeObjects() as $attributeObject) {
   159                  $attributeObject->setData('is_required', $valueConfig['is_required']);
```

## 5. `vendor/magento/module-customer/Setup/CustomerSetup.php:529` (core)

[P-LOOP-03] Magento\Eav\Model\Entity\Attribute\AbstractAttribute::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   523          foreach ($entityAttributes as $entityType => $attributes) {
   524              foreach ($attributes as $attributeCode => $attributeData) {
   525                  $attribute = $this->getEavConfig()->getAttribute($entityType, $attributeCode);
   526                  foreach ($attributeData as $key => $value) {
   527                      $attribute->setData($key, $value);
   528                  }
>  529                  $attribute->save();
   530              }
   531          }
   532      }
   533  }
```

## 6. `vendor/magento/module-sales-rule/Model/Service/CouponManagementService.php:219` (core)

[P-LOOP-03] Magento\SalesRule\Api\CouponRepositoryInterface::deleteById() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   213          $results = $this->couponMassDeleteResultFactory->create();
   214          $failedItems = [];
   215          $fieldValues = array_flip($fieldValues);
   216          foreach ($couponsCollection->getItems() as $coupon) {
   217              $couponValue = ($fieldName == 'code') ? $coupon->getCode() : $coupon->getCouponId();
   218              try {
>  219                  $this->repository->deleteById($coupon->getCouponId());
   220              } catch (\Exception $e) {
   221                  $failedItems[] = $couponValue;
   222              }
   223              unset($fieldValues[$couponValue]);
   224          }
   225          $results->setFailedItems($failedItems);
```

## 7. `vendor/magento/module-newsletter/Model/Plugin/CustomerPlugin.php:313` (core)

[P-LOOP-03] Magento\Newsletter\Model\Subscriber::delete() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   307              } catch (NoSuchEntityException $exception) {
   308                  $this->logger->error($exception);
   309              }
   310          }
   311          /** @var Subscriber $subscriber */
   312          foreach ($collection as $subscriber) {
>  313              $subscriber->delete();
   314          }
   315      }
   316  
   317      /**
   318       * Get Subscriber model by customer
   319       *
```

## 8. `app/code/Snowdog/Menu/Service/Menu/SaveRequestProcessor.php:110` (packagist)

[P-LOOP-03] Snowdog\Menu\Api\NodeRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   104                  continue;
   105              }
   106  
   107              if (!isset($invalidNodes[$nodeId])) {
   108                  $nodeObject = $this->nodeFactory->create();
   109                  $nodeObject->setMenuId($menu->getMenuId());
>  110                  $nodeObject = $this->nodeRepository->save($nodeObject);
   111                  $nodeMap[$nodeId] = $nodeObject;
   112              }
   113          }
   114  
   115          $nodesToDeleteIds = array_keys($nodesToDelete);
   116          $nodesToDeleteImages = $this->imageNode->getNodeListImages($nodesToDeleteIds);
```

## 9. `vendor/magento/module-catalog-rule/Controller/Adminhtml/Promo/Catalog/MassDelete.php:49` (core)

[P-LOOP-03] Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::deleteById() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    43       */
    44      public function execute()
    45      {
    46          $ids = $this->getRequest()->getParam('catalogpricerule');
    47          if ($ids) {
    48              foreach ($ids as $id) {
>   49                  $this->ruleRepository->deleteById($id);
    50              }
    51              $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', count($ids)));
    52          } else {
    53              $this->messageManager->addErrorMessage(__('Please select a catalog price rule(s)'));
    54          }
    55  
```

## 10. `vendor/magento/magento2-base/setup/src/Magento/Setup/Fixtures/AttributeSet/AttributeSetFixture.php:133` (core)

[P-LOOP-03] Magento\Catalog\Api\ProductAttributeRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   127  
   128              /** @var  ProductAttributeInterface $attribute */
   129              $attribute = $this->attributeFactory->create(['data' => $attributeData]);
   130              $attribute->setOptions($options);
   131              $attribute->setNote('auto');
   132  
>  133              $productAttribute = $this->attributeRepository->save($attribute);
   134              $attributeId = $productAttribute->getAttributeId();
   135  
   136              //Associate Attribute to Attribute Set
   137              $this->attributeManagement->assign($attributeSetId, $attributeGroupId, $attributeId, $sortOrder);
   138          }
   139  
```

## 11. `vendor/magento/module-customer/Controller/Adminhtml/Index/MassDelete.php:56` (core)

[P-LOOP-03] Magento\Customer\Api\CustomerRepositoryInterface::deleteById() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    50       * @inheritdoc
    51       */
    52      protected function massAction(AbstractCollection $collection)
    53      {
    54          $customersDeleted = 0;
    55          foreach ($collection->getAllIds() as $customerId) {
>   56              $this->customerRepository->deleteById($customerId);
    57              $customersDeleted++;
    58          }
    59  
    60          if ($customersDeleted) {
    61              $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $customersDeleted));
    62          }
```

## 12. `vendor/magento/module-store/Model/Config/Importer/Processor/Delete.php:186` (core)

[P-LOOP-03] Magento\Framework\Model\ResourceModel\Db\AbstractDb::delete() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   180       */
   181      private function deleteGroups(array $items)
   182      {
   183          $this->groupCollection->addFieldToFilter('code', ['in' => array_keys($items)]);
   184          /** @var Group $group */
   185          foreach ($this->groupCollection as $group) {
>  186              $group->getResource()->delete($group);
   187          }
   188      }
   189  }
```

## 13. `app/code/Adyen/Payment/Cron/StateDataCleanUp.php:57` (packagist)

[P-LOOP-03] Adyen\Payment\Model\ResourceModel\StateData::delete() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    51  
    52      public function execute()
    53      {
    54          $expiredStateDataRows = $this->stateDataCollection->getExpiredStateDataRows();
    55          foreach ($expiredStateDataRows->getIterator() as $expiredStateDataRow) {
    56              try {
>   57                  $this->stateDataResourceModel->delete($expiredStateDataRow);
    58              } catch (\Exception $exception) {
    59                  $this->adyenLogger->error(__("State data was not cleaned-up: %s", $exception->getMessage()));
    60              }
    61          }
    62      }
    63  }
```

## 14. `vendor/magento/module-tax/Model/Calculation/Rule.php:143` (core)

[P-LOOP-03] Magento\Tax\Model\Calculation::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   137                      $dataArray = [
   138                          'tax_calculation_rule_id' => $this->getId(),
   139                          'tax_calculation_rate_id' => $r,
   140                          'customer_tax_class_id' => $c,
   141                          'product_tax_class_id' => $p,
   142                      ];
>  143                      $this->_calculation->setData($dataArray)->save();
   144                  }
   145              }
   146          }
   147      }
   148  
   149      /**
```

## 15. `vendor/magento/module-customer/Model/AccountManagement.php:962` (core)

[P-LOOP-03] Magento\Customer\Api\AddressRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   956          try {
   957              foreach ($customerAddresses as $address) {
   958                  if ($address->getId()) {
   959                      $newAddress = clone $address;
   960                      $newAddress->setId(null);
   961                      $newAddress->setCustomerId($customer->getId());
>  962                      $this->addressRepository->save($newAddress);
   963                  } else {
   964                      $address->setCustomerId($customer->getId());
   965                      $this->addressRepository->save($address);
   966                  }
   967              }
   968              $this->customerRegistry->remove($customer->getId());
```

## 16. `vendor/magento/module-sales-rule/Model/Coupon/Usage/Processor.php:156` (core)

[P-LOOP-03] Magento\SalesRule\Api\CouponRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   150      private function revertCouponTimesUsed(array $incrementedCouponIds): void
   151      {
   152          foreach ($incrementedCouponIds as $couponId) {
   153              $coupon = $this->couponRepository->getById($couponId);
   154              $coupon->setTimesUsed($coupon->getTimesUsed() - 1);
   155              try {
>  156                  $this->couponRepository->save($coupon);
   157              } catch (Exception $e) {
   158                  throw new CouldNotSaveException(__('Error occurred when saving coupon: %1', $e->getMessage()));
   159              }
   160          }
   161      }
   162  
```

## 17. `app/code/Adyen/Payment/Controller/Webhook/Index.php:95` (packagist)

[P-LOOP-03] Adyen\Payment\Api\Repository\AdyenNotificationRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    89              $acceptor = $this->webhookAcceptorFactory->getAcceptor($webhookType);
    90  
    91              $notifications = $acceptor->getNotifications($rawPayload);
    92  
    93              foreach ($notifications as $notification) {
    94                  if (!$notification->isDuplicate()) {
>   95                      $notification = $this->adyenNotificationRepository->save($notification);
    96                      $this->adyenLogger->addAdyenResult(
    97                          sprintf("Notification %s is accepted", $notification->getId())
    98                      );
    99                  }
   100              }
   101  
```

## 18. `vendor/magento/module-ui/Controller/Adminhtml/Bookmark/Save.php:254` (core)

[P-LOOP-03] Magento\Ui\Api\BookmarkRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   248                  $bookmarkConfig = $bookmark->getConfig();
   249                  $existingConfig = $bookmarkConfig['views'][$bookmark->getIdentifier()]['data'] ?? null;
   250                  $currentConfig = $data[self::CURRENT_IDENTIFIER] ?? null;
   251                  if ($existingConfig && $currentConfig && $this->isPositionChanged($existingConfig, $currentConfig)) {
   252                      $bookmarkConfig['views'][$bookmark->getIdentifier()]['data'] = $data[self::CURRENT_IDENTIFIER];
   253                      $bookmark->setConfig($this->serializer->serialize($bookmarkConfig));
>  254                      $this->bookmarkRepository->save($bookmark);
   255                  }
   256                  break;
   257              }
   258          }
   259      }
   260  
```

## 19. `app/code/Adyen/Payment/Gateway/Http/Client/TransactionPayment.php:189` (packagist)

[P-LOOP-03] Adyen\Payment\Model\ResourceModel\PaymentResponse::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   183  
   184              /** @var PaymentResponse $paymentResponse */
   185              $paymentResponse = $this->paymentResponseFactory->create();
   186              $paymentResponse->setResponse((string) $response);
   187              $paymentResponse->setResultCode($response->getResultCode());
   188              $paymentResponse->setMerchantReference($response->getMerchantReference());
>  189              $this->paymentResponseResourceModel->save($paymentResponse);
   190  
   191              if (strcmp($response->getResultCode(), CheckoutApiPaymentResponse::RESULT_CODE_AUTHORISED) !== 0) {
   192                  /*
   193                   * Stop executing the command pool and return the value immediately to the validator pool.
   194                   * There is no point of authorizing the rest of the payment instruments as
   195                   * the partial payment order on Adyen will fail in any case after expiry.
```

## 20. `app/code/Mollie/Payment/Cron/RemoveSentPaymentReminders.php:52` (packagist)

[P-LOOP-03] Mollie\Payment\Api\SentPaymentReminderRepositoryInterface::delete() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    46              $this->builder->addFilter(Order::CREATED_AT, $date, 'lt');
    47              $this->builder->setPageSize(10);
    48  
    49              $result = $this->sentPaymentReminderRepository->getList($this->builder->create());
    50  
    51              foreach ($result->getItems() as $item) {
>   52                  $this->sentPaymentReminderRepository->delete($item);
    53              }
    54          } while ($result->getTotalCount());
    55      }
    56  }
```

## 21. `app/code/Snowdog/Menu/Controller/Adminhtml/Menu/MassDelete.php:80` (packagist)

[P-LOOP-03] Snowdog\Menu\Api\MenuRepositoryInterface::delete() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    74      {
    75          $collection = $this->getMenuCollection();
    76          $collectionSize = $collection->getSize();
    77  
    78          foreach ($collection as $menu) {
    79              try {
>   80                  $this->menuRepository->delete($menu);
    81              } catch (CouldNotDeleteException $exception) {
    82                  --$collectionSize;
    83              }
    84          }
    85  
    86          $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
```

## 22. `vendor/magento/module-media-storage/Model/File/Storage.php:280` (core)

[P-LOOP-03] Magento\MediaStorage\Model\File\Storage\Flag::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   274                      $hasErrors = $this->_synchronizeHasErrors($sourceModel, $destinationModel);
   275                      if ($hasErrors) {
   276                          $flagData['has_errors'] = true;
   277                      }
   278                  }
   279  
>  280                  $flag->setFlagData($flagData)->save();
   281  
   282                  $destinationModel->importFiles($files);
   283                  $offset += count($files);
   284              }
   285              unset($files);
   286          }
```

## 23. `vendor/magento/module-customer/Model/ResourceModel/Customer/Relation.php:39` (core)

[P-LOOP-03] Magento\Customer\Model\Address::delete() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    33  
    34                      if ($address->getId() == $defaultShippingId) {
    35                          $customer->setData('default_shipping', null);
    36                      }
    37  
    38                      $removedAddressId = $address->getId();
>   39                      $address->delete();
    40  
    41                      // Remove deleted address from customer address collection
    42                      $customer->getAddressesCollection()->removeItemByKey($removedAddressId);
    43                  } else {
    44                      $address->setParentId(
    45                          $customer->getId()
```

## 24. `vendor/magento/module-catalog-rule/Controller/Adminhtml/Promo/Catalog/MassActivate.php:48` (core)

[P-LOOP-03] Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    42          $ids = $this->getRequest()->getParam('catalogpricerule');
    43          try {
    44              if ($ids) {
    45                  foreach ($ids as $id) {
    46                      $model = $this->ruleRepository->get($id);
    47                      $model->setIsActive(1);
>   48                      $this->ruleRepository->save($model);
    49                  }
    50                  $this->messageManager->addSuccessMessage(__('You activated a total of %1 records.', count($ids)));
    51              } else {
    52                  $this->messageManager->addErrorMessage(__('Please select a catalog price rule(s)'));
    53              }
    54          } catch (\Exception $e) {
```

## 25. `vendor/magento/module-indexer/Setup/Patch/Data/InitializeIndexerState.php:101` (core)

[P-LOOP-03] Magento\Indexer\Model\Indexer\State::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    95              } else {
    96                  /** @var State $state */
    97                  $state = $this->stateFactory->create();
    98                  $state->loadByIndexer($indexerId);
    99                  $state->setHashConfig($hash);
   100                  $state->setStatus(StateInterface::STATUS_INVALID);
>  101                  $state->save();
   102              }
   103          }
   104      }
   105  
   106      /**
   107       * {@inheritdoc}
```

## 26. `vendor/magento/magento2-base/setup/src/Magento/Setup/Fixtures/StoresFixture.php:236` (core)

[P-LOOP-03] Magento\Store\Model\Website::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   230                      'website_id' => null,
   231                      'code' => $websiteCode,
   232                      'name' => $websiteName,
   233                      'is_default' => false,
   234                  ]
   235              );
>  236              $website->save();
   237              $this->websiteIds[] = $website->getId();
   238              $existedWebsitesCount++;
   239              $this->websiteCodes[] = $websiteCode;
   240          }
   241      }
   242  
```

## 27. `vendor/magento/magento2-base/setup/src/Magento/Setup/Fixtures/CategoriesFixture.php:143` (core)

[P-LOOP-03] Magento\Catalog\Model\Category::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   137                      ->setLevel($parentCategory->getLevel() + 1)
   138                      ->setPosition($i)
   139                      ->setAvailableSortBy('name')
   140                      ->setIsAnchor($nestingLevel <= 2)
   141                      ->setDefaultSortBy('name')
   142                      ->setIsActive(true);
>  143                  $category->save();
   144                  $categoryIndex++;
   145                  if ($nestingLevel < $this->maxNestingLevel) {
   146                      $this->generateCategories(
   147                          $category,
   148                          $categoriesNumberOnLevel,
   149                          $nestingLevel + 1,
```

## 28. `vendor/magento/module-cron/Observer/ProcessCronQueueObserver.php:881` (core)

[P-LOOP-03] Magento\Cron\Model\Schedule::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   875              } catch (\Exception $e) {
   876                  $this->processError($schedule, $e);
   877              }
   878  
   879              $this->retrier->execute(
   880                  function () use ($schedule) {
>  881                      $schedule->save();
   882                  },
   883                  $schedule->getResource()->getConnection()
   884              );
   885          }
   886      }
   887  
```

## 29. `vendor/magento/module-quote/Model/ResourceModel/Quote/Address/Rate/Collection.php:145` (core)

[P-LOOP-03] Magento\Quote\Model\ResourceModel\Quote\Address\Rate::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
   139                  $itemsToSave[] = $item;
   140              }
   141          }
   142          $this->deleteRates->execute($itemsToDelete);
   143          /** @var Rate $item */
   144          foreach ($itemsToSave as $item) {
>  145              $item->save();
   146          }
   147          return $this;
   148      }
   149  }
```

## 30. `vendor/magento/module-sales/Model/ResourceModel/Order/Invoice/Relation.php:62` (core)

[P-LOOP-03] Magento\Sales\Model\ResourceModel\Order\Invoice\Comment::save() is called inside a loop: one write (and its events and plugins) per iteration. Prefer a batch operation where one exists.

```
    56              }
    57          }
    58  
    59          if (null !== $object->getComments()) {
    60              foreach ($object->getComments() as $comment) {
    61                  /** @var \Magento\Sales\Model\Order\Invoice\Comment */
>   62                  $this->invoiceCommentResource->save($comment);
    63              }
    64          }
    65      }
    66  }
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-cron/Observer/ProcessCronQueueObserver.php:660` |  |  | re-check |  |
| 2 | `vendor/magento/module-payment-services-paypal/Cron/SyncOrderPaymentStatus.php:113` |  |  | re-check |  |
| 3 | `app/code/Mollie/Payment/Service/Order/DeletePaymentReminder.php:45` |  |  | re-check |  |
| 4 | `vendor/magento/module-customer/Model/Config/Backend/Show/Customer.php:153` |  |  |  |  |
| 5 | `vendor/magento/module-customer/Setup/CustomerSetup.php:529` |  |  |  |  |
| 6 | `vendor/magento/module-sales-rule/Model/Service/CouponManagementService.php:219` |  |  |  |  |
| 7 | `vendor/magento/module-newsletter/Model/Plugin/CustomerPlugin.php:313` |  |  |  |  |
| 8 | `app/code/Snowdog/Menu/Service/Menu/SaveRequestProcessor.php:110` |  |  |  |  |
| 9 | `vendor/magento/module-catalog-rule/Controller/Adminhtml/Promo/Catalog/MassDelete.php:49` |  |  |  |  |
| 10 | `vendor/magento/magento2-base/setup/src/Magento/Setup/Fixtures/AttributeSet/AttributeSetFixture.php:133` |  |  |  |  |
| 11 | `vendor/magento/module-customer/Controller/Adminhtml/Index/MassDelete.php:56` |  |  |  |  |
| 12 | `vendor/magento/module-store/Model/Config/Importer/Processor/Delete.php:186` |  |  |  |  |
| 13 | `app/code/Adyen/Payment/Cron/StateDataCleanUp.php:57` |  |  |  |  |
| 14 | `vendor/magento/module-tax/Model/Calculation/Rule.php:143` |  |  |  |  |
| 15 | `vendor/magento/module-customer/Model/AccountManagement.php:962` |  |  |  |  |
| 16 | `vendor/magento/module-sales-rule/Model/Coupon/Usage/Processor.php:156` |  |  |  |  |
| 17 | `app/code/Adyen/Payment/Controller/Webhook/Index.php:95` |  |  |  |  |
| 18 | `vendor/magento/module-ui/Controller/Adminhtml/Bookmark/Save.php:254` |  |  |  |  |
| 19 | `app/code/Adyen/Payment/Gateway/Http/Client/TransactionPayment.php:189` |  |  |  |  |
| 20 | `app/code/Mollie/Payment/Cron/RemoveSentPaymentReminders.php:52` |  |  |  |  |
| 21 | `app/code/Snowdog/Menu/Controller/Adminhtml/Menu/MassDelete.php:80` |  |  |  |  |
| 22 | `vendor/magento/module-media-storage/Model/File/Storage.php:280` |  |  |  |  |
| 23 | `vendor/magento/module-customer/Model/ResourceModel/Customer/Relation.php:39` |  |  |  |  |
| 24 | `vendor/magento/module-catalog-rule/Controller/Adminhtml/Promo/Catalog/MassActivate.php:48` |  |  |  |  |
| 25 | `vendor/magento/module-indexer/Setup/Patch/Data/InitializeIndexerState.php:101` |  |  |  |  |
| 26 | `vendor/magento/magento2-base/setup/src/Magento/Setup/Fixtures/StoresFixture.php:236` |  |  |  |  |
| 27 | `vendor/magento/magento2-base/setup/src/Magento/Setup/Fixtures/CategoriesFixture.php:143` |  |  |  |  |
| 28 | `vendor/magento/module-cron/Observer/ProcessCronQueueObserver.php:881` |  |  |  |  |
| 29 | `vendor/magento/module-quote/Model/ResourceModel/Quote/Address/Rate/Collection.php:145` |  |  |  |  |
| 30 | `vendor/magento/module-sales/Model/ResourceModel/Order/Invoice/Relation.php:62` |  |  |  |  |
