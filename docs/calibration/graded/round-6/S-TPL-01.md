# S-TPL-01: calibration sample

30 of 247 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:312` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   306                              maxlength: "<?php echo $this->fcpoGetValue('Year_max'); ?>",
   307                              <?php if ($this->fcpoGetValue('Year_style') == "custom"): ?>
   308                                  style: "<?php echo $this->fcpoGetValue('Year_css'); ?>",
   309                              <?php endif; ?>
   310                              <?php if ($this->fcpoGetValue('Year_iframe') == "custom"): ?>
   311                                  iframe: {
>  312                                      width: "<?php echo $this->fcpoGetValue('Year_width'); ?>",
   313                                      height: "<?php echo $this->fcpoGetValue('Year_height'); ?>"
   314                                  }
   315                              <?php endif; ?>
   316                          }
   317                      },
   318                      defaultStyle: {
```

## 2. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:145` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   139              </tr>
   140              <tr>
   141                  <td><?php echo __('Style') ?></td>
   142                  <td>
   143                      <?php $sFieldIdentCSS = $sField.'_style'; ?>
   144                      <select name="<?php echo $this->getElement()->getName().'['.$sFieldIdentCSS.']'; ?>" onchange="handleCss(this, '<?php echo $sField; ?>')" style="width:inherit;">
>  145                          <?php foreach ($this->getCCStyles() as $sType => $sTitle): ?>
   146                              <option value="<?php echo $sType; ?>" <?php if ($sType == $this->fcpoGetValue($sFieldIdentCSS)) echo 'selected'; ?>><?php echo $sTitle; ?></option>
   147                          <?php endforeach; ?>
   148                      </select>
   149                  </td>
   150              </tr>
   151              <tr>
```

## 3. `app/code/Mageplaza/Productslider/view/frontend/templates/hyva/productslider.phtml:63` (extended)

[S-TPL-01] The use of helpers in templates is discouraged. Use ViewModel instead. (Magento2.Templates.ThisInTemplate)

```
    57  /** @var HeroiconsSolid $heroiconsSolid */
    58  $heroiconsSolid = $viewModels->require(HeroiconsSolid::class);
    59  
    60  $productOptionsViewmodel = $viewModels->require(ProductOptionsData::class);
    61  
    62  /** @var Data $helperData */
>   63  $helperData = $this->helper(Data::class);
    64  $imageDisplayArea = 'category_page_grid';
    65  
    66  // Ensure the required JS is rendered on the page
    67  $viewModels->require(BlockJsDependencies::class)->setBlockTemplateDependency($blockAbstract, 'Magento_Catalog::product/list/js/price-box.phtml');
    68  
    69  $mode         = 'grid';
```

## 4. `app/code/Trustpilot/Reviews/view/adminhtml/templates/system/config/admin.phtml:45` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    39          data-transfer='<?php echo $this->getIntegrationAppUrl(); ?>'
    40          data-past-orders='<?php echo $this->getPastOrdersInfo($scope, $storeId); ?>'
    41          data-settings='<?php echo $this->getSettings($scope, $storeId); ?>'
    42          data-product-identification-options = '<?php echo $this->getProductIdentificationOptions(); ?>'
    43          data-is-from-marketplace='<?php echo \Trustpilot\Reviews\Model\Config::TRUSTPILOT_IS_FROM_MARKETPLACE ?>'
    44          data-configuration-scope-tree = '<?php echo $this->getStoreInformation(); ?>'
>   45          data-plugin-status = '<?php echo $this->getPluginStatus($scope, $storeId); ?>'
    46          onload="onTrustpilotIframeLoad();"
    47      >
    48      </iframe>
    49     <div id='trustpilot-trustbox-preview'
    50          hidden='true'
    51          data-page-urls='<?php echo $this->getPageUrls($scope, $storeId); ?>'
```

