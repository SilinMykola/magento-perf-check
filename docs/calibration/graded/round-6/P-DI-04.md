# P-DI-04: calibration sample

30 of 74 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/ADM/QuickDevBar/Console/Command/EnableToolBar.php:15` (extended)

[P-DI-04] ADM\QuickDevBar\Console\Command\EnableToolBar is created on every bin/magento run, whichever command is asked for, and so are its dependencies $resourceConfig, $eventManager, $writer, $arrayManager. Configure a Proxy in di.xml for those that are costly to create.

```
     9  use Magento\Catalog\Api\Data\ProductInterface;
    10  
    11  /**
    12   * Class ProductAttributesCleanUp
    13   * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
    14   */
>   15  class EnableToolBar extends AbstractStatusToolbar
    16  {
    17      /**
    18       * @var string
    19       */
    20      protected $name='dev:quickdevbar:enable';
    21  
```

## 2. `app/code/Swissup/Marketplace/Console/Command/ChannelAuthKeyAddCommand.php:9` (extended)

[P-DI-04] Swissup\Marketplace\Console\Command\ChannelAuthKeyAddCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $channelRepository, $channelManager, $composerHelper, $questionHelper. Configure a Proxy in di.xml for those that are costly to create.

```
     3  namespace Swissup\Marketplace\Console\Command;
     4  
     5  use Symfony\Component\Console\Input\InputInterface;
     6  use Symfony\Component\Console\Output\OutputInterface;
     7  use Swissup\Marketplace\Api\ChannelInterface;
     8  
>    9  class ChannelAuthKeyAddCommand extends ChannelAbstractCommand
    10  {
    11      /**
    12       * {@inheritdoc}
    13       */
    14      protected function configure(): void
    15      {
```

## 3. `app/code/Hyva/Theme/Console/Command/HyvaConfigGenerate.php:26` (extended)

[P-DI-04] Hyva\Theme\Console\Command\HyvaConfigGenerate is created on every bin/magento run, whichever command is asked for, and so are its dependencies $hyvaModulesConfig. Configure a Proxy in di.xml for those that are costly to create.

```
    20  {
    21      /**
    22       * @var HyvaModulesConfig
    23       */
    24      private $hyvaModulesConfig;
    25  
>   26      public function __construct(HyvaModulesConfig $hyvaModulesConfig)
    27      {
    28          parent::__construct();
    29  
    30          $this->hyvaModulesConfig = $hyvaModulesConfig;
    31      }
    32  
```

## 4. `app/code/TIG/PostNL/Console/Config/Dump.php:24` (extended)

[P-DI-04] TIG\PostNL\Console\Config\Dump is created on every bin/magento run, whichever command is asked for, and so are its dependencies $exporter. Configure a Proxy in di.xml for those that are costly to create.

```
    18  
    19      /**
    20       * @var ConfigExporter
    21       */
    22      private $exporter;
    23  
>   24      public function __construct
    25      (
    26          ConfigExporter $exporter,
    27          $name = null
    28      ) {
    29          parent::__construct($name);
    30          $this->exporter = $exporter;
```

## 5. `app/code/Dotdigitalgroup/Email/Console/Command/ImporterSyncsCommand.php:43` (extended)

[P-DI-04] Dotdigitalgroup\Email\Console\Command\ImporterSyncsCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $syncProvider, $resourceConnection. Configure a Proxy in di.xml for those that are costly to create.

```
    37  
    38      /**
    39       * @param SyncProvider $syncProvider
    40       * @param ResourceConnection $resourceConnection
    41       * @param State $state
    42       */
>   43      public function __construct(
    44          SyncProvider $syncProvider,
    45          ResourceConnection $resourceConnection,
    46          State $state
    47      ) {
    48          $this->syncProvider = $syncProvider;
    49          $this->resourceConnection = $resourceConnection;
```

## 6. `app/code/Yireo/NextGenImages/Console/Command/TestUriCommand.php:37` (extended)

[P-DI-04] Yireo\NextGenImages\Console\Command\TestUriCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $fileUtil. Configure a Proxy in di.xml for those that are costly to create.

```
    31       * TestUriCommand constructor.
    32       * @param FileUtil $fileUtil
    33       * @param TargetImageFactory $targetImageFactory
    34       * @param ImageFactory $imageFactory
    35       * @param string|null $name
    36       */
>   37      public function __construct(
    38          FileUtil $fileUtil,
    39          TargetImageFactory $targetImageFactory,
    40          ImageFactory $imageFactory,
    41          ?string $name = null
    42      ) {
    43          parent::__construct($name);
```

## 7. `app/code/Swissup/Marketplace/Console/Command/PackageDisableCommand.php:7` (extended)

[P-DI-04] Swissup\Marketplace\Console\Command\PackageDisableCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $composerHelper. Configure a Proxy in di.xml for those that are costly to create.

```
     1  <?php
     2  
     3  namespace Swissup\Marketplace\Console\Command;
     4  
     5  use Swissup\Marketplace\Model\Handler\PackageDisable;
     6  
>    7  class PackageDisableCommand extends PackageAbstractCommand
     8  {
     9      /**
    10       * @var string
    11       */
    12      protected $handlerClass = PackageDisable::class;
    13  
```

## 8. `app/code/Swissup/Core/Console/Command/Installer/ChannelDisableCommand.php:12` (extended)

[P-DI-04] Swissup\Core\Console\Command\Installer\ChannelDisableCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $repository. Configure a Proxy in di.xml for those that are costly to create.

```
     6  use Symfony\Component\Console\Command\Command;
     7  use Symfony\Component\Console\Input\InputInterface;
     8  use Symfony\Component\Console\Output\OutputInterface;
     9  
    10  class ChannelDisableCommand extends Command
    11  {
>   12      public function __construct(private ComposerRepository $repository)
    13      {
    14          parent::__construct();
    15      }
    16  
    17      protected function configure()
    18      {
```

## 9. `app/code/Reviewscouk/Reviews/Console/Command/GenerateFeedCommand.php:27` (extended)

[P-DI-04] Reviewscouk\Reviews\Console\Command\GenerateFeedCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $generator. Configure a Proxy in di.xml for those that are costly to create.

```
    21  class GenerateFeedCommand extends Command
    22  {
    23      private $generator;
    24      private $storeManager;
    25      private $appState;
    26  
>   27      public function __construct(
    28          Generator $generator,
    29          StoreManagerInterface $storeManager,
    30          State $appState
    31      ) {
    32          $this->generator = $generator;
    33          $this->storeManager = $storeManager;
```

## 10. `app/code/GhoSter/ChangeCustomerPassword/Command/CustomerChangePasswordCommand.php:58` (extended)

[P-DI-04] GhoSter\ChangeCustomerPassword\Command\CustomerChangePasswordCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $customerRegistry. Configure a Proxy in di.xml for those that are costly to create.

```
    52       *
    53       * @param StoreManagerInterface $storeManager
    54       * @param CustomerResource $resource
    55       * @param CustomerRegistry $customerRegistry
    56       * @param AppState $state
    57       */
>   58      public function __construct(
    59          StoreManagerInterface $storeManager,
    60          CustomerResource $resource,
    61          CustomerRegistry $customerRegistry,
    62          AppState $state
    63      ) {
    64          parent::__construct();
```

## 11. `app/code/CtiDigital/Configurator/Console/Command/RunCommand.php:19` (extended)

[P-DI-04] CtiDigital\Configurator\Console\Command\RunCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $processor. Configure a Proxy in di.xml for those that are costly to create.

```
    13  
    14  class RunCommand extends Command
    15  {
    16      /**
    17       * @param Processor $processor
    18       */
>   19      public function __construct(
    20          private readonly Processor $processor
    21      ) {
    22          parent::__construct();
    23      }
    24  
    25      /**
```

## 12. `app/code/Swissup/Core/Console/Command/StoreCreateCommand.php:47` (extended)

[P-DI-04] Swissup\Core\Console\Command\StoreCreateCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $filterManager. Configure a Proxy in di.xml for those that are costly to create.

```
    41      /**
    42       * @param \Magento\Store\Model\GroupFactory $groupFactory
    43       * @param \Magento\Store\Model\StoreFactory $storeFactory
    44       * @param \Magento\Framework\Filter\FilterManager $filterManager
    45       * @param null $name
    46       */
>   47      public function __construct(
    48          \Magento\Store\Model\GroupFactory $groupFactory,
    49          \Magento\Store\Model\StoreFactory $storeFactory,
    50          \Magento\Framework\Filter\FilterManager $filterManager,
    51          $name = null
    52      ) {
    53          $this->groupFactory = $groupFactory;
```

## 13. `app/code/Swissup/Marketplace/Console/Command/ComposerAuthPathCommand.php:19` (extended)

[P-DI-04] Swissup\Marketplace\Console\Command\ComposerAuthPathCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $composer. Configure a Proxy in di.xml for those that are costly to create.

```
    13       */
    14      private $composer;
    15  
    16      /**
    17       * @param \Swissup\Marketplace\Model\ComposerApplication $composer
    18       */
>   19      public function __construct(
    20          \Swissup\Marketplace\Model\ComposerApplication $composer
    21      ) {
    22          $this->composer = $composer;
    23          parent::__construct();
    24      }
    25  
```

## 14. `app/code/MSP/AdminRestriction/Command/RestrictIp.php:43` (extended)

[P-DI-04] MSP\AdminRestriction\Command\RestrictIp is created on every bin/magento run, whichever command is asked for, and so are its dependencies $config. Configure a Proxy in di.xml for those that are costly to create.

```
    37  
    38      /**
    39       * @var Manager
    40       */
    41      private $cacheManager;
    42  
>   43      public function __construct(
    44          ConfigInterface $config,
    45          Manager $cacheManager
    46      ) {
    47          parent::__construct();
    48          $this->config = $config;
    49          $this->cacheManager = $cacheManager;
```

## 15. `app/code/Baldwin/UrlDataIntegrityChecker/Console/Command/CheckProductUrlPaths.php:26` (extended)

[P-DI-04] Baldwin\UrlDataIntegrityChecker\Console\Command\CheckProductUrlPaths is created on every bin/magento run, whichever command is asked for, and so are its dependencies $resultOutput, $urlPathUpdater, $metaStorage. Configure a Proxy in di.xml for those that are costly to create.

```
    20  {
    21      private $appState;
    22      private $resultOutput;
    23      private $urlPathUpdater;
    24      private $metaStorage;
    25  
>   26      public function __construct(
    27          AppState $appState,
    28          ProductResultOutput $resultOutput,
    29          UrlPathUpdater $urlPathUpdater,
    30          MetaStorage $metaStorage
    31      ) {
    32          $this->appState = $appState;
```

## 16. `app/code/Swissup/Core/Console/Command/ModuleListCommand.php:37` (extended)

[P-DI-04] Swissup\Core\Console\Command\ModuleListCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $loader. Configure a Proxy in di.xml for those that are costly to create.

```
    31  
    32      /**
    33       * Inject dependencies
    34       *
    35       * @param \Swissup\Core\Model\ComponentList\Loader $loader
    36       */
>   37      public function __construct(Loader $loader)
    38      {
    39          $this->loader = $loader;
    40          parent::__construct();
    41      }
    42  
    43  
```

## 17. `app/code/Swissup/Core/Console/Command/Installer/PackageRemoveCommand.php:10` (extended)

[P-DI-04] Swissup\Core\Console\Command\Installer\PackageRemoveCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $composer, $repository, $process, $maintenanceMode, $cleanupFiles, $directoryList. Configure a Proxy in di.xml for those that are costly to create.

```
     4  use Magento\Framework\Console\Cli;
     5  use Symfony\Component\Console\Input\InputArgument;
     6  use Symfony\Component\Console\Input\InputInterface;
     7  use Symfony\Component\Console\Input\InputOption;
     8  use Symfony\Component\Console\Output\OutputInterface;
     9  
>   10  class PackageRemoveCommand extends PackageAbstractCommand
    11  {
    12      protected function configure()
    13      {
    14          $this->setName('swissup:package:remove')
    15              ->setDescription('Remove SwissupLabs package(s) using composer and run setup:upgrade')
    16              ->addArgument(
```

## 18. `app/code/Hyva/BaseLayoutReset/Console/Command/HyvaBaseLayoutResetGenerate.php:52` (extended)

[P-DI-04] Hyva\BaseLayoutReset\Console\Command\HyvaBaseLayoutResetGenerate is created on every bin/magento run, whichever command is asked for, and so are its dependencies $baseLayoutFilesCollector, $basePageLayoutFilesCollector, $layoutFileReset. Configure a Proxy in di.xml for those that are costly to create.

```
    46  
    47      /**
    48       * @var LayoutFileReset
    49       */
    50      private $layoutFileReset;
    51  
>   52      public function __construct(
    53          ViewFileCollector $baseLayoutFilesCollector,
    54          ViewFileCollector $basePageLayoutFilesCollector,
    55          Theme\DataFactory $themeFactory,
    56          LayoutFileReset $layoutFileReset
    57      ) {
    58          $this->baseLayoutFilesCollector = $baseLayoutFilesCollector;
```

## 19. `app/code/Swissup/Marketplace/Console/Command/PackageUpdateCommand.php:7` (extended)

[P-DI-04] Swissup\Marketplace\Console\Command\PackageUpdateCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $composerHelper. Configure a Proxy in di.xml for those that are costly to create.

```
     1  <?php
     2  
     3  namespace Swissup\Marketplace\Console\Command;
     4  
     5  use Swissup\Marketplace\Model\Handler\PackageUpdate;
     6  
>    7  class PackageUpdateCommand extends PackageAbstractCommand
     8  {
     9      /**
    10       * @var string
    11       */
    12      protected $handlerClass = PackageUpdate::class;
    13  
```

## 20. `app/code/GhostUnicorns/WebapiLogs/Console/Command/CleanCommand.php:27` (extended)

[P-DI-04] GhostUnicorns\WebapiLogs\Console\Command\CleanCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $clean. Configure a Proxy in di.xml for those that are costly to create.

```
    21      private $clean;
    22  
    23      /**
    24       * @param Clean $clean
    25       * @param string $name
    26       */
>   27      public function __construct(
    28          Clean $clean,
    29          string $name = 'webapilogs:clean'
    30      ) {
    31          $this->clean = $clean;
    32          parent::__construct($name);
    33      }
```

## 21. `app/code/Swissup/Core/Console/Command/Installer/AuthAddCommand.php:15` (extended)

[P-DI-04] Swissup\Core\Console\Command\Installer\AuthAddCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $repository. Configure a Proxy in di.xml for those that are costly to create.

```
     9  use Symfony\Component\Console\Output\OutputInterface;
    10  
    11  class AuthAddCommand extends Command
    12  {
    13      const INPUT_ARGUMENT_KEY = 'key';
    14  
>   15      public function __construct(private ComposerRepository $repository)
    16      {
    17          parent::__construct();
    18      }
    19  
    20      protected function configure()
    21      {
```

## 22. `app/code/Tweakwise/Magento2Tweakwise/Console/Command/RegenerateFilterUrlSlugs.php:31` (extended)

[P-DI-04] Tweakwise\Magento2Tweakwise\Console\Command\RegenerateFilterUrlSlugs is created on every bin/magento run, whichever command is asked for, and so are its dependencies $searchCriteriaBuilder, $attributeRepository, $filterSlugManager, $productAttributes. Configure a Proxy in di.xml for those that are costly to create.

```
    25       * @param SearchCriteriaBuilder $searchCriteriaBuilder
    26       * @param AttributeRepositoryInterface $attributeRepository
    27       * @param FilterSlugManager $filterSlugManager
    28       * @param StoreManagerInterface $storeManager
    29       * @param ProductAttributes $productAttributes
    30       */
>   31      public function __construct(
    32          private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
    33          private readonly AttributeRepositoryInterface $attributeRepository,
    34          private readonly FilterSlugManager $filterSlugManager,
    35          private readonly StoreManagerInterface $storeManager,
    36          private readonly ProductAttributes $productAttributes,
    37      ) {
```

## 23. `app/code/Baldwin/UrlDataIntegrityChecker/Console/Command/CheckCategoryUrlKeys.php:26` (extended)

[P-DI-04] Baldwin\UrlDataIntegrityChecker\Console\Command\CheckCategoryUrlKeys is created on every bin/magento run, whichever command is asked for, and so are its dependencies $resultOutput, $urlKeyUpdater, $metaStorage. Configure a Proxy in di.xml for those that are costly to create.

```
    20  {
    21      private $appState;
    22      private $resultOutput;
    23      private $urlKeyUpdater;
    24      private $metaStorage;
    25  
>   26      public function __construct(
    27          AppState $appState,
    28          CategoryResultOutput $resultOutput,
    29          UrlKeyUpdater $urlKeyUpdater,
    30          MetaStorage $metaStorage
    31      ) {
    32          $this->appState = $appState;
```

## 24. `app/code/Swissup/Marketplace/Console/Command/ChannelListCommand.php:20` (extended)

[P-DI-04] Swissup\Marketplace\Console\Command\ChannelListCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $channelRepository. Configure a Proxy in di.xml for those that are costly to create.

```
    14       */
    15      private $channelRepository;
    16  
    17      /**
    18       * @param \Swissup\Marketplace\Model\ChannelRepository $channelRepository
    19       */
>   20      public function __construct(
    21          \Swissup\Marketplace\Model\ChannelRepository $channelRepository
    22      ) {
    23          $this->channelRepository = $channelRepository;
    24          parent::__construct();
    25      }
    26  
```

## 25. `app/code/ADM/QuickDevBar/Console/Command/DisableToolBar.php:14` (extended)

[P-DI-04] ADM\QuickDevBar\Console\Command\DisableToolBar is created on every bin/magento run, whichever command is asked for, and so are its dependencies $resourceConfig, $eventManager, $writer, $arrayManager. Configure a Proxy in di.xml for those that are costly to create.

```
     8  use Magento\Catalog\Api\Data\ProductInterface;
     9  
    10  /**
    11   * Class ProductAttributesCleanUp
    12   * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
    13   */
>   14  class DisableToolBar extends AbstractStatusToolbar
    15  {
    16      /**
    17       * @var string
    18       */
    19      protected $name='dev:quickdevbar:disable';
    20  
```

## 26. `app/code/Swissup/Core/Console/Command/Installer/AuthCheckCommand.php:15` (extended)

[P-DI-04] Swissup\Core\Console\Command\Installer\AuthCheckCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $repository, $remote. Configure a Proxy in di.xml for those that are costly to create.

```
     9  use Symfony\Component\Console\Input\InputInterface;
    10  use Symfony\Component\Console\Output\OutputInterface;
    11  use Symfony\Component\Console\Question\ConfirmationQuestion;
    12  
    13  class AuthCheckCommand extends Command
    14  {
>   15      public function __construct(
    16          private ComposerRepository $repository,
    17          private Remote $remote
    18      ) {
    19          parent::__construct();
    20      }
    21  
```

## 27. `app/code/Swissup/Marketplace/Console/Command/ChannelAuthShowCommand.php:8` (extended)

[P-DI-04] Swissup\Marketplace\Console\Command\ChannelAuthShowCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $channelRepository, $channelManager, $composerHelper, $questionHelper. Configure a Proxy in di.xml for those that are costly to create.

```
     2  
     3  namespace Swissup\Marketplace\Console\Command;
     4  
     5  use Symfony\Component\Console\Input\InputInterface;
     6  use Symfony\Component\Console\Output\OutputInterface;
     7  
>    8  class ChannelAuthShowCommand extends ChannelAbstractCommand
     9  {
    10      /**
    11       * {@inheritdoc}
    12       */
    13      protected function configure(): void
    14      {
```

## 28. `app/code/Dotdigitalgroup/Email/Console/Command/TaskRunnerCommand.php:35` (extended)

[P-DI-04] Dotdigitalgroup\Email\Console\Command\TaskRunnerCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $taskProvider. Configure a Proxy in di.xml for those that are costly to create.

```
    29      private $state;
    30  
    31      /**
    32       * @param TaskProvider $taskProvider
    33       * @param State $state
    34       */
>   35      public function __construct(
    36          TaskProvider $taskProvider,
    37          State $state
    38      ) {
    39          $this->taskProvider = $taskProvider;
    40          $this->state = $state;
    41          parent::__construct();
```

## 29. `app/code/Swissup/Marketplace/Console/Command/ChannelDisableCommand.php:8` (extended)

[P-DI-04] Swissup\Marketplace\Console\Command\ChannelDisableCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $channelRepository, $channelManager, $composerHelper, $questionHelper. Configure a Proxy in di.xml for those that are costly to create.

```
     2  
     3  namespace Swissup\Marketplace\Console\Command;
     4  
     5  use Symfony\Component\Console\Input\InputInterface;
     6  use Symfony\Component\Console\Output\OutputInterface;
     7  
>    8  class ChannelDisableCommand extends ChannelAbstractCommand
     9  {
    10      /**
    11       * {@inheritdoc}
    12       */
    13      protected function configure(): void
    14      {
```

## 30. `app/code/Dotdigitalgroup/Email/Console/Command/ImportDataCommand.php:26` (extended)

[P-DI-04] Dotdigitalgroup\Email\Console\Command\ImportDataCommand is created on every bin/magento run, whichever command is asked for, and so are its dependencies $migrateData. Configure a Proxy in di.xml for those that are costly to create.

```
    20      private $migrateData;
    21  
    22      /**
    23       * ImportDataCommand constructor
    24       * @param DataMigrationHelper $migrateData
    25       */
>   26      public function __construct(
    27          DataMigrationHelper $migrateData
    28      ) {
    29          $this->migrateData = $migrateData;
    30          parent::__construct();
    31      }
    32  
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/ADM/QuickDevBar/Console/Command/EnableToolBar.php:15` | TP-low | The config resource model, event manager, deployment config Writer and ArrayManager only store their arguments, and the event manager exists on every run anyway. |  |  |
| 2 | `app/code/Swissup/Marketplace/Console/Command/ChannelAuthKeyAddCommand.php:9` | TP-low | ChannelRepository, ChannelManager, the Composer helper and QuestionHelper only store arguments; Process calls PhpExecutableFinder::find(), which returns PHP_BINARY at once under the CLI. |  |  |
| 3 | `app/code/Hyva/Theme/Console/Command/HyvaConfigGenerate.php:26` | TP-low | HyvaModulesConfig only stores a file driver. |  |  |
| 4 | `app/code/TIG/PostNL/Console/Config/Dump.php:24` | TP-low | ConfigExporter and the matrix rate repository behind it only store config, factories and a search criteria builder. |  |  |
| 5 | `app/code/Dotdigitalgroup/Email/Console/Command/ImporterSyncsCommand.php:43` | TP-low | SyncProvider holds only fourteen factories and ResourceConnection opens no connection in its constructor. |  |  |
| 6 | `app/code/Yireo/NextGenImages/Console/Command/TestUriCommand.php:37` | TP-low | Util\File and its Debugger and UrlConvertor only store arguments, plus one cached getDirectoryWrite(PUB) call. |  |  |
| 7 | `app/code/Swissup/Marketplace/Console/Command/PackageDisableCommand.php:7` | TP-low | The Composer helper only stores arguments, and its Process dependency resolves the PHP binary from PHP_BINARY under the CLI. |  |  |
| 8 | `app/code/Swissup/Core/Console/Command/Installer/ChannelDisableCommand.php:12` | TP-low | ComposerRepository only stores a composer.json finder, scope config, an async HTTP client and a Composer wrapper. |  |  |
| 9 | `app/code/Reviewscouk/Reviews/Console/Command/GenerateFeedCommand.php:27` | TP-low | The feed Generator stores a collection factory, the image helper, stock registry, emulation and config, none of which does work in its constructor. |  |  |
| 10 | `app/code/GhoSter/ChangeCustomerPassword/Command/CustomerChangePasswordCommand.php:58` | TP-low | CustomerRegistry only stores two factories and the store manager. |  |  |
| 11 | `app/code/CtiDigital/Configurator/Console/Command/RunCommand.php:19` | TP | Processor takes ComponentListInterface, whose di.xml argument builds 26 configurator components with repositories, EavSetup, import factories and resource models on every bin/magento run. |  |  |
| 12 | `app/code/Swissup/Core/Console/Command/StoreCreateCommand.php:47` | TP-low | FilterManager only stores the object manager and the filter factory config. |  |  |
| 13 | `app/code/Swissup/Marketplace/Console/Command/ComposerAuthPathCommand.php:19` | TP-low | ComposerApplication only stores a directory list, a composer.json finder and Filesystem. |  |  |
| 14 | `app/code/MSP/AdminRestriction/Command/RestrictIp.php:43` | TP-low | The config resource model only stores its connection resource; no connection is opened in the constructor. |  |  |
| 15 | `app/code/Baldwin/UrlDataIntegrityChecker/Console/Command/CheckProductUrlPaths.php:26` | TP-low | ProductResultOutput has no constructor, and the URL path updater, its checker and Meta storage only store factories, with DateTime already proxied in di.xml. |  |  |
| 16 | `app/code/Swissup/Core/Console/Command/ModuleListCommand.php:37` | TP-low | Loader only stores its local and remote loaders, whose constructors just store helpers and an HTTP client factory. |  |  |
| 17 | `app/code/Swissup/Core/Console/Command/Installer/PackageRemoveCommand.php:10` | TP-low | Composer, ComposerRepository, Process, MaintenanceMode, CleanupFiles and DirectoryList only store their arguments. |  |  |
| 18 | `app/code/Hyva/BaseLayoutReset/Console/Command/HyvaBaseLayoutResetGenerate.php:52` | TP-low | The two view file collectors and LayoutFileReset with its nine special-case resets only store arguments. |  |  |
| 19 | `app/code/Swissup/Marketplace/Console/Command/PackageUpdateCommand.php:7` | TP-low | The Composer helper only stores arguments, and its Process dependency resolves the PHP binary from PHP_BINARY under the CLI. |  |  |
| 20 | `app/code/GhostUnicorns/WebapiLogs/Console/Command/CleanCommand.php:27` | TP-low | Clean only stores its config, a Monolog logger whose handler opens no file until it writes, and a resource model. |  |  |
| 21 | `app/code/Swissup/Core/Console/Command/Installer/AuthAddCommand.php:15` | TP-low | ComposerRepository only stores a composer.json finder, scope config, an async HTTP client and a Composer wrapper. |  |  |
| 22 | `app/code/Tweakwise/Magento2Tweakwise/Console/Command/RegenerateFilterUrlSlugs.php:31` | unclear | SearchCriteriaBuilder, the attribute repository and FilterSlugManager only store arguments, but ProductAttributes comes from Tweakwise Magento2TweakwiseExport, which is not in the corpus, so its constructor cannot be checked. |  |  |
| 23 | `app/code/Baldwin/UrlDataIntegrityChecker/Console/Command/CheckCategoryUrlKeys.php:26` | TP-low | CategoryResultOutput has no constructor, and the URL key updater, its duplicate and empty checkers and Meta storage only store factories and helpers. |  |  |
| 24 | `app/code/Swissup/Marketplace/Console/Command/ChannelListCommand.php:20` | TP-low | ChannelRepository only stores one channel object, ChannelManager and a factory. |  |  |
| 25 | `app/code/ADM/QuickDevBar/Console/Command/DisableToolBar.php:14` | TP-low | The config resource model, event manager, deployment config Writer and ArrayManager only store their arguments, and the event manager exists on every run anyway. |  |  |
| 26 | `app/code/Swissup/Core/Console/Command/Installer/AuthCheckCommand.php:15` | TP-low | ComposerRepository and the Remote loader only store helpers, scope config and HTTP client factories. |  |  |
| 27 | `app/code/Swissup/Marketplace/Console/Command/ChannelAuthShowCommand.php:8` | TP-low | ChannelRepository, ChannelManager, the Composer helper and QuestionHelper only store arguments; Process calls PhpExecutableFinder::find(), which returns PHP_BINARY at once under the CLI. |  |  |
| 28 | `app/code/Dotdigitalgroup/Email/Console/Command/TaskRunnerCommand.php:35` | TP-low | TaskProvider holds only five factories. |  |  |
| 29 | `app/code/Swissup/Marketplace/Console/Command/ChannelDisableCommand.php:8` | TP-low | ChannelRepository, ChannelManager, the Composer helper and QuestionHelper only store arguments; Process calls PhpExecutableFinder::find(), which returns PHP_BINARY at once under the CLI. |  |  |
| 30 | `app/code/Dotdigitalgroup/Email/Console/Command/ImportDataCommand.php:26` | TP-low | DataMigrationHelper builds ten migration types whose constructors each make one default-scope config read, in-memory lookups once config is loaded. |  |  |
