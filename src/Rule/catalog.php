<?php

declare(strict_types=1);

/**
 * Metadata of every rule. Sources come from docs/source-audit.md; core links are
 * pinned to magento/magento2@a385d02f.
 */

use Silin\MagentoPerfCheck\Rule\RuleDefinition;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Rule\Source;
use Silin\MagentoPerfCheck\Rule\SourceLevel;

$a = static fn (string $url, string $confirms): Source => new Source(SourceLevel::A, $url, $confirms);
$b = static fn (string $path, string $confirms): Source => new Source(
    SourceLevel::B,
    'https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/' . $path,
    $confirms,
);

$extensionPerformance = 'https://developer.adobe.com/commerce/php/best-practices/extensions/security';
$codeReview = 'https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review';

return [
    new RuleDefinition('X-LAY-01', 'Block with cacheable="false" in a frontend layout, other than the X-LAY-03 handles', Severity::Warning, RuleStatus::Experimental, [
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
    new RuleDefinition('P-COL-02', 'getFirstItem() on a database collection without setPageSize()', Severity::Error, RuleStatus::Experimental, [
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
    new RuleDefinition('P-LOOP-03', 'save() or delete() of a model or repository inside a loop', Severity::Info, RuleStatus::Default, [
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
    new RuleDefinition('P-DI-01', 'Session or cookie manager injected without a Proxy', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 9.10: Request, Session and Cookie objects must not be injected into constructors.'),
        $b('lib/internal/Magento/Framework/Session/SessionManager.php#L100-L125', 'SessionManager::__construct() calls start().'),
        $b('lib/internal/Magento/Framework/Session/SessionManager.php#L162-L208', 'start() calls session_start() outside CLI.'),
    ], ['magentoPerf.sessionWithoutProxy' => Severity::Warning, 'magentoPerf.cookieManagerWithoutProxy' => Severity::Info]),
    new RuleDefinition('P-DI-02', 'Constructor does more than assign dependencies', Severity::Warning, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 2.3: constructors may only assign dependencies and validate arguments.'),
    ], ['magentoPerf.constructorWork' => null]),
    new RuleDefinition('P-DI-03', 'Event dispatched in a constructor', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 2.3.2: events must not be triggered in constructors.'),
    ], ['magentoPerf.dispatchInConstructor' => null]),
    new RuleDefinition('P-DI-06', 'Direct ObjectManager::getInstance() in PHP classes', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/components/object-manager/', 'Usage rules: do not call the object manager directly, except in factories, proxies, the constructor backward-compatibility fallback and __wakeup/__sleep.'),
    ], ['magentoPerf.objectManagerUsage' => null]),
    new RuleDefinition('P-FPC-01', '$_isScopePrivate in a block', Severity::Info, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/cache/page/private-content', 'Do not use the $_isScopePrivate property: it is obsolete and will not work properly.'),
        $b('lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L158-L166', 'AbstractBlock marks $_isScopePrivate as @deprecated.'),
    ], ['magentoPerf.isScopePrivate' => null]),
    new RuleDefinition('P-FPC-02', 'Block or view model reads customer data from the session', Severity::Warning, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 9.4: server-rendered HTML must not contain user-specific data.'),
        $b('app/code/Magento/Customer/Model/Layout/DepersonalizePlugin.php#L101-L112', 'On cacheable GET pages the customer session is cleared after layout generation.'),
    ], ['magentoPerf.sessionDataInBlock' => null]),
    new RuleDefinition('P-LOOP-04', 'Product URL built inside a loop without addUrlRewrite()', Severity::Warning, RuleStatus::Experimental, [
        $b('app/code/Magento/Catalog/Model/Product/Url.php#L154-L183', 'Without a request path on the product, getUrl() looks the rewrite up through findOneByData().'),
        $b('app/code/Magento/UrlRewrite/Model/Storage/DbStorage.php#L107-L131', 'The lookup runs a query on the url_rewrite table.'),
        $b('app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L1455-L1504', 'addUrlRewrite() fills the request paths of the whole collection.'),
    ], ['magentoPerf.loopProductUrl' => null]),
    new RuleDefinition('P-LOOP-06', 'getAttributeText() or the table source getOptionText() inside a loop', Severity::Warning, RuleStatus::Experimental, [
        $b('app/code/Magento/Eav/Model/Entity/Attribute/Source/Table.php#L106-L119', 'getSpecificOptions() creates and loads an option collection on every call.'),
        $b('app/code/Magento/Catalog/Model/Product.php#L1796-L1801', 'getAttributeText() calls getOptionText() of the attribute source.'),
        new Source(SourceLevel::B, 'https://github.com/magento/magento2/issues/38934', 'Confirmed issue: the table source "loads data from Database every time attribute value is requested". It has no measurements.'),
    ], ['magentoPerf.loopAttributeText' => null]),
    new RuleDefinition('P-COL-03', "addAttributeToSelect('*') on an EAV collection", Severity::Info, RuleStatus::Experimental, [
        $b('app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L583-L588', "For '*', every attribute of the entity is added to the selected attributes."),
        $b('app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1266-L1297', '_loadAttributes() reads the values of all selected attributes. This shows the mechanism; no source measures the cost.'),
    ], ['magentoPerf.selectAllAttributes' => null]),
    new RuleDefinition('X-PLG-02', 'Plugin on a method that runs for every rendered block or layout element', Severity::Info, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/development/components/plugins', 'A plugin on a class also applies to the classes that inherit from it.'),
        $b('lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L666-L692', 'toHtml() runs once per rendered block. That a plugin here is slow is an inference from how often it runs.'),
        $b('lib/internal/Magento/Framework/View/Layout.php#L555-L571', 'renderElement() runs once per layout element.'),
    ], ['magentoPerf.hotRenderPlugin' => null]),
    new RuleDefinition('P-DI-04', 'Console command in CommandList with dependencies that are not lazy', Severity::Info, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/development/components/proxies', 'Constructor injection instantiates a chain of objects, which costs when a resource-intensive one is not needed; a real instance behind a proxy is created only when one of its methods is called. The page does not mention console commands.'),
        $b('lib/internal/Magento/Framework/Console/Cli.php#L196-L232', 'Every run on an installed store creates CommandListInterface, and so the objects in its commands argument. That their dependencies are costly is not measured.'),
    ], ['magentoPerf.commandWithoutProxy' => null]),
    new RuleDefinition('P-COL-04', 'Collection walked without a page size in a cron job or console command', Severity::Warning, RuleStatus::Experimental, [
        $b('lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836', 'loadWithFilter(), which load() runs, adds every fetched row to the collection.'),
        $b('lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763', '_renderLimit() adds a LIMIT only when a page size is set.'),
    ], ['magentoPerf.unpagedBatchCollection' => null]),
    new RuleDefinition('P-FPC-03', 'Value specific to a visitor or a moment in the HTTP context', Severity::Error, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/development/cache/page/public-content', '"Context variables should not be specific to individual users", because each value yields a separate cached copy.'),
        $b('lib/internal/Magento/Framework/App/Http/Context.php#L107-L135', 'Every non-default value goes into the vary string of the page cache key.'),
    ], ['magentoPerf.httpContextUserValue' => null]),
    new RuleDefinition('X-GQL-02', 'Database query in a resolver of a field on a type returned inside a list', Severity::Warning, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/webapi/graphql/develop/resolvers', '"We recommend using batch resolvers for queries because they improve performance"; loading related products per product "would be expensive performance-wise".'),
    ], ['magentoPerf.resolverQuery' => null]),
    new RuleDefinition('P-FPC-05', 'No-cache or private headers on cacheable requests', Severity::Warning, RuleStatus::Default, [
        $b('app/code/Magento/PageCache/etc/varnish6.vcl#L191-L200', 'Varnish does not cache responses marked no-cache or private.'),
        $b('lib/internal/Magento/Framework/App/PageCache/Kernel.php#L154-L183', 'The built-in page cache stores only responses with public cache headers.'),
        $a('https://developer.adobe.com/commerce/php/development/cache/page/public-content', 'Shows no-cache headers as the way to disable caching for a page on purpose.'),
    ], ['magentoPerf.noCacheHeaders' => null]),
    new RuleDefinition('P-FPC-06', 'State change in a GET-only controller', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/cache/page/public-content', '"Use only HTTP POST or PUT methods to change state."'),
        $a('https://developer.adobe.com/commerce/php/development/cache/page/private-content', '"Use only HTTP POST or PUT methods to change state."'),
    ], ['magentoPerf.stateChangeOnGet' => null]),
    new RuleDefinition('P-FPC-09', 'Full page cache or all caches cleaned from code', Severity::Error, RuleStatus::Default, [
        $b('lib/internal/Magento/Framework/App/Cache.php#L96-L111', 'clean() without tags cleans every cache frontend.'),
        $b('app/code/Magento/PageCache/Model/Cache/Type.php#L51-L55', 'Cleaning the full_page type dispatches the full page cache clean event.'),
        $b('app/code/Magento/CacheInvalidate/etc/events.xml#L27-L29', 'That event triggers a full Varnish purge.'),
    ], ['magentoPerf.cacheClean' => null]),
    new RuleDefinition('P-FPC-10', 'reindexAll() in an observer, plugin or controller', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/components/indexing/', 'Update on Save indexers reindex the changed entities through plugins or events.'),
        $b('app/code/Magento/Indexer/Model/Indexer.php#L452-L494', 'reindexAll() rebuilds the whole index synchronously.'),
    ], ['magentoPerf.fullReindex' => null]),
    new RuleDefinition('P-PLG-01', 'around plugin where before/after would do', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/components/plugins', 'Avoid using around method plugins when they are not required, because they increase stack traces and affect performance.'),
    ], ['magentoPerf.unnecessaryAroundPlugin' => null]),
    new RuleDefinition('P-API-01', 'Web API input field that only an unsupported constructor parameter could set', Severity::Warning, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/development/components/web-api/services', 'REST API constructor parameter validation (APSB25-88): supported parameter types are simple types and *\\Api\\Data\\*Interface classes; "Unsupported parameters will not be instantiated from REST payloads."'),
        $b('lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L290-L296', 'A constructor parameter gets a request value only when its type is simple or matches \\Vendor\\Module\\Api\\Data\\.'),
        $b('lib/internal/Magento/Framework/Webapi/ServiceInputProcessor.php#L346-L398', 'Any other field is set through the setter of the requested type; a field without one fails with "%fieldName" is not supported.'),
    ], ['magentoPerf.webapiConstructorParameter' => null]),
    new RuleDefinition('T-01', 'getLayout()->createBlock() in a template', Severity::Info, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines', 'Section 6.2.6: "Templates MUST NOT instantiate objects. All objects MUST be passed from the Block objects."'),
    ], ['magentoPerf.createBlockInTemplate' => null]),
    new RuleDefinition('T-03', 'JavaScript function declared once per item in a template', Severity::Warning, RuleStatus::Experimental, [
        $a('https://docs.hyva.io/hyva-themes/writing-code/rendering-javascript-once.html', 'When the HTML is rendered multiple times, for example, items in a product list, "this can lead to the duplication of JavaScript in the page source"; split the JavaScript into a separate template and render it only once.'),
        new Source(SourceLevel::C, 'https://github.com/hyva-themes/magento2-default-theme/blob/c74658fdeddfb03255516b1ff3cf77b940b8dede/Magento_Catalog/layout/catalog_list_item.xml#L42-L43', 'The Hyvä default theme renders the product list JavaScript (compare, wishlist) in separate blocks, once per list.'),
        new Source(SourceLevel::C, 'https://github.com/hyva-themes/magento2-default-theme/blob/c74658fdeddfb03255516b1ff3cf77b940b8dede/Magento_Catalog/templates/product/list/js/wishlist.phtml#L19-L20', 'initWishlist() is declared there, outside the template rendered for each product.'),
    ], ['magentoPerf.repeatedScriptFunction' => null]),
    new RuleDefinition('T-04', 'Result of $viewModels->require() never used in a template', Severity::Warning, RuleStatus::Experimental, [
        $a('https://docs.hyva.io/hyva-themes/performance/view-model-cache-tags.html', '"When a template calls $viewModels->require() to instantiate a view model, the ViewModelRegistry checks if the view model implements IdentityInterface. If so, the view model is registered for cache tag collection."'),
        new Source(SourceLevel::C, 'https://github.com/hyva-themes/magento2-theme-module/blob/6c922be3cd2e9ff9512447c681f388ca829a9c25/src/Model/ViewModelRegistry.php#L60-L78', 'require() gets the view model and passes it to ViewModelCacheTags::collectFrom().'),
        new Source(SourceLevel::C, 'https://github.com/hyva-themes/magento2-theme-module/blob/6c922be3cd2e9ff9512447c681f388ca829a9c25/src/Model/ViewModelCacheTags.php#L36-L38', 'collectFrom() keeps a view model that implements IdentityInterface; its identities become cache tags of the page.'),
    ], ['magentoPerf.unusedViewModel' => null]),
    new RuleDefinition('T-05', 'Synchronous external script in a storefront template', Severity::Warning, RuleStatus::Default, [
        $a('https://docs.hyva.io/hyva-themes/writing-code/patterns/loading-external-javascript.html', 'About <script type="text/javascript" src="https://www.example.com/render-blocking-script.js" />: "Do not use this, it will lower the page rank metrics".'),
        $a('https://docs.hyva.io/hyva-themes/performance/core-web-vitals.html', '"Every render-blocking script delays LCP. Load scripts that are not needed for the initial render using the defer or async attribute."'),
    ], ['magentoPerf.syncExternalScript' => null]),
    new RuleDefinition('T-06', 'Image without width and height in a storefront template', Severity::Info, RuleStatus::Experimental, [
        $a('https://docs.hyva.io/hyva-themes/performance/core-web-vitals.html', '"Always set width and height attributes on images and videos. Without them, the browser cannot reserve space before the media loads, causing a shift when each asset arrives."'),
        $a('https://docs.hyva.io/hyva-themes/performance/hyva-performance-tips.html', 'Images: "Always set width and height".'),
    ], ['magentoPerf.imageWithoutDimensions' => null]),
    new RuleDefinition('X-LAY-04', 'Script added to the page head by layout XML without async or defer', Severity::Info, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/frontend-core/javascript/resources', '"To address the problem of slow page loads, we exclude JavaScript from the page headers and we added the ability to use the RequireJS library."'),
        $a('https://developer.adobe.com/commerce/frontend-core/guide/layouts/xml-manage', '"The application supports async or defer attribute in script tag".'),
        $a('https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/optimize-css-js-files', '"Consider delivering critical JS and CSS features inline and deferring all non-critical JS/CSS styles."'),
        $b('lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L28-L39', 'The scripts core never defers: the RequireJS loader and its configuration, static and bundle files, sri.js.'),
        $b('lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L459-L470', 'Core adds defer to other head scripts only when dev/js/defer_non_critical is on; it is off by default (app/code/Magento/Backend/etc/config.xml#L23).'),
    ]),
    new RuleDefinition('X-LAY-05', 'Stylesheet added to every storefront page by a module layout', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/frontend-core/guide/css/critical-path', '"All CSS styles loaded from external files are considered as render-blocking. This means that a web page will not be displayed until these files are loaded."'),
        $a('https://developer.adobe.com/commerce/frontend-core/guide/layouts/xml-instructions', '"For example, layout changes added to `app/code/Vendor/Module/view/frontend/layout/default.xml` are loaded on all pages."'),
        $b('lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L397-L399', 'Core renders a css asset as <link rel="stylesheet">, with the attributes written in the layout.'),
        $b('app/code/Magento/Theme/Controller/Result/AsyncCssPlugin.php#L58-L62', 'Core loads stylesheets without blocking only when dev/css/use_css_critical_path is on; it is off by default (app/code/Magento/Theme/etc/config.xml#L74).'),
    ]),
    new RuleDefinition('J-DEP-01', 'Storefront JavaScript that depends on the whole jQuery UI library (jquery/ui)', Severity::Warning, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/frontend-core/javascript/custom', '"Using individual jQuery UI components instead of the monolithic jQuery UI library improves storefront performance."'),
        $a('https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/magento-open-source/2-4-3', 'The dropdownDialog widget now loads only the jquery-ui chunks it needs: "Previously, it loaded large amounts of unnecessary code, which inflated load time and reduced performance."'),
        $b('lib/web/jquery/compat.js#L1-L4', 'jquery/compat imports every jQuery UI widget: "Import every plugin under the sun. Bad for performance".'),
        $b('app/code/Magento/Theme/view/frontend/requirejs-config.js#L30', 'On the storefront core maps jquery/ui to jquery/compat.'),
        new Source(SourceLevel::B, 'https://github.com/magento/magento2/issues/22995', 'Measured in the issue: "jQueryUI is `227kb` of the shared bundle (unminified), even though only 11% of that is used on the homepage."'),
    ]),
    new RuleDefinition('J-DEP-02', 'A module in the top-level deps of a storefront requirejs-config.js, loaded on every page', Severity::Info, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/frontend-core/javascript/requirejs', '"Adding modules under an independent `deps` configuration will load the specified modules in all pages."'),
        $a('https://developer.adobe.com/commerce/frontend-core/javascript/custom', '"Additional dependencies demand more web requests, which can slow rendering."'),
        $b('app/code/Magento/RequireJs/etc/di.xml#L9-L35', 'The merged files: a module\'s view/<area>/requirejs-config.js (Collector\\Base) and a theme\'s, at its root (Collector\\Theme) or in a <Vendor_Module> folder (Collector\\ThemeModular).'),
        $b('lib/internal/Magento/Framework/RequireJs/Config.php#L79-L85', 'Each requirejs-config.js is wrapped in require.config(config).'),
        $b('lib/internal/Magento/Framework/RequireJs/Config.php#L154-L166', 'The requirejs-config.js files of the theme and its modules are merged into one file.'),
        $b('app/code/Magento/Theme/view/frontend/layout/default.xml#L14', 'The merged configuration is a block of the default handle, so every storefront page loads it.'),
    ]),
    // Run through the project's magento/magento-coding-standard (ADR 7); the sniffs are the source, pinned to v41.
    new RuleDefinition('S-PERF-01', 'array_merge() inside a loop (magento-coding-standard)', Severity::Info, RuleStatus::Experimental, [
        new Source(SourceLevel::C, 'https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Performance/ForeachArrayMergeSniff.php#L21', 'Magento2.Performance.ForeachArrayMerge: "array_merge(...) is used in a loop and is a resources greedy construction."'),
    ]),
    new RuleDefinition('S-TPL-01', '$this or a helper in a template (magento-coding-standard)', Severity::Info, RuleStatus::Experimental, [
        new Source(SourceLevel::C, 'https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Templates/ThisInTemplateSniff.php#L16-L17', 'Magento2.Templates.ThisInTemplate: $this in templates is deprecated, helpers are discouraged; use $block and view models.'),
    ]),
    new RuleDefinition('S-TPL-02', 'Object manager in a template (magento-coding-standard)', Severity::Warning, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines#presentation-layer', 'Section 6.2.6: "Templates MUST NOT instantiate objects. All objects MUST be passed from the Block objects."'),
        new Source(SourceLevel::C, 'https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Templates/ObjectManagerSniff.php#L23-L29', 'Magento2.Templates.ObjectManager: templates must not instantiate objects; all objects come from the block.'),
    ]),
    new RuleDefinition('S-DI-01', 'Proxy or Interceptor requested in a constructor (magento-coding-standard)', Severity::Info, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines#class-design', 'Section 2.5: "Proxies and interceptors MUST NEVER be explicitly requested in constructors."'),
        $b('lib/internal/Magento/Framework/ObjectManager/Code/Generator/Proxy.php#L113-L120', 'A generated Proxy builds the real object on its first use.'),
        new Source(SourceLevel::C, 'https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Classes/DiscouragedDependenciesSniff.php#L25', 'Magento2.Classes.DiscouragedDependencies: "Proxies and interceptors MUST never be explicitly requested in constructors."'),
    ]),
    new RuleDefinition('S-FN-01', 'sleep(), time_nanosleep() or session_*() call (magento-coding-standard)', Severity::Warning, RuleStatus::Experimental, [
        new Source(SourceLevel::C, 'https://github.com/magento/magento-coding-standard/blob/e4af7c6c79d0bfa89deb06cba1a3d49405635afc/Magento2/Sniffs/Functions/DiscouragedFunctionSniff.php#L125-L150', 'Magento2.Functions.DiscouragedFunction lists session_*, sleep and time_nanosleep among discouraged functions.'),
    ]),
    new RuleDefinition('X-SETUP-01', 'Heavy work in Setup/Recurring or RecurringData', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/prepare/extension-lifecycle', 'The Recurring schema class runs after every schema installation or upgrade stage; RecurringData after every data stage.'),
        $b('setup/src/Magento/Setup/Model/Installer.php#L1159-L1174', 'The installer runs the Recurring script of every module.'),
    ], ['magentoPerf.heavyRecurringSetup' => null]),
    new RuleDefinition('X-LAY-02', 'ESI ttl on a block in an entity-specific handle', Severity::Error, RuleStatus::Default, [
        $b('app/code/Magento/PageCache/Model/Layout/MergePlugin.php#L42-L54', 'Throws a LogicException for ttl blocks in handles that contain an entity ID.'),
        $b('lib/internal/Magento/Framework/View/Result/Page.php#L236-L252', 'Entity-specific handles are the *_id_* and *_sku_* ones.'),
        $a('https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/varnish/use-varnish-esi', 'How ESI blocks with ttl are rendered by Varnish.'),
    ]),
    new RuleDefinition('X-EVT-01', 'Observer on model_load_after or <entity>_load_after', Severity::Warning, RuleStatus::Default, [
        $b('lib/internal/Magento/Framework/Model/AbstractModel.php#L590-L609', 'AbstractModel::_afterLoad() dispatches model_load_after and the entity-specific load_after event on every load.'),
        $a('https://developer.adobe.com/commerce/php/best-practices/extensions/observers', 'Observers should be lightweight; they run every time their event fires.'),
    ]),
    new RuleDefinition('X-EVT-02', 'Observer on generic model save/delete events', Severity::Warning, RuleStatus::Default, [
        $b('lib/internal/Magento/Framework/Model/AbstractModel.php#L691-L730', 'model_save_before/after are dispatched for every model.'),
        $b('lib/internal/Magento/Framework/Model/AbstractModel.php#L893-L930', 'model_delete_* are dispatched for every model.'),
    ]),
    new RuleDefinition('X-EVT-03', 'Observer on per-block render events', Severity::Warning, RuleStatus::Default, [
        $b('lib/internal/Magento/Framework/View/Layout.php#L555-L571', 'core_layout_render_element is dispatched for every rendered layout element.'),
        $b('lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L666-L692', 'view_block_abstract_to_html_before/after are dispatched for every block.'),
        $a('https://developer.adobe.com/commerce/php/best-practices/extensions/observers', 'Observers should be lightweight; they run every time their event fires.'),
    ]),
    new RuleDefinition('X-SEC-01', 'sections.xml invalidates all sections or on every POST', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/cache/page/private-content', 'Explains action-to-section invalidation in sections.xml.'),
        $b('app/code/Magento/Customer/view/frontend/web/js/section-config.js#L37-L54', 'Sections configured for "*" are invalidated on every POST; "*" as section means all sections.'),
        $b('app/code/Magento/Customer/CustomerData/SectionConfigConverter.php#L18-L35', 'An action without sections is converted to "invalidate all".'),
    ]),
    new RuleDefinition('X-MQ-01', 'Message queue topic synchronous by default', Severity::Warning, RuleStatus::Default, [
        $b('lib/internal/Magento/Framework/Communication/Config/Reader/XmlReader/Converter.php#L127-L153', 'Topics are synchronous unless is_synchronous="false".'),
        $b('lib/internal/Magento/Framework/MessageQueue/Rpc/Publisher.php#L105-L125', 'The AMQP RPC publisher waits for the reply.'),
        $a('https://developer.adobe.com/commerce/php/development/components/message-queues/configuration', 'Documents is_synchronous on topics.'),
    ]),
    new RuleDefinition('X-IDX-01', 'Indexer without an mview subscription', Severity::Warning, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/components/indexing/custom-indexer', 'A custom indexer declares its mview view to support Update by Schedule.'),
        $b('lib/internal/Magento/Framework/Mview/View.php#L185-L189', 'Loading a view that is not declared throws an exception.'),
    ]),
    new RuleDefinition('X-DB-01', 'db_schema.xml table without a primary key', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/best-practices/extensions/security', 'Tables must have a primary key.'),
    ]),
    new RuleDefinition('X-GQL-01', 'GraphQL Query field with @cache but no cacheIdentity', Severity::Error, RuleStatus::Default, [
        $b('app/code/Magento/GraphQlCache/Model/CacheableQueryHandler.php#L59-L85', 'A field with @cache but without cacheIdentity marks the whole query as not cacheable.'),
        $b('app/code/Magento/GraphQlCache/Model/Plugin/Query/Resolver.php#L43-L82', 'The resolver plugin collects cache tags only through the field\'s identity class.'),
        $a('https://developer.adobe.com/commerce/webapi/graphql/develop/identity-class', 'Cacheable queries need an identity class that returns the cache tags.'),
    ]),
    new RuleDefinition('P-LOOP-09', 'Repository getList() with search criteria once per item of a loop', Severity::Warning, RuleStatus::Default, [
        $a($extensionPerformance, '"Running SQL queries in a loop often results in a performance bottleneck."'),
        $a($codeReview, '"Are queries executed in a loop? This loop can be outside of the edited files."'),
    ], ['magentoPerf.loopRepositoryList' => null]),
    new RuleDefinition('P-LOOP-10', 'Resource model load() inside a loop', Severity::Warning, RuleStatus::Default, [
        $a($extensionPerformance, '"Running SQL queries in a loop often results in a performance bottleneck."'),
        $b('lib/internal/Magento/Framework/Model/ResourceModel/Db/AbstractDb.php#L323-L333', 'AbstractDb::load() runs fetchRow() on every call.'),
    ], ['magentoPerf.loopResourceLoad' => null]),
    new RuleDefinition('P-LOOP-11', 'ObjectManager get() or create() inside a loop', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/components/object-manager/', 'Usage rules: do not call the object manager directly, except in factories, proxies, the constructor backward-compatibility fallback and __wakeup/__sleep.'),
        $b('lib/internal/Magento/Framework/ObjectManager/ObjectManager.php#L57-L60', 'create() builds a new instance through the factory on every call.'),
        $b('lib/internal/Magento/Framework/ObjectManager/ObjectManager.php#L68-L76', 'get() builds the shared instance on the first call and returns it from _sharedInstances afterwards.'),
    ], ['magentoPerf.loopObjectManager' => null]),
    new RuleDefinition('U-CLI-01', 'Console command whose execute() has no int return type (Magento 2.4.9, Symfony Console 7.4)', Severity::Error, RuleStatus::Experimental, [
        $a('https://developer.adobe.com/commerce/php/development/backward-incompatible-changes/', 'Symfony 7.4 LTS support (2.4.9): "Custom classes that extend Symfony core classes must have updated type declarations and method signatures aligned with Symfony 7.4".'),
        $b('composer.json#L89', 'Magento requires symfony/console ^7.4.'),
        new Source(SourceLevel::B, 'https://github.com/symfony/console/blob/92f58bc4bf97a92ed1b9f367f0cd44f20bde0e87/Command/Command.php#L240', 'Symfony Console 7.4: Command::execute() declares the return type int.'),
        $b('lib/internal/Magento/Framework/Console/Cli.php#L196-L222', 'bin/magento creates every registered command through CommandListInterface when it builds its default command list.'),
    ], ['magentoPerf.consoleCommandReturnType' => null], ['2.4.9']),
    new RuleDefinition('P-LOOP-12', 'Method that runs a query, called inside a loop', Severity::Warning, RuleStatus::Experimental, [
        $a($codeReview, '"Are queries executed in a loop? This loop can be outside of the edited files."'),
        $a($extensionPerformance, '"Running SQL queries in a loop often results in a performance bottleneck."'),
        $b('lib/internal/Magento/Framework/Model/AbstractModel.php#L564-L568', 'AbstractModel::load() delegates to the resource model: one database round trip per call.'),
        $b('lib/internal/Magento/Framework/Model/ResourceModel/Db/AbstractDb.php#L323-L333', 'AbstractDb::load() runs fetchRow() on every call.'),
    ], ['magentoPerf.loopCallQuery' => null]),
    new RuleDefinition('P-LOOP-05', 'Product getCategoryIds() inside a loop', Severity::Warning, RuleStatus::Default, [
        $b('app/code/Magento/Catalog/Model/Product.php#L753-L769', 'getCategoryIds() asks the resource model when category_ids is not set.'),
        $b('app/code/Magento/Catalog/Model/ResourceModel/Product/CategoryLink.php#L54-L69', 'Each call runs one fetchAll for one product id.'),
        $b('app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L2163-L2200', 'addCategoryIds() runs one query for all loaded items.'),
    ], ['magentoPerf.loopCategoryIds' => null]),
    new RuleDefinition('P-IDX-01', 'reindexRow()/reindexList() in request code without isScheduled()', Severity::Warning, RuleStatus::Default, [
        $a('https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration', '"frequent reindexing degrades database performance which slows site performance"'),
        $b('app/code/Magento/Indexer/Model/Indexer.php#L562-L566', 'Indexer::reindexRow() runs the action without looking at the indexer mode.'),
        $b('lib/internal/Magento/Framework/Indexer/AbstractProcessor.php#L50-L56', 'Core processors return early when the indexer is scheduled.'),
    ], ['magentoPerf.directReindex' => null]),
    new RuleDefinition('X-LAY-03', 'Block with cacheable="false" on the default, catalog or CMS handle', Severity::Error, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/php/development/cache/page/', '"If any block in a layout is designated as uncacheable, the entire page is uncacheable."'),
        $a('https://developer.adobe.com/commerce/php/development/cache/page/public-content', '"Using cacheable="false" inside the default.xml file disables caching for all pages on the site."'),
        $b('lib/internal/Magento/Framework/View/Layout.php#L1192-L1210', 'Layout::isCacheable() returns false when any <block cacheable="false"> is present in the layout.'),
    ]),
    new RuleDefinition('X-DB-02', 'db_schema.xml index that duplicates a key or another index', Severity::Info, RuleStatus::Default, [
        $a($extensionPerformance, 'Indexes "are regenerated during every table INSERT or UPDATE. That can hit performance; only add indexes when necessary."'),
    ]),
    new RuleDefinition('X-AREA-01', 'Observer on an admin event declared in the global area', Severity::Info, RuleStatus::Default, [
        $a($extensionPerformance, '"It is NOT RECOMMENDED to register everything in global area, as the bootstrapping process will become much heavier."'),
        $a('https://developer.adobe.com/commerce/php/development/components/events-and-observers/', '"Declare the observer in the appropriate area."'),
    ]),
    new RuleDefinition('X-GQL-03', 'GraphQL cache id factor added to CacheIdCalculator', Severity::Info, RuleStatus::Default, [
        $a('https://developer.adobe.com/commerce/webapi/graphql/usage/caching/', '"Adding factors could generate too many unique cache keys, thereby reducing the number of caching hits and affecting performance."'),
        $b('app/code/Magento/GraphQlCache/Model/CacheId/CacheIdCalculator.php#L89-L104', 'Every provider value goes into the hash of the cache id.'),
    ]),
];
