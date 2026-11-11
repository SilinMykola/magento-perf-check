# X-PLG-02: calibration sample

7 of 7 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Yireo/CspUtilities/Plugin/AddCspInlineScripts.php:30` (extended)

[X-PLG-02] Yireo\CspUtilities\Plugin\AddCspInlineScripts::afterToHtml() is a plugin on magento\framework\view\element\template, so it runs for every rendered block or layout element of every page. Put it on the specific class it is meant for, or keep it very cheap.

```
    24          $this->replaceInlineScripts = $replaceInlineScripts;
    25          $this->scopeConfig = $scopeConfig;
    26          $this->request = $request;
    27          $this->allowedBlocks = $allowedBlocks;
    28      }
    29  
>   30      public function afterToHtml(Template $block, $html): string
    31      {
    32          if (false === in_array((string)$block->getNameInLayout(), $this->allowedBlocks, true)) {
    33              return (string)$html;
    34          }
    35  
    36          return $this->replaceInlineScripts->replace((string)$html);
```

## 2. `app/code/MSP/DevTools/Plugin/View/Element/AbstractBlockPlugin.php:60` (extended)

[X-PLG-02] MSP\DevTools\Plugin\View\Element\AbstractBlockPlugin::aroundToHtml() is a plugin on magento\framework\view\element\abstractblock, so it runs for every rendered block or layout element of every page. Put it on the specific class it is meant for, or keep it very cheap.

```
    54          $this->blockProcessor = $blockProcessor;
    55          $this->config = $config;
    56          $this->elementRegistry = $elementRegistry;
    57          $this->canInjectCode = $canInjectCode;
    58      }
    59  
