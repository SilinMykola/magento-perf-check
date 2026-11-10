# Rules

34 rules in the default set and 19 experimental ones, after [calibration](calibration.md) on Magento 2.4.9 core and the 25 most-downloaded Packagist modules. Each ID links to its page with examples, how to suppress it, known false positives and the sources it rests on.

**Sources:** A is official Adobe or Hyvä documentation; B is Magento core code pinned to a commit, or a magento/magento2 issue with a confirmed reproduction or measurements. Every rule in the default set has at least one A or B source. See [What our rules are based on](../README.md#what-our-rules-are-based-on).

Severity is the default; change it per rule in [`magento-perf-check.neon`](configuration.md#configuration-file).

## Default set

| ID | Finds | Severity | Sources |
|---|---|---|---|
| [X-LAY-02](rules/X-LAY-02.md) | ESI ttl on a block in an entity-specific handle | error | A, B |
| [P-LOOP-01](rules/P-LOOP-01.md) | Model load() or repository get()/getById() inside a loop | error | A, B |
| [P-LOOP-02](rules/P-LOOP-02.md) | Collection created and loaded inside a loop | error | A, B |
| [P-LOOP-03](rules/P-LOOP-03.md) | save() or delete() of a model or repository inside a loop | info | A, B |
| [P-LOOP-07](rules/P-LOOP-07.md) | Composite product type methods inside a loop | warning | B |
| [P-COL-01](rules/P-COL-01.md) | count() on a database collection instead of getSize() | warning | B |
| [P-DI-01](rules/P-DI-01.md) | Session or cookie manager injected without a Proxy | warning (session), info (cookie manager) | A, B |
| [P-DI-03](rules/P-DI-03.md) | Event dispatched in a constructor | error | A |
| [P-DI-06](rules/P-DI-06.md) | Direct ObjectManager::getInstance() in PHP classes | warning | A |
| [P-FPC-01](rules/P-FPC-01.md) | $_isScopePrivate in a block | info | A, B |
| [P-FPC-05](rules/P-FPC-05.md) | No-cache or private headers on cacheable requests | warning | A, B |
| [P-FPC-06](rules/P-FPC-06.md) | State change in a GET-only controller | error | A |
| [P-FPC-09](rules/P-FPC-09.md) | Full page cache or all caches cleaned from code | error | B |
| [P-FPC-10](rules/P-FPC-10.md) | reindexAll() in an observer, plugin or controller | error | A, B |
| [T-01](rules/T-01.md) | getLayout()->createBlock() in a template | info | A |
| [X-EVT-01](rules/X-EVT-01.md) | Observer on model_load_after or <entity>_load_after | warning | A, B |
| [X-EVT-02](rules/X-EVT-02.md) | Observer on generic model save/delete events | warning | B |
| [X-EVT-03](rules/X-EVT-03.md) | Observer on per-block render events | warning | A, B |
| [X-SEC-01](rules/X-SEC-01.md) | sections.xml invalidates all sections or on every POST | error (`name="*"`), warning (all sections) | A, B |
| [X-GQL-01](rules/X-GQL-01.md) | GraphQL Query field with @cache but no cacheIdentity | error | A, B |
| [X-MQ-01](rules/X-MQ-01.md) | Message queue topic synchronous by default | warning | A, B |
| [X-IDX-01](rules/X-IDX-01.md) | Indexer without an mview subscription | warning | A, B |
| [X-SETUP-01](rules/X-SETUP-01.md) | Heavy work in Setup/Recurring or RecurringData | warning | A, B |
| [X-DB-01](rules/X-DB-01.md) | db_schema.xml table without a primary key | error | A |
| [P-PLG-01](rules/P-PLG-01.md) | around plugin where before/after would do | warning | A |
| [P-LOOP-05](rules/P-LOOP-05.md) | Product getCategoryIds() inside a loop | warning | B |
| [P-LOOP-09](rules/P-LOOP-09.md) | Repository getList() with search criteria once per item of a loop | warning | A |
| [P-LOOP-10](rules/P-LOOP-10.md) | Resource model load() inside a loop | warning | A, B |
| [P-LOOP-11](rules/P-LOOP-11.md) | ObjectManager get() or create() inside a loop | error | A, B |
| [P-IDX-01](rules/P-IDX-01.md) | reindexRow()/reindexList() in request code without isScheduled() | warning | A, B |
| [X-LAY-03](rules/X-LAY-03.md) | Block with cacheable="false" on the default, catalog or CMS handle | error | A, B |
| [X-DB-02](rules/X-DB-02.md) | db_schema.xml index that duplicates a key or another index | info | A |
| [X-AREA-01](rules/X-AREA-01.md) | Observer on an admin event declared in the global area | info | A |
| [X-GQL-03](rules/X-GQL-03.md) | GraphQL cache id factor added to CacheIdCalculator | info | A, B |

## Experimental (off by default)

These are not in the default set: calibration measured them below their precision threshold, or they have too few graded findings to decide. Enable one with `enabled: true` in `magento-perf-check.neon`; with PHPStan directly, also include `extension-experimental.neon`.

| ID | Finds | Severity | Sources |
|---|---|---|---|
| [X-LAY-01](rules/X-LAY-01.md) | Block with cacheable="false" in a frontend layout, other than the X-LAY-03 handles | warning | A, B |
| [P-COL-02](rules/P-COL-02.md) | getFirstItem() on a database collection without setPageSize() | error | B |
| [P-DI-02](rules/P-DI-02.md) | Constructor does more than assign dependencies | warning | A |
| [P-FPC-02](rules/P-FPC-02.md) | Block or view model reads customer data from the session | warning | A, B |
| [P-FPC-03](rules/P-FPC-03.md) | Value specific to a visitor or a moment in the HTTP context | error | A, B |
| [X-GQL-02](rules/X-GQL-02.md) | Database query in a resolver of a field on a type returned inside a list | warning | A |
| [P-LOOP-04](rules/P-LOOP-04.md) | Product URL built inside a loop without addUrlRewrite() | warning | B |
| [P-LOOP-06](rules/P-LOOP-06.md) | getAttributeText() or the table source getOptionText() inside a loop | warning | B |
| [P-COL-03](rules/P-COL-03.md) | addAttributeToSelect('*') on an EAV collection | info | B |
| [X-PLG-02](rules/X-PLG-02.md) | Plugin on a method that runs for every rendered block or layout element | info | A, B |
| [P-DI-04](rules/P-DI-04.md) | Console command in CommandList with dependencies that are not lazy | info | A, B |
| [P-COL-04](rules/P-COL-04.md) | Collection walked without a page size in a cron job or console command | warning | B |
| [T-03](rules/T-03.md) | JavaScript function declared once per item in a template | warning | C |
| [T-04](rules/T-04.md) | Result of $viewModels->require() never used in a template | warning | C |
| [S-PERF-01](rules/S-PERF-01.md) | array_merge() inside a loop, through magento-coding-standard | warning | C |
| [S-TPL-01](rules/S-TPL-01.md) | $this or a helper in a template, through magento-coding-standard | warning | C |
| [S-TPL-02](rules/S-TPL-02.md) | Object manager in a template, through magento-coding-standard | warning | C |
| [S-DI-01](rules/S-DI-01.md) | Proxy or Interceptor requested in a constructor, through magento-coding-standard | warning | C |
| [S-FN-01](rules/S-FN-01.md) | sleep(), time_nanosleep() or session_*() call, through magento-coding-standard | warning | C |

## Planned

Rules marked ⏳ (after calibration) in the [SPEC catalog](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/SPEC.md) are not implemented yet.
