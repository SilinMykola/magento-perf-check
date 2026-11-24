<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Project;

use Silin\MagentoPerfCheck\Project\ListFieldResolvers;
use Silin\MagentoPerfCheck\Project\ProjectIndex;
use Silin\MagentoPerfCheck\Project\ProjectIndexBuilder;
use PHPUnit\Framework\TestCase;

final class ProjectIndexBuilderTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/mpr-index-test-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->cacheDir . '/*/*') ?: [] as $file) {
            unlink($file);
        }
        foreach (glob($this->cacheDir . '/*') ?: [] as $dir) {
            rmdir($dir);
        }
        @rmdir($this->cacheDir);
    }

    private function index(string $root = __DIR__ . '/../../Fixtures/Project/indexed'): ProjectIndex
    {
        $real = realpath($root);
        self::assertIsString($real);

        return (new ProjectIndexBuilder($this->cacheDir))->build($real);
    }

    public function testLayoutPlacementsKeepTheClassNamesAsWritten(): void
    {
        $index = $this->index();

        // PHPStan finds a class it has not read yet only by its written name, so the case must be kept.
        self::assertContains('Acme\\Placed\\Block\\CartChild', $index->classesOnCacheablePages());
        self::assertTrue($index->isOnlyOnUncacheablePages('\\ACME\\Placed\\Block\\CartOnly'));
        self::assertFalse($index->isOnlyOnUncacheablePages('Acme\\Placed\\Block\\Mixed'));

        // The cached index answers the same.
        self::assertContains('Acme\\Placed\\Block\\CartChild', $this->index()->classesOnCacheablePages());
    }

    public function testAccountPagesFromACopiedNavigationAreUncacheable(): void
    {
        $uncacheable = $this->index(__DIR__ . '/../../Fixtures/Rules/X-LAY-01/includes/project')->toArray()['uncacheableHandles'];

        // A page that defines the customer account navigation, and a page that includes a handle defining it that only
        // pages include, are account pages, with that handle; none of them has a cacheable="false" block.
        self::assertArrayHasKey('acmeroute_navpage_index', $uncacheable);
        self::assertArrayHasKey('acmeroute_rewardpage_index', $uncacheable);
        self::assertArrayHasKey('acme_reward_account', $uncacheable);
        // A handle that defines the navigation for a header dropdown that default.xml includes is on every page.
        self::assertArrayNotHasKey('acme_account_menu', $uncacheable);
    }

    public function testModulesFromAppCodeAndComposerPackages(): void
    {
        // Acme_Core, Acme_Gift, Acme_Promo, Magento_Sales, Acme_SrcLayout (in src/); acme/library is not a module.
        self::assertSame(5, $this->index()->moduleCount);
    }

    public function testComposerOneListWithoutInstallPaths(): void
    {
        $source = realpath(__DIR__ . '/../../Fixtures/Project/indexed');
        self::assertIsString($source);
        $copy = sys_get_temp_dir() . '/mpr-index-project-' . bin2hex(random_bytes(4));
        exec('cp -R ' . escapeshellarg($source) . ' ' . escapeshellarg($copy));
        // Composer 1 wrote a plain list and no install-path: packages lie in vendor/<name>.
        file_put_contents($copy . '/vendor/composer/installed.json', '[{"name": "magento/module-sales"}, {"name": "acme/library"}, {"name": "acme/src-module"}, {"name": "acme/not-installed"}]');

        try {
            $index = (new ProjectIndexBuilder($this->cacheDir))->build($copy);

            self::assertSame(5, $index->moduleCount);
            self::assertTrue($index->tableHasPrimaryKey('sales_order'));
        } finally {
            exec('rm -rf ' . escapeshellarg($copy));
        }
    }

    public function testResolversOfFieldsOnListTypes(): void
    {
        $index = $this->index();

        self::assertSame('ProductInterface.brand', $index->listFieldResolvedBy('Acme\Promo\Resolver\Brand'));
        self::assertSame('Shared.shared', $index->listFieldResolvedBy('Acme\Promo\Resolver\OnInterfaceOfListedType'));
        self::assertSame('Leaf.title', $index->listFieldResolvedBy('Acme\Promo\Resolver\OnRecursiveType'));
        self::assertSame('Inherited.value', $index->listFieldResolvedBy('Acme\Promo\Resolver\OnTypeOfInheritedField'));
        self::assertSame('ProductInterface.brand_virtual', $index->listFieldResolvedBy('Acme\Promo\Resolver\BaseOfVirtualBrand'));
        self::assertNull($index->listFieldResolvedBy('Acme\Promo\Resolver\OnTypeOfSibling'));
        self::assertNull($index->listFieldResolvedBy('Acme\Promo\Resolver\OnSingleType'));
    }

    public function testResolversSurviveTheCache(): void
    {
        $this->index();

        $cached = $this->index();

        self::assertSame('ProductInterface.brand', $cached->listFieldResolvedBy('Acme\Promo\Resolver\Brand'));
        self::assertContains('magento\framework\view\element\abstractblock', $cached->pluginTargetsOf('Acme\Render\OnBlock'));
    }

    public function testPluginTargetsLeaveOutAdminAndDisabledPlugins(): void
    {
        $index = $this->index();

        self::assertSame([], $index->pluginTargetsOf('Acme\Render\InAdmin'));
        self::assertSame([], $index->pluginTargetsOf('Acme\Render\Disabled'));
        self::assertSame([], $index->pluginTargetsOf('Acme\Render\DisabledByName'));
    }

    public function testConsoleCommandsAndCronJobs(): void
    {
        $index = $this->index();

        self::assertTrue($index->isConsoleCommand('Acme\Batch\Sync'));
        self::assertTrue($index->isConsoleCommand('\Acme\Batch\BaseOfVirtual'));
        self::assertTrue($index->isConsoleCommand('Acme\Batch\OnTheClass'));
        self::assertFalse($index->isConsoleCommand('Acme\Batch\AdminOnly'));
        self::assertFalse($index->isConsoleCommand('Acme\Batch\Unregistered'));
        self::assertTrue($index->isCronJob('Acme\Batch\NightlyJob'));
        self::assertTrue($index->isCronJob('Acme\Batch\BaseOfVirtualJob'));
        self::assertFalse($index->isCronJob('Acme\Batch\Sync'));

        $cached = $this->index();
        self::assertTrue($cached->isConsoleCommand('Acme\Batch\Sync'));
        self::assertTrue($cached->isCronJob('Acme\Batch\NightlyJob'));
    }

    public function testAListCountsForEverythingBelowIt(): void
    {
        $schema = tempnam(sys_get_temp_dir(), 'mpr-schema');
        self::assertIsString($schema);
        file_put_contents($schema, <<<'GRAPHQL'
            type Query { a: [A!]! single: S }
            type A { nested: B }
            type B { c: String @resolver(class: "OnNested") }
            type S { d: String @resolver(class: "OnSingle") }
            type Mutation { m: [M] }
            type M { e: String @resolver(class: "OnMutation") }
            GRAPHQL);

        try {
            self::assertSame(['onnested' => 'B.c', 'onmutation' => 'M.e'], ListFieldResolvers::fromSchemas([$schema]));
        } finally {
            unlink($schema);
        }
    }

    public function testASchemaThatDoesNotParseLeavesTheOthersIndexed(): void
    {
        $broken = tempnam(sys_get_temp_dir(), 'mpr-schema');
        self::assertIsString($broken);
        file_put_contents($broken, 'type {');
        $good = tempnam(sys_get_temp_dir(), 'mpr-schema');
        self::assertIsString($good);
        file_put_contents($good, 'type Query { a: [B] } type B { c: String @resolver(class: "D") }');

        try {
            self::assertSame(['d' => 'B.c'], ListFieldResolvers::fromSchemas([$broken, $good]));
        } finally {
            unlink($broken);
            unlink($good);
        }
    }

    public function testProxyConfiguredInAnotherModule(): void
    {
        $index = $this->index();

        self::assertTrue($index->hasProxyArgument('Acme\Promo\Block\Banner', 'customerSession'));
        self::assertFalse($index->hasProxyArgument('Acme\Promo\Block\Banner', 'title'));
    }

    public function testAreaSpecificProxy(): void
    {
        self::assertTrue($this->index()->hasProxyArgument('\Acme\Promo\Block\Header', 'customerSession'));
    }

    public function testProxyOnVirtualTypeCoversItsClass(): void
    {
        self::assertTrue($this->index()->hasProxyArgument('Acme\Promo\Model\Exporter', 'checkoutSession'));
    }

    public function testWebapiRoutesAndPreferences(): void
    {
        $index = $this->index();

        self::assertSame(['acme\pickup\api\pickupsearchinterface' => ['execute', 'count', 'removed']], $index->webapiMethods());
        $preferences = $index->webapiPreferences();
        self::assertSame(['acme\pickup\model\searchrequest'], $preferences['acme\pickup\api\data\searchrequestinterface']);
        self::assertSame(['acme\pickup\model\result'], $preferences['acme\pickup\api\data\resultinterface']);
        self::assertSame(['acme\pickup\model\area'], $preferences['acme\pickup\api\data\areainterface']);
        self::assertSame(['acme\pickup\model\customcontact'], $preferences['acme\pickup\model\contact']);
        self::assertArrayNotHasKey('acme\pickup\api\data\noteinterface', $preferences);

        self::assertSame($index->webapiPreferences(), $this->index()->webapiPreferences());
    }

    public function testPluginClassKnown(): void
    {
        $index = $this->index();

        self::assertTrue($index->isPluginClass('Acme\Promo\Plugin\CartPlugin'));
        self::assertFalse($index->isPluginClass('Acme\Promo\Block\Banner'));
    }

    public function testObserversMviewAndTables(): void
    {
        $index = $this->index();

        self::assertSame(
            [['class' => 'Acme\Promo\Observer\OrderPlaced', 'area' => 'global', 'module' => 'Acme_Promo']],
            $index->observersOf('sales_order_place_after'),
        );
        self::assertTrue($index->hasMviewView('acme_promo_rule'));
        self::assertTrue($index->tableHasPrimaryKey('sales_order'));
        self::assertFalse($index->tableHasPrimaryKey('acme_gift_log'));
    }

    public function testUniqueAndPrimaryKeysFromSchema(): void
    {
        foreach ([false, true] as $cached) {
            $index = $this->index(__DIR__ . '/../../PhpStan/Fixtures/unique-keys');

            self::assertSame([['id'], ['label_id', 'order_id']], $index->uniqueKeysOf('acme_tag_order'), $cached ? 'cached' : 'built');
            // Acme_Notes widens one constraint by its referenceId and disables the other two, with "true" and "1".
            self::assertSame([['code', 'store_id']], $index->uniqueKeysOf('acme_tag_note'));
            self::assertSame([], $index->uniqueKeysOf('acme_tag_stock'));
            // Acme_Notes disables the whole table.
            self::assertSame([], $index->uniqueKeysOf('acme_tag_legacy'));
            // A declared table owns its keys: setup scripts do not add any to it.
            self::assertTrue($index->declaresTable('acme_tag_order'));
            self::assertTrue($index->declaresTable('acme_tag_legacy'));
            self::assertFalse($index->declaresTable('acme_tag_stock'));
            // Table names are case-insensitive, as MySQL on macOS and Windows treats them, and rules look them up in lower case.
            self::assertSame([['code']], $index->uniqueKeysOf('acme_tag_case'));
        }
        self::assertSame([['entity_id']], $this->index()->uniqueKeysOf('sales_order'));
    }

    public function testProjectWithoutModulesIsEmpty(): void
    {
        self::assertTrue($this->index(__DIR__)->isEmpty());
    }

    public function testCacheRebuiltWhenDiXmlChanges(): void
    {
        $source = realpath(__DIR__ . '/../../Fixtures/Project/indexed');
        self::assertIsString($source);
        $copy = sys_get_temp_dir() . '/mpr-index-project-' . bin2hex(random_bytes(4));
        exec('cp -R ' . escapeshellarg($source) . ' ' . escapeshellarg($copy));
        $builder = new ProjectIndexBuilder($this->cacheDir);

        self::assertTrue($builder->build($copy)->hasProxyArgument('Acme\Promo\Block\Banner', 'customerSession'));

        $diXml = $copy . '/app/code/Acme/Core/etc/di.xml';
        file_put_contents($diXml, str_replace('Session\Proxy', 'Session', (string) file_get_contents($diXml)));
        touch($diXml, time() + 5);

        self::assertFalse($builder->build($copy)->hasProxyArgument('Acme\Promo\Block\Banner', 'customerSession'));
        exec('rm -rf ' . escapeshellarg($copy));
    }

    public function testFingerprintFollowsStylesheets(): void
    {
        $source = realpath(__DIR__ . '/../../Fixtures/Project/indexed');
        self::assertIsString($source);
        $copy = sys_get_temp_dir() . '/mpr-index-project-' . bin2hex(random_bytes(4));
        exec('cp -R ' . escapeshellarg($source) . ' ' . escapeshellarg($copy));
        $builder = new ProjectIndexBuilder($this->cacheDir);
        $before = $builder->fingerprint($copy);

        // T-06 reads the module's stylesheets: a changed one makes cached PHP results out of date.
        $less = $copy . '/app/code/Acme/Promo/view/frontend/web/css/source/_module.less';
        mkdir(dirname($less), 0777, true);
        file_put_contents($less, ".banner img { width: 10px; height: 10px; }\n");
        $added = $builder->fingerprint($copy);
        self::assertNotSame($before, $added);

        file_put_contents($less, ".banner img { width: 20px; height: 10px; }\n");
        touch($less, time() + 5);
        self::assertNotSame($added, $builder->fingerprint($copy));
        exec('rm -rf ' . escapeshellarg($copy));
    }

    public function testFingerprintFollowsSetupScripts(): void
    {
        $source = realpath(__DIR__ . '/../../PhpStan/Fixtures/unique-keys');
        self::assertIsString($source);
        $copy = sys_get_temp_dir() . '/mpr-index-project-' . bin2hex(random_bytes(4));
        exec('cp -R ' . escapeshellarg($source) . ' ' . escapeshellarg($copy));
        $builder = new ProjectIndexBuilder($this->cacheDir);
        $before = $builder->fingerprint($copy);

        // P-COL-02 reads unique keys from setup scripts: a new or changed one makes cached PHP results out of date.
        $install = $copy . '/app/code/Acme/Tags/Setup/InstallSchema.php';
        file_put_contents($install, "<?php\n");
        $added = $builder->fingerprint($copy);
        self::assertNotSame($before, $added);

        file_put_contents($copy . '/app/code/Acme/Tags/Setup/UpgradeSchema.php', "<?php\n// changed\n", FILE_APPEND);
        self::assertNotSame($added, $builder->fingerprint($copy));
        exec('rm -rf ' . escapeshellarg($copy));
    }

    public function testDisabledModuleLeftOutAndCacheFollowsConfig(): void
    {
        $source = realpath(__DIR__ . '/../../Fixtures/Project/indexed');
        self::assertIsString($source);
        $copy = sys_get_temp_dir() . '/mpr-index-project-' . bin2hex(random_bytes(4));
        exec('cp -R ' . escapeshellarg($source) . ' ' . escapeshellarg($copy));
        $builder = new ProjectIndexBuilder($this->cacheDir);
        $config = $copy . '/app/etc/config.php';

        // Acme_Core configures the Proxy; Acme_Gift is missing from the list and stays enabled.
        file_put_contents($config, "<?php\nreturn [\n    'modules' => [\n        'Magento_Store' => 1, // kept (see notes)\n        // 'Acme_Promo' => 0,\n        'Acme_Core' => false,\n        'Acme_Promo' => 1,\n    ],\n    'Acme_Gift' => 0,\n];\n");
        $index = $builder->build($copy);
        self::assertFalse($index->hasProxyArgument('Acme\Promo\Block\Banner', 'customerSession'));
        self::assertSame(4, $index->moduleCount);
        self::assertSame([$copy . '/app/code/Acme/Core' => 'Acme_Core'], $builder->disabledModuleRoots($copy));

        file_put_contents($config, "<?php\nreturn ['modules' => ['Acme_Core' => 1, 'Acme_Promo' => 1]];\n");
        touch($config, time() + 5);
        self::assertTrue($builder->build($copy)->hasProxyArgument('Acme\Promo\Block\Banner', 'customerSession'));
        self::assertSame([], $builder->disabledModuleRoots($copy));

        // A module with no indexed file changes no file list, only the module count.
        mkdir($copy . '/app/code/Acme/Bare/etc', 0777, true);
        file_put_contents($copy . '/app/code/Acme/Bare/etc/module.xml', '<?xml version="1.0"?><config><module name="Acme_Bare"/></config>');
        self::assertSame(6, $builder->build($copy)->moduleCount);
        file_put_contents($config, "<?php\nreturn ['modules' => ['Acme_Bare' => 0, 'Acme_Core' => 1]];\n");
        touch($config, time() + 10);
        self::assertSame(5, $builder->build($copy)->moduleCount);
        exec('rm -rf ' . escapeshellarg($copy));
    }

    public function testBuildingWritesNothingIntoTheProject(): void
    {
        $root = realpath(__DIR__ . '/../../Fixtures/Project/indexed');
        self::assertIsString($root);
        $before = shell_exec('find ' . escapeshellarg($root) . ' -type f | sort');

        $this->index();

        self::assertSame($before, shell_exec('find ' . escapeshellarg($root) . ' -type f | sort'));
    }
}