## 5. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:304` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   298                                      height: "<?php echo $this->fcpoGetValue('Month_height'); ?>"
   299                                  }
   300                              <?php endif; ?>
   301                          },
   302                          cardexpireyear: {
   303                              selector: "cardexpireyear", // put name of your div-container here
>  304                              type: "<?php echo $this->fcpoGetValue('Year_type'); ?>",
   305                              size: "<?php echo $this->fcpoGetValue('Year_count'); ?>",
   306                              maxlength: "<?php echo $this->fcpoGetValue('Year_max'); ?>",
   307                              <?php if ($this->fcpoGetValue('Year_style') == "custom"): ?>
   308                                  style: "<?php echo $this->fcpoGetValue('Year_css'); ?>",
   309                              <?php endif; ?>
   310                              <?php if ($this->fcpoGetValue('Year_iframe') == "custom"): ?>
```

## 6. `app/code/Trustpilot/Reviews/view/adminhtml/templates/system/config/admin.phtml:41` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    35          data-plugin-version='<?php echo \Trustpilot\Reviews\Model\Config::TRUSTPILOT_PLUGIN_VERSION ?>'
    36          data-source='Magento2'
    37          data-version='<?php echo 'Magento2-'. $this->getVersion(); ?>'
    38          data-page-urls='<?php echo $this->getPageUrls($scope, $storeId); ?>'
    39          data-transfer='<?php echo $this->getIntegrationAppUrl(); ?>'
    40          data-past-orders='<?php echo $this->getPastOrdersInfo($scope, $storeId); ?>'
>   41          data-settings='<?php echo $this->getSettings($scope, $storeId); ?>'
    42          data-product-identification-options = '<?php echo $this->getProductIdentificationOptions(); ?>'
    43          data-is-from-marketplace='<?php echo \Trustpilot\Reviews\Model\Config::TRUSTPILOT_IS_FROM_MARKETPLACE ?>'
    44          data-configuration-scope-tree = '<?php echo $this->getStoreInformation(); ?>'
    45          data-plugin-status = '<?php echo $this->getPluginStatus($scope, $storeId); ?>'
    46          onload="onTrustpilotIframeLoad();"
    47      >
```

## 7. `app/code/Mageplaza/SocialLogin/view/frontend/templates/hyva/form/social.phtml:29` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    23  /** @var \Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer */
    24  
    25  $availableSocials = $block->getAvailableSocials();
    26  $childBlock       = $block->getChildBlock('request.info');
    27  $position         = $block->getData('position');
    28  
>   29  $helperData  = $this->helper(\Mageplaza\SocialLogin\Helper\Data::class);
    30  
    31  if ($block->canShow($position) && sizeof($availableSocials) && $helperData->isEnabled()):
    32      ?>
    33      <div class="block social-login-authentication-channel account-social-login">
    34          <div class="block-content">
    35              <?php foreach ($availableSocials as $key => $social): ?>
```

## 8. `app/code/Mageplaza/Blog/view/frontend/templates/position/topmenuinfortis.phtml:26` (extended)

[S-TPL-01] The use of helpers in templates is discouraged. Use ViewModel instead. (Magento2.Templates.ThisInTemplate)

```
    20   */
    21  
    22  use Mageplaza\Blog\Helper\Data;
    23  use Magento\Framework\Escaper;
    24  
    25  /** @var Escaper $escaper */
>   26  $helper = $this->helper(Data::class);
    27  
    28  if ($helper->isEnabled() && $helper->getBlogConfig('display/toplinks')) : ?>
    29      <li class="nav-item level0 nav-5 level-top classic">
    30          <a href="<?= $escaper->escapeUrl($helper->getBlogUrl('')) ?>" class="level-top">
    31              <span><?= $helper->getBlogConfig('display/name') ?: $escaper->escapeHtml(__('Blog')) ?></span>
    32          </a>
```

## 9. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:305` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   299                                  }
   300                              <?php endif; ?>
   301                          },
   302                          cardexpireyear: {
   303                              selector: "cardexpireyear", // put name of your div-container here
   304                              type: "<?php echo $this->fcpoGetValue('Year_type'); ?>",
>  305                              size: "<?php echo $this->fcpoGetValue('Year_count'); ?>",
   306                              maxlength: "<?php echo $this->fcpoGetValue('Year_max'); ?>",
   307                              <?php if ($this->fcpoGetValue('Year_style') == "custom"): ?>
   308                                  style: "<?php echo $this->fcpoGetValue('Year_css'); ?>",
   309                              <?php endif; ?>
   310                              <?php if ($this->fcpoGetValue('Year_iframe') == "custom"): ?>
   311                                  iframe: {
```

## 10. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:277` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   271                          },
   272                          cardcvc2: {
   273                              selector: "cardcvc2", // put name of your div-container here
   274                              type: "<?php echo $this->fcpoGetValue('CVC_type'); ?>", // text (default), password, tel
   275                              size: "<?php echo $this->fcpoGetValue('CVC_count'); ?>",
   276                              maxlength: "<?php echo $this->fcpoGetValue('CVC_max'); ?>",
>  277                              <?php if ($this->fcpoGetValue('CVC_style') == "custom"): ?>
   278                                  style: "<?php echo $this->fcpoGetValue('CVC_css'); ?>",
   279                              <?php endif; ?>
   280                              <?php if ($this->fcpoGetValue('CVC_iframe') == "custom"): ?>
   281                                  iframe: {
   282                                      width: "<?php echo $this->fcpoGetValue('CVC_width'); ?>",
   283                                      height: "<?php echo $this->fcpoGetValue('CVC_height'); ?>"
```

## 11. `app/code/Mageplaza/Productslider/view/frontend/templates/widget/productslider.phtml:118` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   112                                                         __('Add to Wish List') ?>">
   113                                                          <span><?= /** @noEscape */
   114                                                              __('Add to Wish List') ?></span>
   115                                                      </a>
   116                                                  <?php endif; ?>
   117                                                  <?php if ($block->getAddToCompareUrl()): ?>
>  118                                                      <?php $compareHelper = $this->helper('Magento\Catalog\Helper\Product\Compare'); ?>
   119                                                      <a href="#" class="action tocompare"
   120                                                         data-post='<?= /** @noEscape */
   121                                                         $compareHelper->getPostDataParams($_item); ?>'
   122                                                         data-role="add-to-links"
   123                                                         title="<?= /** @noEscape */
   124                                                         __('Add to Compare') ?>">
```

## 12. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:191` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   185              </td>
   186          </tr>
   187          <tr>
   188              <td><?php echo __('Width'); ?></td>
   189              <td>
   190                  <?php $sFieldIdent = 'Iframe_width'; ?>
>  191                  <input type="text" size="4" name="<?php echo $this->getElement()->getName().'['.$sFieldIdent.']'; ?>" value="<?php echo $this->fcpoGetValue($sFieldIdent); ?>">
   192              </td>
   193          </tr>
   194          <tr>
   195              <td><?php echo __('Height'); ?></td>
   196              <td>
   197                  <?php $sFieldIdent = 'Iframe_height'; ?>
```

## 13. `app/code/Payone/Core/view/frontend/templates/onepage/amazon.phtml:169` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   163          window.amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
   164          }
   165      };
   166  </script>
   167  
   168  <ol id="checkoutSteps" style="list-style: none;padding:0;">
>  169      <li class="section allow" onclick="window.location.href = '<?php echo $this->getCartUrl(); ?>'; return false;">
   170          <div class="step-title">
   171              <span class="number">1</span>
   172              <h2><?php echo __('Review shopping cart'); ?></h2>
   173              <a href="#"><?php echo __('View'); ?></a>
   174          </div>
   175      </li>
```

## 14. `app/code/Payone/Core/view/frontend/templates/onepage/amazon.phtml:143` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   137              },
   138              design: {
   139                  designMode: 'responsive'
   140              },
   141              onError: window.onAmazonPaymentsError
   142          });
>  143          walletWidget.setPresentmentCurrency("<?php echo $this->getCurrency(); ?>");
   144          walletWidget.bind('walletWidgetDiv').renderRequested = initiatedByPopup;
   145          removeActionInput();
   146          widgetInitialized = true;
   147          payoneShowInitStep();
   148      };
   149      window.onAmazonWidgetsInitialized = function (orderReference) {
```

## 15. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:105` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    99                  </td>
   100              </tr>
   101              <tr>
   102                  <td><?php echo __('Digit-<br>count') ?></td>
   103                  <td>
   104                      <?php $sFieldIdent = $sField.'_count'; ?>
>  105                      <input type="text" class="txt" size="4" name="<?php echo $this->getElement()->getName().'['.$sFieldIdent.']'; ?>" value="<?php echo $this->fcpoGetValue($sFieldIdent); ?>">
   106                  </td>
   107              </tr>
   108              <tr>
   109                  <td><?php echo __('Max-<br>digits') ?></td>
   110                  <td>
   111                      <?php $sFieldIdent = $sField.'_max'; ?>
```

## 16. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/ratepay_show_shop_config.phtml:26` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    20   * @author    FATCHIP GmbH <support@fatchip.de>
    21   * @copyright 2003 - 2016 Payone GmbH
    22   * @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
    23   * @link      http://www.payone.de
    24   */
    25  
>   26  $aShopConfigs = $this->getRatepayShopConfig();
    27  ?>
    28  <div>
    29      <?php if (empty($aShopConfigs)): ?>
    30          <?php echo __('Payment method has no configured Ratepay shop configs'); ?>
    31      <?php else: ?>
    32          <?php foreach ($aShopConfigs as $aProfile): ?>
```

## 17. `app/code/Mageplaza/SocialLogin/view/frontend/templates/broadcast_channel.phtml:22` (extended)

[S-TPL-01] The use of helpers in templates is discouraged. Use ViewModel instead. (Magento2.Templates.ThisInTemplate)

```
    16   * @category    Mageplaza
    17   * @package     Mageplaza_SocialLogin
    18   * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
    19   * @license     https://www.mageplaza.com/LICENSE.txt
    20   */
    21  /** @var \Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer */
>   22  $helperData = $this->helper(\Mageplaza\SocialLogin\Helper\Data::class);
    23  ?>
    24  <?php ob_start(); ?>
    25      var channel = new BroadcastChannel('social-login-channel');
    26  
    27      channel.onmessage = function (event) {
    28          if (event.data.event === 'socialLoginSuccess') {
```

## 18. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:221` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   215          </tr>
   216          <tr>
   217              <td><?php echo __('Language'); ?></td>
   218              <td>
   219                  <?php $sFieldIdent = 'Errors_lang'; ?>
   220                  <select name="<?php echo $this->getElement()->getName().'['.$sFieldIdent.']'; ?>">
>  221                      <option value="de" <?php if ($this->fcpoGetValue($sFieldIdent) == 'de') echo 'selected'; ?>><?php echo __('German'); ?></option>
   222                      <option value="en" <?php if ($this->fcpoGetValue($sFieldIdent) == 'en') echo 'selected'; ?>><?php echo __('English'); ?></option>
   223                  </select>
   224              </td>
   225          </tr>
   226      </table>
   227      <br>
```

## 19. `app/code/Swissup/Core/view/adminhtml/templates/popup_messages.phtml:3` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
     1  <?php /** @var \Magento\Framework\Escaper $escaper */ ?>
     2  <ul id="swissup-popup-messages" style="display: none">
>    3  <?php foreach ($this->getData('popup_messenger')->getPopups(true) as $id => $message) : ?>
     4      <li id="swissup-popup-<?= $escaper->escapeHtmlAttr($id) ?>">
     5          <div class="swissup__popup-title"><?php echo $this->escapeHtml($message['title']) ?></div>
     6          <div class="swissup__popup-text">
     7              <pre><?php echo $this->escapeHtml($message['text']) ?></pre>
     8          </div>
     9      </li>
```

## 20. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:212` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   206              <td></td>
   207          </tr>
   208          <tr>
   209              <td><?php echo __('Active'); ?></td>
   210              <td>
   211                  <?php $sFieldIdent = 'Errors_active'; ?>
>  212                  <input type="hidden" name="<?php echo $this->getElement()->getName().'['.$sFieldIdent.']'; ?>" value="false">
   213                  <input type="checkbox" name="<?php echo $this->getElement()->getName().'['.$sFieldIdent.']'; ?>" value="true" <?php if ($this->fcpoGetValue($sFieldIdent) == "true") echo 'checked'; ?>>
   214              </td>
   215          </tr>
   216          <tr>
   217              <td><?php echo __('Language'); ?></td>
   218              <td>
```

## 21. `app/code/Payone/Core/view/frontend/templates/amazon/scripts.phtml:46` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    40      };
    41  
    42      function initializeAmazonPayButton(sElementId) {
    43          OffAmazonPayments.Button(sElementId, '<?php echo $this->getSellerId(); ?>', {
    44              type: '<?php echo $this->getButtonType(); ?>',
    45              color: '<?php echo $this->getButtonColor(); ?>',
>   46              language: '<?php echo $this->getButtonLanguage(); ?>',
    47              size: 'medium',
    48              authorization: function() {
    49                  var loginOptions = {scope: 'profile payments:widget payments:shipping_address payments:billing_address', popup: true};
    50                  var authRequest = amazon.Login.authorize (loginOptions, '<?php echo $this->getRedirectUrl(); ?>');
    51              }
    52          });
```

## 22. `app/code/Mageplaza/Productslider/view/frontend/templates/productslider.phtml:26` (extended)

[S-TPL-01] The use of helpers in templates is discouraged. Use ViewModel instead. (Magento2.Templates.ThisInTemplate)

```
    20   */
    21  
    22  use Mageplaza\Productslider\Block\AbstractSlider;
    23  use Magento\Framework\App\Action\Action;
    24  
    25  /** @var AbstractSlider $block */
>   26  $helper = $this->helper(\Mageplaza\Productslider\Helper\Data::class);
    27  $mode = 'grid';
    28  $type = 'widget-product-grid';
    29  $title = $block->getTitle();
    30  $blockId = $block->getSliderId() . uniqid('-', false);
    31  $items = $block->getProductCollection();
    32  if ($block->getHelperData()->isEnabled() && $items && $items->getSize()): ?>
```

## 23. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:146` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   140              <tr>
   141                  <td><?php echo __('Style') ?></td>
   142                  <td>
   143                      <?php $sFieldIdentCSS = $sField.'_style'; ?>
   144                      <select name="<?php echo $this->getElement()->getName().'['.$sFieldIdentCSS.']'; ?>" onchange="handleCss(this, '<?php echo $sField; ?>')" style="width:inherit;">
   145                          <?php foreach ($this->getCCStyles() as $sType => $sTitle): ?>
>  146                              <option value="<?php echo $sType; ?>" <?php if ($sType == $this->fcpoGetValue($sFieldIdentCSS)) echo 'selected'; ?>><?php echo $sTitle; ?></option>
   147                          <?php endforeach; ?>
   148                      </select>
   149                  </td>
   150              </tr>
   151              <tr>
   152                  <td><?php echo __('CSS') ?></td>
```

## 24. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:94` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    88                  <td></td>
    89              </tr>
    90              <tr>
    91                  <td><?php echo __('Type') ?></td>
    92                  <td>
    93                      <?php $sFieldIdent = $sField.'_type'; ?>
>   94                      <select name="<?php echo $this->getElement()->getName().'['.$sFieldIdent.']'; ?>" style="width:inherit;">
    95                          <?php foreach ($this->getCCTypes($sField) as $sType => $sTitle): ?>
    96                              <option value="<?php echo $sType; ?>" <?php if ($sType == $this->fcpoGetValue($sFieldIdent)) echo 'selected'; ?>><?php echo $sTitle; ?></option>
    97                          <?php endforeach; ?>
    98                      </select>
    99                  </td>
   100              </tr>
```

## 25. `app/code/Mageplaza/Productslider/view/frontend/templates/productslider.phtml:101` (extended)

[S-TPL-01] The use of helpers in templates is discouraged. Use ViewModel instead. (Magento2.Templates.ThisInTemplate)

```
    95                                                     class="action towishlist" data-action="add-to-wishlist"
    96                                                     title="<?= /** @noEscape */ __('Add to Wish List') ?>">
    97                                                      <span><?= /** @noEscape */ __('Add to Wish List') ?></span>
    98                                                  </a>
    99                                              <?php endif; ?>
   100                                              <?php if ($block->getAddToCompareUrl()): ?>
>  101                                                  <?php $compareHelper = $this->helper('Magento\Catalog\Helper\Product\Compare'); ?>
   102                                                  <a href="#" class="action tocompare"
   103                                                     data-post='<?= /** @noEscape */ $compareHelper->getPostDataParams($_item); ?>'
   104                                                     data-role="add-to-links"
   105                                                     title="<?= /** @noEscape */ __('Add to Compare') ?>">
   106                                                      <span><?= /** @noEscape */ __('Add to Compare') ?></span>
   107                                                  </a>
```

## 26. `app/code/Payone/Core/view/frontend/templates/onepage/success.phtml:31` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    25  ?>
    26  <br>
    27  <?php if ($this->showMandateLink()): ?>
    28      <a target="_blank" class="link" href="<?php echo $this->getMandateDownloadUrl(); ?>">
    29          <?php echo __('Your mandate as PDF'); ?>
    30      </a>
>   31  <?php elseif ($this->getInstructionNotes()): ?>
    32      <?php echo $this->getInstructionNotes(); ?>
    33  <?php elseif ($this->showAmazonPendingMessage()): ?>
    34      <div class="messages">
    35          <div class="message message-error notice">
    36              <div><?php echo __('Your transaction with Amazon Pay is currently being validated. Please be aware that we will inform you shortly as needed.'); ?></div>
    37          </div>
```

## 27. `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:267` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   261                              maxlength: "<?php echo $this->fcpoGetValue('Number_max'); ?>",
   262                              <?php if ($this->fcpoGetValue('Number_style') == "custom"): ?>
   263                                  style: "<?php echo $this->fcpoGetValue('Number_css'); ?>",
   264                              <?php endif; ?>
   265                              <?php if ($this->fcpoGetValue('Number_iframe') == "custom"): ?>
   266                                  iframe: {
>  267                                      width: "<?php echo $this->fcpoGetValue('Number_width'); ?>",
   268                                      height: "<?php echo $this->fcpoGetValue('Number_height'); ?>"
   269                                  }
   270                              <?php endif; ?>
   271                          },
   272                          cardcvc2: {
   273                              selector: "cardcvc2", // put name of your div-container here
```

## 28. `app/code/Payone/Core/view/frontend/templates/onepage/amazon.phtml:267` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
   261              OffAmazonPayments.initConfirmationFlow('<?php echo $this->getSellerId(); ?>', window.amazonOrderReferenceId, function(confirmationFlow) {
   262                  window.payoneConfirmOrderReference(confirmationFlow);
   263              });
   264          };
   265          window.payoneConfirmOrderReference = function (confirmationFlow) {
   266              $.ajax({
>  267                  url: '<?php echo $this->getLoadReviewUrl(); ?>',
   268                  type: 'POST',
   269                  dataType: 'html',
   270                  data: {
   271                      action: 'confirmOrderReference'
   272                  },
   273                  showLoader: true,
```

## 29. `app/code/Payone/Core/view/webapi_rest/templates/bnpl/installment_plan.phtml:46` (extended)

[S-TPL-01] The use of $this in templates is deprecated. Use $block instead. (Magento2.Templates.ThisInTemplate)

```
    40          <?php foreach ($aInstallmentData as $sKey => $aInstallment): ?>
    41              <div class="payoneBNPLselector">
    42                  <input type="radio" name="bnplCondition" id="bnplCondition_<?php echo $sKey; ?>">
    43                  <a href="#" class="bnplCondition" onclick="return false;">
    44                      <?php echo $this->getSelectLinkText($aInstallment) ?>
    45                  </a>
>   46              </div><input class="hiddenSwitchInstallmentPlanBnpl" type="hidden" value="<?php echo $sKey; ?>|||<?php echo $this->getCode(); ?>|||<?php echo $aInstallment['installment_option_id']; ?>">
    47          <?php endforeach; ?>
    48  
    49          <?php foreach ($aInstallmentData as $sKey => $aInstallment): ?>
    50              <div id="bnpl_installment_overview_<?php echo $sKey; ?>" class="bnpl_installment_overview" style="display:none;">
    51                  <br>
    52                  <strong><?php echo __('Overview'); ?></strong><br>
```

## 30. `app/code/Mageplaza/SocialLogin/view/frontend/templates/popup/form/create.phtml:244` (extended)

[S-TPL-01] The use of helpers in templates is discouraged. Use ViewModel instead. (Magento2.Templates.ThisInTemplate)

```
   238                              <label for="city" class="label"><span><?= /* @noEscape */ __('City') ?></span></label>
   239                              <div class="control">
   240                                  <input type="text" name="city"
   241                                         value="<?= $block->escapeHtml($block->getFormData()->getCity()) ?>"
   242                                         id="city"
   243                                         title="<?= /* @noEscape */ __('City') ?>"
>  244                                         class="input-text <?= /* @noEscape */ $this->helper('Magento\Customer\Helper\Address')->getAttributeValidationClass('city') ?>"/>
   245                              </div>
   246                          </div>
   247                          <div class="field region required">
   248                              <label for="region_id" class="label"><span><?= /* @noEscape */ __('State/Province') ?></span></label>
   249                              <div class="control">
   250                                  <select id="region_id" name="region_id" title="<?= /* @noEscape */ __('State/Province') ?>"
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:312` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 2 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:145` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 3 | `app/code/Mageplaza/Productslider/view/frontend/templates/hyva/productslider.phtml:63` | TP-low | $this->helper() returns the shared helper instance after the first object manager lookup, so the cost is negligible. |  |  |
| 4 | `app/code/Trustpilot/Reviews/view/adminhtml/templates/system/config/admin.phtml:45` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 5 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:304` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 6 | `app/code/Trustpilot/Reviews/view/adminhtml/templates/system/config/admin.phtml:41` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 7 | `app/code/Mageplaza/SocialLogin/view/frontend/templates/hyva/form/social.phtml:29` | TP-low | This is the $this half of a $this->helper() call, which returns the shared helper instance after the first lookup, a negligible cost. |  |  |
| 8 | `app/code/Mageplaza/Blog/view/frontend/templates/position/topmenuinfortis.phtml:26` | TP-low | $this->helper() returns the shared helper instance after the first object manager lookup, so the cost is negligible. |  |  |
| 9 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:305` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 10 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:277` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 11 | `app/code/Mageplaza/Productslider/view/frontend/templates/widget/productslider.phtml:118` | TP-low | $this->helper() inside the product loop repeats an object manager lookup per item that returns the shared instance, a negligible cost. |  |  |
| 12 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:191` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 13 | `app/code/Payone/Core/view/frontend/templates/onepage/amazon.phtml:169` | TP-low | $this is the same block object as $block, so the call costs nothing extra. |  |  |
| 14 | `app/code/Payone/Core/view/frontend/templates/onepage/amazon.phtml:143` | TP-low | $this is the same block object as $block, so the call costs nothing extra. |  |  |
| 15 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:105` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 16 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/ratepay_show_shop_config.phtml:26` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 17 | `app/code/Mageplaza/SocialLogin/view/frontend/templates/broadcast_channel.phtml:22` | TP-low | $this->helper() returns the shared helper instance after the first object manager lookup, so the cost is negligible. |  |  |
| 18 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:221` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 19 | `app/code/Swissup/Core/view/adminhtml/templates/popup_messages.phtml:3` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 20 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:212` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 21 | `app/code/Payone/Core/view/frontend/templates/amazon/scripts.phtml:46` | TP-low | $this is the same block object as $block, so the call costs nothing extra. |  |  |
| 22 | `app/code/Mageplaza/Productslider/view/frontend/templates/productslider.phtml:26` | TP-low | $this->helper() returns the shared helper instance after the first object manager lookup, so the cost is negligible. |  |  |
| 23 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:146` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 24 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:94` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 25 | `app/code/Mageplaza/Productslider/view/frontend/templates/productslider.phtml:101` | TP-low | $this->helper() inside the product loop repeats an object manager lookup per item that returns the shared instance, a negligible cost. |  |  |
| 26 | `app/code/Payone/Core/view/frontend/templates/onepage/success.phtml:31` | TP-low | $this is the same block object as $block, so the call costs nothing extra. |  |  |
| 27 | `app/code/Payone/Core/view/adminhtml/templates/system/config/form/field/creditcard_template.phtml:267` | TP-low | $this is the same block object as $block, so the call costs nothing extra; admin configuration template. |  |  |
| 28 | `app/code/Payone/Core/view/frontend/templates/onepage/amazon.phtml:267` | TP-low | $this is the same block object as $block, so the call costs nothing extra. |  |  |
| 29 | `app/code/Payone/Core/view/webapi_rest/templates/bnpl/installment_plan.phtml:46` | TP-low | $this is the same block object as $block in this web API template, so the call costs nothing extra. |  |  |
| 30 | `app/code/Mageplaza/SocialLogin/view/frontend/templates/popup/form/create.phtml:244` | TP-low | $this->helper() returns the shared helper instance after the first object manager lookup, so the cost is negligible. |  |  |
