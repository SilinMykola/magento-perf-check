<?php

declare(strict_types=1);

/**
 * Metadata of every rule. Sources come from docs/source-audit.md; core links are
 * pinned to magento/magento2@a385d02f.
 */

use MagentoPerfRules\Rule\RuleDefinition;
use MagentoPerfRules\Rule\RuleStatus;
use MagentoPerfRules\Rule\Severity;
use MagentoPerfRules\Rule\Source;
use MagentoPerfRules\Rule\SourceLevel;

$a = static fn (string $url, string $confirms): Source => new Source(SourceLevel::A, $url, $confirms);
$b = static fn (string $path, string $confirms): Source => new Source(
    SourceLevel::B,
    'https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/' . $path,
    $confirms,
);

$extensionPerformance = 'https://developer.adobe.com/commerce/php/best-practices/extensions/security';
$codeReview = 'https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review';

return [
    new RuleDefinition('X-LAY-01', 'Block with cacheable="false" in a frontend layout', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/cache/page/', '"If any block in a layout is designated as uncacheable, the entire page is uncacheable."'),
        $a('https://developer.adobe.com/commerce/php/development/cache/page/public-content', '"Using cacheable="false" inside the default.xml file disables caching for all pages on the site."'),
        $b('lib/internal/Magento/Framework/View/Layout.php#L1192-L1210', 'Layout::isCacheable() returns false when any <block cacheable="false"> is present in the layout.'),
        $b('app/code/Magento/PageCache/Model/Layout/LayoutPlugin.php#L70-L75', 'Public cache headers are set only when the layout is cacheable.'),
    ]),
    new RuleDefinition('P-LOOP-01', 'Model load() or repository get()/getById() inside a loop', Severity::Error, RuleStatus::Default, [
        $a($extensionPerformance, '"Running SQL queries in a loop often results in a performance bottleneck."'),
        $a($codeReview, '"Are queries executed in a loop? This loop can be outside of the edited files."'),
        $b('lib/internal/Magento/Framework/Model/AbstractModel.php#L564-L568', 'AbstractModel::load() delegates to the resource model: one database round trip per call.'),
        $b('app/code/Magento/Catalog/Model/ProductRepository.php#L308-L328', 'ProductRepository::getById() loads the product unless that id is already cached.'),
    ], ['magentoPerf.loopModelLoad' => null]),
    new RuleDefinition('P-COL-02', 'getFirstItem() on a database collection without setPageSize()', Severity::Error, RuleStatus::Default, [
        $b('lib/internal/Magento/Framework/Data/Collection.php#L305-L315', 'getFirstItem() calls load() and returns the first loaded item; it adds no LIMIT.'),
        $b('lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763', '_renderLimit() applies limitPage() only when a page size is set.'),
    ], ['magentoPerf.getFirstItemWithoutLimit' => null]),
    new RuleDefinition('P-COL-01', 'count() on a database collection instead of getSize()', Severity::Warning, RuleStatus::Default, [
        $b('lib/internal/Magento/Framework/Data/Collection.php#L858-L862', 'Collection::count() calls load() before counting the items.'),
        $b('lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441', 'AbstractDb::getSize() runs a COUNT query without loading items.'),
    ], ['magentoPerf.collectionCount' => null]),
    new RuleDefinition('P-LOOP-02', 'Collection created and loaded inside a loop', Severity::Error, RuleStatus::Default, [
        $a($extensionPerformance, '"Running SQL queries in a loop often results in a performance bottleneck."'),
        $b('lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836', 'AbstractDb::load() runs the select and builds every item.'),
        $b('lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441', 'getSize() runs its own COUNT query.'),
    ], ['magentoPerf.loopCollectionLoad' => null]),
    new RuleDefinition('P-LOOP-03', 'save() or delete() of a model or repository inside a loop', Severity::Warning, RuleStatus::Default, [
        $a($extensionPerformance, '"Running SQL queries in a loop often results in a performance bottleneck."'),
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 6.4.3.2: service contracts SHOULD support batch data processing.'),
        $b('lib/internal/Magento/Framework/Model/AbstractModel.php#L680-L684', 'AbstractModel::save() delegates to the resource model: one write per call.'),
        $b('lib/internal/Magento/Framework/Model/AbstractModel.php#L881-L885', 'AbstractModel::delete() delegates to the resource model: one write per call.'),
    ], ['magentoPerf.loopSave' => null]),
    new RuleDefinition('P-LOOP-07', 'Composite product type methods inside a loop', Severity::Warning, RuleStatus::Default, [
        $b('app/code/Magento/ConfigurableProduct/Model/Product/Type/Configurable.php#L1411-L1420', 'Configurable::getUsedProducts() loads a child collection for each product.'),
        $b('app/code/Magento/ConfigurableProduct/Model/ResourceModel/Product/Type/Configurable.php#L159-L208', 'getChildrenIds() and getParentIdsByChild() run a query per call and accept arrays.'),
        $b('app/code/Magento/GroupedProduct/Model/Product/Type/Grouped.php#L203-L237', 'Grouped::getAssociatedProducts() loads a collection per product.'),
    ], ['magentoPerf.loopCompositeType' => null]),
    new RuleDefinition('P-DI-01', 'Session or cookie manager injected without a Proxy', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 9.10: Request, Session and Cookie objects must not be injected into constructors.'),
        $b('lib/internal/Magento/Framework/Session/SessionManager.php#L100-L125', 'SessionManager::__construct() calls start().'),
        $b('lib/internal/Magento/Framework/Session/SessionManager.php#L162-L208', 'start() calls session_start() outside CLI.'),
    ], ['magentoPerf.sessionWithoutProxy' => Severity::Error, 'magentoPerf.cookieManagerWithoutProxy' => Severity::Warning]),
    new RuleDefinition('P-DI-02', 'Constructor does more than assign dependencies', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 2.3: constructors may only assign dependencies and validate arguments.'),
    ], ['magentoPerf.constructorWork' => null]),
    new RuleDefinition('P-DI-03', 'Event dispatched in a constructor', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 2.3.2: events must not be triggered in constructors.'),
    ], ['magentoPerf.dispatchInConstructor' => null]),
    new RuleDefinition('P-DI-06', 'Direct ObjectManager::getInstance() in PHP classes', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/components/object-manager/', 'Usage rules: do not call the object manager directly, except in factories, proxies, the constructor backward-compatibility fallback and __wakeup/__sleep.'),
    ], ['magentoPerf.objectManagerUsage' => null]),
];
