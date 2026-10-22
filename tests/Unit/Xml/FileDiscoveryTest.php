<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Xml;

use MagentoPerfRules\Xml\FileDiscovery;
use MagentoPerfRules\Xml\XmlFile;
use PHPUnit\Framework\TestCase;

final class FileDiscoveryTest extends TestCase
{
    private const PROJECT = __DIR__ . '/../../Fixtures/Xml/project';

    /**
     * @param list<string> $paths
     * @return array<string, XmlFile>
     */
    private function discover(array $paths): array
    {
        $root = realpath(self::PROJECT);
        self::assertIsString($root);
        $byPath = [];
        foreach ((new FileDiscovery($root))->discover($paths) as $file) {
            $byPath[$file->relativePath] = $file;
        }

        return $byPath;
    }

    public function testModuleLayoutFile(): void
    {
        $file = $this->discover(['app/code'])['app/code/Acme/Promo/view/frontend/layout/default.xml'];

        self::assertSame(XmlFile::KIND_LAYOUT, $file->kind);
        self::assertSame('frontend', $file->area);
        self::assertSame('Acme_Promo', $file->module);
        self::assertSame('default', $file->handle);
    }

    public function testThemeLayoutOverride(): void
    {
        $file = $this->discover(['app/design'])['app/design/frontend/Acme/shop/Magento_Catalog/layout/catalog_product_view.xml'];

        self::assertSame(XmlFile::KIND_LAYOUT, $file->kind);
        self::assertSame('frontend', $file->area);
        self::assertSame('Magento_Catalog', $file->module);
        self::assertSame('catalog_product_view', $file->handle);
    }

    public function testThemePackageAreaComesFromRegistration(): void
    {
        $file = $this->discover(['vendor/acme'])['vendor/acme/theme-frontend-shop/Magento_Theme/layout/default.xml'];

        self::assertSame('frontend', $file->area);
        self::assertSame('Magento_Theme', $file->module);
    }

    public function testUnrelatedXmlIsIgnored(): void
    {
        $files = $this->discover(['app/code']);

        self::assertArrayNotHasKey('app/code/Acme/Promo/view/adminhtml/ui_component/acme_promo_listing.xml', $files);
    }

    public function testGlobalConfigArea(): void
    {
        $file = $this->discover(['app/code'])['app/code/Acme/Promo/etc/events.xml'];

        self::assertSame(XmlFile::KIND_CONFIG, $file->kind);
        self::assertSame('global', $file->area);
        self::assertSame('Acme_Promo', $file->module);
        self::assertNull($file->handle);
    }

    public function testAreaSpecificConfig(): void
    {
        $file = $this->discover(['app/code'])['app/code/Acme/Promo/etc/frontend/di.xml'];

        self::assertSame('frontend', $file->area);
        self::assertSame('Acme_Promo', $file->module);
    }

    public function testAdminLayoutArea(): void
    {
        $file = $this->discover(['app/code'])['app/code/Acme/Promo/view/adminhtml/layout/sales_order_view.xml'];

        self::assertSame('adminhtml', $file->area);
        self::assertSame('sales_order_view', $file->handle);
    }

    public function testFilesOutsideModulesAreIgnored(): void
    {
        self::assertArrayNotHasKey('app/etc/di.xml', $this->discover(['app']));
    }

    public function testCoreVendorSkippedUnlessExplicit(): void
    {
        $files = $this->discover(['vendor']);

        self::assertArrayHasKey('vendor/acme/module-blog/view/frontend/layout/blog_post_view.xml', $files);
        self::assertArrayNotHasKey('vendor/magento/module-cms/view/frontend/layout/cms_index_index.xml', $files);
    }

    public function testCoreVendorAnalyzedWhenExplicit(): void
    {
        $file = $this->discover(['vendor/magento'])['vendor/magento/module-cms/view/frontend/layout/cms_index_index.xml'];

        self::assertSame('Magento_Cms', $file->module);
    }

    public function testDefaultPathsDoNotReachVendor(): void
    {
        foreach (array_keys($this->discover(['app/code', 'app/design'])) as $path) {
            self::assertStringStartsWith('app/', $path);
        }
    }

    public function testGraphQlSchema(): void
    {
        $file = $this->discover(['app/code'])['app/code/Acme/Promo/etc/schema.graphqls'];

        self::assertSame(XmlFile::KIND_GRAPHQL, $file->kind);
        self::assertSame('graphql', $file->area);
        self::assertSame('Acme_Promo', $file->module);
    }

    public function testFrontendSections(): void
    {
        $file = $this->discover(['app/code'])['app/code/Acme/Promo/etc/frontend/sections.xml'];

        self::assertSame(XmlFile::KIND_CONFIG, $file->kind);
        self::assertSame('frontend', $file->area);
    }
}