>   60      public function aroundToHtml(AbstractBlock $subject, \Closure $proceed)
    61      {
    62          if (!$this->canInjectCode->execute()) {
    63              return $proceed();
    64          }
    65  
    66          if ($subject->getTemplate() === null) {
```

## 3. `app/code/Yireo/Webp2/Plugin/AddCspInlineScripts.php:18` (extended)

[X-PLG-02] Yireo\Webp2\Plugin\AddCspInlineScripts::afterToHtml() is a plugin on magento\framework\view\element\template, so it runs for every rendered block or layout element of every page. Put it on the specific class it is meant for, or keep it very cheap.

```
    12      public function __construct(
    13          ReplaceInlineScripts $replaceInlineScripts
    14      ) {
    15          $this->replaceInlineScripts = $replaceInlineScripts;
    16      }
    17  
>   18      public function afterToHtml(Template $block, $html): string
    19      {
    20          if (false === strstr((string)$block->getNameInLayout(), 'yireo_webp2.')) {
    21              return (string)$html;
    22          }
    23  
    24          return $this->replaceInlineScripts->replace((string)$html);
```

## 4. `app/code/MSP/DevTools/Plugin/View/LayoutPlugin.php:99` (extended)

[X-PLG-02] MSP\DevTools\Plugin\View\LayoutPlugin::aroundRenderElement() is a plugin on magento\framework\view\layout, so it runs for every rendered block or layout element of every page. Put it on the specific class it is meant for, or keep it very cheap.

```
    93          $html = '<!-- START_MSPDEV[' . $blockId . ']: ' . $name . ' -->' . $html
    94              . '<!-- /END_MSPDEV[' . $blockId . ']: ' . $name . ' -->';
    95  
    96          return $html;
    97      }
    98  
>   99      public function aroundRenderElement(Layout $subject, \Closure $proceed, $name, $useCache = true)
   100      {
   101          if (!$this->canInjectCode->execute()) {
   102              return $proceed($name, $useCache);
   103          }
   104  
   105          if ($subject->isUiComponent($name) || $subject->isBlock($name)) {
```

## 5. `app/code/Yireo/GoogleTagManager2/Plugin/AddCspInlineScripts.php:18` (extended)

[X-PLG-02] Yireo\GoogleTagManager2\Plugin\AddCspInlineScripts::afterToHtml() is a plugin on magento\framework\view\element\template, so it runs for every rendered block or layout element of every page. Put it on the specific class it is meant for, or keep it very cheap.

```
    12      public function __construct(
    13          ReplaceInlineScripts $replaceInlineScripts
    14      ) {
    15          $this->replaceInlineScripts = $replaceInlineScripts;
    16      }
    17  
>   18      public function afterToHtml(Template $block, $html): string
    19      {
    20          if (false === strstr((string)$block->getNameInLayout(), 'yireo_googletagmanager2.')) {
    21              return (string)$html;
    22          }
    23  
    24          return $this->replaceInlineScripts->replace((string)$html);
```

## 6. `app/code/Ho/Templatehints/Plugin/View/LayoutPlugin.php:75` (extended)

[X-PLG-02] Ho\Templatehints\Plugin\View\LayoutPlugin::aroundRenderElement() is a plugin on magento\framework\view\layout, so it runs for every rendered block or layout element of every page. Put it on the specific class it is meant for, or keep it very cheap.

```
    69       * @param string  $name
    70       * @param bool    $useCache
    71       *
    72       * @return string
    73       * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    74       */
>   75      public function aroundRenderElement(Layout $layout, Closure $proceed, $name, $useCache = false) // @codingStandardsIgnoreLine
    76      {
    77          $result = $proceed($name, $useCache);
    78          if ($this->hintConfig->isHintEnabled() === false) {
    79              return $result;
    80          }
    81  
```

## 7. `app/code/Yireo/NextGenImages/Plugin/AddCspInlineScripts.php:18` (extended)

[X-PLG-02] Yireo\NextGenImages\Plugin\AddCspInlineScripts::afterToHtml() is a plugin on magento\framework\view\element\template, so it runs for every rendered block or layout element of every page. Put it on the specific class it is meant for, or keep it very cheap.

```
    12      public function __construct(
    13          ReplaceInlineScripts $replaceInlineScripts
    14      ) {
    15          $this->replaceInlineScripts = $replaceInlineScripts;
    16      }
    17  
>   18      public function afterToHtml(Template $block, $html): string
    19      {
    20          if (false === strstr((string)$block->getNameInLayout(), 'yireo_nextgenimages.')) {
    21              return (string) $html;
    22          }
    23  
    24          return $this->replaceInlineScripts->replace((string)$html);
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Yireo/CspUtilities/Plugin/AddCspInlineScripts.php:30` | TP-low | The frontend plugin on Template runs for every template block, but returns after an in_array() on the block name, and the allowedBlocks list is empty by default. |  |  |
| 2 | `app/code/MSP/DevTools/Plugin/View/Element/AbstractBlockPlugin.php:60` | TP-low | The global plugin on AbstractBlock wraps every toHtml(), but returns at once when CanInjectCode::execute() is false, and that result is memoized after the first call. |  |  |
| 3 | `app/code/Yireo/Webp2/Plugin/AddCspInlineScripts.php:18` | TP-low | The frontend plugin on Template runs for every template block, but returns after one strstr() on the block name for blocks whose name does not contain yireo_webp2. |  |  |
| 4 | `app/code/MSP/DevTools/Plugin/View/LayoutPlugin.php:99` | TP-low | The global plugin on Layout wraps every renderElement(), but returns at once when the memoized CanInjectCode::execute() is false. |  |  |
| 5 | `app/code/Yireo/GoogleTagManager2/Plugin/AddCspInlineScripts.php:18` | TP-low | The frontend plugin on Template runs for every template block, but returns after one strstr() on the block name for blocks whose name does not contain yireo_googletagmanager2. |  |  |
| 6 | `app/code/Ho/Templatehints/Plugin/View/LayoutPlugin.php:75` | TP-low | The global plugin on Layout wraps every renderElement(), but after proceed() it only checks the ath request parameter unless hints are requested. |  |  |
| 7 | `app/code/Yireo/NextGenImages/Plugin/AddCspInlineScripts.php:18` | TP-low | The frontend plugin on Template runs for every template block, but returns after one strstr() on the block name for blocks whose name does not contain yireo_nextgenimages. |  |  |
