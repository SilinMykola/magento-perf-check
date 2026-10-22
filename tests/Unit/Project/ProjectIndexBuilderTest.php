<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Project;

use MagentoPerfRules\Project\ProjectIndex;
use MagentoPerfRules\Project\ProjectIndexBuilder;
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
        // Acme_Core, Acme_Gift, Acme_Promo, Magento_Sales; acme/library is not a module.
        self::assertSame(4, $this->index()->moduleCount);
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
