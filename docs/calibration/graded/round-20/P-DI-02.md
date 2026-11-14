# P-DI-02: calibration grades, round 20

12 of 53 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Divante/PimcoreIntegration/Cron/UnusedAttributeOptionsRemover.php:49` | TP-low | getLoggerInstance() makes one in-memory config read and builds a Monolog logger whose handlers open no file until they write, and the class is built only when its cron job runs. |
| 2 | `app/code/Divante/PimcoreIntegration/Http/Request/RequestClient.php:96` | TP-low | Creating the HTTP client and the logger is cheap object construction, and the client is built only by the Pimcore queue actions that run from the import commands. |
| 3 | `app/code/Divante/PimcoreIntegration/Queue/Action/Asset/PathResolver.php:44` | TP-low | Filesystem::getDirectoryWrite(MEDIA) returns a cached directory object, and the resolver is built only by the asset queue actions of the import commands. |
| 4 | `app/code/Faonni/SmartCategory/Model/Rule/Condition/Product/News.php:44` | TP-low | setType() and setValue() only write to the data array, a known cheap pattern on a rule condition built in admin rule editing and smart category processing. |
| 5 | `app/code/MagePal/GuestToCustomer/Block/Adminhtml/Order/View/GuestToCustomerButtonList.php:39` | TP-low | A registry read, one config read and one admin URL build run once per admin order view page, the only place the adminhtml di.xml injects this button list. |
| 6 | `app/code/MagePsycho/CustomShipping/Helper/Data.php:35` | TP-low | $context->getLogger() is a plain getter on the helper context, negligible cost. |
| 7 | `app/code/Magepow/Categories/Block/Categories.php:96` | TP-low | getDirectoryRead(PUB) returns a cached directory object and the later isShowThumbnail() is an in-memory array lookup, a small cost on the category page block. |
| 8 | `app/code/Magepow/Categories/Helper/Data.php:27` | TP-low | One scopeConfig->getValue() of the module section is an in-memory lookup once config is loaded, a small cost per helper creation on category pages. |
| 9 | `app/code/Mageprince/Faq/Model/Faq/DataProvider.php:64` | TP-low | Creating the collection runs no query, and the data provider is built only for the admin FAQ edit form. |
| 10 | `app/code/Mageprince/LogViewer/Ui/DataProvider/LogFile/ListingDataProvider.php:94` | TP-low | DirectoryList::getPath() and one config read are cheap, and the provider is built only for the admin log file listing. |
| 11 | `app/code/Magiccart/Magicmenu/Controller/Adminhtml/Action.php:94` | TP-low | $context->getResultRedirectFactory() is a plain getter on the action context in an admin controller, negligible cost. |
| 12 | `app/code/Swissup/SearchMysqlLegacy/SearchAdapter/Mysql/TemporaryStorage.php:49` | FP | $config !== null ? $config : ObjectManager::getInstance()->get(DeploymentConfig::class) is the documented backward-compatible fallback, written as a full ternary instead of ??. |
