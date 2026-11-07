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

    public function testModulesFromAppCodeAndComposerPackages(): void
    {
        // Acme_Core, Acme_Gift, Acme_Promo, Magento_Sales, Acme_SrcLayout (in src/); acme/library is not a module.
        self::assertSame(5, $this->index()->moduleCount);
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

    public function testBuildingWritesNothingIntoTheProject(): void
    {
        $root = realpath(__DIR__ . '/../../Fixtures/Project/indexed');
        self::assertIsString($root);
        $before = shell_exec('find ' . escapeshellarg($root) . ' -type f | sort');

        $this->index();

        self::assertSame($before, shell_exec('find ' . escapeshellarg($root) . ' -type f | sort'));
    }
}
