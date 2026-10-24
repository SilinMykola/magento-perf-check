# Magento Performance Rules: техническое задание

> Техническое задание инструмента. Владелец: Mykola (backend Magento 2, 8+ лет, контрибьютор ядра).
> Код, README, документация правил, коммиты: английский.
> Имя проекта: `magento-perf-check` (пакет `silin/magento-perf-check`, репозиторий [SilinMykola/magento-perf-check](https://github.com/SilinMykola/magento-perf-check)).

---

## 1. Цель

Статический анализатор, который находит в коде Magento 2 / Mage-OS **анти-паттерны производительности и Full Page Cache** до того, как они попадут на прод. Принципы:

1. **Работает без настройки.** `composer require --dev`, одна команда, сразу результат.
2. **Точность важнее количества.** Шумный инструмент отключают через неделю. Правило с низкой точностью не попадает в стандартный набор, пока не откалибровано (раздел 7).
3. **Каждая находка учит.** Почему это плохо, что исправить, пример «плохо / хорошо», ссылка на первоисточник.
4. **Живёт в существующих пайплайнах:** PHPStan, GrumPHP, CaptainHook, GitHub Actions, GitLab CI, агенты для кода.

---

## 2. Политика источников (обязательна)

Каждый вывод инструмента должен опираться на проверяемый первоисточник. Это главный аргумент доверия: у автора нет узнаваемости, поэтому авторитет дают ссылки, а не имя.

| Уровень | Что считается | Роль |
|---|---|---|
| **A** | Официальная документация Adobe (developer.adobe.com, experienceleague.adobe.com), включая Technical Guidelines. Для Hyvä-специфичных правил официальная документация Hyvä | достаточно для включения правила |
| **B** | Код ядра Magento (ссылка **на конкретный коммит**, не на ветку, чтобы ссылка не протухала) и issues в magento/magento2 с подтверждённым воспроизведением или замерами | достаточно для включения правила |
| **C** | Блоги, community-документация, skills для агентов, сторонние стандарты (ECG и т.п.) | только дополнительное объяснение, **никогда не единственное основание** |

Правила:
- Правило попадает в стандартный набор (✅ или ⏳), только если у него есть хотя бы один источник уровня **A или B**. Правило только с источниками C остаётся 🧪 до появления A/B.
- Ссылки на код ядра фиксируются на коммит. Текущая база: `magento/magento2@a385d02f` (2.4-develop, 2026-10-16). При обновлении базы ссылки перепроверяются скриптом в CI.
- На странице каждого правила (`docs/rules/<ID>.md`) есть раздел **Basis**: какие источники, какого уровня, и одной фразой, что именно в источнике подтверждает вывод (цитата до 15 слов или пересказ).
- В README обязателен раздел **«What our rules are based on»**: эта политика, таблица правил с уровнями источников, ссылка на `docs/calibration.md` (измеренная точность) и честная оговорка, что статический анализ даёт эвристики, а не гарантию: каждая находка это повод проверить, а не приговор.
- Сообщение каждой находки содержит ссылку на страницу правила, где видны источники.

---

## 2a. Существующие инструменты и наше отличие

| Инструмент | Что покрывает | Ограничения |
|---|---|---|
| [magento/magento-coding-standard](https://github.com/magento/magento-coding-standard) (phpcs) | одно правило производительности: `Magento2.Performance.ForeachArrayMerge`. Плюс смежные: Proxy и Interceptor в конструкторе, ObjectManager и `$this` в шаблонах, discouraged functions (`sleep`, `session_*`) | правил FPC и N+1 нет |
| [magento-ecg/coding-standard](https://github.com/magento-ecg/coding-standard) (phpcs) | `Ecg.Performance.Loop` (load/save/delete/getFirstItem и методы composite-типов в циклах), `CollectionCount`, `GetFirstItem`, `FetchAll`, `Sql.SlowQuery`, `ObjectManagerUsage` | **токены без типов**: любой `->get()` в цикле считается нарушением. Последний коммит в 2024-08. XML не проверяет |
| [bitExpert/phpstan-magento](https://github.com/bitExpert/phpstan-magento) (PHPStan, около 2,9 млн установок) | type extensions для factory, proxy и магических методов. 5 архитектурных правил (сервисные контракты, коллекции через factory, `setTemplate` в блоках) | производительности и FPC нет |
| Core integrity tests (`dev/tests/static/testsuite/Magento/Test/Integrity`) | в т.ч. `DBSchema/PrimaryKeyTest` | только для кода ядра, на проектах не запускаются |
| [run_as_root/magento-cli-auto-proxy](https://packagist.org/packages/run_as_root/magento-cli-auto-proxy) | runtime: подставляет Proxy в конструкторы CLI-команд | не статика |
| Adobe Marketplace EQP Varnish test | динамический тест MISS → HIT на страницах | не статика, только для Marketplace |

**Наше отличие:**
- PHP-правила на PHPStan: знают **тип** (это `Collection`, `ProductRepositoryInterface`, `SessionManagerInterface`), а не имя метода. Это на порядок меньше ложных срабатываний, чем у ECG.
- **XML- и FPC-слой, которого нет ни у кого:** layout (`cacheable`, `ttl`), `sections.xml`, `events.xml`, `di.xml`, `.graphqls`, `communication.xml`, `queue_consumer.xml`, `crontab.xml`, `indexer.xml`/`mview.xml`, `db_schema.xml`.
- Правила, которые дублируют MCS, мы **не реализуем** (раздел 4.9).

---

## 3. Архитектура

Один composer-пакет, две подсистемы, одна точка входа.

```
vendor/bin/magento-perf-check            ← одна команда для пользователя
   ├── XML-анализатор (собственный, DOM)  → правила X-*
   ├── PHPStan с нашим extension          → правила P-*
   └── Reporter: объединённый отчёт, общие ID правил, baseline, форматы
```

### 3.1 PHPStan-расширение (`type: phpstan-extension`)
- Регистрируется автоматически через [phpstan/extension-installer](https://github.com/phpstan/extension-installer). Можно подключить и вручную в `phpstan.neon` проекта, если там уже есть PHPStan.
- Правила работают на любом уровне PHPStan (пользовательские правила от уровня не зависят).
- Для типов factory, proxy и магических методов используется bitExpert/phpstan-magento. Сделать его зависимостью или рекомендацией, решить в ADR на M0. **Проверить совместимость с PHPStan 1.12 и 2.x** (раздел 8).
- Каждое правило имеет PHPStan identifier (`magentoPerf.loopModelLoad` и т.д.) для `@phpstan-ignore` и baseline.
- **Контекст цикла:** NodeVisitor помечает узлы внутри `foreach`/`for`/`while`/`do`, а также внутри замыканий, переданных в `array_map`, `array_walk` и `->walk()` коллекции. Межметодный анализ (цикл вызывает метод, который грузит модель) делается на M4 через PHPStan Collectors.

### 3.2 XML-анализатор
- Сканирует `app/code`, `app/design` и сторонние пакеты в `vendor` (не `magento/*`, не `mage-os/*`), если включено флагом.
- Парсинг через DOM/XPath. Каждое правило получает файл, область (area) и модуль.
- Определение области: `etc/di.xml` = global, `etc/frontend/di.xml` = frontend и т.д.
- Для правил, где нужна связка XML ↔ PHP (observer → класс, plugin → методы), XML-анализатор строит индекс, а PHP-часть проверяется через PHPStan по этому индексу (передаётся через параметры extension или временный файл).

### 3.3 Общее
- **Конфиг** `magento-perf-check.neon` (или секция в `phpstan.neon`): пути, отключение правил, severity override, allowlist хэндлов для `cacheable="false"`, свои горячие события и методы.
- **Подавление:** PHP через `@phpstan-ignore magentoPerf.xxx (reason)`. XML через комментарий `<!-- perf-ignore X-LAY-01: reason -->` перед элементом. Причина обязательна.
- **Baseline:** `magento-perf-check --generate-baseline` для легаси-проектов. Новые нарушения ловятся, старые не мешают.
- **Форматы:** table (по умолчанию), JSON, Checkstyle, SARIF 2.1.0, GitHub annotations, GitLab Code Quality.
- **Exit codes:** `0` чисто, `1` есть находки уровня `--fail-on` (по умолчанию `error`), `2` ошибка инструмента.
- **`--project=<path>`:** анализ проекта по пути без установки в него (раздел 9a, CI).

---

## 4. Каталог правил

Обозначения:
- **Sev**: `error` (почти наверняка проблема), `warning` (вероятная проблема, нужен ревью), `info` (рекомендация).
- **FP**: риск ложных срабатываний (low / med / high).
- **MVP**: ✅ входит в первый релиз, ⏳ после калибровки, 🧪 экспериментальное (выключено по умолчанию).
- **Источник**: уровни A/B/C по разделу 2. (doc) страница документации; (code) код ядра или существующего стандарта. 🔎 означает, что первоисточник A/B ещё нужно найти: без него правило не выходит из 🧪.

### 4.1 Запросы в циклах (N+1), PHP

| ID | Правило | Обнаружение | Sev | FP | MVP | Источник |
|---|---|---|---|---|---|---|
| P-LOOP-01 | `load()` модели или `get()`/`getById()` репозитория внутри цикла | тип вызывающего `AbstractModel`/`*RepositoryInterface` + контекст цикла | error | low | ✅ | A: [Adobe: extension performance](https://developer.adobe.com/commerce/php/best-practices/extensions/security) («Use data collections»), [Adobe code review](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review); B: [AbstractModel::load()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L564-L568), [ProductRepository::getById()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ProductRepository.php#L308-L328) |
| P-LOOP-02 | Создание и загрузка коллекции (`create()` + `load`/`getItems`/`getSize`/`getFirstItem`) внутри цикла | тип `AbstractDb` collection + цикл | error | low | ✅ | A: [Adobe: extension performance](https://developer.adobe.com/commerce/php/best-practices/extensions/security); B: [AbstractDb::load()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836), [getSize()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441) |
| P-LOOP-03 | `save()`/`delete()` модели или репозитория в цикле | тип + цикл | info | med | ✅ | A: [Adobe: extension performance](https://developer.adobe.com/commerce/php/best-practices/extensions/security), [Technical guidelines 6.4.3.2](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) (batch-операции в сервисных контрактах); B: [AbstractModel::save()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L680-L684), [delete()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L881-L885) |
| P-LOOP-04 | `getProductUrl()` / `getUrlModel()->getUrl()` в цикле по коллекции без `addUrlRewrite()` | вызов на элементе коллекции продуктов + отсутствие `addUrlRewrite` на этой коллекции в методе | warning | med | ⏳ | B: [Product/Url.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/Product/Url.php#L154-L183) (поиск rewrite на каждый продукт), [DbStorage](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/UrlRewrite/Model/Storage/DbStorage.php#L107-L131), [addUrlRewrite()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L1455-L1504) |
| P-LOOP-05 | `getCategoryIds()` в цикле без `addCategoryIds()` на коллекции | аналогично; `addCategoryIds()` до `load()` ничего не делает, исправлением считается только вызов после загрузки | warning | med | ⏳ | B: [Product::getCategoryIds()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/Product.php#L753-L769), [CategoryLink](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/CategoryLink.php#L54-L69) (запрос на продукт), [addCategoryIds()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Catalog/Model/ResourceModel/Product/Collection.php#L2163-L2200) |
| P-LOOP-06 | `getAttributeText()` / `getFrontend()->getValue()` в цикле | тип продукта + цикл; стоимость только при source model `Source\Table` | warning | med | ⏳ | B: [magento2#38934](https://github.com/magento/magento2/issues/38934) (Confirmed, без замеров), [Source/Table::getSpecificOptions()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Attribute/Source/Table.php#L106-L119) |
| P-LOOP-07 | Методы composite-типов в цикле: `getUsedProducts`, `getConfigurableAttributes`, `getChildrenIds`, `getParentIdsByChild`, `getUsedProductIds`, `getAssociatedProducts` | тип `Product\Type\AbstractType`-наследника + цикл. `getIdBySku` не метод типа: ловится на `Catalog\Model\Product` и его resource model | warning | med | ✅ | B: [Configurable::getUsedProducts() грузит коллекцию на каждый продукт](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/ConfigurableProduct/Model/Product/Type/Configurable.php#L1411-L1420), [getChildrenIds/getParentIdsByChild принимают массив](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/ConfigurableProduct/Model/ResourceModel/Product/Type/Configurable.php#L159-L208), [Grouped::getAssociatedProducts()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GroupedProduct/Model/Product/Type/Grouped.php#L203-L237); C: ECG LoopSniff |
| P-LOOP-08 | `in_array()` с нелитеральным haystack внутри цикла (O(n·m)) | AST | info | med | ⏳ | B: [magento2#36386](https://github.com/magento/magento2/issues/36386) (5,5 с → 0,013 с), исправление в ядре: [ProductDataMapper](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Elasticsearch/Model/Adapter/BatchDataMapper/ProductDataMapper.php#L372-L391) |

### 4.2 Коллекции и выборки, PHP

| ID | Правило | Обнаружение | Sev | FP | MVP | Источник |
|---|---|---|---|---|---|---|
| P-COL-01 | `count($collection)` вместо `getSize()` | `count()` с аргументом типа collection | warning | low | ✅ | B: [Collection::count() вызывает load()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L858-L862), [AbstractDb::getSize() делает только COUNT](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L433-L441); C: ECG CollectionCountSniff |
| P-COL-02 | `getFirstItem()` без `setPageSize(1)`: неявного `LIMIT 1` нет, грузится вся выборка | вызов на collection, в цепочке или выше в методе нет `setPageSize`/`getSelect()->limit`/`limitPage`. `setCurPage` один лимита не даёт | error | low | 🧪 | B: [Collection::getFirstItem() вызывает load() всей коллекции](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection.php#L305-L315), [_renderLimit() ставит LIMIT только при заданном pageSize](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763); C: ECG GetFirstItemSniff, best-practices skill |
| P-COL-03 | `addAttributeToSelect('*')` | литерал `'*'` у EAV-коллекции | info | med | ⏳ | B (только механизм, без замеров): [`*` выбирает все атрибуты](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L583-L588), [_loadAttributes() читает значения всех](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Eav/Model/Entity/Collection/AbstractCollection.php#L1266-L1297). Ядро само использует `'*'`; C: skill, ECG |
| P-COL-04 | Итерация коллекции без пагинации в cron, CLI, экспорте | класс из `crontab.xml` или `Command` + foreach/load без `setPageSize` | warning | med | ⏳ | B: [load() без pageSize кладёт все строки в _items](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L813-L836), [_renderLimit()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Data/Collection/AbstractDb.php#L756-L763). A-источника нет |
| P-COL-05 | `fetchAll()`/`fetchCol()` на Select без `limit()` | тип `Select` | info | high | 🧪 | (code) [ECG FetchAllSniff](https://github.com/magento-ecg/coding-standard) |

### 4.3 Жизненный цикл объектов и DI, PHP

| ID | Правило | Обнаружение | Sev | FP | MVP | Источник |
|---|---|---|---|---|---|---|
| P-DI-01 | **Сессия или CookieManager в конструкторе без Proxy.** Конструктор `SessionManager` стартует сессию (`session_start()`) на каждом запросе, где класс создаётся | параметр конструктора типа `SessionManagerInterface`, `*\Session`, `CookieManagerInterface` + в di.xml нет `\Proxy` для этого аргумента. Исправление: Proxy через di.xml, не type hint (§2.5, MCS) | warning (сессия) / info (CookieManager) | med | ✅ | A: [Technical guidelines 9.10](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) (не внедрять Request, Session, Cookie в конструктор); B: [SessionManager::__construct() вызывает start()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Session/SessionManager.php#L100-L125), [start()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Session/SessionManager.php#L162-L208); C: [Atwix: session depersonalization](https://www.atwix.com/magento-2/what-is-session-depersonalization/) |
| P-DI-02 | Конструктор делает что-то кроме присваивания зависимостей и валидации аргументов | AST `__construct`: разрешены `$this->x = $param`, `parent::__construct`, валидация (`if`/`foreach` с `throw`), BC-fallback `?: ObjectManager::getInstance()` | warning | med | 🧪 | A: [Technical guidelines 2.3](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) |
| P-DI-03 | Диспатч события в конструкторе | `->dispatch(` в `__construct` | error | low | ✅ | A: [Technical guidelines 2.3.2](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) |
| P-DI-04 | Тяжёлые зависимости в конструкторе CLI-команды без Proxy: замедляют каждый `bin/magento` | команда в аргументе `commands` у `CommandList` в di.xml, параметры конструктора без `\Proxy`; команды через `CommandLoader` ленивые и исключаются | info | med | ⏳ | A: [Adobe: Proxies](https://developer.adobe.com/commerce/php/development/components/proxies); B: [Cli создаёт все команды CommandList на каждом запуске](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Console/Cli.php#L196-L232). Учесть runtime-альтернативу [magento-cli-auto-proxy](https://packagist.org/packages/run_as_root/magento-cli-auto-proxy) |
| P-DI-05 | Дорогая зависимость широко используемого класса без Proxy | эвристика: зависимость с тяжёлым конструктором (БД, HTTP, сессия) | info | high | 🧪 | [Adobe: Proxies](https://developer.adobe.com/commerce/php/development/components/proxies) |
| P-DI-06 | Прямой `ObjectManager::getInstance()` в PHP-классах (в шаблонах уже ловит MCS) | статический вызов; исключения по документации: BC-fallback в конструкторе, factories и proxies, `__wakeup`/`__sleep` | info | low | ✅ | A: [Adobe: Object Manager, Usage rules](https://developer.adobe.com/commerce/php/development/components/object-manager/); C: ECG `ObjectManagerUsage` |

### 4.4 Full Page Cache и кэш, PHP

| ID | Правило | Обнаружение | Sev | FP | MVP | Источник |
|---|---|---|---|---|---|---|
| P-FPC-01 | `$_isScopePrivate` в блоке: свойство устарело и работает неправильно | присваивание или объявление свойства в наследнике `AbstractBlock` | info | low | ✅ | A: [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content); B: [AbstractBlock: @deprecated](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L158-L166) |
| P-FPC-02 | Блок читает данные клиента из сессии (`getCustomer`, `getCustomerId`, `getQuote`) | наследник `AbstractBlock`/ViewModel + вызовы на типах сессий | warning | med | 🧪 | A: [Technical guidelines 9.4](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) (в серверном HTML нет данных пользователя); B: [DepersonalizePlugin очищает сессию клиента на кэшируемых страницах](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/Model/Layout/DepersonalizePlugin.php#L101-L112); C: [FPC strategy](https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html) |
| P-FPC-03 | В HTTP context добавлено значение, специфичное для пользователя (ID клиента, email, сессия, корзина, время): FPC дробится на запись под каждое значение | `Http\Context::setValue()` с аргументом из таких источников | error | med | ⏳ | A: [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) («Context variables should not be specific to individual users»); B: [Http/Context.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Http/Context.php#L107-L135) |
| P-FPC-04 | Значение HTTP context у гостей отличается от default: первый хит каждого гостя некэшируемый | `setValue($name, $value, $default)`, где `$value` может отличаться от `$default` для анонимных | warning | med | 🧪 | B: [Http/Context.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Http/Context.php#L70-L78), [varnish6.vcl](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/etc/varnish6.vcl#L180-L187) |
| P-FPC-05 | Код frontend или graphql ставит no-cache или private заголовки: ответ становится некэшируемым, проверить, что это намеренно | `setNoCacheHeaders()`, `setPrivateHeaders()`, `setHeader('Cache-Control'/'Pragma', ...)` в GET-доступных контроллерах и query-резолверах | warning | med | ✅ | B: [varnish6.vcl](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/etc/varnish6.vcl#L191-L200), [PageCache Kernel](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/PageCache/Kernel.php#L154-L183); A: [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content) (no-cache как способ отключить кэш) |
| P-FPC-06 | Изменение состояния в GET-контроллере (`HttpGetActionInterface` без `HttpPostActionInterface`, внутри save/delete/добавление в корзину) | интерфейсы класса + вызовы в `execute()` | error | med | ✅ | A: [Adobe: Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content), [Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content) («Use only HTTP POST or PUT methods to change state») |
| P-FPC-07 | Блок с `cache_lifetime`, не переопределивший `getCacheKeyInfo()`, но читающий request, сессию или registry: в block_html попадает неверный вариант | свойство или аргумент `cache_lifetime` + отсутствие override (или своего `cache_key`, но тогда проверить, из чего он собран) + чтения | error | med | ⏳ | B: [AbstractBlock](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L1037-L1074), [Template.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/Template.php#L327-L336) |
| P-FPC-08 | Блок рендерит данные сущности, но не реализует `IdentityInterface` (страница не инвалидируется). Корректность кэша, а не скорость | эвристика: блок грузит сущности через репозиторий или коллекцию, нет `getIdentities` | warning | high | 🧪 | C: [FPC strategy](https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html) |
| P-FPC-09 | Очистка кэша в коде: `cleanType('full_page')`, `App\Cache::clean()` без тегов (чистит все frontend-кэши) | вызовы на типах `TypeListInterface`, `CacheInterface`, `App\Cache` | error | low | ✅ | B: [App/Cache::clean() без тегов](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/App/Cache.php#L96-L111), [full_page clean](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Cache/Type.php#L51-L55) → [полный purge Varnish](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/CacheInvalidate/etc/events.xml#L27-L29); C: [FPC strategy](https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html) |
| P-FPC-10 | `reindexAll()` в observer, плагине или контроллере. `reindexList()`/`reindexRow()` не ловится: это документированный паттерн Update on Save | тип `IndexerInterface` + класс-observer/plugin/controller | error | low | ✅ | A: [Adobe: Indexing](https://developer.adobe.com/commerce/php/development/components/indexing/), [indexer configuration](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration); B: [Indexer::reindexAll() синхронно перестраивает весь индекс](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Indexer/Model/Indexer.php#L452-L494) |

### 4.5 Плагины, observers и внешние вызовы, PHP + XML

| ID | Правило | Обнаружение | Sev | FP | MVP | Источник |
|---|---|---|---|---|---|---|
| P-PLG-01 | around-плагин, который безусловно вызывает `$proceed` и не меняет аргументы: хватило бы before/after | AST: `$proceed(...)` вызывается ровно один раз, не внутри условия, аргументы проброшены как есть | warning | low | 🧪 | A: [Adobe: Plugins](https://developer.adobe.com/commerce/php/development/components/plugins) («avoid using around method plugins when they are not required») |
| X-PLG-02 | Плагин на горячие методы рендера: `AbstractBlock::toHtml`, `getChildHtml`, `Layout::renderElement`, `Template::fetchView` | di.xml `<type name>` + методы плагина | warning | med | ⏳ | A: [Adobe: Plugins](https://developer.adobe.com/commerce/php/development/components/plugins) (плагин действует на всех наследников); B: [toHtml() на каждый блок](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L666-L692), [renderElement()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L555-L571). Что это медленно, вывод, а не прямое утверждение источника |
| P-EXT-01 | Синхронный внешний HTTP-вызов (`curl_*`, `Curl`, Guzzle, `file_get_contents('http…')`) в классах frontend-пути (контроллеры, блоки, observers и плагины frontend или global области) | типы и функции + область класса из XML-индекса | warning | med | ⏳ | A: [Adobe backend performance](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/backend-performance) |
| P-EXT-02 | То же в observers событий заказа и checkout (`sales_order_*`, `checkout_*`, `sales_model_service_quote_submit_*`): оформление заказа ждёт чужой API | events.xml + HTTP-клиент в классе observer | warning | high | 🧪 | A (общий): [Adobe backend performance](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/backend-performance). Источника A/B именно про checkout нет; синхронные вызовы налогов и оплаты легитимны |

### 4.6 XML: layout, события, области, секции

| ID | Правило | Обнаружение | Sev | FP | MVP | Источник |
|---|---|---|---|---|---|---|
| X-LAY-01 | `cacheable="false"` у `<block>`: отключает FPC для всех страниц с этим хэндлом. `default.xml`, `catalog_*`, `cms_*` дают error, остальные warning. Allowlist по умолчанию (с M1): `customer_*`, `loginascustomer_*`, `newsletter_manage_*`, `review_customer_*`, `downloadable_customer_*`, `checkout_*`, `multishipping_*`, `sales_*`, `paypal_*`, `vault_*`, `paymentservicespaypal_vault_*`, `wishlist_*`, `catalog_product_compare_*` (персональные хэндлы, которые ядро само делает некэшируемыми, см. `docs/runs/m0-core.md`), настраивается | layout XML; атрибут есть только у `<block>` (не у `<referenceBlock>`); удалённый позже блок FPC не отключает | error/warning | low | 🧪 | A: [Adobe: Page caching](https://developer.adobe.com/commerce/php/development/cache/page/), [Public content](https://developer.adobe.com/commerce/php/development/cache/page/public-content), [Adobe code review](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review); B: [Layout::isCacheable()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L1192-L1210), [LayoutPlugin](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Layout/LayoutPlugin.php#L70-L75); C: [Sam James: non-cacheable blocks](https://www.samdjames.uk/docs/platforms/magento/development/non-cacheable-blocks/) |
| X-LAY-02 | `ttl=` (ESI) у блока в хэндле, привязанном к сущности (`*_id_*`, `*_sku_*`): ядро бросает LogicException | layout XML | error | low | ✅ | B: [MergePlugin.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/PageCache/Model/Layout/MergePlugin.php#L42-L54), [Result/Page.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Result/Page.php#L236-L252); A: [Adobe: Varnish ESI](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/varnish/use-varnish-esi) |
| X-EVT-01 | Observer на `model_load_after` (каждая загрузка любой модели) или `<prefix>_load_after` (каждая загрузка сущности этого типа) | events.xml; сообщение различает два случая | warning | low | ✅ | B: [AbstractModel::_afterLoad()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L590-L609); A: [Adobe: observers](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) |
| X-EVT-02 | Observer на общие `model_*` события (`model_save_*`, `model_delete_*`, `model_save_commit_after`): срабатывает для **каждой** модели, а не одной сущности | events.xml | warning | low | ✅ | B: [AbstractModel save events](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L691-L730), [delete events](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Model/AbstractModel.php#L893-L930) |
| X-EVT-03 | Observer на `core_layout_render_element`, `view_block_abstract_to_html_before`/`_after`: сотни срабатываний на страницу | events.xml; собственный observer PageCache в ядре исключается | warning | low | ✅ | B: [Layout::renderElement()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Layout.php#L555-L571), [AbstractBlock::toHtml()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L666-L692); A: [Adobe: observers](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) |
| X-AREA-01 | Observer или плагин объявлен в глобальной области (`etc/events.xml`, `etc/di.xml`), хотя класс или событие относятся к одной области: утяжеляет бутстрап | глобальный файл + эвристика области (namespace `Controller\Adminhtml`, события `adminhtml_*`/`controller_action_*` и т.д.) | info | med | ⏳ | A: [Adobe: extension performance](https://developer.adobe.com/commerce/php/best-practices/extensions/security), [Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/), [Adobe: observers](https://developer.adobe.com/commerce/php/best-practices/extensions/observers) |
| X-SEC-01 | `sections.xml`: `<action name="*">` перезагружает customer-секции на каждом POST. Секция `*` или action без перечисления секций перезагружает все секции на этом действии | sections.xml; allowlist по умолчанию для логина, логаута, создания и редактирования аккаунта (ядро делает так само) | error (`name="*"`) / warning | low | ✅ | A: [Adobe: Private content](https://developer.adobe.com/commerce/php/development/cache/page/private-content); B: [section-config.js](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/view/frontend/web/js/section-config.js#L37-L54), [SectionConfigConverter](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/CustomerData/SectionConfigConverter.php#L18-L35); C: [dev.to: customer sections](https://dev.to/magevanta/magento-2-customer-data-sections-localstorage-performance-optimization-4ci9) |
| T-01 | `$block->getLayout()->createBlock()` в phtml: нарушение архитектурного правила, возможное влияние на производительность (`$this->helper()` уже ловит MCS) | phtml (AST PHP-вставок) | info | low | ✅ | A: [Technical guidelines 6.2.6](https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines) |
| T-02 | ViewModel в кэшируемом шаблоне грузит сущности, но не отдаёт cache tags | phtml + тип ViewModel без `IdentityInterface` | warning | high | 🧪 | [Hyvä: view model cache tags](https://docs.hyva.io/hyva-themes/performance/view-model-cache-tags.html) |

### 4.7 XML: GraphQL, очереди, cron, индексаторы, схема БД

| ID | Правило | Обнаружение | Sev | FP | MVP | Источник |
|---|---|---|---|---|---|---|
| X-GQL-01 | Поле `Query` с `@cache` без `cacheIdentity` и без `cacheable: false`: делает некэшируемым **весь** ответ | `.graphqls` (объявление и `extend type Query`) | error | med | ✅ | B: [CacheableQueryHandler.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/CacheableQueryHandler.php#L59-L85), [Query/Resolver plugin](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/Plugin/Query/Resolver.php#L43-L82); A: [Adobe: identity class](https://developer.adobe.com/commerce/webapi/graphql/develop/identity-class) |
| X-GQL-04 | Поле storefront-запроса `Query` вообще без `@cache` (риск устаревших данных или некэшируемости, если поле одно в запросе). Выделено из X-GQL-01 на M1 | `.graphqls` | warning | high | 🧪 | Нет A/B-источника, отличающего storefront-запрос от приватного; ядро намеренно оставляет cart и customer без `@cache` |
| X-GQL-02 | Резолвер поля на типе, который отдаётся внутри списка, реализует `ResolverInterface`, а не `BatchResolverInterface`: N+1 на вложенных объектах | `.graphqls` (поля типов, достижимых через поля-списки) + класс резолвера | warning | med | ⏳ | A: [Adobe: GraphQL resolvers](https://developer.adobe.com/commerce/webapi/graphql/develop/resolvers) |
| X-GQL-03 | Добавлены `idFactorProviders` для `CacheIdCalculator`: размножают ключи кэша GraphQL | di.xml | info | med | 🧪 | A: [Adobe: GraphQL caching](https://developer.adobe.com/commerce/webapi/graphql/usage/caching/) описывает это как штатную точку расширения, без предупреждения о производительности; B: [CacheIdCalculator](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/GraphQlCache/Model/CacheId/CacheIdCalculator.php#L89-L104) |
| X-MQ-01 | `communication.xml`: topic с `schema=` или парой `request`+`response` без `is_synchronous="false"`. По умолчанию синхронный, на AMQP это RPC-паблишер, который ждёт ответа | communication.xml | warning | low | ✅ | B: [Communication Converter.php](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Communication/Config/Reader/XmlReader/Converter.php#L127-L153), [Rpc/Publisher](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/MessageQueue/Rpc/Publisher.php#L105-L125); A: [Adobe: MQ configuration](https://developer.adobe.com/commerce/php/development/components/message-queues/configuration) |
| X-MQ-02 | `queue_consumer.xml`: `maxIdleTime`/`sleep` заданы, а `maxMessages` нет, при этом консьюмер запускается не из cron (из cron без `maxMessages` подставляется 10000) | queue_consumer.xml | info | high | 🧪 | B: [ConsumersRunner](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/MessageQueue/Model/Cron/ConsumersRunner.php#L176-L198), [Consumer::process()](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/MessageQueue/Consumer.php#L129-L145); A: [Adobe: MQ configuration](https://developer.adobe.com/commerce/php/development/components/message-queues/configuration) |
| X-CRON-01 | Cron-задача `* * * * *` или `*/1` в группе `default`: конкурирует с задачами ядра | crontab.xml | info | high | 🧪 | [Adobe: backend performance](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/backend-performance) |
| X-IDX-01 | Свой индексатор без подписки mview: не может нормально работать в «Update by Schedule» | `indexer.xml` `view_id` без `<view>` в `mview.xml` | warning | low | ✅ | A: [Adobe: custom indexer](https://developer.adobe.com/commerce/php/development/components/indexing/custom-indexer), [indexer configuration](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration); B: [Mview View::load() бросает исключение без view](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Mview/View.php#L185-L189) |
| X-SETUP-01 | Тяжёлая работа в `Setup/Recurring.php` или `RecurringData.php`: выполняется при **каждом** `setup:upgrade` | файл + в теле коллекции, циклы, save, reindex | warning | low | ✅ | A: [Adobe: extension lifecycle](https://developer.adobe.com/commerce/php/development/prepare/extension-lifecycle); B: [Installer запускает Recurring каждого модуля](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/setup/src/Magento/Setup/Model/Installer.php#L1159-L1174) |
| X-DB-01 | Таблица в `db_schema.xml` без первичного ключа (проблемы репликации в кластере) | db_schema.xml | error | low | ✅ | A: [Adobe: extension performance](https://developer.adobe.com/commerce/php/best-practices/extensions/security) |

### 4.8 Опционально: конфигурация окружения (если `app/etc/env.php` доступен)

| ID | Правило | Sev | MVP | Источник |
|---|---|---|---|---|
| C-01 | `cron_consumers_runner.max_messages` = 0: консьюмер никогда не завершается (Adobe не рекомендует) | warning | ⏳ | A: [Adobe: manage message queues](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues); B: [ConsumersRunner](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/MessageQueue/Model/Cron/ConsumersRunner.php#L141-L161), [Consumer](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/MessageQueue/Consumer.php#L134-L135) |

Группа выключена по умолчанию: `env.php` обычно не в репозитории, и это не код.

### 4.9 Сознательно НЕ реализуем

| Идея | Причина |
|---|---|
| `array_merge` в foreach | уже есть в MCS (`Magento2.Performance.ForeachArrayMerge`) |
| ObjectManager и `$this->helper()` в шаблонах | уже есть в MCS (`Templates.ObjectManager`, `Templates.ThisInTemplate`) |
| Proxy или Interceptor, явно запрошенный в конструкторе | уже есть в MCS (`Classes.DiscouragedDependencies`) |
| `sleep`, `usleep`, `session_*` | уже есть в MCS (`Functions.DiscouragedFunction`) |
| `count()` в условии `for`, try/catch в цикле | эффект мизерный, есть в ECG и guidelines; шум без пользы |
| Медленный SQL (GROUP BY, DISTINCT, LIKE, UNION) | очень высокий FP, конструкции часто нужны |
| FK без индекса в db_schema | InnoDB сам создаёт индекс для внешнего ключа; польза сомнительна |
| Registry, `shared="false"`, scopeConfig в цикле, `getMediaGalleryImages` в цикле, коллекция продуктов без фильтров visibility/status, много `<update handle>`, `cache_lifetime=null` | нет первоисточника, связывающего это с производительностью, либо это не анти-паттерн само по себе |

**Итого:** 53 правила. Стандартный набор (✅): **24**. После калибровки (⏳): 14 (включая C-01). Экспериментальные (🧪): 15. Статусы, severity и источники сверены аудитом на M0 (2026-10-21), подробности и обоснование каждого изменения в [`docs/source-audit.md`](source-audit.md). На M1 X-GQL-01 разделено на X-GQL-01 (✅) и X-GQL-04 (🧪). На M2 по итогам [калибровки](calibration.md) X-LAY-01, P-COL-02, P-DI-02, P-FPC-02 и P-PLG-01 ушли в 🧪, P-DI-01 понижено до warning, P-LOOP-03, P-DI-06, P-FPC-01 и T-01 до info.

---

## 5. Документация правил

Для каждого правила файл `docs/rules/<ID>.md` по шаблону:
1. Что ловит (одно предложение).
2. Почему это вредит производительности (с цифрами, если есть в источнике).
3. Пример «плохо» и «хорошо» (реальный Magento-код).
4. Как подавить, если это осознанное решение.
5. Известные ложные срабатывания.
6. **Basis**: источники с уровнями A/B/C и одной фразой, что именно подтверждает вывод (раздел 2).

Сообщение находки содержит короткое объяснение и ссылку на страницу правила.

---

## 6. Интеграции

| Интеграция | Как |
|---|---|
| PHPStan в проекте | extension-installer подключает правила автоматически. PHP-находки видны в PhpStorm через его встроенную интеграцию с PHPStan |
| Одна команда | `vendor/bin/magento-perf-check` запускает XML-анализ и PHPStan с нашими правилами, отдаёт объединённый отчёт |
| GrumPHP | PHP-правила работают через штатную задачу `phpstan`. Для полного прогона нужен документированный `shell`-таск или своё расширение GrumPHP (решить на M2). Пример конфига в README, с отсылкой к [milindsingh/magento2-grumphp](https://github.com/milindsingh/magento2-grumphp) как популярному шаблону |
| CaptainHook | пример конфигурации |
| GitHub Actions | готовый workflow: SARIF в Code Scanning + annotations в PR |
| GitLab CI | формат Code Quality |
| Агенты для кода | `AGENTS.md` и skill-файл в репозитории: «перед коммитом запусти `magento-perf-check`, исправь находки уровня error, для остальных объясни решение». Пример: AGENTS.md в [MageLens](https://github.com/VitaliyBoyko/MageLens) и [elgentos/magento2-playwright](https://github.com/elgentos/magento2-playwright) |

---

## 7. Калибровка точности (обязательна до публичного релиза)

1. **Корпус:** ядро Magento (`app/code/Magento`), Mage-OS и 20–30 самых скачиваемых open-source модулей Magento 2 на Packagist (`type: magento2-module`, сортировка по загрузкам). Конкретный список зафиксировать на M2.
2. **Ручная проверка выборки:** до 30 случайных находок на правило, каждая размечается true positive / false positive.
3. **Пороги:** правило с severity `error` должно давать точность ≥ 90%, `warning` ≥ 75%. Ниже порога правило понижается в severity или уходит в 🧪.
4. **Метрики** публикуются в `docs/calibration.md` (корпус, версия, точность по правилам). Это аргумент доверия для пользователей.
5. **Реальные находки в open-source модулях** отправляются авторам как PR с исправлением и упоминанием инструмента. Это главный канал распространения.

---

## 8. Совместимость и качество

- PHP 8.1+ (минимальный PHP для Magento 2.4.6). Анализируемый код: Magento 2.4.4+ и Mage-OS.
- **PHPStan 1.12 и 2.x.** Проверено на чистой Magento 2.4.9: в `composer.json` проекта `require-dev` содержит `"phpstan/phpstan": "^1.9"`, в `composer.lock` стоит 1.12.33. Расширение только под PHPStan 2.x не установится в стандартный проект без правки этого ограничения. На M0 решить в ADR: поддерживать обе мажорные версии (общий код правил, адаптеры) или 1.12 как минимум. Проверить ограничения в 2.4.4–2.4.8 и Mage-OS.
- В той же установке 2.4.9: `nikic/php-parser` v5.8.0, `magento/magento-coding-standard` 40, `squizlabs/php_codesniffer` 3.13.5.
- Тесты: на каждое правило фикстуры с положительными **и отрицательными** кейсами (похоже на нарушение, но не оно). Для PHP через `RuleTestCase` PHPStan, для XML golden-тесты.
- PHPStan max level на собственном коде, CI на матрице PHP-версий.
- Семантическое версионирование. Добавление правила в стандартный набор считается minor-изменением, и это выносится в changelog, потому что у пользователей может упасть CI.

---

## 9. Этапы

| Этап | Результат | Критерий готовности |
|---|---|---|
| **M0. Скелет** | Аудит источников: у каждого ✅/⏳ правила есть A/B, иначе 🧪. Пакет, `magento-perf-check`, XML-анализатор и PHPStan-extension в одном отчёте. 3 сквозных правила: X-LAY-01, P-COL-02, P-LOOP-01. ADR по зависимости от bitExpert/phpstan-magento и по поддержке PHPStan 1.12 + 2.x (раздел 8) | Прогон на ядре Magento. Число находок и время работы |
| **M1. MVP** | Все правила ✅ (29), документация правил, baseline, форматы table/JSON/SARIF/Checkstyle | Фикстуры зелёные, документация на каждое правило |
| **M2. Калибровка** | Корпус, ручная разметка, `docs/calibration.md`, корректировка severity. GrumPHP и GitHub Actions | Пороги точности выполнены |
| **M3. Релиз 0.1** | Публикация на Packagist, README с реальными примерами находок, AGENTS.md и skill. Первые PR авторам модулей | Пакет ставится и работает на чистом проекте за 2 минуты |
| **M4. Расширение** | Правила ⏳, межметодный анализ циклов через PHPStan Collectors | Каждое новое правило проходит калибровку |
| **M5 (опц.)** | Экспериментальные правила 🧪, Rector-автоисправления для простых случаев (`count()` → `getSize()`, `getFirstItem` + `setPageSize(1)`). Готового Magento-набора Rector нет | |

---

## 9a. Среда разработки

- Репозиторий: [SilinMykola/magento-perf-check](https://github.com/SilinMykola/magento-perf-check), локальная копия `~/projects/magento-perf-check` (macOS, PHP 8.5 и Composer 2.10 на хосте).
- Тестовый Magento: `~/projects/magento-2.4.9.ddev` (DDEV, PHP 8.4 в контейнере, Mutagen). Vendor синхронизирован на хост.
- **Инструмент запускается на хосте против папки Magento**, без установки в проект: `php bin/magento-perf-check --project=~/projects/magento-2.4.9.ddev`. Статическому анализу не нужны контейнер и база.
- **Не ставить пакет в Magento-проект через composer path-репозиторий с symlink.** Mutagen в DDEV синхронизирует символические ссылки в portable-режиме, и ссылка из `vendor` наружу проекта может не синхронизироваться. Проверка установки через composer выполняется на M3 в отдельной копии проекта, через path-репозиторий в режиме копирования (`"symlink": false`).
- Поэтому опция `--project=<path>` обязательна с M0: она же даёт режим запуска в CI без установки модуля.

---

## 10. Вне скоупа
- Runtime-профилирование (для этого есть debug bar-модули).
- Производительность фронтенда (JS, CSS, картинки).
- Настройки инфраструктуры (Redis, Varnish, MySQL, PHP), кроме опциональной группы C-*.
- Безопасность и общий code style (это MCS, ECG и т.д.).

---

## 11. Открытые вопросы к владельцу
1. Имя проекта и vendor на Packagist.
2. Лицензия: MIT (рекомендую для инструментов разработчика) или OSL-3.0.
3. Зависимость от bitExpert/phpstan-magento: жёсткая или рекомендуемая (решается в ADR на M0).
4. Предлагать ли позже часть XML-правил в magento-coding-standard как upstream-вклад.

---

## 12. Источники (все открыты или прочитаны в коде)
- Adobe Technical guidelines: https://developer.adobe.com/commerce/php/coding-standards/technical-guidelines
- Adobe extension security & performance: https://developer.adobe.com/commerce/php/best-practices/extensions/security
- Adobe code review best practices: https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/code-review
- Adobe backend performance: https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/backend-performance
- Adobe indexer configuration: https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration
- Adobe public / private content: https://developer.adobe.com/commerce/php/development/cache/page/public-content , https://developer.adobe.com/commerce/php/development/cache/page/private-content
- Adobe plugins / proxies / events: https://developer.adobe.com/commerce/php/development/components/plugins , https://developer.adobe.com/commerce/php/development/components/proxies , https://developer.adobe.com/commerce/php/development/components/events-and-observers/ , https://developer.adobe.com/commerce/php/best-practices/extensions/observers
- Adobe GraphQL: https://developer.adobe.com/commerce/webapi/graphql/develop/resolvers , https://developer.adobe.com/commerce/webapi/graphql/usage/caching/ , https://developer.adobe.com/commerce/webapi/graphql/develop/identity-class
- Adobe MQ, lifecycle, custom indexer, Varnish ESI: https://developer.adobe.com/commerce/php/development/components/message-queues/configuration , https://developer.adobe.com/commerce/php/development/prepare/extension-lifecycle , https://developer.adobe.com/commerce/php/development/components/indexing/custom-indexer , https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/varnish/use-varnish-esi
- Hyvä view model cache tags: https://docs.hyva.io/hyva-themes/performance/view-model-cache-tags.html
- FPC strategy (community docs): https://docs.magento-opensource.com/developer/guide-howto-full-page-cache-strategy.html
- Sam James, non-cacheable blocks: https://www.samdjames.uk/docs/platforms/magento/development/non-cacheable-blocks/
- Atwix, session depersonalization: https://www.atwix.com/magento-2/what-is-session-depersonalization/
- Customer sections performance: https://dev.to/magevanta/magento-2-customer-data-sections-localstorage-performance-optimization-4ci9
- magento2 issues: https://github.com/magento/magento2/issues/38934 , https://github.com/magento/magento2/issues/36386
- Код ядра: https://github.com/magento/magento2, коммит `a385d02f60ee6dfa052f12cdc7d82e956581fba5` (файлы и строки по ссылкам в таблицах)
- Существующие стандарты: https://github.com/magento/magento-coding-standard , https://github.com/magento-ecg/coding-standard , https://github.com/bitExpert/phpstan-magento
- Best-practices skill: https://github.com/o0mohd0o/magento2-best-practices-skill
- GrumPHP-шаблон: https://github.com/milindsingh/magento2-grumphp
- CLI auto proxy: https://packagist.org/packages/run_as_root/magento-cli-auto-proxy
