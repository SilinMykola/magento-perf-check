# Proposal

## Why

Four rules from the catalog stay planned although the code they look at is common: across the 56 modules checked so far, `getProductUrl()` and its relatives appear 42 times, `getAttributeText()` 18 times, `addAttributeToSelect('*')` 6 times, and 5 plugins sit on `toHtml()` or `fetchView()` of every block. Findings on these modules can measure them.

Running them showed a wider problem. Of the 1205 factory parameters in those modules, 680 name a factory that is neither generated in the checked project nor written by hand, so calls on them went unchecked. Findings therefore depended on whether `setup:di:compile` had run, and how far: a partly generated directory gives no notice either.

## What Changes

- **P-LOOP-04:** a product URL built inside a loop, unless `addUrlRewrite()` was called in the same function. Without it, each product runs a lookup in the URL rewrite table.
- **P-LOOP-06:** `getAttributeText()` inside a loop, or `getOptionText()` on the `Source\Table` model. Each call loads an option collection.
- **P-COL-03:** `addAttributeToSelect('*')` on an EAV collection.
- **X-PLG-02:** a plugin method on `toHtml()`, `getChildHtml()`, `fetchView()` or `renderElement()` of the base block and layout classes, configured outside the admin area. The catalog had it as `warning`; it becomes `info`, because the sources show how often such a plugin runs and not what it costs.
- **P-LOOP-02:** a collection created and limited in a `while`, `do` or `for` loop is paging, not an N+1. Two of the four findings that factory types added to this rule were this case.
- **Test stubs:** they leave Composer's autoload-dev. PHPStan loads this package's autoloader, so when the tool ran from a checkout, the stand-ins for `Product`, `AbstractModel` and the collections replaced the real classes, and every measurement so far undercounted. An installed package never had this.
- **Project index:** remembers which classes each plugin is configured on.
- **Factories:** `create()` of a factory that does not exist yet is typed from its name, the way Magento generates it. Untyped private properties take their type from the constructor.

## Impact

The four rules start experimental. Factory types do change the default result: on the 56 modules without `generated/code`, the default rules report 54 more findings, in P-LOOP-01, P-LOOP-02, P-LOOP-03, P-LOOP-07 and P-LOOP-10, and none disappears. A project with generated factories had them already. This goes into `CHANGELOG.md`.
